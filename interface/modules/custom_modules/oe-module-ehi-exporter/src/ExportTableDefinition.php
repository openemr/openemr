<?php

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Common\Database\QueryUtils;

class ExportTableDefinition
{
    public ?string $table;
    public string $selectClause;

    /**
     * @var string[]|int[]
     */
    private array $keyColumnsHashmap;

    private bool $hasNewData;

    public function __construct(?string $table = null)
    {
        $this->table = $table;
        $this->keyColumnsHashmap = [];
        $this->hasNewData = false;
        $this->selectClause = '*';
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

    private function addValueToHashmap($key, $value) {
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

    public function setSelectColumns(array $columns)
    {
        $select = [];
        foreach ($columns as $column) {
            $select[] = QueryUtils::escapeColumnName($column);
        }
        $this->selectClause = implode(',', $columns);
    }

    public function getSelectClause()
    {
        return $this->selectClause;
    }

    public function getRecords()
    {
        $whereClause = [];
        $bindColumns = [];
        // we do this inline instead of using getWhereClause due to memory concerns
        foreach ($this->keyColumnsHashmap as $key => $items) {
            $safeColumn = QueryUtils::escapeColumnName($key);
            $whereClause[] = "($safeColumn IN (" . str_repeat('?,', count($items) - 1) . "?))";
            $bindColumns = array_merge($bindColumns, array_values($items));
        }
        // we want the union of all of the keys so we will join with OR
        $whereClause = implode(" OR ", $whereClause);

//        $whereClause = $this->getWhereClause();
        $sql = "SELECT {$this->getSelectClause()} FROM {$this->table} WHERE $whereClause";
        return QueryUtils::fetchRecords($sql, $bindColumns, false);
    }

//    private function getWhereClause() {
//        $whereClause = [];
//        $bindColumns = [];
//        foreach ($this->keyColumnsHashmap as $key => $items) {
//            $safeColumn = QueryUtils::escapeColumnName($key);
//            $whereClause[] = "($safeColumn IN (" . str_repeat('?,', count($items) - 1) . "?))";
//            $bindColumns = array_merge($bindColumns, array_values($items));
//        }
//        return ['whereClause' => implode(" OR ", $whereClause), 'bindColumns' => $bindColumns];
//    }
    public function setHasNewData(bool $newData)
    {
        $this->hasNewData = false;
    }
}
