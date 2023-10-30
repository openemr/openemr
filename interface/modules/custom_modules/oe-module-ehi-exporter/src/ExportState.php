<?php

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Modules\EhiExporter\ExportResult;
use OpenEMR\Modules\EhiExporter\ExportKeyDefinition;
use OpenEMR\Modules\EhiExporter\ExportTableDefinition;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJobTask;

class ExportState
{
    public \SimpleXMLElement $rootNode;
    private \SplQueue $queue;
    private ExportResult $result;
    private array $tableDefinitionsMap;
    private SystemLogger $logger;

    // we use this to make sure if we are scheduled to hit an item again
    private $inQueueList = [];

    private ExportTableDataFilterer $dataFilterer;

    /**
     * @var string the temp directory to use for this export
     */
    private string $tempDir;

    public function __construct(SystemLogger $logger, \SimpleXMLElement $node, EhiExportJobTask $jobTask)
    {
        $this->rootNode = $node;
        $this->queue = new \SplQueue();
        $this->result = new ExportResult();
        $this->tableDefinitionsMap = [];
        $this->dataFilterer = new ExportTableDataFilterer();
        $this->keyFilterer = new ExportKeyDefinitionFilterer();
        $this->jobTask = $jobTask;

        $this->logger = $logger;
    }

    public function getTempSysDir()
    {
        if (!isset($this->tempDir)) {
            $this->tempDir = tempnam(sys_get_temp_dir(), 'ehi-export-');
            if (file_exists($this->tempDir)) {
                unlink($this->tempDir);
            }
            mkdir($this->tempDir);
            if (!is_dir($this->tempDir)) {
                throw new \RuntimeException("Failed to make temporary directory for export in temp directory");
            }
        }
        return $this->tempDir;
    }

    public function getJobTask()
    {
        return $this->jobTask;
    }

    public function addExportResultTable(string $tableName, int $recordCount)
    {
        $result = new ExportTableResult();
        $result->tableName = $tableName;
        $result->count = $recordCount;
        $this->result->exportedTables[$tableName] = $result;
        $this->logger->debug("Adding export result table ", ['table' => $tableName, 'count' => $recordCount]);
    }

    public function getExportResult()
    {
        return $this->result;
    }

    public function xmlXPath(string $xpath)
    {
        return $this->rootNode->xpath($xpath);
    }

    public function getTableDefinitionForTable(string $tableName): ?ExportTableDefinition
    {
        if (isset($this->tableDefinitionsMap[$tableName])) {
            return $this->tableDefinitionsMap[$tableName];
        }
        return null;
    }

    public function getNextTableDefinitionToProcess(): ExportTableDefinition
    {
        $item = $this->queue->dequeue();
        if ($item instanceof ExportTableDefinition) {
            $this->logger->debug("Retrieving next table definition from queue", ['table' => $item->table, 'hasMoreData' => $item->hasNewData()]);
            if (isset($this->inQueueList[$item->table])) {
                unset($this->inQueueList[$item->table]);
            }
            return $item;
        }
        throw new \RuntimeException("Invalid item in queue");
    }

    public function hasTableDefinitions()
    {
        return !$this->queue->isEmpty();
    }

    public function addTableDefinition(ExportTableDefinition $tableDefinition)
    {
        // should exist already, but double check
        if (!isset($this->tableDefinitionsMap[$tableDefinition->table])) {
            $this->tableDefinitionsMap[$tableDefinition->table] = $tableDefinition;
        }
        if (!isset($this->inQueueList[$tableDefinition->table])) {
            $this->queue->enqueue($tableDefinition);
            $this->inQueueList[$tableDefinition->table] = $tableDefinition;
            $this->logger->debug("QUEUE: Adding table definition to queue", ['table' => $tableDefinition->table]);
        } else {
            $this->logger->debug("QUEUE: Table already exists in queue", ['table' => $tableDefinition->table]);
        }
    }

