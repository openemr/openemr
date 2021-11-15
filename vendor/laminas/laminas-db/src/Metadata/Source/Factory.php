<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Metadata\Source;

use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Exception\InvalidArgumentException;
use Laminas\Db\Metadata\MetadataInterface;

/**
 * Source metadata factory.
 */
class Factory
{
    /**
     * Create source from adapter
     *
     * @param  Adapter $adapter
     * @return MetadataInterface
     * @throws InvalidArgumentException If adapter platform name not recognized.
     */
    public static function createSourceFromAdapter(Adapter $adapter)
    {
        $platformName = $adapter->getPlatform()->getName();

        switch ($platformName) {
            case 'MySQL':
                return new MysqlMetadata($adapter);
            case 'SQLServer':
                return new SqlServerMetadata($adapter);
            case 'SQLite':
                return new SqliteMetadata($adapter);
            case 'PostgreSQL':
                return new PostgresqlMetadata($adapter);
            case 'Oracle':
                return new OracleMetadata($adapter);
            default:
                throw new InvalidArgumentException("Unknown adapter platform '{$platformName}'");
        }
    }
}
