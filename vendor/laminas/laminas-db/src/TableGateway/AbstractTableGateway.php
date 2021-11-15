<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\TableGateway;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\ResultSet\ResultSetInterface;
use Laminas\Db\Sql\Delete;
use Laminas\Db\Sql\Insert;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\TableIdentifier;
use Laminas\Db\Sql\Update;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\Feature\EventFeatureEventsInterface;

/**
 *
 * @property AdapterInterface $adapter
 * @property int $lastInsertValue
 * @property string $table
 */
abstract class AbstractTableGateway implements TableGatewayInterface
{
    /**
     * @var bool
     */
    protected $isInitialized = false;

    /**
     * @var AdapterInterface
     */
    protected $adapter = null;

    /**
     * @var string|array|TableIdentifier
     */
    protected $table = null;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var Feature\FeatureSet
     */
    protected $featureSet = null;

    /**
     * @var ResultSetInterface
     */
    protected $resultSetPrototype = null;

    /**
     * @var Sql
     */
    protected $sql = null;

    /**
     *
     * @var int
     */
    protected $lastInsertValue = null;

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    /**
     * Initialize
     *
     * @throws Exception\RuntimeException
     * @return null
     */
    public function initialize()
    {
        if ($this->isInitialized) {
            return;
        }

        if (! $this->featureSet instanceof Feature\FeatureSet) {
            $this->featureSet = new Feature\FeatureSet;
        }

        $this->featureSet->setTableGateway($this);
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_INITIALIZE, []);

        if (! $this->adapter instanceof AdapterInterface) {
            throw new Exception\RuntimeException('This table does not have an Adapter setup');
        }

        if (! is_string($this->table) && ! $this->table instanceof TableIdentifier && ! is_array($this->table)) {
            throw new Exception\RuntimeException('This table object does not have a valid table set.');
        }

        if (! $this->resultSetPrototype instanceof ResultSetInterface) {
            $this->resultSetPrototype = new ResultSet;
        }

