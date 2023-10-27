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
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Utils\FileUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\Export\ExportException;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJob;
use OpenEMR\Modules\EhiExporter\Models\EhiExportJobTask;
use OpenEMR\Modules\EhiExporter\Services\EhiExportJobService;
use OpenEMR\Modules\EhiExporter\Services\EhiExportJobTaskResultService;
use OpenEMR\Modules\EhiExporter\Services\EhiExportJobTaskService;
use OpenEMR\Services\DocumentService;
use OpenEMR\Services\ListService;
use OpenEMR\Modules\EhiExporter\ExportState;
use OpenEMR\Modules\EhiExporter\ExportTableDefinition;
use OpenEMR\Modules\EhiExporter\ExportKeyDefinition;
use Ramsey\Uuid\Rfc4122\UuidV4;
use Twig\Environment;

class EhiExporter
{
    const EHI_DOCUMENT_CATEGORY = "EHI Export Zip File";

    /**
     * The folder name that export documents are stored in.
     */
    const EHI_DOCUMENT_FOLDER = 'system-ehi-export';

    const PARENT_FK_TABLES_TRAVERSAL = ['patient_data', 'insurance_data', 'eligibility_verification', 'form_vitals', 'lbt_data',  'lbf_data', 'patient_tracker', 'therapy_groups'];
    const ZIP_MIME_TYPE = "application/zip";
    const PATIENT_TASK_BATCH_FETCH_LIMIT = 5000;
    const CYCLE_MAX_ITERATIONS_LIMIT = 500;

    private SystemLogger $logger;
    private EhiExportJobTaskService $taskService;
    public function __construct(private $modulePublicDir, private $modulePublicUrl, private $xmlConfigPath, private Environment $twig)
    {
        $this->logger = new SystemLogger();
        $this->taskService = new EhiExportJobTaskService();
        $this->jobService = new EhiExportJobService();
        $this->taskResultService = new EhiExportJobTaskResultService();
        $this->twig = $twig;
    }

    public function exportPatient(int $pid, bool $includePatientDocuments, $defaultZipSize)
    {
        $patientPids = [$pid];
        $job = null;
        try {
            $job = $this->createJobForRequest($patientPids, $includePatientDocuments, $defaultZipSize);
            return $this->processJob($job);
        } catch (\Exception $exception) {
            if ($job !== null) {
                $job->setStatus("failed");
                try {
                    $this->jobService->update($job);
                } catch (\Exception $exception) {
                    $this->logger->errorLogCaller("Failed to mark job as failed ", [$exception->getMessage()]);
                    return $job;
                }
            }
            throw $exception;
        }
    }
    public function exportAll(bool $includePatientDocuments, $defaultZipSize): EhiExportJob
    {
        try {
            $sql = "SELECT pid FROM patient_data"; // We do everything here
            $patientPids = QueryUtils::fetchTableColumn($sql, 'pid', []);
            $job = $this->createJobForRequest($patientPids, $includePatientDocuments, $defaultZipSize);
            return $this->processJob($job);
        } catch (\Exception $exception) {
            if ($job !== null) {
                $job->setStatus("failed");
                try {
                    $this->jobService->update($job);
                } catch (\Exception $exception) {
                    $this->logger->errorLogCaller("Failed to mark job as failed ", [$exception->getMessage()]);
                    return $job;
                }
            }
            throw $exception;
        }
    }

    /**
     * @param array $patientPids
     * @param bool $includePatientDocuments
     * @param int $defaultZipSize
     * @return EhiExportJob
     * @throws \Exception
     */
    private function createJobForRequest(array &$patientPids, bool $includePatientDocuments, $defaultZipSize)
    {

        // TODO: @adunsulag need to store the max size.  If the size is over 4000MB we reject it as the max zip size
        // can be 4GB or 4096 MB which if we have 4000MB of patient documents gives us still 96MB to handle all the db
        // which would be around 1818 patients assuming a patient average doc size of 2.2MB.  96MB of export data should
        // fairly easily cover the DB data for 1818 patients which is highly compressible.
        if ($defaultZipSize > 4000) {
            throw new \InvalidArgumentException("Zip size is too large, please reduce the size to be less than 4000MB");
        }

        $job = new EhiExportJob();
        $job->uuid = UuidV4::uuid4();
        $job->include_patient_documents = $includePatientDocuments;
        $job->addPatientIdList($patientPids);
        $job->setDocumentLimitSize($defaultZipSize * 1024 * 1024); // set our max size in bytes
        $updatedJob = $this->jobService->insert($job);
        return $updatedJob;
    }

