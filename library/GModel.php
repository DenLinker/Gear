<?php

namespace gear\library;

use \gear\Core;
use \gear\library\GObject;
use \gear\library\GException;

/**
 * Базовый класс моделей
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright Kukushkin Denis 2013
 * @version 1.0.0
 * @since 01.08.2013
 */
class GModel extends GObject
{
    /* Const */
    /* Private */
    /* Protected */
    protected $_pk = 'id';
    /* Public */
    
    /**
     * Конструктор класса
     * Принимает ассоциативный массив свойств объекта и их значений
     *
     * @access public
     * @param array $properties
     * @return void
     */
    public function __construct(array $properties = array(), $owner = null) { parent::__construct($properties, $owner); }

    /**
     * Клонирование
     *
     * @access public
     * @return void
     */
    public function __clone() { parent::__clone(); }

    /**
     * Установка названия поля являющимся PRIMARY LEY
     *
     * @access public
     * @param string $pk
     * @return $this
     */
    public function setPk($pk)
    {
        $this->_pk = $pk;
        return $this;
    }

    /**
     * Получение названия поля являющимся PRIMARY LEY
     *
     * @access public
     * @return string
     */
    public function getPk() { return $this->_pk; }
}

/**
 * Исключения базовой модели
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright Kukushkin Denis 2013
 * @version 1.0.0
 * @since 01.08.2013
 */
class ModelException extends GException
{
    /* Const */
    /* Private */
    /* Protected */
    /* Public */
}
