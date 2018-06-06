<?php

namespace Gear\Interfaces;

/**
 * Интерфейс контроллеров
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 * @since 0.0.1
 * @version 0.0.1
 */
interface IController
{
    /**
     * Вызов метода $this->exec()
     *
     * @param IRequest $request
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function __invoke(IRequest $request);

    /**
     * Запуск контроллера
     *
     * @param IRequest $request
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function exec(IRequest $request);
}

/**
 * Интерфейс api-методов
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 * @since 0.0.1
 * @version 0.0.1
 */
interface IApi
{
    /**
     * Вызов метода $this->exec();
     *
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function __invoke();

    /**
     * Вызов api-метода
     *
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function exec();
}
