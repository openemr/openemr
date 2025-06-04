<?php

/**
 * Standard Services Base class
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldStatementResolver;
use OpenEMR\Validators\ProcessingResult;
use Particle\Validator\Exception\InvalidValueException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

require_once(__DIR__  . '/../../custom/code_types.inc.php');

class BaseService
{
    /**
     * Passed in data should be vetted and fully qualified from calling service class
     * Expect to see some search helpers here as well.
     */
    private $table;
    private $fields;
    private $autoIncrements;

    /**
     * @var SystemLogger
     */
    private $logger;

    private const PREFIXES = array(
        'eq' => "=",
        'ne' => "!=",
        'gt' => ">",
        'lt' => "<",
        'ge' => ">=",
        'le' => "<=",
        'sa' => "",
        'eb' => "",
        'ap' => ""
    );

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * Default constructor.
     */
    public function __construct($table)
    {
        $this->table = $table;
        $this->fields = sqlListFields($table);
        $this->autoIncrements = self::getAutoIncrements($table);
        $this->setLogger(new SystemLogger());
        $this->eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function setEventDispatcher(EventDispatcher $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }

    /**
     * Get the name of our base database table
     *
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the fields/column-names on the database table
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Return the fields that should be used in a standard select clause.  Can be overwritten by inheriting classes
     * @param string $alias if the field should be prefixed with a table alias
     * @param string $columnPrefix prefix to add to each field, used if there is a possibility of column name conflicts in select statement, prefix's can only be ascii alphabetic and the underscore characters
     * @return array
     */
    public function getSelectFields(string $tableAlias = '', string $columnPrefix = ""): array
    {
        $tableAlias = trim($tableAlias);
        if ($tableAlias == '') {
            $tableAlias = '`' . $this->getTable() . '`';
        }
        // only allow ascii characters and underscore
        $columnPrefix = preg_replace("/[^a-z_]+/i", "", $columnPrefix);
        $tableAlias .= ".";
        // since we are often joining a bunch of fields we need to make sure we normalize our regular field array
        // by adding the table name for our own table values.
        $fields = $this->getFields();
        $normalizedFields = [];
        // processing is cheap
        foreach ($fields as $field) {
            $normalizedFields[] = $tableAlias . '`' . $columnPrefix . $field . '`';
        }

        return $normalizedFields;
    }

    public function getUuidFields(): array
    {
        return [];
    }

    /**
     * Allows sub classes to grab additional table joins to add to the select query.  Each join table definition needs
     * to be in the following format:
     * [
     *  'table' => JOIN_TABLE_NAME, 'alias' => JOIN_TABLE_ALIAS, 'type' => JOIN_TYPE(left,right,outer,etc)
     *  , 'column' => TABLE_COLUMN_NAME, 'join_column' => JOIN_TABLE_COLUMN_NAME]
     * ]
     *
     * An example of a join on the users table joining against list_options like so:
     * ['table' => 'list_options', 'alias' => 'abook', 'type' => 'LEFT JOIN',
     *     'column' => 'abook_type', 'join_column' => 'option_id']
     *
     * @return array
     */
    public function getSelectJoinTables(): array
    {
        return [];
    }

    /**
     * queryFields
     * Build SQL Query for Selecting Fields
     *
     * @param array $map
     * @return array
     */
    public function queryFields($map = null, $data = null)
    {
        if ($data == null || $data == "*" || $data == "all") {
            $value = "*";
        } else {
            $value = implode(", ", $data);
        }
        $sql = "SELECT $value from $this->table";
        return $this->selectHelper($sql, $map);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * buildInsertColumns
     * Build an insert set and bindings
     *
     * @param array $passed_in
     * @param array $options configuration options for building.
     *                  null_value defines what NULL should be stored as in the table, default is empty string ''
     * @return array
     */
    protected function buildInsertColumns($passed_in = array(), $options = array())
    {
        $keyset = '';
        $bind = array();
        $result = array();
        $null_value = array_key_exists('null_value', $options) ? $options['null_value'] : '';

        foreach ($passed_in as $key => $value) {
            // Ensure auto's not passed in.
            if (in_array($key, array_column($this->autoIncrements, 'Field'))) {
                continue;
            }
            // include existing columns
            if (!in_array($key, $this->fields)) {
                continue;
            }
            if ($value == 'YYYY-MM-DD' || $value == 'MM/DD/YYYY') {
                $value = "";
            } elseif ($value === "NULL") {
                // make it consistent with our update columns... I really don't like this magic string constant
                // if someone intends to actually store the value NULL as a string this will break....
                $value = $null_value;
            }
            if ($value === null || $value === false) {
                $value = $null_value;
            }
            if (!empty($key)) {
                $keyset .= ($keyset) ? ", `$key` = ? " : "`$key` = ? ";
                // for dates which should be saved as null
                if (empty($value) && (strpos($key, 'date') !== false)) {
                    $bind[] = null;
                } else {
                    $bind[] = ($value === null || $value === false) ? $null_value : $value;
                }
            }
        }

        $result['set'] = $keyset;
        $result['bind'] = $bind;

        return $result;
    }

    /**
     * buildUpdateColumns
     * Build an update set and bindings
     *
     * @param array $passed_in
     * @param array $options configuration options for building.
     *                       null_value defines what NULL should be stored as in the table, default is empty string ''
     * @return array
     */
    protected function buildUpdateColumns($passed_in = array(), $options = array())
    {
        $keyset = '';
        $bind = array();
        $result = array();
        // can't use ??,empty, or isset as null_value could be NULL.  We have to deal with legacy which defaults to ''
        $null_value = array_key_exists('null_value', $options) ? $options['null_value'] : '';

        foreach ($passed_in as $key => $value) {
            if (in_array($key, array_column($this->autoIncrements, 'Field'))) {
                continue;
            }
            // exclude uuid columns
            if ($key == 'uuid') {
                continue;
            }
            // exclude pid columns
            if ($key == 'pid') {
                continue;
            }
            if (!in_array($key, $this->fields)) {
                // placeholder. could be for where clauses
                /*
                 * // Patched out 11/15/22 to match buildInsertColumns()
                 * WHERE part should be handled by calling method.
                 * Also prevents adding a bind because of a missing column in query part.
                 * $bind[] = ($value == 'NULL') ? $null_value : $value;
                */
                continue;
            }
            if ($value == 'YYYY-MM-DD' || $value == 'MM/DD/YYYY') {
                $value = "";
            }
            if (
                (
                    $value === null
                    || $value === false
                )
                && (strpos($key, 'date') === false)
            ) {
                // in case unwanted values passed in.
                continue;
            }
            if (!empty($key)) {
                $keyset .= ($keyset) ? ", `$key` = ? " : "`$key` = ? ";
                if (empty($value) && (strpos($key, 'date') !== false)) {
                    $bind[] = null;
                } else {
                    $bind[] = $value;
                }
            }
        }

        $result['set'] = $keyset;
        $result['bind'] = $bind;

        return $result;
    }

    /**
     * @param $table
     * @return array
     */
    private static function getAutoIncrements($table)
    {
        $results = array();
        $rtn = sqlStatementNoLog(
            "SHOW COLUMNS FROM $table Where extra Like ?",
            array('%auto_increment%')
        );
        while ($row = sqlFetchArray($rtn)) {
            array_push($results, $row);
        }

        return $results;
    }

    /**
     * Shared getter for SQL selects.
     *
     * @param $sqlUpToFromStatement - The sql string up to (and including) the FROM line.
     * @param $map                  - Query information (where clause(s), join clause(s), order, data, etc).
     * @return array of associative arrays
     */
    public function selectHelper($sqlUpToFromStatement, $map)
    {
        $records = QueryUtils::selectHelper($sqlUpToFromStatement, $map);
        if ($records !== null) {
            $records = is_array($records) ? $records : [$records];
        }
        return $records;
    }

    /**
     * Build and Throw Invalid Value Exception
     *
     * @param $message              - The error message which will be displayed
     * @param $type                 - Type of Exception
     * @throws InvalidValueException
     */
    public static function throwException($message, $type = "Error")
    {
        throw new InvalidValueException($message, $type);
    }

    // Taken from -> https://stackoverflow.com/a/24401462
    /**
     * Validate Date and Time
     *
     * @param $dateString              - The Date string which is to be verified
     * @return bool
     */
    public static function isValidDate($dateString)
    {
        return (bool) strtotime($dateString);
    }

    /**
     * Check and Return SQl (AND | OR) Operators
     *
     * @param $condition              - Boolean to check AND | OR
     * @return string of (AND | OR) Operator
     */
    public static function sqlCondition($condition)
    {
        return (string) $condition ? ' AND ' : ' OR ';
    }


    /**
     * Fetch ID by UUID of Resource
     *
     * @param string $uuid              - UUID of Resource
     * @param string $table             - Table reffering to the ID field
     * @param string $field             - Identifier field
     * @return false if nothing found otherwise return ID
     */
    public static function getIdByUuid($uuid, $table, $field)
    {
        $sql = "SELECT $field from $table WHERE uuid = ?";
        $result = sqlQuery($sql, array($uuid));
        return $result[$field] ?? false;
    }

    /**
     * Fetch UUID by ID of Resource
     *
     * @param string $id                - ID of Resource
     * @param string $table             - Table reffering to the UUID field
     * @param string $field             - Identifier field
     * @return false if nothing found otherwise return UUID
     */
    public static function getUuidById($id, $table, $field)
    {
        $table = escape_table_name($table);
        $sql = "SELECT uuid from $table WHERE $field = ?";
        $result = sqlQuery($sql, array($id));
        return $result['uuid'] ?? false;
    }

    /**
     * Process DateTime as per FHIR Standard
     *
     * @param string $date             - DateTime String
     * @return array processed prefix with value
     */
    public static function processDateTime($date)
    {
        $processedDate = array();
        $result = substr($date, 0, 2);

        // Assign Default
        $processedDate['prefix'] = self::PREFIXES['eq'];
        $processedDate['value'] = $date;

        foreach (self::PREFIXES as $prefix => $value) {
            if ($prefix == $result) {
                $date = substr($date, 2);
                $processedDate['prefix'] = $value;
                $processedDate['value'] = $date;
                return $processedDate;
            }
        }

        return $processedDate;
    }

    /**
     * Generates New Primary Id
     *
     * @param string $idField                   - Name of Primary Id Field
     * @param string $table                     - Name of Table
     * @return string Generated Id
     */
    public function getFreshId($idField, $table)
    {
        $resultId = sqlQuery("SELECT MAX($idField)+1 AS $idField FROM $table");
        return $resultId[$idField] === null ? 1 : intval($resultId[$idField]);
    }

    /**
     * Filter all the Whitelisted Fields from the given Fields Array
     *
     * @param array $data                       - Fields passed by user
     * @param array $whitelistedFields          - Whitelisted Fields, if empty defaults to service table fields
     * @return array Filtered Data
     */
    public function filterData($data, $whitelistedFields = null)
    {
        // use the current service fields for our whitelist if its empty
        if (empty($whitelistedFields)) {
            $whitelistedFields = $this->getFields();
        }

        return array_filter(
            $data,
            function ($key) use ($whitelistedFields) {
                return in_array($key, $whitelistedFields);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Returns a list of records matching the search criteria.
     * Search criteria is conveyed by array where key = field/column name, value is an ISearchField
     * If an empty array of search criteria is provided, all records are returned.
     *
     * The search will grab the intersection of all possible values if $isAndCondition is true, otherwise it returns
     * the union (logical OR) of the search.
     *
     * More complicated searches with various sub unions / intersections can be accomplished
     * through a CompositeSearchField that allows you to combine multiple search clauses on a single search field.
     *
     * @param ISearchField[] $search Hashmap of string => ISearchField
     *                                   where the key is the field name of the search field
     * @param bool $isAndCondition Whether to join each search field with a logical OR or a logical AND.
     * @return ProcessingResult The results of the search.
     */
    public function search($search, $isAndCondition = true)
    {
        $processingResult = new ProcessingResult();
        try {
            $selectFields = $this->getSelectFields();

            $selectFields = array_combine(
                $selectFields,
                $selectFields
            ); // make it a dictionary so we can add/remove this.
            $from = [$this->getTable()];
            $sql = "SELECT " . implode(",", array_keys($selectFields)) . " FROM " . implode(",", $from);
            $join = $this->getSelectJoinClauses();
            $whereFragment = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);


            $selectHelperMap = [
                'join' => $join
                , 'where' => $whereFragment->getFragment()
                , 'data' => $whereFragment->getBoundValues()
            ];
            $records = $this->selectHelper($sql, $selectHelperMap);

            if (!empty($records)) {
                foreach ($records as $row) {
                    $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                    $processingResult->addData($resultRecord);
                }
            }
        } catch (SearchFieldException $exception) {
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    /**
     * Allows any mapping data conversion or other properties needed by a service to be returned.
     * @param $row The record returned from the database
     */
    protected function createResultRecordFromDatabaseResult($row)
    {
        $uuidFields = $this->getUuidFields();
        if (empty($uuidFields)) {
            return $row;
        } else {
            // convert all of our byte columns to strings
            foreach ($uuidFields as $fieldName) {
                if (isset($row[$fieldName])) {
                    $row[$fieldName] = UuidRegistry::uuidToString($row[$fieldName]);
                }
            }
        }
        return $row;
    }

    /**
     * Convert Diagnosis Codes String to Code:Description Array
     *
     * @param string $diagnosis                 - All Diagnosis Codes
     * @return array Array of Code as Key mapped to an array containing the code,
     *                   code_type, description, and system (URI or OID if found)
     */
    protected function addCoding($diagnosis)
    {
        if (empty($diagnosis)) {
            return [];
        }
        $codesService = new CodeTypesService();
        $diags = explode(";", $diagnosis);
        $diagnosis = array();
        foreach ($diags as $diag) {
            $parsedCode = $codesService->parseCode($diag);
            $codeType = $parsedCode['code_type'];
            $code = $parsedCode['code'];
            $system = $codesService->getSystemForCodeType($codeType);
            $codedesc = $codesService->lookup_code_description($diag);
            $diagnosis[$code] = [
                'code' => $code
                , 'description' => $codedesc
                , 'code_type' => $codeType
                , 'system' => $system
            ];
        }
        return $diagnosis;
    }

    /**
     * Split IDs and Process the fields subsequently
     *
     * @param string $fields                    - All IDs sperated with | sign
     * @param string $table                     - Name of the table of targeted ID
     * @param string $primaryId                 - Name of Primary ID field
     * @return array Array UUIDs
     */
    protected function splitAndProcessMultipleFields($fields, $table, $primaryId = "id")
    {
        $fields = explode("|", $fields);
        $result = array();
        foreach ($fields as $field) {
            $data = sqlQuery("SELECT uuid
                    FROM $table WHERE $primaryId = ?", array($field));
            if ($data) {
                array_push($result, UuidRegistry::uuidToString($data['uuid']));
            }
        }
        return $result;
    }

    protected function getSelectJoinClauses()
    {
        $joins = $this->getSelectJoinTables();
        $clause = '';
        if (empty($joins)) {
            return $clause;
        }
        foreach ($joins as $tableDefinition) {
            // if it is a temporary table that starts with a ( then we don't need to wrap it in backticks
            $table = $tableDefinition['table'][0] === '(' ? $tableDefinition['table'] : '`' . $tableDefinition['table'] . '`';

            $clause .= $tableDefinition['type'] . ' ' . $table . " `{$tableDefinition['alias']}` "
                . ' ON ';
            if (isset($tableDefinition['join_clause'])) {
                $clause .= $tableDefinition['join_clause'];
            } else {
                $table = $tableDefinition['join_table'] ?? $this->getTable();
                $clause .= "`" . $table . '`.`' . $tableDefinition['column']
                . '` = `' . $tableDefinition['alias'] . '`.`' . $tableDefinition['join_column'] . '` ';
            }
        }
        return $clause;
    }
}
