<?php

/**
 * InsuranceService - Service class for patient insurance policy (coverage) data
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\Search\{
    CompositeSearchField,
    DateSearchField,
    FhirSearchWhereClauseBuilder,
    SearchModifier,
    TokenSearchField,
    TokenSearchValue,
};
use OpenEMR\Validators\{
    CoverageValidator,
    ProcessingResult,
};

class InsuranceService extends BaseService
{
    private const COVERAGE_TABLE = "insurance_data";
    private const PATIENT_TABLE = "patient_data";
    private const INSURANCE_TABLE = "insurance_companies";
    /**
     * @var CoverageValidator $coverageValidator
     */
    private $coverageValidator;


    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::COVERAGE_TABLE);
        // TODO: we need to migrate the addresses in these tables into the Address table
        UuidRegistry::createMissingUuidsForTables([self::COVERAGE_TABLE, self::PATIENT_TABLE, self::INSURANCE_TABLE]);
        $this->coverageValidator = new CoverageValidator();
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid'];
    }

    public function validate($data)
    {
        return $this->coverageValidator->validate($data);
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

        $orderClause = " ORDER BY `patient_data_pid` ASC,`type` ASC"
            // sort by 1 first then 0
        . ", (`date_end` is null or `date_end` > NOW()) DESC"
        . ", (`date_end` IS NOT NULL AND `date_end` > NOW()) DESC"
        . ", `date` DESC, `date_end` DESC, `policy_number` ASC";

        $sql .= $whereClause->getFragment() . $orderClause;
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

    /**
     * @deprecated use search instead
     * @param $search
     * @param $isAndCondition
     * @return ProcessingResult|true
     */
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

    public function update($data)
    {
        $validationResult = $this->coverageValidator->validate($data, CoverageValidator::DATABASE_UPDATE_CONTEXT);
        if (!$validationResult->isValid()) {
            return $validationResult;
        }

        $processingResult = new ProcessingResult();

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
        $sql .= "   date_end=?,";
        $sql .= "   subscriber_sex=?,";
        $sql .= "   accept_assignment=?,";
        $sql .= "   policy_type=?,";
        $sql .= "   type=?";
        $sql .= "   WHERE uuid = ? ";

        $serviceSaveEvent = new ServiceSaveEvent($this, $data);
        $this->getEventDispatcher()->dispatch($serviceSaveEvent, ServiceSaveEvent::EVENT_PRE_SAVE);
        $data = $serviceSaveEvent->getSaveData();
        $uuid = UuidRegistry::uuidToBytes($data['uuid']);


        $results = sqlStatement(
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
                empty($data["date_end"]) ? null : $data["date_end"],
                $data["subscriber_sex"],
                $data["accept_assignment"],
                $data["policy_type"],
                $data['type'],
                $uuid
            )
        );
        if ($results) {
            $serviceSavePostEvent = new ServiceSaveEvent($this, $data);
            $this->getEventDispatcher()->dispatch($serviceSavePostEvent, ServiceSaveEvent::EVENT_POST_SAVE);
            $processingResult = $this->getOne($data['uuid']);
        } else {
            $processingResult->addProcessingError("error processing SQL Update");
        }
        return $processingResult;
    }

    public function insert($data): ProcessingResult
    {
        $validationResult = $this->coverageValidator->validate($data, CoverageValidator::DATABASE_INSERT_CONTEXT);
        if (!$validationResult->isValid()) {
            return $validationResult;
        }

        $data['uuid'] = UuidRegistry::getRegistryForTable(self::COVERAGE_TABLE)->createUuid();

        $sql = " INSERT INTO insurance_data SET ";
        $sql .= "   uuid=?,";
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

        $serviceSaveEvent = new ServiceSaveEvent($this, $data);
        $dispatchedEvent = $this->getEventDispatcher()->dispatch($serviceSaveEvent, ServiceSaveEvent::EVENT_PRE_SAVE);
        $data = $dispatchedEvent->getSaveData();

        $insuranceDataId = sqlInsert(
            $sql,
            array(
                $data['uuid'],
                $data['type'],
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
                $data['pid'],
                $data["subscriber_sex"] ?? '',
                $data["accept_assignment"] ?? '',
                $data["policy_type"] ?? ''
            )
        );
        // I prefer exceptions... but we will try to match other service handler formats for consistency
        $processingResult = new ProcessingResult();
        $stringUuid = UuidRegistry::uuidToString($data['uuid']);
        if ($insuranceDataId) {
            $data['id'] = $insuranceDataId;
            $processingResult->addData([
                'id' => $insuranceDataId
                ,'uuid' => $stringUuid
            ]);
            $this->getEventDispatcher()->dispatch($serviceSaveEvent, ServiceSaveEvent::EVENT_POST_SAVE);
            $processingResult = $this->getOne($stringUuid);
        } else {
            $processingResult->addProcessingError("error processing SQL Update");
        }

        return $processingResult;
    }

    /**
     * Return an array of encounters within a date range
     *
     * @param  $start_date  Any encounter starting on this date
     * @param  $end_date  Any encounter ending on this date
     * @return Array Encounter data payload.
     */
    public function getPidsForPayerByEffectiveDate($provider, $type, $startDate, $endDate)
    {
        // most common case of null in 'date' field aka effective date which signifies is only insurance of that type
        // TBD: add another token for 'date_end' field
        $dateMissing = new TokenSearchField('date', [new TokenSearchValue(null)]);
        $dateMissing->setModifier(SearchModifier::MISSING);

        // search for encounters by passed in start and end dates
        $dateField = new DateSearchField('date', ['ge' . $startDate, 'le' . $endDate], DateSearchField::DATE_TYPE_DATE);

        // set up composite search with false signifying an OR condition for the effective date
        $composite = new CompositeSearchField('date', [], false);
        $composite->addChild($dateMissing);
        $composite->addChild($dateField);

        $insuranceDataResult = $this->search(
            [
                'provider' => $provider,
                'type' => $type,
                'date' => $composite,
            ]
        );
        if ($insuranceDataResult->hasData()) {
            $result = $insuranceDataResult->getData();
        } else {
            $result = [];
        }

        return $result;
    }

    public function getPoliciesOrganizedByTypeForPatientPid($pid)
    {
        $insurancePolicies = $this->search(['pid' => $pid]);
        $result = [];
        foreach ($insurancePolicies->getData() as $insurancePolicy) {
            if (empty($insurancePolicy['type'])) {
                $result[$insurancePolicy['type']] = [];
                continue;
            }
            $result[$insurancePolicy['type']][] = $insurancePolicy;
        }
        $organizedResults = [];
        foreach ($result as $key => $policies) {
            if (count($policies) > 0) {
                reset($policies);
                $current = current($policies);
                $history = array_splice($policies, 1);

                $organizedResults[$key] = [
                    'current' => $current
                    ,'history' => $history // we want in descending order
                ];
            }
        }
        return $organizedResults;
    }

    public function swapInsurance($pid, string $targetType, string $insuranceUuid)
    {
        $transactionCommitted = false;
        $validateData = ['pid' => $pid, 'type' => $targetType, 'uuid' => $insuranceUuid];
        $validationResult = $this->coverageValidator->validate($validateData, CoverageValidator::DATABASE_SWAP_CONTEXT);
        if (!$validationResult->isValid()) {
            return $validationResult;
        }
        $processingResult = new ProcessingResult();

        try {
            QueryUtils::startTransaction();

            $targetUuid = QueryUtils::fetchSingleValue("SELECT uuid FROM insurance_data WHERE pid = ? AND type = ? ORDER BY (date IS NULL) ASC, date DESC", 'uuid', [$pid, $targetType]);
            $targetInsurance = null;
            if (!empty($targetUuid)) {
                $targetResult = $this->getOne(UuidRegistry::uuidToString($targetUuid));
                if ($targetResult->hasData()) {
                    $targetInsurance = $targetResult->getFirstDataResult();
                    if (!$this->coverageValidator->validate($targetInsurance, CoverageValidator::DATABASE_UPDATE_CONTEXT)->isValid()) {
                        $processingResult->setValidationMessages(
                            [
                                'type' => ['Record::TARGET_INSURANCE_UPDATE_PROHIBITED' => xl('Target insurance could not be saved as it was missing data required for database updates')]
                            ]
                        );
                        // note the finally clause will rollback the transaction
                        return $processingResult;
                    }
                }
            }
            $srcResult = $this->getOne($insuranceUuid);
            if (!$srcResult->hasData()) {
                // shouldn't happen as the validator should have caught this
                throw new \InvalidArgumentException("Could not find insurance policy with uuid: $insuranceUuid");
            }
            $srcInsurance = $srcResult->getFirstDataResult();
            if (!$this->coverageValidator->validate($srcInsurance, CoverageValidator::DATABASE_UPDATE_CONTEXT)->isValid()) {
                $processingResult->setValidationMessages(
                    [
                        'type' => ['Record::SOURCE_INSURANCE_UPDATE_PROHIBITED' => xl('Source insurance could not be saved as it was missing data required for database updates')]
                    ]
                );
                // note the finally clause will rollback the transaction
                return $processingResult;
            }

            // we need to look at changing up the date of the current insurance policy
            $resetStartDate = null;
            if (!empty($targetInsurance)) {
                $resetStartDate = $targetInsurance['date'];
                if ($resetStartDate != $srcInsurance['date']) {
                    $resetStartDate = null;
                } else {
                    // set to the largest possible date we can which should not conflict with any possible date
                    // I don't like this but due to the pid-type-date db constraint we have to set a temporary date so we don't conflict
                    // with the current insurance policy, the chances of conflict are infitisemally small.
                    // If OpenEMR is still around in 7K+ years, someone should have fixed this by then.
                    // again since its all wrapped in a transaction, this date should never permanently save
                    $targetInsurance["date"] = "9999-12-31";
                }
                $targetInsurance['type'] = $srcInsurance['type'];
                $updateResult = $this->update($targetInsurance);
                if ($updateResult->hasErrors()) {
                    throw new \InvalidArgumentException("Failed to update insurance policy with uuid: $insuranceUuid");
                }
            }

            // we have to do this in multiple steps due to the way the db constraing on the type and date are set
            $srcInsurance['type'] = $targetType;
            $this->update($srcInsurance);

            if (!empty($targetInsurance)) {
                if (!empty($resetStartDate)) {
                    $targetInsurance["date"] = $resetStartDate;
                }
                $this->update($targetInsurance);
            }
            QueryUtils::commitTransaction();
            $transactionCommitted = true;
            $result = [
                'src' => $srcInsurance
                ,'target' => $targetInsurance
            ];
            $processingResult->addData($result);
        } catch (\Exception $e) {
            $processingResult->addInternalError($e->getMessage());
        } finally {
            try {
                if (!$transactionCommitted) {
                    QueryUtils::rollbackTransaction();
                }
            } catch (\Exception $e) {
                (new SystemLogger())->errorLogCaller(
                    "Failed to rollback transaction " . $e->getMessage(),
                    ['type' => $targetType, 'insuranceUuid' => $insuranceUuid, 'pid' => $pid]
                );
            }
        }
        return $processingResult;
    }
}