    /**
     * @param $job
     * @param $patientPids
     * @return mixed
     * @throws \Exception
     */
    private function processJob(EhiExportJob $job)
    {
        $jobTasks = $this->createExportTasksFromJob($job);
        if (empty($jobTasks)) {
            $job->setStatus("failed"); // no tasks to process, we mark as failed.
        }
        foreach ($jobTasks as $jobTask) {
            $jobTask = $this->processJobTask($jobTask);
            if ($jobTask->getStatus() == 'failed') {
                $job->setStatus($jobTask->getStatus());
            }
            $job->addJobTask($jobTask);
        };
        if ($job->getStatus() != 'failed') {
            $job->setStatus('completed');
        }
        return $this->jobService->update($job);
    }

    /**
     * @param EhiExportJob $job
     * @param array $patientPids
     * @return array
     * @throws \Exception
     */
    private function createExportTasksFromJob(EhiExportJob $job)
    {
        $hasMorePatients = true;
        $iterations = -1;
        $fetchLimit = self::PATIENT_TASK_BATCH_FETCH_LIMIT;
        $tasks = [];
        $task = new EhiExportJobTask();
        $task->ehi_export_job_id = $job->getId();
        $task->ehiExportJob = $job;
        $jobPatientIds = $job->getPatientIds();
        $jobPatientIdsCount = count($jobPatientIds);

        $currentDocumentSize = 0; // we want to start at 0 for our iterations
        while ($hasMorePatients && $iterations++ < self::CYCLE_MAX_ITERATIONS_LIMIT) {
            $limitPos = $iterations * $fetchLimit;
            $fetch = ($limitPos + $fetchLimit) >= $jobPatientIdsCount ? ($jobPatientIdsCount - $limitPos) : $fetchLimit;
            $pidSlice = array_slice($jobPatientIds, $limitPos, $fetch);
            $sql = "SELECT sum(size) AS total_size,foreign_id AS pid FROM `documents` WHERE foreign_id > 0 AND foreign_id IN ( "
                . str_repeat("?, ", count($pidSlice) - 1) . "? )  GROUP BY foreign_id ";

            $patientDocumentSizes = QueryUtils::fetchRecords($sql, $pidSlice);
            $recordCount = count($patientDocumentSizes);
            if ($recordCount < $fetchLimit) {
                $hasMorePatients = false;
            }
            for ($i = 0; $i < $recordCount; $i++) {
                $currentDocumentSize += intval($patientDocumentSizes[$i]['total_size']);
                $task->addPatientId(intval($patientDocumentSizes[$i]['pid']));
                if ($currentDocumentSize >= $job->getDocumentLimitSize()) {
                    $task = $this->taskService->insert($task);
                    $tasks[] = $task;
                    $task = new EhiExportJobTask();
                    $task->ehi_export_job_id = $job->getId();
                    $task->ehiExportJob = $job;
                    $currentDocumentSize = 0;
                }
            }
        }

        // now handle the patients that have no documents


        // we will do batches of 5000 patients at a time if they have no documents
        $hasMorePatients = true;
        $iterations = -1;
        $fetchLimit = self::PATIENT_TASK_BATCH_FETCH_LIMIT;
        $patientSizePerRecord = 100 * 1024; // average size we estimate to be 100KB per patient in data exports so we will add that up per patient
        // maxes out at 2.5 Million patients which is a lot of patients and should be enough for most use cases
        while ($hasMorePatients && $iterations++ < self::CYCLE_MAX_ITERATIONS_LIMIT) {
            $limitPos = $iterations * $fetchLimit;
            $fetch = ($limitPos + $fetchLimit) >= $jobPatientIdsCount ? ($jobPatientIdsCount - $limitPos) : $fetchLimit;
            $pidSlice = array_slice($jobPatientIds, $limitPos, $fetch);
            $sql = "SELECT pid FROM patient_data LEFT JOIN documents ON patient_data.pid = documents.foreign_id WHERE documents.id IS NULL AND patient_data.pid IN ( "
                . str_repeat("?, ", count($pidSlice) - 1) . "? )";
            $patientRecords = QueryUtils::fetchRecords($sql, $pidSlice);
            $recordCount = count($patientRecords);
            if ($recordCount < $fetchLimit) {
                $hasMorePatients = false;
            }

            for ($i = 0; $i < $recordCount; $i++) {
                $task->addPatientId(intval($patientRecords[$i]['pid']));
                $currentDocumentSize += $patientSizePerRecord;
                if ($currentDocumentSize >= $job->getDocumentLimitSize()) {
                    $task = $this->taskService->insert($task);
                    $tasks[] = $task;
                    $task = new EhiExportJobTask();
                    $task->ehi_export_job_id = $job->getId();
                    $task->ehiExportJob = $job;
                    $currentDocumentSize = 0;
                }
            }
            // at the end we add the task if we have patient ids
            if ($task->hasPatientIds()) {
                // make sure to insert the task
                $task = $this->taskService->insert($task);
                $tasks[] = $task;
            }
        }


        return $tasks;
    }

