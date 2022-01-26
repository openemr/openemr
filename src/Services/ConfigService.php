<?php

/**
 * Config service
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2022 Robert Down <robertdown@live.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Exceptions\Config\AmbiguousWriteException;
use OpenEMR\Core\Entity\Config;

class ConfigService extends BaseService
{
    public const TABLE_NAME = 'config';

    private const TABLE_PLACEHOLDER = "__table__";

    public function __construct($base_table = null)
    {
        parent::__construct($base_table ?? self::TABLE_NAME);
    }

    /**
     * Query for the config options and return an array of Config objects
     *
     * @param string $sql
     * @param array $binds
     * @return array
     */
    private function query(string $sql, array $binds) : array
    {
        $sql = $this->prepareQuery($sql);
        $query = sqlStatement($sql, $binds);
        $results = [];
        while($row = sqlFetchArray($query)) {
            $_tmp = new Config($row);
            $results[] = $_tmp;
        }
        return $results;
    }

    /**
     * Replace the table placeholder with the actual table name.
     *
     * This may not be the most efficient thing to do, but it helps keep the
     * SQL string neater.
     *
     * @param string $sql
     * @return string
     */
    public function prepareQuery(string $sql) : string
    {
        return str_replace(self::TABLE_PLACEHOLDER, self::TABLE_NAME, $sql);
    }

    public function getByNamespace(string $namespace)
    {
        $sql = "SELECT name, value FROM __table__ WHERE namespace = ?";
        return $this->query($sql, [$namespace]);
    }

    public function getByName(string $name)
    {
        $sql = "SELECT namespace, name, value FROM __table__ WHERE name = ?";
        return $this->query($sql, [$name]);
    }

    public function getByNamespaceAndName(string $namespace, string $name)
    {
        $sql = "SELECT * FROM __table__ WHERE namespace = ? AND name = ?";
        return $this->query($sql, [$namespace, $name]);
    }

    public function writeSetting(string $namespace, string $name, string $value) : int
    {
        // Determine if we are inserting or updating
        $sql = "SELECT id FROM __table__ WHERE namespace = ? AND name = ?";
        $result = sqlQuery($this->prepareQuery($sql), [$name, $value]);

        $_tmp = [];
        while ($row = sqlFetchArray($result)) {
            $_tmp[] = $row;
        }

        if (count($_tmp) == 0) {
            $sql = "INSERT INTO __table__ (namespace, name, value) VALUES (?, ?, ?)";
            $query = sqlInsert($this->prepareQuery($sql), [$namespace, $name, $value]);
        } elseif (count($_tmp) == 1) {
            $sql = "UPDATE __table__ SET name=?, value=? WHERE id = ? AND namespace = ?";
            $query = sqlQuery($this->prepareQuery($sql), [$name, $_tmp['id'], $namespace]);
        } else {
            throw new AmbiguousWriteException("The given namespace and name has more than 1 row and cannot be updated or inserted");
        }

        return $query;
    }
}
