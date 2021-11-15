<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql;

use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Adapter\Platform\PlatformInterface;

/**
 *
 * @property Where $where
 */
class Delete extends AbstractPreparableSql
{
    /**@#+
     * @const
     */
    const SPECIFICATION_DELETE = 'delete';
    const SPECIFICATION_WHERE = 'where';
    /**@#-*/

    /**
     * {@inheritDoc}
     */
    protected $specifications = [
        self::SPECIFICATION_DELETE => 'DELETE FROM %1$s',
        self::SPECIFICATION_WHERE => 'WHERE %1$s'
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
     * @var array
     */
    protected $set = [];

    /**
     * @var null|string|Where
     */
    protected $where = null;

    /**
     * Constructor
     *
     * @param  null|string|TableIdentifier $table
     */
    public function __construct($table = null)
    {
        if ($table) {
            $this->from($table);
        }
        $this->where = new Where();
    }

    /**
     * Create from statement
     *
     * @param  string|TableIdentifier $table
     * @return self Provides a fluent interface
     */
    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param null $key
     *
     * @return mixed
     */
    public function getRawState($key = null)
    {
        $rawState = [
            'emptyWhereProtection' => $this->emptyWhereProtection,
            'table' => $this->table,
            'set' => $this->set,
            'where' => $this->where
        ];
        return (isset($key) && array_key_exists($key, $rawState)) ? $rawState[$key] : $rawState;
    }

    /**
     * Create where clause
     *
     * @param  Where|\Closure|string|array $predicate
     * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
     *
     * @return self Provides a fluent interface
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
     * @param PlatformInterface       $platform
     * @param DriverInterface|null    $driver
     * @param ParameterContainer|null $parameterContainer
     *
     * @return string
     */
    protected function processDelete(
        PlatformInterface $platform,
        DriverInterface $driver = null,
        ParameterContainer $parameterContainer = null
    ) {
        return sprintf(
            $this->specifications[static::SPECIFICATION_DELETE],
            $this->resolveTable($this->table, $platform, $driver, $parameterContainer)
        );
    }

    /**
     * @param PlatformInterface       $platform
     * @param DriverInterface|null    $driver
     * @param ParameterContainer|null $parameterContainer
     *
     * @return null|string
     */
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

    /**
     * Property overloading
     *
     * Overloads "where" only.
     *
     * @param  string $name
     *
     * @return Where|null
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'where':
                return $this->where;
        }
    }
}
