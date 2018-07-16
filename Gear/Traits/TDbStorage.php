<?php

namespace Gear\Traits;

use Gear\Core;
use Gear\Interfaces\IDbCollection;
use Gear\Interfaces\IDbConnection;
use Gear\Interfaces\IDbCursor;
use Gear\Interfaces\IDbDatabase;
use Gear\Interfaces\IModel;

/**
 * Трейт компонентов для выполнения операций с моделями
 * в базах данных
 *
 * @package Gear Framework
 * @author Kukushkin Denis
 * @copyright 2016 Kukushkin Denis
 * @license http://www.spdx.org/licenses/MIT MIT License
 * @since 0.0.1
 * @version 0.0.1
 */
trait TDbStorage
{
    /**
     * Добавление модели в набор (сохранение в коллекции-таблице в базе данных)
     *
     * @param IModel|array of IModel $model
     * @return int
     * @since 0.0.1
     * @version 0.0.1
     */
    public function add($model): int
    {
        return $this->selectCollection()->insert($model);
    }

    /**
     * Выборка всех моделей из коллекции
     *
     * @return iterable
     * @since 0.0.1
     * @version 0.0.1
     */
    public function all(): iterable
    {
        return $this->getIterator($this->getDefaultCursor());
    }

    /**
     * Выборка модели по значению первичного ключа
     *
     * @param int|string $pkValue
     * @return \Gear\Interfaces\IObject|null
     * @since 0.0.1
     * @version 0.0.1
     */
    public function byPk($pkValue)
    {
        $class = $this->factory['class'];
        $result = $this->selectCollection()->findOne([$class::primaryKey() => "'$pkValue'"]);
        return $result ? $this->factory($result) : $result;
    }

    /**
     * Поиск моделей по указанному критерию
     *
     * @param array|string $criteria
     * @param array|string $fields
     * @return \Iterator
     * @since 0.0.1
     * @version 0.0.1
     */
    public function find($criteria = [], $fields = [])
    {
        return $this->getIterator($this->selectCollection()->find($criteria, $fields));
    }

    /**
     * Поиск модели, соответствующей указанному критерию
     *
     * @param array|string $criteria
     * @return \Gear\Interfaces\IObject|null
     * @since 0.0.1
     * @version 0.0.1
     */
    public function findOne($criteria = [])
    {
        $result = $this->selectCollection()->findOne($criteria);
        return $result ? $this->factory($result) : $result;
    }

    /**
     * Возвращает название таблицы
     *
     * @return string
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCollectionName(): string
    {
        return $this->_collectionName;
    }

    /**
     * Возвращает компонент подключения к базе данных
     *
     * @return IDbConnection
     * @throws \CoreException
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getConnection(): IDbConnection
    {
        if (!$this->_connection) {
            $this->_connection = Core::c($this->connectionName);
        }
        return $this->_connection;
    }

    /**
     * Возвращает название компонента подключения к серверу базы данных
     *
     * @return string
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getConnectionName(): string
    {
        return $this->_connectionName;
    }

    /**
     * Возвращает курсор коллекции
     *
     * @return IDbCursor
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getCursor(): IDbCursor
    {
        return $this->selectCollection()->cursor;
    }

    /**
     * Возвращает название базы данных
     *
     * @return string
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getDbName(): string
    {
        return $this->_dbName;
    }

    /**
     * Возвращает курсор с параметрами по-умолчанию
     *
     * @return IDbCursor
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getDefaultCursor(): IDbCursor
    {
        $criteria = [];
        if ($this->_defaultParams['where']) {
            $criteria = $this->_defaultParams['where'];
        }
        $fields = [];
        if ($this->_defaultParams['fields']) {
            $fields = $this->_defaultParams['fields'];
        }
        $cursor = $this->cursor->find($criteria, $fields);
        if ($this->_defaultParams['sort']) {
            $cursor->sort($this->_defaultParams['sort']);
        }
        if ($this->_defaultParams['limit']) {
            $cursor->limit($this->_defaultParams['limit']);
        }
        return $cursor;
    }

    /**
     * Возвращает итератор со записями
     *
     * @param mixed $cursor
     * @return iterable
     * @since 0.0.1
     * @version 0.0.1
     */
    public function getIterator($cursor = null): iterable
    {
        if ($cursor instanceof \Iterator) {
            $cursor = $this->delegate($cursor);
        } else if (is_string($cursor)) {
            $cursor = $this->delegate($this->cursor->runQuery($cursor));
        } else {
            $cursor = $this->delegate($this->getDefaultCursor());
        }
        return $cursor;
    }

    /**
     * Удаление модели
     *
     * @param array|IModel|array of IModel $model
     * @since 0.0.1
     * @version 0.0.1
     */
    public function remove($model)
    {
        $this->selectCollection()->remove($model);
    }

    /**
     * Сохранение модели
     *
     * @param array|IModel|array of IModel $model
     * @since 0.0.1
     * @version 0.0.1
     */
    public function save($model)
    {
        $this->selectCollection()->save($model);
    }

    /**
     * Выбор коллекции
     *
     * @param string $alias
     * @return IDbCollection
     * @since 0.0.1
     * @version 0.0.1
     */
    public function selectCollection(string $alias = ""): IDbCollection
    {
        return $this->connection->selectCollection($this->dbName, $this->collectionName, $alias);
    }

    /**
     * Выбор базы данных
     *
     * @return IDbDatabase
     * @since 0.0.1
     * @version 0.0.1
     */
    public function selectDB(): IDbDatabase
    {
        return $this->connection->selectDB($this->dbName);
    }

    /**
     * Устновка названия коллекции, в которой располагаются модели
     *
     * @param string $collectionName
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setCollectionName(string $collectionName)
    {
        $this->_collectionName = $collectionName;
    }

    /**
     * Устновка подключения к серверу базы данных
     *
     * @param IDbConnection $connection
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setConnection(IDbConnection $connection)
    {
        $this->_connection = $connection;
    }

    /**
     * Установка названия компонента, выполняющего подключение к
     * серверу базы данных
     *
     * @param string $connectionName
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setConnectionName(string $connectionName)
    {
        $this->_connectionName = $connectionName;
    }

    /**
     * Установка названия базы данных с коллекциями моделей
     *
     * @param string $dbName
     * @return void
     * @since 0.0.1
     * @version 0.0.1
     */
    public function setDbName(string $dbName)
    {
        $this->_dbName = $dbName;
    }

    /**
     * Обновление существующей модели
     *
     * @param $model
     * @return int
     * @since 0.0.1
     * @version 0.0.1
     */
    public function update($model)
    {
        $result = 0;
        if ($model instanceof IModel) {
            if ($model->onBeforeUpdate()) {
                $result = $this->selectCollection()->update($model);
                $model->onAfterUpdate();
            }
        } else {
            $result = $this->selectCollection()->update($model);
        }
        return $result;
    }
}