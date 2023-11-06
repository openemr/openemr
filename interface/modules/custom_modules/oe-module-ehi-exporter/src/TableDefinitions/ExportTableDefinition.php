<?php

/**
 * Export table definition class for a table.  Responsible for retrieving the records for a given
 * table definition as well as holding all of the key values for the table.  The key values are used
 * for retrieving the table records based upon all of the foreign key values that have been added to the table
 * to filter on.  Table records are retrieved using the union (SQL OR clause) of all of the key values.
 *
 * Custom tables that have more specific queries can extend this class to override the getRecords method.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\TableDefinitions;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Modules\EhiExporter\Models\ExportKeyDefinition;

class ExportTableDefinition
{
    public ?string $table;
    public string $selectClause;

    /**
     * @var string[]|int[]
     */
    private array $keyColumnsHashmap;

    private bool $hasNewData;

    /**
     * @var string[]
     */
    private array $tableColumnNames;

    private array $primaryKeys;

    public function __construct(?string $table = null, array $pks = [])
    {
        $this->table = $table;
        $this->keyColumnsHashmap = [];
        $this->hasNewData = false;
        $this->selectClause = '*';
        $this->primaryKeys = $pks;
    }

    public function addPrimaryKey(string $key)
    {
        $this->primaryKeys[] = $key;
    }

    private function createPrimaryKeyHashFromRecord(&$record)
    {
        $hash = [];
        foreach ($this->primaryKeys as $key) {
            $hash[] = $record[$key];
        }
        return implode('::', $hash);
    }

    public function addKeyValue(ExportKeyDefinition $keyDefinition, int|string $value)
    {
        $key = $keyDefinition->foreignKeyColumn;
        if ($keyDefinition->isDenormalized && is_string($value)) {
            $valueList = explode($keyDefinition->denormalizedKeySeparator, $value);
            foreach ($valueList as $value) {
                $this->addValueToHashmap($key, $value);
            }
        } else {
            $this->addValueToHashmap($key, $value);
        }
    }

    private function addValueToHashmap($key, $value)
    {
        $hasValue = $this->keyColumnsHashmap[$key][$value] ?? null;
        if (!isset($hasValue)) {
            if (!isset($this->keyColumnsHashmap[$key])) {
                $this->keyColumnsHashmap[$key] = [];
            }
            $this->keyColumnsHashmap[$key][$value] = $value;
            $this->hasNewData = true;
        }
    }

    public function addKeyValueList(ExportKeyDefinition $key, array $values)
    {
        foreach ($values as $value) {
            $this->addKeyValue($key, $value);
        }
    }

    public function hasNewData()
    {
        return $this->hasNewData;
    }

    public function setSelectClause(string $clause)
    {
        $this->selectClause = $clause;
    }

    /**
     * @deprecated
     * @param array $columns
     * @return void
     */
    public function setSelectColumns(array $columns)
    {
        $select = [];
        foreach ($columns as $column) {
            $select[] = QueryUtils::escapeColumnName($column, [$this->table]);
        }
        $this->selectClause = implode(',', $columns);
    }

    public function getSelectClause()
    {
        return $this->selectClause;
    }

    protected function getHashmapForKey($key)
    {
        return $this->keyColumnsHashmap[$key] ?? [];
    }

    public function getRecords()
    {
        $maxIterations = 500; // always have a loop safety in case the loop logic breaks, which is 500 * 25000 = 12,500,000 records
        $iterations = 0;

        $batchSize = 25000;
        // we will just go through each key and grab the records in batches of 25000
        // we'll grab the PK definition, then we'll grab the records in batches, we'll compute a PK hash for each record
        // if the hash is in the PK hashmap then we'll skip it, otherwise we'll add it to the hashmap and add it to the records
        $recordKeyHash = [];
        $resultRecords = [];
        foreach ($this->keyColumnsHashmap as $key => $items) {
            $pos = 0;
            $bindColumnsCount = count($items);
            do {
                $fetchSize = min($batchSize, $bindColumnsCount - $pos);
                // key has already been escaped when we created the table definitions so we can just search against the valid
                // table columns, if it exists we are good to go, otherwise we fail.
                if (array_search($key, $this->tableColumnNames) === false) {
                    throw new \RuntimeException("Invalid key column name for table " . $this->table . ": $key");
                }
                $whereClause = "($key IN (" . str_repeat('?,', $fetchSize - 1) . "?))";
                $bindColumns = array_slice($items, $pos, $fetchSize);

                $sql = "SELECT {$this->getSelectClause()} FROM {$this->table} WHERE $whereClause";
                $records = QueryUtils::sqlStatementThrowException($sql, $bindColumns, false);
                foreach ($records as $record) {
                    $pkHash = $this->createPrimaryKeyHashFromRecord($record);
                    if (!isset($recordKeyHash[$pkHash])) {
                        $recordKeyHash[$pkHash] = 1; // keep it sane
                        $resultRecords[] = $record;
                    }
                }
                $pos += $fetchSize;
            } while ($pos < $bindColumnsCount && $iterations++ < $maxIterations);
        }
        return $resultRecords;
    }

    public function setHasNewData(bool $newData)
    {
        $this->hasNewData = false;
    }

    /**
     * @param string[]  $safeColumnNames
     * @return void
     */
    public function setColumnNames(array $safeColumnNames)
    {
        $this->tableColumnNames = $safeColumnNames;
    }

    /**
     * @return string[]
     */
    public function getColumnNames(): array
    {
        return $this->tableColumnNames;
    }
}
