<?php

namespace Gear\Modules\Users\Plugins;

use Gear\Core;
use Gear\Interfaces\IModel;
use Gear\Library\Db\GDbStoragePlugin;
use Gear\Library\GEvent;
use Gear\Modules\Users\Interfaces\ISession;
use Gear\Modules\Users\Interfaces\IUser;
use Gear\Modules\Users\Interfaces\IUserIdentityPlugin;

/**
 * Плагин идентификации пользователя по его PHP-сессии
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 *
 * @property null|int cookieLifeTime
 * @property int maxSessionsByUser
 * @property null|ISession session
 * @property null|int sessionLifeTime
 * @property string sessionName
 *
 * @since 0.0.1
 * @version 0.0.1
 */
class GSessionUserIdentity extends GDbStoragePlugin implements IUserIdentityPlugin
{
    /* Traits */
    /* Const */
    /* Private */
    /* Protected */
    protected static $_initialized = false;
    protected $_collectionName = 'sessions';
    protected $_connectionName = 'db';
    protected $_cookieLifeTime = 60*24*7; // 7 дней
    protected $_dbName = 'simple';
    protected $_factoryProperties = [
        'class' => '\Gear\Modules\Users\Models\GSession',
        'maxSessionsByUser' => -1,
    ];
    protected $_maxSessionsByUser = -1;
    protected $_session = null;
    protected $_sessionLifeTime = 900;
    protected $_sessionName = '_user_session_';
    /* Public */

    /**
     * Вызывается после утсновки плагина и выполняет установку обработчиков событий владельца
     *
     * @return mixed
     * @throws \ObjectException
     * @since 0.0.1
     * @version 0.0.1
     */
    public function afterInstallService()
    {
        $this->on('onUserIdentity', [$this, 'handlerAfterUserIdentity']);
        $this->on('onUserLogin', [$this, 'handlerUserLogin']);
        $this->on('onUserLogout', [$this, 'handlerUserLogout']);
        return parent::afterInstallService();
    }

    /**
     * Установка времени жизни сессии в куках
     *
     * @return int
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCookieLifeTime(): int
    {
        return $this->_cookieLifeTime;
    }

    /**
     * Генерирует новый хэш для сессии
     *
     * @return string
     * @throws \Exception
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getHash(): string
    {
        $hash = password_hash((string)(time() + microtime(true)) . random_bytes(128) , PASSWORD_BCRYPT, ['cost' => 12]);
        return str_replace('$', '+', $hash);
    }

    /**
     * Возвращает максимальное количество одновременнызх сессий для одного
     * пользователя
     *
     * @return int
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getMaxSessionsByUser(): int
    {
        return $this->_maxSessionsByUser;
    }

    /**
     * Возврашщает текущую сессию или NULL, если таковой не существует
     *
     * @return ISession|null
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getSession(): ?Isession
    {
        return $this->_session;
    }

    /**
     * Установка времени жизни сессии
     *
     * @return int
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getSessionLifeTime(): int
    {
        return $this->_sessionLifeTime;
    }

    /**
     * Возвращает название, под которым сессия будет храниться в
     * глобальных массивах $_SESSION и $_COOKIE
     *
     * @return string
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getSessionName(): string
    {
        return $this->_sessionName;
    }

    /**
     * Обработчик события onAfterUserIdentity, возникающего после удачной
     * идентификации и загрузки пользователя из базы данных
     *
     * @param GEvent $event
     * @return bool
     * @since 0.0.1
     * @version 0.0.1
     */
    public function handlerAfterUserIdentity(GEvent $event)
    {
        $this->updateSession($event->user);
        return true;
    }

    /**
     * Обработчик события onUserLogin, возникающего после удачной
     * аутентификации пользователя
     *
     * @param GEvent $event
     * @return bool
     * @throws \CoreException
     * @since 0.0.1
     * @version 0.0.1
     */
    public function handlerUserLogin(GEvent $event)
    {
        $this->startNewSession($event->user);
        return true;
    }

    /**
     * Обработчик события onUserLogout, возникающего после как пользователь
     * вышел
     *
     * @param GEvent $event
     * @return bool
     * @since 0.0.1
     * @version 0.0.1
     */
    public function handlerUserLogout(GEvent $event)
    {
        $this->removeSession($event->user->session);
        $event->user->session = null;
        return true;
    }

