<?php

namespace Gear\Library;

use Gear\Core;
use Gear\Interfaces\IModule;
use Gear\Traits\TComponentContained;

/**
 * Класс приложений
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 * @since 0.0.1
 * @version 0.0.1
 */
class GApplication extends GModule implements IModule
{
    /* Traits */
    /* Const */
    /* Private */
    /* Protected */
    protected static $_isInitialized = false;
    protected static $_config = [
        'components' => [
            'router' => [
                'class' => '\Gear\Components\Router\GRouterComponent',
            ],
        ],
        'plugins' => [
            'request' => ['class' => '\Gear\Plugins\Http\GRequest'],
            'response' => ['class' => '\Gear\Plugins\Http\GResponse'],
        ],
    ];
    /* Public */

    /**
     * Запускается перед завершении работы приложения
     *
     * @param mixed $result
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function afterRun($result)
    {
        return Core::onAfterRunApplication(new \GEvent($this));
    }

    /**
     * Запускается перед началом работы приложения
     *
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function beforeRun()
    {
        return Core::onBeforeRunApplication(new \GEvent($this));
    }

    /**
     * Завершение работы приложения
     *
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    final public function end($result)
    {
        $this->afterRun($result);
        $this->response->send($result);
        exit(0);
    }

    /**
     * Запуск приложения
     *
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    final public function run()
    {
        if ($this->beforeRun()) {
            $result = $this->router->exec($this->request, $this->response);
            $this->end($result);
        }
    }
}