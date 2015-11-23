<?php

namespace gear\traits;
use gear\Core;
use gear\library\GEvent;

/**
 * Трейт фабрики объектов
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright Kukushkin Denis 2013
 * @version 1.0.0
 * @since 01.05.2015
 * @php 5.3.x
 */
trait TStaticFactory
{
    /**
     * Создание объекта
     *
     * @access public
     * @param array|\Closure $properties
     * @return object
     */
    public static function factory($properties = array(), $owner = null)
    {
        if (!$owner || ($owner && $owner->event('onBeforeFactory', new GEvent(array('sender' => $owner)), $properties, static::getFactory())))
        {
            if ($properties instanceof \Closure)
                $properties = $properties(static::getFactory());
            $properties = array_merge
            (
                static::getFactory(),
                array('owner' => $owner),
                $properties
            );
            list($class, $config, $properties) = Core::getRecords($properties);
            if (method_exists($class, 'init'))
                $class::init($config);
            $object = method_exists($class, 'it') ? $class::it($properties) : new $class($properties);
            if ($owner)
                $owner->event('onAfterFactory', new GEvent(array('sender' => $owner)), $object);
            return $object;
        }
        static::e('Error on factoring process');
    }

    /**
     * Установка параметров создаваемых объектов
     *
     * @access public
     * @param array $factory
     */
    public static function setFactory(array $factory) { static::$_factory = $factory; }

    /**
     * Получение параметров создаваемых объектов
     *
     * @access public
     * @return array
     */
    public static function getFactory() { return static::$_factory; }
}