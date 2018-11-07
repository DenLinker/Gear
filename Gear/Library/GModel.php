<?php

namespace Gear\Library;

use Gear\Interfaces\IModel;
use Gear\Interfaces\IObject;
use Gear\Traits\TPluginContained;
use Gear\Traits\TServiceContained;

/**
 * Базовый класс моделей
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 * @since 0.0.1
 * @version 0.0.1
 */
class GModel extends GObject implements IModel
{
    /* Traits */
    use TServiceContained;
    use TPluginContained;
    /* Const */
    /* Private */
    /* Protected */
    /* Public */
    public static $primaryKeyName = 'id';

    /**
     * GModel constructor.
     *
     * @param array|\Closure $properties
     * @param null|IObject $owner
     * @since 0.0.1
     * @version 0.0.1
     */
    public function __construct($properties = [], IObject $owner = null)
    {
        parent::__construct($properties, $owner);
    }

    /**
     * Возвращает значение поля, которое является первичным ключом
     *
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getPrimaryKey()
    {
        return $this->props(static::$primaryKeyName);
    }

    /**
     * Возвращает название поля, которое является первичным ключом
     *
     * @return string
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getPrimaryKeyName(): string
    {
        return static::$primaryKeyName;
    }

    /**
     * Устанавливает значение для поля, которое является первичным ключом
     *
     * @param mixed $value
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setPrimaryKey($value)
    {
        $this->props(static::$primaryKeyName, $value);
    }

    /**
     * Устанавливает название поля, которое является первичным ключом
     *
     * @param string $pkName
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setPrimaryKeyName(string $pkName)
    {
        static::$primaryKeyName = $pkName;
    }
}