        if (! $this->sql instanceof Sql) {
            $this->sql = new Sql($this->adapter, $this->table);
        }

        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_INITIALIZE, []);

        $this->isInitialized = true;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * @return Feature\FeatureSet
     */
    public function getFeatureSet()
    {
        return $this->featureSet;
    }

    /**
     * Get select result prototype
     *
     * @return ResultSetInterface
     */
    public function getResultSetPrototype()
    {
        return $this->resultSetPrototype;
    }

    /**
     * @return Sql
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Select
     *
     * @param Where|\Closure|string|array $where
     * @return ResultSetInterface
     */
    public function select($where = null)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }

        $select = $this->sql->select();

        if ($where instanceof \Closure) {
            $where($select);
        } elseif ($where !== null) {
            $select->where($where);
        }

        return $this->selectWith($select);
    }

    /**
     * @param Select $select
     * @return ResultSetInterface
     */
    public function selectWith(Select $select)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        return $this->executeSelect($select);
    }

    /**
     * @param Select $select
     * @return ResultSetInterface
     * @throws Exception\RuntimeException
     */
    protected function executeSelect(Select $select)
    {
        $selectState = $select->getRawState();
        if (isset($selectState['table'])
            && $selectState['table'] != $this->table
            && (is_array($selectState['table'])
                && end($selectState['table']) != $this->table)
        ) {
            throw new Exception\RuntimeException(
                'The table name of the provided Select object must match that of the table'
            );
        }

        if (isset($selectState['columns'])
            && $selectState['columns'] == [Select::SQL_STAR]
            && $this->columns !== []) {
            $select->columns($this->columns);
        }

        // apply preSelect features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_SELECT, [$select]);

        // prepare and execute
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        // build result set
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        // apply postSelect features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_SELECT, [$statement, $result, $resultSet]);

        return $resultSet;
    }

    /**
     * Insert
     *
     * @param  array $set
     * @return int
     */
    public function insert($set)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        $insert = $this->sql->insert();
        $insert->values($set);
        return $this->executeInsert($insert);
    }

    /**
     * @param Insert $insert
     * @return int
     */
    public function insertWith(Insert $insert)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        return $this->executeInsert($insert);
    }

    /**
     * @todo add $columns support
     *
     * @param Insert $insert
     * @return int
     * @throws Exception\RuntimeException
     */
    protected function executeInsert(Insert $insert)
    {
        $insertState = $insert->getRawState();
        if ($insertState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Insert object must match that of the table'
            );
        }

        // apply preInsert features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_INSERT, [$insert]);

        // Most RDBMS solutions do not allow using table aliases in INSERTs
        // See https://github.com/zendframework/zf2/issues/7311
        $unaliasedTable = false;
        if (is_array($insertState['table'])) {
            $tableData      = array_values($insertState['table']);
            $unaliasedTable = array_shift($tableData);
            $insert->into($unaliasedTable);
        }

        $statement = $this->sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
        $this->lastInsertValue = $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();

        // apply postInsert features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_INSERT, [$statement, $result]);

        // Reset original table information in Insert instance, if necessary
        if ($unaliasedTable) {
            $insert->into($insertState['table']);
        }

        return $result->getAffectedRows();
    }

    /**
     * Update
     *
     * @param  array $set
     * @param  string|array|\Closure $where
     * @param  null|array $joins
     * @return int
     */
    public function update($set, $where = null, array $joins = null)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        $sql = $this->sql;
        $update = $sql->update();
        $update->set($set);
        if ($where !== null) {
            $update->where($where);
        }

        if ($joins) {
            foreach ($joins as $join) {
                $type = isset($join['type']) ? $join['type'] : Join::JOIN_INNER;
                $update->join($join['name'], $join['on'], $type);
            }
        }

        return $this->executeUpdate($update);
    }

    /**
     * @param \Laminas\Db\Sql\Update $update
     * @return int
     */
    public function updateWith(Update $update)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        return $this->executeUpdate($update);
    }

    /**
     * @todo add $columns support
     *
     * @param Update $update
     * @return int
     * @throws Exception\RuntimeException
     */
    protected function executeUpdate(Update $update)
    {
        $updateState = $update->getRawState();
        if ($updateState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Update object must match that of the table'
            );
        }

        // apply preUpdate features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_UPDATE, [$update]);

        $unaliasedTable = false;
        if (is_array($updateState['table'])) {
            $tableData      = array_values($updateState['table']);
            $unaliasedTable = array_shift($tableData);
            $update->table($unaliasedTable);
        }

        $statement = $this->sql->prepareStatementForSqlObject($update);
        $result = $statement->execute();

        // apply postUpdate features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_UPDATE, [$statement, $result]);

        // Reset original table information in Update instance, if necessary
        if ($unaliasedTable) {
            $update->table($updateState['table']);
        }

        return $result->getAffectedRows();
    }

    /**
     * Delete
     *
     * @param  Where|\Closure|string|array $where
     * @return int
     */
    public function delete($where)
    {
        if (! $this->isInitialized) {
            $this->initialize();
        }
        $delete = $this->sql->delete();
        if ($where instanceof \Closure) {
            $where($delete);
        } else {
            $delete->where($where);
        }
        return $this->executeDelete($delete);
    }

    /**
     * @param Delete $delete
     * @return int
     */
    public function deleteWith(Delete $delete)
    {
        $this->initialize();
        return $this->executeDelete($delete);
    }

    /**
     * @todo add $columns support
     *
     * @param Delete $delete
     * @return int
     * @throws Exception\RuntimeException
     */
    protected function executeDelete(Delete $delete)
    {
        $deleteState = $delete->getRawState();
        if ($deleteState['table'] != $this->table) {
            throw new Exception\RuntimeException(
                'The table name of the provided Delete object must match that of the table'
            );
        }

        // pre delete update
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_PRE_DELETE, [$delete]);

        $unaliasedTable = false;
        if (is_array($deleteState['table'])) {
            $tableData      = array_values($deleteState['table']);
            $unaliasedTable = array_shift($tableData);
            $delete->from($unaliasedTable);
        }

        $statement = $this->sql->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        // apply postDelete features
        $this->featureSet->apply(EventFeatureEventsInterface::EVENT_POST_DELETE, [$statement, $result]);

        // Reset original table information in Delete instance, if necessary
        if ($unaliasedTable) {
            $delete->from($deleteState['table']);
        }

        return $result->getAffectedRows();
    }

    /**
     * Get last insert value
     *
     * @return int
     */
    public function getLastInsertValue()
    {
        return $this->lastInsertValue;
    }

    /**
     * __get
     *
     * @param  string $property
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function __get($property)
    {
        switch (strtolower($property)) {
            case 'lastinsertvalue':
                return $this->lastInsertValue;
            case 'adapter':
                return $this->adapter;
            case 'table':
                return $this->table;
        }
        if ($this->featureSet->canCallMagicGet($property)) {
            return $this->featureSet->callMagicGet($property);
        }
        throw new Exception\InvalidArgumentException('Invalid magic property access in ' . __CLASS__ . '::__get()');
    }

    /**
     * @param string $property
     * @param mixed $value
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __set($property, $value)
    {
        if ($this->featureSet->canCallMagicSet($property)) {
            return $this->featureSet->callMagicSet($property, $value);
        }
        throw new Exception\InvalidArgumentException('Invalid magic property access in ' . __CLASS__ . '::__set()');
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws Exception\InvalidArgumentException
     */
    public function __call($method, $arguments)
    {
        if ($this->featureSet->canCallMagicCall($method)) {
            return $this->featureSet->callMagicCall($method, $arguments);
        }
        throw new Exception\InvalidArgumentException(sprintf(
            'Invalid method (%s) called, caught by %s::__call()',
            $method,
            __CLASS__
        ));
    }

    /**
     * __clone
     */
    public function __clone()
    {
        $this->resultSetPrototype = (isset($this->resultSetPrototype)) ? clone $this->resultSetPrototype : null;
        $this->sql = clone $this->sql;
        if (is_object($this->table)) {
            $this->table = clone $this->table;
        } elseif (is_array($this->table)
            && count($this->table) == 1
            && is_object(reset($this->table))
        ) {
            foreach ($this->table as $alias => &$tableObject) {
                $tableObject = clone $tableObject;
            }
        }
    }
}
