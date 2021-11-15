<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver;

interface ConnectionInterface
{
    /**
     * Get current schema
     *
     * @return string
     */
    public function getCurrentSchema();

    /**
     * Get resource
     *
     * @return mixed
     */
    public function getResource();

    /**
     * Connect
     *
     * @return ConnectionInterface
     */
    public function connect();

    /**
     * Is connected
     *
     * @return bool
     */
    public function isConnected();

    /**
     * Disconnect
     *
     * @return ConnectionInterface
     */
    public function disconnect();

    /**
     * Begin transaction
     *
     * @return ConnectionInterface
     */
    public function beginTransaction();

    /**
     * Commit
     *
     * @return ConnectionInterface
     */
    public function commit();

    /**
     * Rollback
     *
     * @return ConnectionInterface
     */
    public function rollback();

    /**
     * Execute
     *
     * @param  string $sql
     * @return ResultInterface
     */
    public function execute($sql);

    /**
     * Get last generated id
     *
     * @param  null $name Ignored
     * @return int
     */
    public function getLastGeneratedValue($name = null);
}
