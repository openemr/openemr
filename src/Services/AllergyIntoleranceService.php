<?php

/**
 * AllergyIntoleranceService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\ProcessingResult;

class AllergyIntoleranceService extends BaseService
{
    private const ALLERGY_TABLE = "lists";
    private const PATIENT_TABLE = "patient_data";
    private const PRACTITIONER_TABLE = "users";
    private $uuidRegistery;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('lists');
        $this->uuidRegistery = new UuidRegistry(['table_name' => self::ALLERGY_TABLE]);
        $this->uuidRegistery->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PRACTITIONER_TABLE]))->createMissingUuids();
    }

    /**
     * Returns a list of allergyIntolerance matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true)
    {
        $sqlBindArray = array();
        $sql = "SELECT lists.id as id,
                        lists.uuid as uuid,
                        lists.date as recorded_date,
                        type,
                        subtype,
                        lists.title as title,
                        begdate,
                        enddate,
                        returndate,
                        referredby,
                        extrainfo,
                        diagnosis,
                        lists.pid as pid,
                        outcome,
                        reaction,
                        severity_al,
                        us.uuid as practitioner,
                        patient.uuid as puuid
                        FROM lists
                        LEFT JOIN icd10_dx_order_code as code ON code.short_desc = lists.reaction
                        LEFT JOIN users as us ON us.id = lists.referredby
                        LEFT JOIN patient_data as patient ON patient.id = lists.pid
                        WHERE type = 'allergy'";

        if (!empty($search)) {
            $sql .= ' AND ';
            $whereClauses = array();
            foreach ($search as $fieldName => $fieldValue) {
                array_push($whereClauses, $fieldName . ' = ?');
                array_push($sqlBindArray, $fieldValue);
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }

        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $row['practitioner'] = $row['practitioner'] ?
                UuidRegistry::uuidToString($row['practitioner']) :
                $row['practitioner'];
            $processingResult->addData($row);
        }
        return $processingResult;
    }

    /**
     * Returns a single allergyIntolerance record by uuid.
     * @param $uuid - The allergyIntolerance uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = BaseValidator::validateId("uuid", "lists", $uuid, true);

        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT lists.id as id,
                        lists.uuid as uuid,
                        lists.date as recorded_date,
                        type,
                        subtype,
                        lists.title as title,
                        begdate,
                        enddate,
                        returndate,
                        referredby,
                        extrainfo,
                        diagnosis,
                        lists.pid as pid,
                        outcome,
                        reaction,
                        severity_al,
                        us.uuid as practitioner,
                        patient.uuid as puuid
                        FROM lists
                        LEFT JOIN icd10_dx_order_code as code ON code.short_desc = lists.reaction
                        LEFT JOIN users as us ON us.id = lists.referredby
                        LEFT JOIN patient_data as patient ON patient.id = lists.pid
                        AND lists.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $sqlResult['practitioner'] = $sqlResult['practitioner'] ?
            UuidRegistry::uuidToString($sqlResult['practitioner']) :
            $sqlResult['practitioner'];
        $processingResult->addData($sqlResult);
        return $processingResult;
    }
}
