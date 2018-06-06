<?php

namespace Gear\Library\Db;

use Gear\Library\GComponent;
use Gear\Library\GEvent;
use Gear\Traits\TDelegateFactory;
use Gear\Traits\TFactory;

/**
 * Компонент подключения к базе данных
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 * @since 0.0.1
 * @version 0.0.1
 */
abstract class GDbConnection extends GComponent implements \IteratorAggregate
{
    /* Traits */
    use TFactory;
    use TDelegateFactory;
    /* Const */
    /* Private */
    /* Protected */
    protected static $_initialized = false;
    protected static $_defaultProperties = [
        'host' => 'localhost',
        'user' => '',
        'password' => '',
        'port' => '',
    ];
    protected $_cursorFactory = [
        'class' => '\Gear\Library\Db\GDbCursor',
    ];
    protected $_handler = null;
    protected $_items = [];
    protected $_current = null;
    /* Public */

    /**
     * Генерация события onAfterConnect после подключения к серверу
     *
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function afterConnect()
    {
        return $this->trigger('onAfterConnect', new GEvent($this, ['target' => $this]));
    }

    /**
     * Генерация события onBeforeConnect до подключения к серверу
     *
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function beforeConnect()
    {
        return $this->trigger('onBeforeConnect', new GEvent($this, ['target' => $this]));
    }

    /**
     * Завершение соединения с сервером баз данных
     *
     * @abstract
     * @return GDbConnection
     * @since 0.0.1
     * @version 0.0.1
     */
    abstract public function close(): GDbConnection;

    /**
     * Подготовка и вызов метода непосредственного подключения к серверу баз данных
     *
     * @return GDbConnection
     * @since 0.0.1
     * @version 0.0.1
     */
    public function connect(): GDbConnection
    {
        if ($this->beforeConnect()) {
            $this->open();
            $this->afterConnect();
        }
        return $this;
    }

    /**
     * Возвращает текущую выбранную базу данных
     *
     * @return null|GDbDatabase
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCurrent(): ?GDbDatabase
    {
        return $this->_current;
    }

    /**
     * Возвращает курсор
     *
     * @return GDbCursor
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCursor(): GDbCursor
    {
        return $this->factory($this->_cursorFactory, $this);
    }

    /**
     * Возвращает ресурс подключения к базе данных
     *
     * @return null|resource
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getHandler()
    {
        return $this->_handler;
    }

    /**
     * Возвращает true если соединение уже установлено, иначе false
     *
     * @return bool
     * @since 0.0.1
     * @version 0.0.1
     */
    public function isConnected(): bool
    {
        return $this->_handler ? true : false;
    }

    /**
     * Подключение к серверу базы данных
     *
     * @return GDbConnection
     * @since 0.0.1
     * @version 0.0.1
     */
    abstract public function open(): GDbConnection;

    /**
     * Выполняет подключение к серверу, если соединение ещё не было
     * установлено
     *
     * @return $this
     * @since 0.0.1
     * @version 0.0.1
     */
    public function reconnect()
    {
        if (!$this->isConnected()) {
            $this->connect();
        }
        return $this;
    }

    /**
     * Выбор указанной базы данных и таблицы
     *
     * @param string $dbName
     * @param string $collectionName
     * @param string $alias
     * @return GDbCollection
     * @since 0.0.1
     * @version 0.0.1
     */
    public function selectCollection(string $dbName, string $collectionName, string $alias = ''): GDbCollection
    {
        return $this->selectDB($dbName)->selectCollection($collectionName, $alias);
    }

    /**
     * Выбор указанной базы данных
     *
     * @param string $name
     * @return GDbDatabase
     * @since 0.0.1
     * @version 0.0.1
     */
    public function selectDB(string $name): GDbDatabase
    {
        if (isset($this->_items[$name])) {
            if (!$this->current || $this->current->name !== $name) {
                $this->current = $this->_items[$name];
                $this->current->select();
            }
        } else {
            $db = $this->factory(['name' => $name], $this);
            $this->current = $this->_items[$name] = $db;
            $this->current->select();
        }
        return $this->_current;
    }

    /**
     * Установка текущей базы данных
     *
     * @param GDbDatabase $current
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setCurrent(GDbDatabase $current)
    {
        $this->_current = $current;
    }

    /**
     * Устанавливает ресурс подключения к базе данных
     *
     * @param mixed $handler
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setHandler($handler)
    {
        $this->_handler = $handler;
    }
}
