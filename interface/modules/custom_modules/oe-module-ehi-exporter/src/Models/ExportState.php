<?php

/**
 * Represents the state of an export operation holding all of the working data that is needed
 * to process the export.  Including the current queue of table definitions to export, the xml meta table
 * information as well as the xml concrete table information.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter\Models;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportFormsGroupsEncounterTableDefinition;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportClinicalNotesFormTableDefinition;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportEsignatureTableDefinition;
use OpenEMR\Modules\EhiExporter\Services\ExportKeyDefinitionFilterer;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportOnsiteMailTableDefinition;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportOnsiteMessagesTableDefinition;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportOpenEmrPostCalendarEventsTableDefinition;
use OpenEMR\Modules\EhiExporter\Services\ExportTableDataFilterer;
use OpenEMR\Modules\EhiExporter\Models\ExportTableResult;
use OpenEMR\Modules\EhiExporter\Models;
use OpenEMR\Modules\EhiExporter\Models\ExportResult;
use OpenEMR\Modules\EhiExporter\Models\ExportKeyDefinition;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportTableDefinition;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJobTask;
use OpenEMR\Modules\EhiExporter\TableDefinitions\ExportTrackAnythingFormTableDefinition;

class ExportState
{
    public \SimpleXMLElement $rootNode;
    private \SplQueue $queue;
    private Models\ExportResult $result;
    private array $tableDefinitionsMap;
    private SystemLogger $logger;

    // we use this to make sure if we are scheduled to hit an item again
    private $inQueueList = [];

    private ExportTableDataFilterer $dataFilterer;

    /**
     * @var string the temp directory to use for this export
     */
    private string $tempDir;

    private \SimpleXMLElement $metaNode;

    private ExportKeyDefinitionFilterer $keyFilterer;

    private EhiExportJobTask $jobTask;

    public function __construct(SystemLogger $logger, \SimpleXMLElement $tableNode, \SimpleXMLElement $metaNode, EhiExportJobTask $jobTask)
    {
        $this->rootNode = $tableNode;
        $this->metaNode = $metaNode;
        $this->queue = new \SplQueue();
        $this->result = new Models\ExportResult();
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

    public function xmlMetaXPath(string $xpath)
    {
        return $this->metaNode->xpath($xpath);
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

    public function addTableDefinition(\OpenEMR\Modules\EhiExporter\TableDefinitions\ExportTableDefinition $tableDefinition)
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
                            if (!isset($this->tableDefinitionsMap[$foreignTableName])) {
                                // TODO: @adunsulag is there a better location higher up the chain to do this
                                // or would it be cleaner to have a NOOP table definition that we can use for this?
                                if (!$this->existsTable($foreignTableName)) {
                                    // we are skipping any tables that don't exist due to the fact that they may not be installed
                                    // such as an optional form.
                                    continue;
                                } else {
                                    $foreignTableDefinition = $this->createTableDefinition($foreignTableName);
                                }
                            } else {
                                $foreignTableDefinition = $this->tableDefinitionsMap[$foreignTableName];
                            }
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

    private function hasDenormalizedKeys(\OpenEMR\Modules\EhiExporter\TableDefinitions\ExportTableDefinition $tableDefinition)
    {
        if ($tableDefinition->table === 'patient_data' || $tableDefinition->table === 'patient_history') {
            return true;
        }
    }

    private function getDenormalizedKeys(\OpenEMR\Modules\EhiExporter\TableDefinitions\ExportTableDefinition $tableDefinition)
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
        // need to make sure we sanitize this
        $safeTableName = QueryUtils::escapeTableName($tableName);
        // we are going to do our safe escaping here so we don't have to do it in the rest of the code.
        $tableDef = $this->exportTableDefininitionFactory($safeTableName);
        $primaryKeys = $this->xmlXPath("//table[@name='" . $safeTableName . "']/primaryKey");
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
        // this will be used to make sure we don't have any sql injection attacks
        $safeColumnNames = QueryUtils::listTableFields($safeTableName);
        $tableDef->setColumnNames($safeColumnNames);
        $this->dataFilterer->generateSelectQueryForTableFromMetadata($tableDef, $this->metaNode);
        $this->tableDefinitionsMap[$safeTableName] = $tableDef;
        return $tableDef;
    }

    private function exportTableDefininitionFactory(string $tableName)
    {
        // for specific tables that we need to do special handling with
        if ($tableName == ExportOnsiteMessagesTableDefinition::TABLE_NAME) {
            return new ExportOnsiteMessagesTableDefinition($tableName);
        } else if ($tableName == ExportOnsiteMailTableDefinition::TABLE_NAME) {
            return new ExportOnsiteMailTableDefinition($tableName);
        } else if ($tableName == ExportEsignatureTableDefinition::TABLE_NAME) {
            return new ExportEsignatureTableDefinition($tableName);
        } else if ($tableName == ExportOpenEmrPostCalendarEventsTableDefinition::TABLE_NAME) {
            return new ExportOpenEmrPostCalendarEventsTableDefinition($tableName);
        } else if ($tableName == ExportClinicalNotesFormTableDefinition::TABLE_NAME) {
            return new ExportClinicalNotesFormTableDefinition($tableName);
        } else if ($tableName == ExportFormsGroupsEncounterTableDefinition::TABLE_NAME) {
            return new ExportFormsGroupsEncounterTableDefinition($tableName);
        } else if ($tableName == ExportTrackAnythingFormTableDefinition::TABLE_NAME) {
            return new ExportTrackAnythingFormTableDefinition($tableName);
        }
        return new \OpenEMR\Modules\EhiExporter\TableDefinitions\ExportTableDefinition($tableName);
    }

    private function existsTable(string $foreignTableName)
    {
        return QueryUtils::existsTable($foreignTableName);
    }
}