    private function processJobTask(EhiExportJobTask $jobTask)
    {
        $updatedJobTask = $jobTask;
        try {
            $updatedJobTask = $this->exportBreadthAlgorithm($jobTask);
            $updatedJobTask->setStatus("completed"); // we've finished the task
            $updatedJobTask = $this->taskService->update($updatedJobTask);
        } catch (\Exception $exception) {
            $updatedJobTask->error_message = $exception->getMessage();
            $updatedJobTask->setStatus('failed');
        }
        return $updatedJobTask;
    }

    private function exportBreadthAlgorithm(EhiExportJobTask $jobTask): EhiExportJobTask
    {
        $patientPids = $jobTask->getPatientIds();
        $includePatientDocuments = $jobTask->includePatientDocuments;
        $contents = file_get_contents($this->xmlConfigPath);
        if ($contents === false) {
            throw new \RuntimeException("Failed to find openemr.openemr.xml file");
        }
        $xml = simplexml_load_string($contents);
        $exportState = new ExportState($this->logger, $xml, $jobTask);

        $tableDefinition = new ExportTableDefinition();
        $tableDefinition->table = 'patient_data';
        $pidKey = new ExportKeyDefinition();
        $pidKey->foreignKeyTable = "patient_data";
        $pidKey->foreignKeyColumn = "pid";
        $tableDefinition->addKeyValueList($pidKey, $patientPids);
        $exportState->addTableDefinition($tableDefinition);

        $maxCycleLimit = self::CYCLE_MAX_ITERATIONS_LIMIT;
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
            if ($this->shouldSkipTableDefinition($tableDefinition)) {
                continue;
            }
            // otherwise if we have no records we skip as well.
            $records = $tableDefinition->getRecords();
            if (empty($records)) {
                continue;
            }

            $keyDefinitions = $exportState->getKeyDataForTable($tableDefinition);
            // write out the csv file
            $this->writeCsvFile($jobTask, $records, $tableDefinition->table);
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
        $document = $this->generateZipfile($jobTask, $exportedResult);
        $documentService = new DocumentService();
        $exportedResult->downloadLink = $documentService->getDownloadLink($document->get_id());
        $jobTask->exportedResult = $exportedResult;
        $jobTask->document = $document;
        $jobTask->export_document_id = $document->get_id();
        return $jobTask;
    }

