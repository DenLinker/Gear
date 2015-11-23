<?php

namespace gear\library;
use gear\interfaces\IBehavior;

/** 
 * Класс описывающий поведение
 * 
 * @package Gear Framework
 * @abstract
 * @author Kukushkin Denis
 * @copyright Kukushkin Denis
 * @version 1.0.0
 * @since 03.08.2013
 * @php 5.4.x or higher
 * @release 1.0.0
 */
abstract class GBehavior implements IBehavior
{
    /* Const */
    /* Private */
    /* Protected */
    protected $_owner = null;
    /* Public */

    /**
     * Конструктор поведения
     *
     * @access protected
     * @param object $owner
     * @return GBehavior
     */
    protected function __construct($owner) { $this->_owner = $owner; }

    /**
     * Код поведения
     *
     * @abstract
     * @access public
     * @return mixed
     */
    abstract public function __invoke();
    
    /**
     * Установка значения для указанного свойства владельца
     * 
     * @access public
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value) { $this->_owner->$name = $value; }
    
    /**
     * Получение значения свойства владельца
     * 
     * @access public
     * @param string $name
     * @return mixed
     */
    public function __get($name) { return $this->_owner->$name; }

    /**
     * Возвращает true если $name является:
     * - событием, для которого имеются обработчики
     * - зарегестрированным компонентом модуля
     * - поведением
     * - плагином
     * - свойством объекта
     * иначе возвращает false
     *
     * @access public
     * @param string $name
     * @return mixed
     */
    public function __isset($name) { return isset($this->_owner->$name); }

    /**
     * Метод производит удаление:
     * - обработчиков события, если $name таковым является
     * - деинсталлирует компонент модуля
     * - отключает поведение, если $name является названием поведения
     * - деинсталлирует плагин
     * - удаляет свойство объекта, если таковое имеется
     *
     * @access public
     * @param string $name
     * @return void
     */
    public function __unset($name) { unset($this->_owner->$name); }

    /**
     * Перевод вызова несуществующей функции на владельца поведения
     * 
     * @access public
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call($name, $args) { return call_user_func_array([$this->_owner, $name], $args); }
    
    /**
     * Метод, который выполняется во время подключения поведения.
     * 
     * @access public
     * @static
     * @param GObject $owner
     * @return GBehavior
     */
    public static function attach($owner) { return new static($owner); }

    /**
     * Возвращает владельца поведения
     *
     * @access public
     * @return object
     */
    public function getOwner() { return $this->_owner; }
}