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
        $sql = "SELECT lists.*,
                        us.uuid as practitioner,
                        patient.uuid as puuid,
                        reaction.title as reaction_title,
                        verification.title as verification_title
                        FROM lists
                        LEFT JOIN list_options as reaction ON reaction.option_id = lists.reaction
                        LEFT JOIN list_options as verification ON verification.option_id = lists.verification
                        LEFT JOIN users as us ON us.id = lists.referredby
                        RIGHT JOIN patient_data as patient ON patient.id = lists.pid
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
            if ($row['diagnosis'] != "") {
                $this->addDiagnosis($row['diagnosis']);
            }
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

        $sql = "SELECT lists.*,
                        us.uuid as practitioner,
                        patient.uuid as puuid,
                        reaction.title as reaction_title,
                        verification.title as verification_title
                        FROM lists
                        LEFT JOIN list_options as reaction ON reaction.option_id = lists.reaction
                        LEFT JOIN list_options as verification ON verification.option_id = lists.verification
                        LEFT JOIN users as us ON us.id = lists.referredby
                        RIGHT JOIN patient_data as patient ON patient.id = lists.pid
                        WHERE type = 'allergy' AND lists.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $sqlResult['practitioner'] = $sqlResult['practitioner'] ?
            UuidRegistry::uuidToString($sqlResult['practitioner']) :
            $sqlResult['practitioner'];
        if ($sqlResult['diagnosis'] != "") {
            $this->addDiagnosis($sqlResult['diagnosis']);
        }
        $processingResult->addData($sqlResult);
        return $processingResult;
    }

    private function addDiagnosis(&$diagnosis)
    {
        $diags = explode(";", $diagnosis);
        $diagnosis = array();
        foreach ($diags as $diag) {
            $code = explode(':', $diag)[1];
            $codeSql = "SELECT long_desc FROM icd10_dx_order_code WHERE active = 1
                                AND valid_for_coding = '1'
                                AND formatted_dx_code = '$code'";
            $codedesc = sqlQuery($codeSql);
            $diagnosis[$code] = $codedesc['long_desc'];
        }
    }
}