    private function generateZipfile(EhiExportJobTask $jobTask, $exportedResult)
    {
        $zip = new \ZipArchive();

        $tempDir = $GLOBALS['temporary_files_dir'];
        if (!file_exists($tempDir)) {
            throw new \RuntimeException("Could not access globals temporary_files_dir location verify the property is set correctly and the webserver has write acess to the location");
        }

        $zipName = uniqid('ehi-export-') . '.zip';
        $zipOutput = $tempDir . DIRECTORY_SEPARATOR . $zipName;
        $openStatus = $zip->open($zipOutput, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        if ($openStatus == false) {
            throw new \RuntimeException("Failed to open zip archive at location " . $zipOutput);
        }
        foreach ($exportedResult->exportedTables as $result) {
            if ($this->shouldExportAdditionalAssets($result->tableName)) {
                $this->exportAdditionalAssets($zip, $result->tableName);
            }
            $taskResultContents = $this->taskResultService->getResultDataForJob($jobTask, $result->tableName);
            $addedToZip = $zip->addFromString($result->tableName . '.csv', $taskResultContents);
            if (!$addedToZip) {
                $this->logger->errorLogCaller("Failed to add " . $result->tableName . " to zip file");
                throw new \Exception("Failed to add " . $result->tableName . " to zip file");
            }
            //$zip->addFile($this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . $result->tableName . '.csv', $result->tableName . '.csv');
        }
        if ($jobTask->ehiExportJob->include_patient_documents) {
            $this->addPatientDocuments($exportedResult, $zip, $jobTask->getPatientIds());
        }
        $this->addDocumentationReadme($zip);
        $saved = $zip->close();
        if (!$saved) {
            $this->logger->errorLogCaller("Failed to save zip file ", ['zipName' => $zipName]);
            throw new \Exception("Failed to generate zip file for job " . $jobTask->ehi_task_id . " zip status is " . $zip->status);
        }
        unset($zip);
        $document = $this->createDatabaseDocumentFromZip($jobTask, $zipOutput, $zipName);
        // now we remove the zip file
        if (!unlink($zipOutput)) {
            $this->logger->errorLogCaller("Failed to EHI zip file export", ['zipName' => $zipOutput]);
        }
        $this->taskResultService->clearResultsForJob($jobTask);
        return $document;
    }

    private function createDatabaseDocumentFromZip(EhiExportJobTask $jobTask, string $zipLocation, string $zipName): \Document
    {
        $folder = self::EHI_DOCUMENT_FOLDER;
        $categoryId = sqlQuery('Select `id` FROM categories WHERE name=?', [self::EHI_DOCUMENT_CATEGORY]);
        if ($categoryId === false) {
            throw new ExportException("document category id does not exist in system");
        }

        $higherLevelPath = "";
        $pathDepth = 1;
        $owner = $_SESSION['authUserID'];  // userID
        $thumbnailTmpLocation = null;
        $dateExpires = null;
        $data = file_get_contents($zipLocation);
        $document = new \Document();
        // I don't like how we use the $patient_id for the folder... but it is what it is
        $result = $document->createDocument(
            $folder,
            $categoryId,
            $zipName,
            self::ZIP_MIME_TYPE,
            $data,
            $higherLevelPath,
            $pathDepth,
            $owner,
            $thumbnailTmpLocation,
            $dateExpires,
            $jobTask->ehi_task_id,
            EhiExportJobTaskService::TABLE_NAME
        );
        if (!empty($result)) {
            throw new \RuntimeException("Failed to save document for task. Message: " . $result);
        }
        return $document;
    }

    private function exportCustomTables(ExportState $state)
    {
        $this->exportEsignatureData($state);
    }
    private function exportEsignatureData(ExportState $state)
    {
        // need to grab dynamic tables from LBT & LBF
        // looks like I just need to grab the form table and form_encounter table where the pid is present
        // can use the table mapping for all this data including the patient_data table to grab all the pids
        $formsTableDef = $state->getTableDefinitionForTable('forms');
        $formsEncounterTableDef = $state->getTableDefinitionForTable('form_encounter');

        $bind = [];
        if (isset($formsTableDef)) {
            $records = $formsTableDef->getRecords();
            // now we need to grab our esignatures
            if (!empty($records)) {
                $clauses[] = "(`table`='forms' AND tid IN ( " . str_repeat("?, ", count($records) - 1) . "? ) )";
                $bind = array_map(function ($item) {
                    return $item['id'];
                }, $records);
            }
        }
        if (isset($formsEncounterTableDef)) {
            $encounterRecords = $formsEncounterTableDef->getRecords();
            // now we need to grab our esignatures
            if (!empty($encounterRecords)) {
                $clauses[] = "(`table`='form_encounter' AND tid IN ( " . str_repeat("?, ", count($encounterRecords) - 1) . "? ) )";
                $encounterBind = array_map(function ($item) {
                    return $item['encounter'];
                }, $encounterRecords);
                $bind = array_merge($bind, $encounterBind);
            }
        }
        if (!empty($clauses)) {
            $sql = "SELECT * FROM esign_signatures WHERE " . implode(" OR ", $clauses);
            $records = QueryUtils::fetchRecords($sql, $bind);
            $jobTask = $state->getJobTask();
            $this->writeCsvFile($jobTask, $records, 'esign_signatures');
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
                    $this->logger->errorLogCaller("File exists but failed to export to zip", ['path' => $assetsToExport['path']]);
                }
            } else {
                $this->logger->errorLogCaller("Failed to export additional asset as file is missing", ['path' => $assetsToExport['path']]);
            }
        }
    }

    private function writeCsvFile($jobTask, &$records, $tableName)
    {
        $uuidDefinition = UuidRegistry::getUuidTableDefinitionForTable($tableName);
        if (!empty($uuidDefinition)) {
            $convertUuid = true;
        } else {
            $convertUuid = false;
        }
        $columns = QueryUtils::listTableFields($tableName);
        // note I am intentionally avoiding php://temp/maxmemory here which would be more performant but runs a higher risk of files being
        // left around on the hard disk which we do not want to do.  Memory is harder to read against but does run the risk of overloading the server
        // if there isn't enough RAM or if the php ini max memory setting is too low.
        $csvFile = fopen("php://memory", 'r+');
        fputcsv($csvFile, $columns);
        $recordCount = 0;
        foreach ($records as $record) {
            if ($convertUuid) {
                $record['uuid'] = UuidRegistry::uuidToString($record['uuid']);
            }
            fputcsv($csvFile, $record);
            $recordCount++;
        }
        rewind($csvFile);
        $dataContents = stream_get_contents($csvFile);
        // free up memory by closing the connection and run the garbage collector since these files could be potentially
        // huge if there is a lot of patients represented
        fclose($csvFile);
        unset($csvFile);
        $this->taskResultService->insertResult($jobTask, $tableName, $dataContents);
        unset($dataContents);

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
                $this->logger->errorLogCaller("Failed to add document to zip file", ['document' => $docFolder . $docName, 'zipStatus' => $zip->status]);
            } else {
                $docCount++;
            }
        }
        $exportedResult->exportedDocumentCount = $docCount;
    }

    public function getExportSizeSettings()
    {
        $maxDocSize = QueryUtils::fetchSingleValue("select max(size) as size FROM documents WHERE foreign_id != 0", 'size', []);
        $totalPatients = QueryUtils::fetchSingleValue("select count(*) as cnt FROM patient_data", 'cnt', []);
        $freeSpace = disk_free_space($GLOBALS['OE_SITES_BASE']);
        if ($freeSpace === false) {
            $freeSpace = xl("Could not read disk space");
        } else {
            $freeSpace = FileUtils::getHumanReadableFileSize($freeSpace);
        }
        return [
            'php_memory_limit' => ini_get('memory_limit') ?: xl("Unknown")
            ,'max_document_size' => FileUtils::getHumanReadableFileSize($maxDocSize)
            ,'disk_free_space' =>  $freeSpace
            ,'total_patients' => $totalPatients
            ,'default_zip_size' => '500'
        ];
    }

    private function addDocumentationReadme(\ZipArchive $zip)
    {
        $readmeContents = $this->twig->render(Bootstrap::MODULE_NAME . '/README.text.twig', [
            'webBaseUrl' => $GLOBALS['site_addr_oath'] . $GLOBALS['webroot']
            // TODO: @brady.miller do we have a latest certified release version stored anywhere?
            ,'certifiedReleaseVersion' => Bootstrap::CERTIFIED_RELEASE_VERSION
        ]);
        if (!$zip->addFromString("README", $readmeContents)) {
            $this->logger->errorLogCaller("Failed to add README file");
        }
    }

    private function shouldSkipTableDefinition(ExportTableDefinition $tableDefinition)
    {
        // we need to check if the table even exists in the database, some tables do not get installed (such as form tables)
        // without user intervention and we need to skip over those tables
        return !QueryUtils::existsTable($tableDefinition->table);
    }
}