    public function getKeyDataForTable(ExportTableDefinition $tableDefinition)
    {
        $keyData = [
            'tables' => []
            ,'keys' => []
        ];
        $elements = $this->xmlXPath("//table[@name='" . $tableDefinition->table . "']/column");
        if ($elements !== false) {
            foreach ($elements as $element) {
                $localColumnName = (string)($element->attributes()['name'] ?? null);
                if (isset($localColumnName) && $element->count() > 0) {
                    foreach ($element->children() as $child) {
                        $foreignTableName = (string)($child->attributes()['table'] ?? null);
                        $foreignColumnName = (string)($child->attributes()['column'] ?? null);
                        $keyType = $child->getName();
                        if (!empty($foreignTableName) && !empty($foreignColumnName)) {
                            $foreignTableDefinition = $this->tableDefinitionsMap[$foreignTableName] ?? $this->createTableDefinition($foreignTableName);
                            $keyData['tables'][$foreignTableName] = $foreignTableDefinition;
                            $key = new ExportKeyDefinition();
                            $key->foreignKeyTable = $foreignTableName;
                            $key->foreignKeyColumn = $foreignColumnName;
                            $key->localTable = $tableDefinition->table;
                            $key->localColumn = $localColumnName;
                            $key->keyType = $keyType;
                            if ($this->keyFilterer->hasMultipleKeysForColumn($key)) {
                                $keys = $this->keyFilterer->filterMultipleKeys($key);
                                foreach ($keys as $key) {
                                    $keyData['keys'][] = $key;
                                }
                            } else {
                                $key = $this->keyFilterer->filterKey($key);
                                $keyData['keys'][] = $key;
                            }
                        }
                    }
                }
            }
        }
        // for any hard-coded denormalized tables we need to handlethose here.
        if ($this->hasDenormalizedKeys($tableDefinition)) {
            $keys = $this->getDenormalizedKeys($tableDefinition);
            foreach ($keys as $key) {
                $foreignTableName = $key->foreignKeyTable;
                $foreignTableDefinition = $this->getTableDefinitionForTable($foreignTableName) ?? $this->createTableDefinition($foreignTableName);
                $keyData['tables'][$foreignTableName] = $foreignTableDefinition;
                $keyData['keys'][] = $key;
            }
        }
        return $keyData;
    }

    private function hasDenormalizedKeys(ExportTableDefinition $tableDefinition)
    {
        if ($tableDefinition->table === 'patient_data' || $tableDefinition->table === 'patient_history') {
            return true;
        }
    }

    private function getDenormalizedKeys(ExportTableDefinition $tableDefinition)
    {
        // these columns are denormalized data and have the ids separated by a pipe (|)
        if ($tableDefinition->table === 'patient_data' || $tableDefinition->table == 'patient_history') {
            $care_team_provider = new ExportKeyDefinition();
            $care_team_provider->localTable = $tableDefinition->table;
            $care_team_provider->localColumn = "care_team_provider";
            $care_team_provider->foreignKeyColumn = "id";
            $care_team_provider->foreignKeyTable = "users";
            $care_team_provider->isDenormalized = true;
            $care_team_provider->denormalizedKeySeparator = "|";

            $care_team_facility = new ExportKeyDefinition();
            $care_team_facility->localTable = $tableDefinition->table;
            $care_team_facility->localColumn = "care_team_facility";
            $care_team_facility->foreignKeyColumn = "id";
            $care_team_facility->foreignKeyTable = "facility";
            $care_team_provider->isDenormalized = true;
            $care_team_provider->denormalizedKeySeparator = "|";
            return [$care_team_provider, $care_team_facility];
        }
        return [];
    }

    public function createTableDefinition(string $tableName)
    {
        $tableDef = new ExportTableDefinition($tableName);
        $primaryKeys = $this->xmlXPath("//table[@name='" . $tableName . "']/primaryKey");
        $pkBySequence = [];
        foreach ($primaryKeys as $primaryKey) {
            $columnName = (string)($primaryKey->attributes()['column']) ?? "";
            $sequenceNo = (int)($primaryKey->attributes()['sequenceNumberInPK'] ?? 0);
            $pkBySequence[$sequenceNo] = $columnName;
        }
        foreach ($pkBySequence as $sequenceNo => $columnName) {
            // since we add the sequence by integer, it will be in order and we can add the primary keys here so we create our hashes properly.
            $tableDef->addPrimaryKey($columnName);
        }
        $selectQuery = $this->dataFilterer->getSelectQueryForTable($tableName);
        if ($selectQuery != '*') {
            $tableDef->setSelectColumns($selectQuery);
        }
        $this->tableDefinitionsMap[$tableName] = $tableDef;
        return $tableDef;
    }
}
