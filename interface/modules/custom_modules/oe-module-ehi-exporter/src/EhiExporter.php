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
use OpenEMR\Common\Uuid\UuidRegistry;

class EhiExporter
{
    public function __construct(private $modulePublicDir, private $modulePublicUrl)
    {
        // TODO: @adunsulag look at getting the write directory to go to the right location.
    }

    public function exportAll() : ExportResult {
        // grab the xml file from schemaspy in public/ehi-docs/openemr.openemr.xml file for this module
        // create an xml document from the file
        // use xpath on xml document to grab table[@name="patient_data"] element node
        // use xpath on table element node to grab column[@name="pid"] element nodes
        // use xpath on column element nodes to grab child[foreignKey] element nodes
        // loop through foreignKey element nodes and grab attribute[@name="table"] value
        // construct sql query to grab all data from each table where the pid column for that table is in the pid column of the patient_data table
        // create a csv output file for each table
        // loop through each row of the sql query result and write each row to the csv output file
        // zip all csv output files into a single zip file
        // create a link to the zip file
        // return the link

        $contents = file_get_contents( $this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . 'openemr.openemr.xml');
        if ($contents === false) {
            throw new \RuntimeException("Failed to find openemr.openemr.xml file");
        }
        $xml = simplexml_load_string($contents);
        $tableNode = $xml->xpath("//table[@name='patient_data']");
        $columnNode = $xml->xpath("//table[@name='patient_data']/column[@name='pid']");
        $foreignKeyTables = $xml->xpath("//table[@name='patient_data']/column[@name='pid']/child[@foreignKey]");
        $tables = [];
        foreach ($foreignKeyTables as $foreignKeyTable) {
            $tableName = (string)($foreignKeyTable->attributes()['table']);
            $joinColumn = (string)($foreignKeyTable->attributes()['column']);
//            $parentNode = current($foreignKeyTable->xpath('parent::*'));
            $tables[$tableName] = $joinColumn ?? null; // should always have column
        }
        $tables['patient_data'] = 'pid';
        $exportedResult = new ExportResult();
        $sql = "SELECT UNIQUE pid FROM patient_data"; // We do everything here
        $patientPids = QueryUtils::fetchTableColumn($sql, 'pid', []);
        $patientPids = array_map('intval', $patientPids);
        $inClause = "IN (" . str_repeat('?,', count($patientPids) - 1) . "?)";
        foreach ($tables as $table => $columnName) {
            $convertUuid = false;
            $columns = QueryUtils::listTableFields($table);
            $records = $this->getRecordsForTable($table, $columnName, $inClause, $patientPids);
            $uuidDefinition = UuidRegistry::getUuidTableDefinitionForTable($table);
            if (!empty($uuidDefinition)) {
                $convertUuid = true;
            }
            if (!empty($records)) {

                // need to check the xml table definition and see if the table has foreign keys pointing to the current table
                // if so, then we need to grab the foreign key column name(s) to add to our table exporter process
                // if not, we just export the table as is
                // as we loop through each record, we need to save off the foreign key column values with the table name so we can avoid cyclical loops
                // we also need to save off the primary key value of this table to make sure we avoid fetching the currently exported record again

                $csvFile = fopen($this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . $table . '.csv', 'w');
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
                $exportedTable = new ExportTableResult();
                $exportedTable->count = $recordCount;
                $exportedTable->tableName = $table;
                $exportedResult->exportedTables[] = $exportedTable;
            }
        }
        $zip = new \ZipArchive();
        $openStatus = $zip->open($this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . 'ehi-export.zip', \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($exportedResult->exportedTables as $result) {
            $zip->addFile($this->modulePublicDir . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . $result->tableName . '.csv', $result->tableName . '.csv');
        }
        $saved = $zip->close();
        $exportedResult->downloadLink = $this->modulePublicUrl . DIRECTORY_SEPARATOR . 'ehi-docs' . DIRECTORY_SEPARATOR . 'ehi-export.zip';
        return $exportedResult;
    }
    private function getPrimaryKeyForTable(&$xml, $tableName) {
        // for now we are only going to work with the first primary key
        $primaryKeys = $xml->xpath("//table[@name='" . $tableName . "']/primaryKey");
        if (!empty($primaryKeys)) {
            return (string)(current($primaryKeys)->attributes()['column']);
        }
    }
    private function getForeignKeyTableDefinitionsForTable(&$xml, $tableName, $primaryKey) {
        $foreignKeyTables = $xml->xpath("//table[@name='" . $tableName . "']/column[@name='"  . $primaryKey. "']/child[@foreignKey]");
        $tables = [];
        foreach ($foreignKeyTables as $foreignKeyTable) {
            $tableName = (string)($foreignKeyTable->attributes()['table']);
            $joinColumn = (string)($foreignKeyTable->attributes()['column']);
//            $parentNode = current($foreignKeyTable->xpath('parent::*'));
            $tables[$tableName] = $joinColumn ?? null; // should always have column
        }
        return $tables;
    }

    private function getRecordsForTable($table, $columnName, &$inClause, &$patientPids) {

        $safeTableName = QueryUtils::escapeTableName($table);
        $escapeColumnName = QueryUtils::escapeColumnName($columnName);
        $tableQuery = "SELECT * FROM $safeTableName WHERE $escapeColumnName $inClause";

        if ($table == 'extended_log') {
            $tableQuery .= " AND event IN (SELECT option_id FROM list_options WHERE list_id = 'disclosure_type' AND activity = 1) ";
        }
        return QueryUtils::fetchRecords($tableQuery, $patientPids, false);
    }
}
