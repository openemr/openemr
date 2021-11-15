<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Sql;

class InsertIgnore extends Insert
{
    /**
     * @var array Specification array
     */
    protected $specifications = [
        self::SPECIFICATION_INSERT => 'INSERT IGNORE INTO %1$s (%2$s) VALUES (%3$s)',
        self::SPECIFICATION_SELECT => 'INSERT IGNORE INTO %1$s %2$s %3$s',
    ];
}
