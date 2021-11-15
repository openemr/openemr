<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql;

use Laminas\Db\Adapter\Platform\PlatformInterface;

interface SqlInterface
{
    /**
     * Get SQL string for statement
     *
     * @param null|PlatformInterface $adapterPlatform
     *
     * @return string
     */
    public function getSqlString(PlatformInterface $adapterPlatform = null);
}
