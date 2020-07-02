<?php

/**
 * PractitionerRoleService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\PractitionerRoleValidator;

class PractitionerRoleService extends BaseService
{

    private const PRACTITIONER_ROLE_TABLE = "facility_user_ids";
    private $practitionerRoleValidator;
    private $uuidRegistery;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('facility_user_ids');
        $this->uuidRegistery = new UuidRegistry(['table_name' => self::PRACTITIONER_ROLE_TABLE]);
        $this->uuidRegistery->createMissingUuids();
        $this->practitionerRoleValidator = new PractitionerRoleValidator();
    }

    /**
     * Returns a list of practitioner-role matching optional search criteria.
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

        $sql = "SELECT prac_role.id,
                prac_role.uuid,
                prac_role.field_value as role_code,
                specialty.field_value as specialty_code,
                us.uuid as user_uuid,
                us.fname as user_fname,
                us.mname as user_mname,
                us.lname as user_lname,
                fac.uuid as facility_uuid,
                fac.name as facility_name,
                role.title as role,
                spec.title as specialty
                FROM facility_user_ids as prac_role
                LEFT JOIN users as us ON us.id = prac_role.uid
                LEFT JOIN facility_user_ids as specialty ON
                specialty.uid = prac_role.uid AND specialty.field_id = 'specialty_code'
                LEFT JOIN facility as fac ON fac.id = prac_role.facility_id
                LEFT JOIN list_options as role ON role.option_id = prac_role.field_value
                LEFT JOIN list_options as spec ON spec.option_id = specialty.field_value
                WHERE prac_role.field_id = 'role_code'";


        if (!empty($search)) {
            $sql .= " AND ";
            $whereClauses = array();
            $wildcardFields = array('us.fname', 'us.mname', 'us.lname');
            foreach ($search as $fieldName => $fieldValue) {
                // support wildcard match on specific fields
                if (in_array($fieldName, $wildcardFields)) {
                    array_push($whereClauses, $fieldName . ' LIKE ?');
                    array_push($sqlBindArray, '%' . $fieldValue . '%');
                } else {
                    // equality match
                    array_push($whereClauses, $fieldName . ' = ?');
                    array_push($sqlBindArray, $fieldValue);
                }
            }
            $sqlCondition = ($isAndCondition == true) ? 'AND' : 'OR';
            $sql .= implode(' ' . $sqlCondition . ' ', $whereClauses);
        }
        $statementResults = sqlStatement($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $row['uuid'] = UuidRegistry::uuidToString($row['uuid']);
            $row['user_uuid'] = UuidRegistry::uuidToString($row['user_uuid']);
            $row['facility_uuid'] = UuidRegistry::uuidToString($row['facility_uuid']);
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single practitioner-role record by id.
     * @param $uuid - The practitioner-role uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = $this->practitionerRoleValidator->validateId("uuid", "facility_user_ids", $uuid, true);

        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT prac_role.id,
                prac_role.uuid,
                prac_role.field_value as role_code,
                specialty.field_value as specialty_code,
                us.uuid as user_uuid,
                us.fname as user_fname,
                us.mname as user_mname,
                us.lname as user_lname,
                fac.uuid as facility_uuid,
                fac.name as facility_name,
                role.title as role,
                spec.title as specialty
                FROM facility_user_ids as prac_role
                LEFT JOIN users as us ON us.id = prac_role.uid
                LEFT JOIN facility_user_ids as specialty ON
                specialty.uid = prac_role.uid AND specialty.field_id = 'specialty_code'
                LEFT JOIN facility as fac ON fac.id = prac_role.facility_id
                LEFT JOIN list_options as role ON role.option_id = prac_role.field_value
                LEFT JOIN list_options as spec ON spec.option_id = specialty.field_value
                WHERE prac_role.uuid = ? AND prac_role.field_id = 'role_code'";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['user_uuid'] = UuidRegistry::uuidToString($sqlResult['user_uuid']);
        $sqlResult['facility_uuid'] = UuidRegistry::uuidToString($sqlResult['facility_uuid']);
        $processingResult->addData($sqlResult);
        return $processingResult;
    }
}
