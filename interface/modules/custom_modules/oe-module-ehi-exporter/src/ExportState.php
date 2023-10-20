<?php

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Common\Logging\SystemLogger;

use OpenEMR\Modules\EhiExporter\ExportResult;
use OpenEMR\Modules\EhiExporter\ExportKeyDefinition;
use OpenEMR\Modules\EhiExporter\ExportTableDefinition;

class ExportState {
    public \SimpleXMLElement $rootNode;
    private \SplQueue $queue;
    private ExportResult $result;
    private array $tableDefinitionsMap;
    private SystemLogger $logger;

    // we use this to make sure if we are scheduled to hit an item again
    private $inQueueList = [];

    public function __construct(SystemLogger $logger, \SimpleXMLElement $node)
    {
        $this->rootNode = $node;
        $this->queue = new \SplQueue();
        $this->result = new ExportResult();
        $this->tableDefinitionsMap = [];
        $this->logger = $logger;
    }

    public function addExportResultTable(string $tableName, int $recordCount) {
        $result = new ExportTableResult();
        $result->tableName = $tableName;
        $result->count = $recordCount;
        $this->result->exportedTables[$tableName] = $result;
        $this->logger->debug("Adding export result table ", ['table' => $tableName, 'count' => $recordCount]);
    }

    public function getExportResult() {
        return $this->result;
    }

    public function xmlXPath(string $xpath) {
        return $this->rootNode->xpath($xpath);
    }

    public function getNextTableDefinition() : ExportTableDefinition {
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

    public function hasTableDefinitions() {
        return !$this->queue->isEmpty();
    }

    public function addTableDefinition(ExportTableDefinition $tableDefinition) {
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
        // //table[@name='patient_data']/column
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
                            $foreignTableDefinition = $this->tableDefinitionsMap[$foreignTableName] ?? new ExportTableDefinition($foreignTableName);
                            $keyData['tables'][$foreignTableName] = $foreignTableDefinition;
                            $key = new ExportKeyDefinition();
                            $key->foreignKeyTable = $foreignTableName;
                            $key->foreignKeyColumn = $foreignColumnName;
                            $key->localTable = $tableDefinition->table;
                            $key->localColumn = $localColumnName;
                            $key->keyType = $keyType;
                            $keyData['keys'][] = $key;
                        }
                    }
                }
            }
        }
        return $keyData;
    }
}
