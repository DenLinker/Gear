<?php

namespace gear\library;

use gear\Core;
use gear\library\GObject;
use gear\library\GException;

/** 
 * Класс сервисов
 * 
 * @package Gear Framework
 * @abstract
 * @author Kukushkin Denis
 * @copyright Kukushkin Denis 2013
 * @version 1.0.0
 * @since 25.12.2014
 * @php 5.3.x
 */
abstract class GService extends GObject
{
    /* Const */
    /* Private */
    /* Protected */
    protected static $_config = array();
    protected static $_init = false;
    protected $_name = null;
    /* Public */
    
    /**
     * Копирование сервиса
     * 
     * @access public
     * @return void
     */
    public function __clone() {}
    
    /**
     * Установка компонента
     * 
     * @access public
     * @static
     * @param string|array $config
     * @return GService
     */
    public static function install($config)
    {
        if (static::$_init === false)
            static::init($config);
        $args = func_get_args();
        array_shift($args);
        $instance = call_user_func_array(array(static::class, 'it'), $args);
        $instance->event('onInstalled');
        return $instance;
    }
    
    /**
     * Конфигурирование класса компонента
     * 
     * @access public
     * @static
     * @param string|array $config
     * @return void
     */
    public static function init($config)
    {
        if (is_string($config))
            $config = require(Core::resolvePath($config));
        if (!is_array($config))
            static::e('Incorrect configuration');
        static::$_config = array_replace_recursive(static::$_config, $config);
        list(,,static::$_config) = Core::getRecords(static::$_config);
        if (isset(static::$_config['components']))
        {
            foreach(static::$_config['components'] as $componentName => $component)
                Core::services()->registerService(static::class . '.components.' . $componentName, $component);
        }
    }
    
    /**
     * Получение экхемпляра компонента
     * 
     * @access public
     * @static
     * @param array $properties
     * @param nulll|object $owner
     * @return GComponent
     */
    public static function it(array $properties = array(), $owner = null)
    {
        return new static($properties, $owner);
    }

    /**
     * Возвращает true, если компонент может быть перегружен, иначе false
     * 
     * @access public
     * @return boolean
     */
    public function isOverride()
    {
        return isset($this->_properties['override']) && (bool)$this->_properties['override'] === true;
    }

    /**
     * Возвращает имя сервиса
     *
     * @access public
     * @return string
     */
    public function getName() { return $this->_name; }

    /**
     * Устанавливает имя сервиса
     *
     * @access public
     * @param string $nameService
     * @return $this
     */
    public function setName($nameService)
    {
        $this->_name = $nameService;
        return $this;
    }

    /**
     * Возвращает имя сервиса
     *
     * @access public
     * @return string
     */
    public function name() { return $this->getName(); }
}

/** 
 * Исключения сервисов
 * 
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright Kukushkin Denis 2013
 * @version 0.0.1
 * @since 01.08.2013
 */
class ServiceException extends GException
{
    /* Const */
    /* Private */
    /* Protected */
    /* Public */
}