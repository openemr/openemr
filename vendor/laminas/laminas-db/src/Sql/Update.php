<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Driver\Pdo\Pdo;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Stdlib\PriorityList;

/**
 *
 * @property Where $where
 */
class Update extends AbstractPreparableSql
{
    /**@#++
     * @const
     */
    const SPECIFICATION_UPDATE = 'update';
    const SPECIFICATION_SET = 'set';
    const SPECIFICATION_WHERE = 'where';
    const SPECIFICATION_JOIN = 'joins';

    const VALUES_MERGE = 'merge';
    const VALUES_SET   = 'set';
    /**@#-**/

    protected $specifications = [
        self::SPECIFICATION_UPDATE => 'UPDATE %1$s',
        self::SPECIFICATION_JOIN => [
            '%1$s' => [
                [3 => '%1$s JOIN %2$s ON %3$s', 'combinedby' => ' ']
            ]
        ],
        self::SPECIFICATION_SET => 'SET %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s',
    ];

    /**
     * @var string|TableIdentifier
     */
    protected $table = '';

    /**
     * @var bool
     */
    protected $emptyWhereProtection = true;

    /**
     * @var PriorityList
     */
    protected $set;

    /**
     * @var string|Where
     */
    protected $where = null;

    /**
     * @var null|Join
     */
    protected $joins = null;

    /**
     * Constructor
     *
     * @param  null|string|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->table($table);
        }
        $this->where = new Where();
        $this->joins = new Join();
        $this->set = new PriorityList();
        $this->set->isLIFO(false);
    }

    /**
     * Specify table for statement
     *
     * @param  string|TableIdentifier $table
     * @return self Provides a fluent interface
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set key/value pairs to update
     *
     * @param  array $values Associative array of key values
     * @param  string $flag One of the VALUES_* constants
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function set(array $values, $flag = self::VALUES_SET)
    {
        if ($flag == self::VALUES_SET) {
            $this->set->clear();
        }
        $priority = is_numeric($flag) ? $flag : 0;
        foreach ($values as $k => $v) {
            if (! is_string($k)) {
                throw new Exception\InvalidArgumentException('set() expects a string for the value key');
            }
            $this->set->insert($k, $v, $priority);
        }
        return $this;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function where($predicate, $combination = Predicate\PredicateSet::OP_AND)
    {
        if ($predicate instanceof Where) {
            $this->where = $predicate;
        } else {
            $this->where->addPredicates($predicate, $combination);
        }
        return $this;
    }

    /**
     * Create join clause
     *
     * @param  string|array $name
     * @param  string $on
     * @param  string $type one of the JOIN_* constants
     * @return self Provides a fluent interface
     * @throws Exception\InvalidArgumentException
     */
    public function join($name, $on, $type = Join::JOIN_INNER)
    {
        $this->joins->join($name, $on, [], $type);

        return $this;
    }

    public function getRawState($key = null)
    {
        $rawState = [
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set->toArray(),
            'where' => $this->where,
            'joins' => $this->joins
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    protected function processUpdate(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return sprintf(
            $this->specifications[static::SPECIFICATION_UPDATE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer)
        );
    }

    protected function processSet(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        $setSql = [];
        $i      = 0;
        foreach ($this->set as $column => $value) {
            $prefix = $this->resolveColumnValue(
                [
                    'column'       => $column,
                    'fromTable'    => '',
                    'isIdentifier' => true,
                ],
                $platform,
                $driver,
                $parameterContainer,
                'column'
            );
            $prefix .= ' = ';
            if (is_scalar($value) && $parameterContainer) {
                // use incremental value instead of column name for PDO
                // @see https://github.com/zendframework/zend-db/issues/35
                if ($driver instanceof Pdo) {
                    $column = 'c_' . $i++;
                }
                $setSql[] = $prefix . $driver->formatParameterName($column);
                $parameterContainer->offsetSet($column, $value);
            } else {
                $setSql[] = $prefix . $this->resolveColumnValue(
                    $value,
                    $platform,
                    $driver,
                    $parameterContainer
                );
            }
        }

        return sprintf(
            $this->specifications[static::SPECIFICATION_SET],
            implode(', ', $setSql)
        );
    }

    protected function processWhere(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        if ($this->where->count() == 0) {
            return;
        }
        return sprintf(
            $this->specifications[static::SPECIFICATION_WHERE],
            $this->processExpression($this->where, $platform, $driver, $parameterContainer, 'where')
        );
    }

    protected function processJoins(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return $this->processJoin($this->joins, $platform, $driver, $parameterContainer);
    }

    /**
     * Variable overloading
     *
     * Proxies to "where" only
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (strtolower($name) == 'where') {
            return $this->where;
        }
    }

    /**
     * __clone
     *
     * Resets the where object each time the Update is cloned.
     *
     * @return void
     */
    public function __clone()
    {
        $this->where = clone $this->where;
        $this->set = clone $this->set;
    }
}
