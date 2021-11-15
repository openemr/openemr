<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter\Driver;

use Laminas\Db\Adapter\ParameterContainer;
use Laminas\Db\Adapter\StatementContainerInterface;

interface StatementInterface extends StatementContainerInterface
{
    /**
     * Get resource
     *
     * @return resource
     */
    public function getResource();

    /**
     * Prepare sql
     *
     * @param string $sql
     */
    public function prepare($sql = null);

    /**
     * Check if is prepared
     *
     * @return bool
     */
    public function isPrepared();

    /**
     * Execute
     *
     * @param null|array|ParameterContainer $parameters
     * @return ResultInterface
     */
    public function execute($parameters = null);
}