    /**
     * Идентификация пользователя
     *
     * @return array|null
     * @since 0.0.1
     * @version 0.0.1
     */
    public function identity(): ?array
    {
        $hash = null;
        $criteria = null;
        if (isset($_COOKIE[$this->sessionName])) {
            $hash = $_COOKIE[$this->sessionName];
        } elseif (isset($_SESSION[$this->sessionName])) {
            $hash = $_SESSION[$this->sessionName];
        }
        if ($hash) {
            $session = $this->loadSession(['hash' => $hash]);
            if ($session && $this->isValid($session)) {
                $criteria = ['id' => $session->user];
            }
        }
        return $criteria;
    }

    /**
     * Возвращает true, если сессия не устарела
     *
     * @param ISession $session
     * @return bool
     * @since 0.0.1
     * @version 0.0.1
     */
    public function isValid(ISession $session): bool
    {
        return !(time() - strtotime($session->lastTime) > $this->sessionLifeTime);
    }

    /**
     * Возвращает количество одновременных сессий одного пользователя
     *
     * @param mixed $user
     * @return int
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCountSessionsByUser($user): int
    {
        $count = $this->find(['user' => $user])->count();
        return $count ? $count : 0;
    }

    /**
     * Загружет сессию из базы данных по указанному критерию
     *
     * @param array $criteria
     * @return ISession|null
     * @since 0.0.1
     * @version 0.0.1
     */
    public function loadSession(array $criteria): ?ISession
    {
        return $this->findOne($criteria);
    }

    /**
     * Удаление сессии
     *
     * @param ISession $session
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function removeSession(ISession $session)
    {
        $this->remove($session);
        $this->session = null;
        unset($_SESSION[$this->sessionName], $_COOKIE[$this->sessionName]);
    }

    /**
     * Установка времени жизни кук
     *
     * @param int $lifeTime
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setCookieLifeTime(int $lifeTime)
    {
        $this->_cookieLifeTime = $lifeTime;
    }

    /**
     * Устанавливает максимальное количество одновременнызх сессий для одного
     * пользователя
     *
     * @param int $maxSessionsByUser
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setMaxSessionsByUser(int $maxSessionsByUser)
    {
        $this->_maxSessionsByUser = $maxSessionsByUser;
    }

    /**
     * Установка текущей сессии
     *
     * @param null|ISession $session
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setSession(?ISession $session)
    {
        $this->_session = $session;
    }

    /**
     * Установка времени жизни сессии
     *
     * @param int $lifeTime
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setSessionLifeTime(int $lifeTime)
    {
        $this->_sessionLifeTime = $lifeTime;
    }

    /**
     * Установка названия, под которым сессия будет храниться в
     * глобальных массивах $_SESSION и $_COOKIE
     *
     * @param string $sessionName
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setSessionName(string $sessionName)
    {
        $this->_sessionName = $sessionName;
    }

    /**
     * Запуск новой сессии для пользователя
     *
     * @param IUser $user
     * @return ISession
     * @throws \CoreException
     * @since 0.0.1
     * @version 0.0.1
     */
    public function startNewSession(IUser $user): ISession
    {
        /**
         * @var IModel $user
         * @var ISession $session
         */
        if ($user->session) {
            $this->removeSession($session);
            $user->session = null;
        }
        $session = $this->factory([
            'hash' => $this->hash,
            'user' => $user->getPrimaryKey(),
            'ip' => Core::app()->request->getRemoteAddress(),
            'lastTime' => date('Y-m-d H:i:s')
        ], $this);
        $this->save($session);
        $this->session = $user->session = $session;
        return $session;
    }

    /**
     * Обновление текущей сессии
     *
     * @param IUser $user
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function updateSession(IUser $user)
    {
        $user->session->props(['hash' => $this->hash, 'lastTime' => date('Y-m-d H:i:s')]);
        $this->update($user->session);
        $this->session = $user->session;
        $_SESSION[$this->sessionName] = $this->session->props();
        setcookie($this->sessionName, $this->session->hash, time() + $this->cookieLifeTime);
    }
}
