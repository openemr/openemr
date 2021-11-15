<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver\Oci8;

use Iterator;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\Adapter\Exception;

class Result implements Iterator, ResultInterface
{
    /**
     * @var resource
     */
    protected $resource = null;

    /**
     * @var null|int
     */
    protected $rowCount = null;

    /**
     * Cursor position
     * @var int
     */
    protected $position = 0;

    /**
     * Number of known rows
     * @var int
     */
    protected $numberOfRows = -1;

    /**
     * Is the current() operation already complete for this pointer position?
     * @var bool
     */
    protected $currentComplete = false;

    /**
     * @var bool|array
     */
    protected $currentData = false;

    /**
     *
     * @var array
     */
    protected $statementBindValues = ['keys' => null, 'values' => []];

    /**
     * @var mixed
     */
    protected $generatedValue = null;

    /**
     * Initialize
     * @param resource $resource
     * @param null|int $generatedValue
     * @param null|int $rowCount
     * @return self Provides a fluent interface
     */
    public function initialize($resource, $generatedValue = null, $rowCount = null)
    {
        if (! is_resource($resource) && get_resource_type($resource) !== 'oci8 statement') {
            throw new Exception\InvalidArgumentException('Invalid resource provided.');
        }
        $this->resource = $resource;
        $this->generatedValue = $generatedValue;
        $this->rowCount = $rowCount;
        return $this;
    }

    /**
     * Force buffering at driver level
     *
     * Oracle does not support this, to my knowledge (@ralphschindler)
     *
     * @throws Exception\RuntimeException
     */
    public function buffer()
    {
        return;
    }

    /**
     * Is the result buffered?
     *
     * @return bool
     */
    public function isBuffered()
    {
        return false;
    }

    /**
     * Return the resource
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Is query result?
     *
     * @return bool
     */
    public function isQueryResult()
    {
        return (oci_num_fields($this->resource) > 0);
    }

    /**
     * Get affected rows
     * @return int
     */
    public function getAffectedRows()
    {
        return oci_num_rows($this->resource);
    }

    /**
     * Current
     * @return mixed
     */
    public function current()
    {
        if ($this->currentComplete == false) {
            if ($this->loadData() === false) {
                return false;
            }
        }
        return $this->currentData;
    }

    /**
     * Load from oci8 result
     *
     * @return bool
     */
    protected function loadData()
    {
        $this->currentComplete = true;
        $this->currentData = oci_fetch_assoc($this->resource);
        if ($this->currentData !== false) {
            $this->position++;
            return true;
        }
        return false;
    }

    /**
     * Next
     */
    public function next()
    {
        return $this->loadData();
    }

    /**
     * Key
     * @return mixed
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Rewind
     */
    public function rewind()
    {
        if ($this->position > 0) {
            throw new Exception\RuntimeException('Oci8 results cannot be rewound for multiple iterations');
        }
    }

    /**
     * Valid
     * @return bool
     */
    public function valid()
    {
        if ($this->currentComplete) {
            return ($this->currentData !== false);
        }
        return $this->loadData();
    }

    /**
     * Count
     * @return null|int
     */
    public function count()
    {
        if (is_int($this->rowCount)) {
            return $this->rowCount;
        }
        if (is_callable($this->rowCount)) {
            $this->rowCount = (int) call_user_func($this->rowCount);
            return $this->rowCount;
        }
        return;
    }

    /**
     * @return int
     */
    public function getFieldCount()
    {
        return oci_num_fields($this->resource);
    }

    /**
     * @return null
     */
    public function getGeneratedValue()
    {
        // @todo OCI8 generated value in Driver Result
        return;
    }
}
