<?php

/**
 * ListService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ListValidator;
use OpenEMR\Validators\ProcessingResult;

// TODO rewrite this using there new way!
// TODO: @adunsulag should we rename this to be ListOptions service since that is the table it corresponds to?  The lists table is a patient issues table so this could confuse new developers
class ListService extends BaseService
{
    private const LISTS_TABLE = "lists";
    private const PATIENT_TABLE = "patient_data";

    /**
     * @var $listValidator ListValidator
     */
    private $listValidator;

    public function getUuidFields(): array
    {
        //copy past from condition service
        return ['condition_uuid', 'puuid', 'encounter_uuid', 'uuid', 'patient_uuid', 'provider_uuid'];
    }

  /**
   * Default constructor.
   */
    public function __construct()
    {
        parent::__construct(self::LISTS_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::LISTS_TABLE, self::PATIENT_TABLE]);
        $this->listValidator = new ListValidator();
    }

    public function getAll($pid, $list_type, $isAndCondition = true)
    {
        $search = [];
        $orderBy = " ORDER BY date DESC";
        $sql = "SELECT * FROM lists";

        $search['type'] = new StringSearchField('type', [$list_type], SearchModifier::EXACT);
        $search['pid'] = new StringSearchField('pid', [$pid], SearchModifier::EXACT);

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment() . $orderBy;

        $sqlBindArray = $whereClause->getBoundValues();

        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($statementResults)) {

            $resultRecord = $this->createResultRecordFromDatabaseResult($row);

            $processingResult->addData($resultRecord);
        }

        return $processingResult;
    }

    public function getOptionsByListName($list_name, $search = array())
    {
        $sql = "SELECT * FROM list_options WHERE list_id = ? ";
        $binding = [$list_name];


        $whitelisted_columns = [
            "option_id", "seq", "is_default", "option_value", "mapping", "notes", "codes", "activity", "edit_options", "toggle_setting_1", "toggle_setting_2", "subtype"
        ];
        foreach ($whitelisted_columns as $column) {
            if (!empty($search[$column])) {
                $sql .= " AND $column = ? ";
                $binding[] = $search[$column];
            }
        }
        $sql .= " ORDER BY `seq` ";

        $statementResults = sqlStatementThrowException($sql, $binding);

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    /**
     * Returns the list option record that was found
     * @param $list_id
     * @param $option_id
     * @param array $search
     * @return array Record
     */
    public function getListOption($list_id, $option_id)
    {
        $records = $this->getOptionsByListName($list_id, ['option_id' => $option_id]);
        if (!empty($records)) { // should only be one record
            return $records[0];
        }
        return null;
    }

    public function getOne($pid, $list_type, $list_id)
    {
        $sql = "SELECT * FROM lists WHERE pid=? AND type=? AND id=? ORDER BY date DESC";

        $statementResults =  QueryUtils::sqlStatementThrowException($sql, [$pid, $list_type, $list_id]);

        $processingResult = new ProcessingResult();

        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }

        return $processingResult;
    }

    public function insert($data)
    {
        $processingResult = $this->listValidator->validate(
            $data,
            BaseValidator::DATABASE_INSERT_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $data['uuid'] = (new UuidRegistry(['table_name' => self::LISTS_TABLE]))->createUuid();

        $query = $this->buildInsertColumns($data);

        $sql  = " INSERT INTO lists SET";
        $sql .= " date=NOW(),";
        $sql .= " activity=1, ";
        $sql .= $query['set'];

        $results = sqlInsert(
            $sql,
            $query['bind']
        );

        if ($results) {
            $processingResult->addData([
                'id' => $results,
                'uuid' => UuidRegistry::uuidToString($data['uuid'])
            ]);
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }

    public function update($pid, $list_id, $list_type, $data)
    {
        if (empty($data)) {
            $processingResult = new ProcessingResult();
            $processingResult->setValidationMessages("Invalid Data");
            return $processingResult;
        }

        $data['pid'] = $pid;

        $data['id'] = $list_id;

        $processingResult = $this->listValidator->validate(
            $data,
            BaseValidator::DATABASE_UPDATE_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $query = $this->buildUpdateColumns($data);

        $sql = "UPDATE lists SET ";
        $sql .= $query['set'];
        $sql .= "WHERE `pid` = ?";
        $sql .= " AND `type` = ?";
        $sql .= " AND `id` = ?";

        $sqlResult = sqlStatement(
            $sql,
            array_merge(
                $query['bind'],
                [
                    $pid,
                    $list_type,
                    $list_id
                ]
            )
        );

        if (!$sqlResult) {
            $processingResult->setValidationMessages("error processing SQL Update");
        } else {
            $processingResult = $this->getOne($pid, $list_type, $list_id);
        }
        return $processingResult;

    }

    public function delete($pid, $list_id, $list_type)
    {
        $processingResult = new ProcessingResult();

        $isValidList = $this->listValidator->validateId('id', self::LISTS_TABLE, $list_id);

        $isPatientValid = $this->listValidator->validateId('pid', self::LISTS_TABLE, $pid);

        if ($isValidList !== true) {
            $processingResult->setValidationMessages(['sid' => $isValidList->getValidationMessages()['id']]);
            return $processingResult;
        }

        if ($isPatientValid !== true) {
            $processingResult->setValidationMessages($isPatientValid->getValidationMessages());
            return $processingResult;
        }

        $sql = "DELETE FROM lists WHERE pid=? AND id=? AND type=?";

        $result = sqlStatement($sql, [$pid, $list_id, $list_type]);

        if ($result) {
            $processingResult->addData([
                'id' => (int)$list_id
            ]);
        } else {
            $processingResult->addInternalError("error processing SQL Insert");
        }

        return $processingResult;
    }
}
