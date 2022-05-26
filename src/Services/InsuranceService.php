<?php

/**
 * InsuranceService
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
use OpenEMR\Services\AddressService;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;
use OpenEMR\Validators\CoverageValidator;
use Particle\Validator\Validator;

class InsuranceService extends BaseService
{
    private const COVERAGE_TABLE = "insurance_data";
    private const PATIENT_TABLE = "patient_data";
    private const INSURANCE_TABLE = "insurance_companies";
    private $coverageValidator;
    private $addressService = null;


    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->addressService = new AddressService();
        UuidRegistry::createMissingUuidsForTables([self::COVERAGE_TABLE, self::PATIENT_TABLE, self::INSURANCE_TABLE]);
        $this->coverageValidator = new CoverageValidator();
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid'];
    }

    public function validate($data)
    {
        $validator = new Validator();

        $validator->required('pid')->numeric();
        $validator->required('type')->inArray(array('primary', 'secondary', 'tertiary'));
        $validator->required('provider')->numeric();
        $validator->required('plan_name')->lengthBetween(2, 255);
        $validator->required('policy_number')->lengthBetween(2, 255);
        $validator->required('group_number')->lengthBetween(2, 255);
        $validator->required('subscriber_lname')->lengthBetween(2, 255);
        $validator->optional('subscriber_mname')->lengthBetween(2, 255);
        $validator->required('subscriber_fname')->lengthBetween(2, 255);
        $validator->required('subscriber_relationship')->lengthBetween(2, 255);
        $validator->required('subscriber_ss')->lengthBetween(2, 255);
        $validator->required('subscriber_DOB')->datetime('Y-m-d');
        $validator->required('subscriber_street')->lengthBetween(2, 255);
        $validator->required('subscriber_postal_code')->lengthBetween(2, 255);
        $validator->required('subscriber_city')->lengthBetween(2, 255);
        $validator->required('subscriber_state')->lengthBetween(2, 255);
        $validator->required('subscriber_country')->lengthBetween(2, 255);
        $validator->required('subscriber_phone')->lengthBetween(2, 255);
        $validator->required('subscriber_sex')->lengthBetween(1, 25);
        $validator->required('accept_assignment')->lengthBetween(1, 5);
        $validator->required('policy_type')->lengthBetween(1, 25);
        $validator->optional('subscriber_employer')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_street')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_postal_code')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_state')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_country')->lengthBetween(2, 255);
        $validator->optional('subscriber_employer_city')->lengthBetween(2, 255);
        $validator->optional('copay')->lengthBetween(2, 255);
        $validator->optional('date')->datetime('Y-m-d');

        return $validator->validate($data);
    }

    public function getOneByPid($id, $type)
    {
        $sql = "SELECT * FROM insurance_data WHERE pid=? AND type=?";
        return sqlQuery($sql, array($id, $type));
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "SELECT `insurance_data`.*,
                       `puuid`
                FROM `insurance_data`
                LEFT JOIN (
                    SELECT
                    `pid` AS `patient_data_pid`,
                    `uuid` as `puuid`
                    FROM `patient_data`
                ) `patient_data` ON `insurance_data`.`pid` = `patient_data`.`patient_data_pid` ";
        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);

        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);

        $processingResult = new ProcessingResult();
        while ($row = sqlFetchArray($statementResults)) {
            $resultRecord = $this->createResultRecordFromDatabaseResult($row);
            $processingResult->addData($resultRecord);
        }
        return $processingResult;
    }

    public function getOne($uuid)
    {

        $processingResult = new ProcessingResult();
        $isValid = $this->coverageValidator->validateId('uuid', self::COVERAGE_TABLE, $uuid, true);
        if ($isValid !== true) {
            return $isValid;
        }
        $uuidBytes = UuidRegistry::uuidToBytes($uuid);
        $sql = "SELECT * FROM insurance_data WHERE uuid=? ";

        $sqlResult = sqlQuery($sql, array($uuidBytes));
        if ($sqlResult) {
            $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
            $processingResult->addData($sqlResult);
        } else {
            $processingResult->addInternalError("error processing SQL");
        }
        return $processingResult;
    }

    public function getAll($search = array(), $isAndCondition = true)
    {

        // Validating and Converting Patient UUID to PID
        // Validating and Converting UUID to ID
        if (isset($search['pid'])) {
            $isValidcondition = $this->coverageValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['pid'],
                true
            );
            if ($isValidcondition !== true) {
                return $isValidcondition;
            }
            $puuidBytes = UuidRegistry::uuidToBytes($search['pid']);
            $search['pid'] = $this->getIdByUuid($puuidBytes, self::PATIENT_TABLE, "pid");
        }
        // Validating and Converting Payor UUID to provider
        if (isset($search['provider'])) {
            $isValidcondition = $this->coverageValidator->validateId(
                'uuid',
                self::INSURANCE_TABLE,
                $search['provider'],
                true
            );
            if ($isValidcondition !== true) {
                return $isValidcondition;
            }
            $uuidBytes = UuidRegistry::uuidToBytes($search['provider']);
            $search['provider'] = $this->getIdByUuid($uuidBytes, self::INSURANCE_TABLE, "provider");
        }

        // Validating and Converting UUID to ID
        if (isset($search['id'])) {
            $isValidcondition = $this->coverageValidator->validateId(
                'uuid',
                self::COVERAGE_TABLE,
                $search['id'],
                true
            );
            if ($isValidcondition !== true) {
                return $isValidcondition;
            }
            $uuidBytes = UuidRegistry::uuidToBytes($search['id']);
            $search['id'] = $this->getIdByUuid($uuidBytes, self::COVERAGE_TABLE, "id");
        }
        $sqlBindArray = array();
        $sql = "SELECT * FROM insurance_data ";
        if (!empty($search)) {
            $sql .= ' WHERE ';
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
            $patientuuidBytes = $this->getUuidById($row['pid'], self::PATIENT_TABLE, "id");
            $row['puuid'] = UuidRegistry::uuidToString($patientuuidBytes);
            $insureruuidBytes = $this->getUuidById($row['provider'], self::INSURANCE_TABLE, "id");
            //When No provider data is available
            if (strlen($insureruuidBytes) > 0) {
                $row['insureruuid'] = UuidRegistry::uuidToString($insureruuidBytes);
                $processingResult->addData($row);
            }
        }
        return $processingResult;
    }

    public function doesInsuranceTypeHaveEntry($pid, $type = '')
    {
        if (!empty($type)) {
            return sqlQuery("Select `id` From `insurance_data` Where pid = ? And type = ?", [$pid, $type])['id'] ?? null;
        }
        return $this->getOne($pid, $type) !== false;
    }

    public function update($pid, $type, $data)
    {
        $sql = " UPDATE insurance_data SET ";
        $sql .= "   provider=?,";
        $sql .= "   plan_name=?,";
        $sql .= "   policy_number=?,";
        $sql .= "   group_number=?,";
        $sql .= "   subscriber_lname=?,";
        $sql .= "   subscriber_mname=?,";
        $sql .= "   subscriber_fname=?,";
        $sql .= "   subscriber_relationship=?,";
        $sql .= "   subscriber_ss=?,";
        $sql .= "   subscriber_DOB=?,";
        $sql .= "   subscriber_street=?,";
        $sql .= "   subscriber_postal_code=?,";
        $sql .= "   subscriber_city=?,";
        $sql .= "   subscriber_state=?,";
        $sql .= "   subscriber_country=?,";
        $sql .= "   subscriber_phone=?,";
        $sql .= "   subscriber_employer=?,";
        $sql .= "   subscriber_employer_street=?,";
        $sql .= "   subscriber_employer_postal_code=?,";
        $sql .= "   subscriber_employer_state=?,";
        $sql .= "   subscriber_employer_country=?,";
        $sql .= "   subscriber_employer_city=?,";
        $sql .= "   copay=?,";
        $sql .= "   date=?,";
        $sql .= "   subscriber_sex=?,";
        $sql .= "   accept_assignment=?,";
        $sql .= "   policy_type=?";
        $sql .= "   WHERE pid=?";
        $sql .= "     AND type=?";

        return sqlStatement(
            $sql,
            array(
                $data["provider"],
                $data["plan_name"],
                $data["policy_number"],
                $data["group_number"],
                $data["subscriber_lname"],
                $data["subscriber_mname"],
                $data["subscriber_fname"],
                $data["subscriber_relationship"],
                $data["subscriber_ss"],
                $data["subscriber_DOB"],
                $data["subscriber_street"],
                $data["subscriber_postal_code"],
                $data["subscriber_city"],
                $data["subscriber_state"],
                $data["subscriber_country"],
                $data["subscriber_phone"],
                $data["subscriber_employer"],
                $data["subscriber_employer_street"],
                $data["subscriber_employer_postal_code"],
                $data["subscriber_employer_state"],
                $data["subscriber_employer_country"],
                $data["subscriber_employer_city"],
                $data["copay"],
                $data["date"],
                $data["subscriber_sex"],
                $data["accept_assignment"],
                $data["policy_type"],
                $pid,
                $type
            )
        );
    }

    public function insert($pid, $type, $data)
    {
        if ($this->doesInsuranceTypeHaveEntry($pid, $type)) {
            return $this->update($pid, $type, $data);
        }

        $sql = " INSERT INTO insurance_data SET ";
        $sql .= "   type=?,";
        $sql .= "   provider=?,";
        $sql .= "   plan_name=?,";
        $sql .= "   policy_number=?,";
        $sql .= "   group_number=?,";
        $sql .= "   subscriber_lname=?,";
        $sql .= "   subscriber_mname=?,";
        $sql .= "   subscriber_fname=?,";
        $sql .= "   subscriber_relationship=?,";
        $sql .= "   subscriber_ss=?,";
        $sql .= "   subscriber_DOB=?,";
        $sql .= "   subscriber_street=?,";
        $sql .= "   subscriber_postal_code=?,";
        $sql .= "   subscriber_city=?,";
        $sql .= "   subscriber_state=?,";
        $sql .= "   subscriber_country=?,";
        $sql .= "   subscriber_phone=?,";
        $sql .= "   subscriber_employer=?,";
        $sql .= "   subscriber_employer_street=?,";
        $sql .= "   subscriber_employer_postal_code=?,";
        $sql .= "   subscriber_employer_state=?,";
        $sql .= "   subscriber_employer_country=?,";
        $sql .= "   subscriber_employer_city=?,";
        $sql .= "   copay=?,";
        $sql .= "   date=?,";
        $sql .= "   pid=?,";
        $sql .= "   subscriber_sex=?,";
        $sql .= "   accept_assignment=?,";
        $sql .= "   policy_type=?";

        return sqlInsert(
            $sql,
            array(
                $type,
                $data["provider"],
                $data["plan_name"] ?? '',
                $data["policy_number"] ?? '',
                $data["group_number"] ?? '',
                $data["subscriber_lname"] ?? '',
                $data["subscriber_mname"] ?? '',
                $data["subscriber_fname"] ?? '',
                $data["subscriber_relationship"] ?? '',
                $data["subscriber_ss"] ?? '',
                $data["subscriber_DOB"] ?? '',
                $data["subscriber_street"] ?? '',
                $data["subscriber_postal_code"] ?? '',
                $data["subscriber_city"] ?? '',
                $data["subscriber_state"] ?? '',
                $data["subscriber_country"] ?? '',
                $data["subscriber_phone"] ?? '',
                $data["subscriber_employer"] ?? '',
                $data["subscriber_employer_street"] ?? '',
                $data["subscriber_employer_postal_code"] ?? '',
                $data["subscriber_employer_state"] ?? '',
                $data["subscriber_employer_country"] ?? '',
                $data["subscriber_employer_city"] ?? '',
                $data["copay"] ?? '',
                $data["date"] ?? '',
                $pid,
                $data["subscriber_sex"] ?? '',
                $data["accept_assignment"] ?? '',
                $data["policy_type"] ?? ''
            )
        );
    }
}
