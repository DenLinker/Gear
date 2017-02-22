<?php

namespace gear\library\db;

use gear\library\GModel;
use gear\traits\TDelegateFactory;

/**
 * Библиотека коллекций (таблиц)
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 * @since 0.0.1
 * @version 0.0.1
 */
abstract class GDbCollection extends GModel implements \IteratorAggregate
{
    /* Traits */
    use TFactory;
    use TDelegateFactory;
    /* Const */
    /* Private */
    /* Protected */
    protected $_factory = [
        'class' => '\gear\library\GModel',
    ];
    protected $_cursorFactory = [
        'class' => '\gear\library\db\GDbCursor',
    ];
    protected $_items = [];
    protected $_current = null;
    /* Public */

    public function __call(string $name, array $arguments)
    {
        $result = null;
        if (method_exists($this->_cursorFactory['class'], $name)) {
            $result = $this->cursor->$name(... $arguments);
        } else {
            $result = parent::__call($name, $arguments);
        }
        return $result;
    }

    /**
     * Удаление базы данных
     *
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    abstract public function drop();

    /**
     * Возвращает соединение с сервером базы данных
     *
     * @return GDbConnection
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getConnection(): GDbConnection
    {
        return $this->owner->getConnection();
    }

    /**
     * Возвращает текущий выполняемый запрос
     *
     * @return GDbCursor
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCurrent(): GDbCursor
    {
        return $this->_current;
    }

    /**
     * Возвращает инстанс курсора для работы с запросами
     *
     * @return GDbCursor
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCursor(): GDbCursor
    {
        $this->current = $this->factory($this->_cursorFactory);
        return $this->current;
    }

    /**
     * Возвращает базу данных, в которой находится коллекция курсора
     *
     * @return GDbDatabase
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getDatabase(): GDbDatabase
    {
        return $this->owner;
    }

    /**
     * Возвращает ресурс соединения с сервером базы данных
     *
     * @return mixed
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getHandler()
    {
        return $this->owner->handler;
    }

    /**
     * Возвращает ID последней вставленной записи в таблицу
     *
     * @return int
     * @since 0.0.1
     * @version 0.0.1
     */
    public function lastInsertId(): int
    {
        return $this->current ? $this->current->lastInsertId : 0;
    }

    /**
     * Удаление таблицы
     *
     * @access public
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function remove()
    {
        $this->drop();
    }

    /**
     * Сброс результатов выполнения последнего запроса
     *
     * @return GDbCollection
     * @since 0.0.1
     * @version 0.0.1
     */
    public function reset(): GDbCollection
    {
        if ($this->current) {
            $this->current->reset();
        }
        return $this;
    }

    /**
     * Установка текущего выполняемого запроса
     *
     * @param GDbCursor $cursor
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setCurrent(GDbCursor $cursor)
    {
        $this->_current = $cursor;
    }

    /**
     * Очистка таблицы от записей
     *
     * @return GDbCollection
     * @since 0.0.1
     * @version 0.0.1
     */
    abstract public function truncate(): GDbCollection;
}
