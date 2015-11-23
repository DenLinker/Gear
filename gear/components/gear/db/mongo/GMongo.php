<?php

namespace gear\components\gear\db\mongo;
use \gear\Core;
use \gear\GException;
use \gear\interfaces\IComponent;

/**
 * Компонент для работы с Mongo-серверами
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright Kukushkin Denis
 * @version 0.0.1
 * @since 10.03.2013
 */
class GMongo extends \Mongo implements IComponent
{
    /* Const */
    /* Private */
    /* Protected */
    protected static $_config = array();
    protected $_properties = array();
    protected $_owner = null;
    /* Public */
    
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
     * Метод, который выполняется во время инсталляции компонента.
     * Запускает инициализацию класса (конфигурирование) и возвращает
     * инстанс.
     * 
     * @access public
     * @static
     * @param array|string as path to file $config
     * @param array $properties
     * @return GComponent
     * @see Core::installcomponent()
     */
    public static function install($config, array $properties = array(), $owner = null)
    {
        static::init($config);
        return static::it($properties);
    }

    /**
     * Метод осуществляет конфигурирование класса компонента.
     * В качестве параметра необходимо передавать методу либо массив настроек,
     * либо путь к php-сценарию, который должен вернуть этих массив настроек.
     * 
     * @access public
     * @static
     * @param array|string as path to file $config
     * @throws ComponentException
     * @return void
     */
    public static function init($config) {}

    /**
     * Создаёт и возвращает инстанс класса
     * 
     * @access public
     * @static
     * @param array $properties
     * @param null|GObject $owner
     * @return GComponent
     */
    public static function it(array $properties = array(), $owner = null)
    {
        return new static($properties);
    }

    /**
     * Конструктор
     * 
     * @access public
     * @param array $props
     * @return void
     */
    public function __construct($props)
    {
        $this->_properties = $props;
        $host = 'mongodb://' . $this->_properties['host'];
        parent::__construct($host);
//        parent::__construct('mongodb://' . $this->_props['host'] . ':' . (isset($this->_props['port']) ? $this->_props['port'] : ''));
    }
}