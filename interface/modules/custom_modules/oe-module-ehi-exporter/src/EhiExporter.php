<?php

/**
 * Main class for EhiExporter for exporting data from the db
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Stephen Nielson <snielson@discoverandchange.com
 * @copyright Copyright (c) 2023 OpenEMR Foundation, Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\EhiExporter;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\ListService;
use OpenEMR\Modules\EhiExporter\ExportState;
use OpenEMR\Modules\EhiExporter\ExportTableDefinition;
use OpenEMR\Modules\EhiExporter\ExportKeyDefinition;

class EhiExporter
{
    const PARENT_FK_TABLES_TRAVERSAL = ['patient_data', 'insurance_data', 'eligibility_verification', 'form_vitals', 'lbt_data',  'lbf_data', 'patient_tracker', 'therapy_groups'];

    private SystemLogger $logger;
    public function __construct(private $modulePublicDir, private $modulePublicUrl)
    {
        // TODO: @adunsulag look at getting the write directory to go to the right location.
        $this->logger = new SystemLogger();
    }

    public function exportPatient(int $pid, bool $includePatientDocuments)
    {
        return $this->exportBreadthAlgorithm([$pid], $includePatientDocuments);
    }
    public function exportAll(bool $includePatientDocuments): ExportResult
    {
        $sql = "SELECT UNIQUE pid FROM patient_data"; // We do everything here
        $patientPids = QueryUtils::fetchTableColumn($sql, 'pid', []);
        $patientPids = array_map('intval', $patientPids);
        return $this->exportBreadthAlgorithm($patientPids, $includePatientDocuments);
    }

    private function exportBreadthAlgorithm(array $patientPids, bool $includePatientDocuments)
    {
        $contents = file_get_contents($this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . 'openemr.openemr.xml');
        if ($contents === false) {
            throw new \RuntimeException("Failed to find openemr.openemr.xml file");
        }
        $xml = simplexml_load_string($contents);
        $exportState = new ExportState($this->logger, $xml);

        $tableDefinition = new ExportTableDefinition();
        $tableDefinition->table = 'patient_data';
        $pidKey = new ExportKeyDefinition();
        $pidKey->foreignKeyTable = "patient_data";
        $pidKey->foreignKeyColumn = "pid";
        $tableDefinition->addKeyValueList($pidKey, $patientPids);
        $exportState->addTableDefinition($tableDefinition);

        $maxCycleLimit = 500;
        $iterations = 0;

        /**
         * We go through a queue of the tables to do a breadth first traversal of the foreign key
         * links of each table.  We do this so we can grab the largest amount of datasets and minimize
         * rework as much as possible.
         * We grab all of the key definitions for each table and loop primarily through parent relationships
         * (IE where the table has a column with a foreign key that points to another table, ie its parent relationship)
         * In limited cases (such as patient_data and a few others) we grab the child relationships (IE where another table has a column with a foreign key that points to the current table)
         *
         * We loop through each of the tables and we track the actual key values (both FK&PK) in order to avoid grabbing the same datasets
         * at the same time.  Granted this could end up holding a ton of data in memory especially for keys with string values and we will need to bench mark this for performance
         * We write out the data records to disk as a csv file and then record tabulated result data of the total records written.
         * If a table has been processed previously but is reached again via a different key relationship it will be added to the queue
         * to be processed again ONLY IF there is new key values that are added.
         * This means that some tables will be written out to disk multiple times, which creates some redundancy.
         * For simplicity we just grab the entire unioned data set from all of the keys for that table and then rewrite over the same file.
         * Additional optimizations work could be done to make this more efficient but I chose in the interest of time for a working algorithm
         * than a highly efficient algorithm that would take more time to implement.
         *
         * Once the data has been exported, the exporter will grab all of the documents and export them to the zip file
         * as well.  If there are dependent assets for linked tables (such as images in the case of the pain map form)
         * those also get exported.
         *
         * For safety purposes we limit our max cycles that we will iterate through the tables in order to avoid
         * any kind of infinite loop routine.
         */
        while ($exportState->hasTableDefinitions() && $iterations++ <= $maxCycleLimit) {
            $tableDefinition = $exportState->getNextTableDefinitionToProcess();
            $records = $tableDefinition->getRecords();
            if (empty($records)) {
                continue;
            }

            $keyDefinitions = $exportState->getKeyDataForTable($tableDefinition);
            // write out the csv file
            $this->writeCsvFile($records, $tableDefinition->table);
            $exportState->addExportResultTable($tableDefinition->table, count($records));
            $tableDefinition->setHasNewData(false);
            if (!empty($keyDefinitions)) {
                foreach ($keyDefinitions['keys'] as $keyDefinition) {
                    if (!($keyDefinition instanceof ExportKeyDefinition)) {
                        throw new \RuntimeException("Invalid key definition");
                    }
                    $tableDefinition = $keyDefinitions['tables'][$keyDefinition->foreignKeyTable];
                    // we process ALL parent keys, or if it is a child key we only process a select few of these keys.
                    if ($this->shouldProcessForeignKey($keyDefinition)) {
                        foreach ($records as $record) {
                            $keyColumnName = $keyDefinition->localColumn;
                            // we have in some cases a need to override the local value such as with our list_options
                            // table so we can handle some more dynamic values here.
                            if (isset($keyDefinition->localValueOverride)) {
                                $recordValue = $keyDefinition->localValueOverride;
                            } else {
                                $recordValue = $record[$keyColumnName] ?? null;
                            }
                            if (isset($recordValue)) {
                                $tableDefinition->addKeyValue($keyDefinition, $recordValue);
                            }
                        }
                        // we only add it to be processed if there is new data to do so.
                        if ($tableDefinition->hasNewData()) {
                            // if the table already is in the queue the operation is a noop
                            $exportState->addTableDefinition($tableDefinition);
                        }
                    }
                }
            }
        }
        $this->exportCustomTables($exportState);
        if ($iterations > $maxCycleLimit) {
            throw new \RuntimeException("Max iterations reached, check for cyclic dependencies");
        }
        $exportedResult = $exportState->getExportResult();
        $zip = new \ZipArchive();

        $zipName = uniqid('ehi-export-') . '.zip';
        $zipOutput = $this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . $zipName;
        $openStatus = $zip->open($zipOutput, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        // TODO: @adunsulag check openStatus for exceptions
        foreach ($exportedResult->exportedTables as $result) {
            if ($this->shouldExportAdditionalAssets($result->tableName)) {
                $this->exportAdditionalAssets($zip, $result->tableName);
            }
            $zip->addFile($this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . $result->tableName . '.csv', $result->tableName . '.csv');
        }
        if ($includePatientDocuments) {
            $this->addPatientDocuments($exportedResult, $zip, $patientPids);
        }
        $saved = $zip->close();
        $exportedResult->downloadLink = $this->modulePublicUrl . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . $zipName;
        return $exportedResult;
    }

    private function exportCustomTables(ExportState $state) {
        $this->exportEsignatureData($state);
    }
    private function exportEsignatureData(ExportState $state) {
        // need to grab dynamic tables from LBT & LBF
        // looks like I just need to grab the form table and form_encounter table where the pid is present
        // can use the table mapping for all this data including the patient_data table to grab all the pids
        $formsTableDef = $state->getTableDefinitionForTable('forms');
        $formsEncounterTableDef = $state->getTableDefinitionForTable('form_encounter');

        if (isset($formsTableDef)) {
            $records = $formsTableDef->getRecords();
            // now we need to grab our esignatures
            if (!empty($records)) {
                $clauses[] = "(`table`='forms' AND tid IN (" . str_repeat("?, ", count($records) - 1) . "?) )";
            }
        }
        if (isset($formsEncounterTableDef)) {
            $records = $formsEncounterTableDef->getRecords();
            // now we need to grab our esignatures
            if (!empty($records)) {
                $clauses[] = "(`table`='form_encounter' AND tid IN (" . str_repeat("?, ", count($records) - 1) . "?) )";
            }
        }
        if (!empty($clauses)) {
            $sql = "SELECT * FROM esign_signatures WHERE " . implode(" OR ", $clauses);
            $records = QueryUtils::fetchRecords($sql);
            $this->writeCsvFile($records, 'esign_signatures');
        }
    }
    private function shouldProcessForeignKey(ExportKeyDefinition $definition)
    {
        if ($definition->keyType == 'parent') {
            return true; // we process parent keys as we want to traverse all of the data
        }
        if ($definition->keyType == 'child') {
            $tableName = $definition->localTable;
            // TODO: @adunsulag need to test and make sure we get eligibility_verification AND benefit_eligibility as part of our export here.
            $parentTraversalTables = self::PARENT_FK_TABLES_TRAVERSAL;
            return in_array($tableName, $parentTraversalTables);
        }
        return false;
    }

    private function shouldExportAdditionalAssets($tableName)
    {
        $additionalAssets = ['form_painmap'];
        return in_array($tableName, $additionalAssets);
    }
    private function exportAdditionalAssets(\ZipArchive $zip, $tableName)
    {
        $additionalAssets = [
            'form_painmap' => [
                ['name' => 'images/painmap.png', 'path' => $GLOBALS['webserver_root'] . "/interface/forms/painmap/templates/painmap.png"]
            ]
        ];
        $assets = $additionalAssets[$tableName] ?? [];
        foreach ($assets as $assetsToExport) {
            if (file_exists($assetsToExport['path'])) {
                if (!$zip->addFile($assetsToExport['path'], $assetsToExport['name'])) {
                    // TODO: @adunsulag should we throw an exception here?
                    $this->logger->errorLogCaller("File exists but failed to export to zip", ['path' => $assetsToExport['path']]);
                }
            } else {
                $this->logger->errorLogCaller("Failed to export additional asset as file is missing", ['path' => $assetsToExport['path']]);
            }
        }
    }

    private function writeCsvFile(&$records, $tableName)
    {
        $uuidDefinition = UuidRegistry::getUuidTableDefinitionForTable($tableName);
        if (!empty($uuidDefinition)) {
            $convertUuid = true;
        } else {
            $convertUuid = false;
        }
        // TODO: @adunsulag need to look at FhirOperationExportRestController::createExportJobFile
        // for examples of how we are handling all the other exports....
        // we could write all of the csv to a memory stream, grab the string contents and then stuff it into our documents
        // in order to handle the export properly....
        // TODO: @adunsulag consider the security of logging this action so that it can be traced that an export was initiated for 'all' patients or a specific 'patient' so we can track
        // who is receiving the data....
        $columns = QueryUtils::listTableFields($tableName);
        $csvFile = fopen($this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . $tableName . '.csv', 'w');
        fputcsv($csvFile, $columns);
        $recordCount = 0;
        foreach ($records as $record) {
            if ($convertUuid) {
                $record['uuid'] = UuidRegistry::uuidToString($record['uuid']);
            }
            fputcsv($csvFile, $record);
            $recordCount++;
        }
        fclose($csvFile);
        return $recordCount;
    }

    private function getRecordsForTable($table, $columnName, &$inClause, &$patientPids)
    {

        $safeTableName = QueryUtils::escapeTableName($table);
        $escapeColumnName = QueryUtils::escapeColumnName($columnName);
        $tableQuery = "SELECT * FROM $safeTableName WHERE $escapeColumnName $inClause";

        if ($table == 'extended_log') {
            $tableQuery .= " AND event IN (SELECT option_id FROM list_options WHERE list_id = 'disclosure_type' AND activity = 1) ";
        }
        return QueryUtils::fetchRecords($tableQuery, $patientPids, false);
    }

    private function addPatientDocuments(ExportResult $exportedResult, \ZipArchive $zip, array $patientPids)
    {
        $inClause = "IN (" . str_repeat('?,', count($patientPids) - 1) . "?)";
        $documentRecords = $this->getRecordsForTable('documents', 'foreign_id', $inClause, $patientPids);
        $docCount = 0;
        $docFolder = "documents/";
        foreach ($documentRecords as $documentRecord) {
            $documentId = $documentRecord['id'];
            $documentObj = new \Document($documentId);
            $documentContents = $documentObj->get_data();

            // we want to make sure the documents are stored by patient id they can be distinguished here.
            $docName = $documentRecord['foreign_id'] . '/' . $documentObj->get_name();
            // store it inside of a folder called documents
            if (!$zip->addFromString($docFolder . $docName, $documentContents)) {
                // TODO: @adunsulag need to add error logging in the export.
            } else {
                $docCount++;
            }
        }
        $exportedResult->exportedDocumentCount = $docCount;
    }
}
