<?php

/**
 * ImmunizationService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\ImmunizationValidator;
use OpenEMR\Validators\ProcessingResult;

class ImmunizationService extends BaseService
{

    private const IMMUNIZATION_TABLE = "immunizations";
    private const PATIENT_TABLE = "patient_data";
    private $immunizationValidator;
    private $uuidRegistry;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::IMMUNIZATION_TABLE);
        $this->uuidRegistry = new UuidRegistry(['table_name' => self::IMMUNIZATION_TABLE]);
        $this->uuidRegistry->createMissingUuids();
        (new UuidRegistry(['table_name' => self::PATIENT_TABLE]))->createMissingUuids();
        $this->immunizationValidator = new ImmunizationValidator();
    }

    /**
     * Returns a list of immunizations matching optional search criteria.
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

        if (isset($search['patient.uuid'])) {
            $isValidEncounter = $this->immunizationValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $search['patient.uuid'],
                true
            );
            if ($isValidEncounter !== true) {
                return $isValidEncounter;
            }
            $search['patient.uuid'] = UuidRegistry::uuidToBytes($search['patient.uuid']);
        }

        $sql = "SELECT immunizations.id,
                immunizations.uuid,
                patient.uuid as puuid,
                administered_date,
                cvx_code,
                cvx.code_text as cvx_code_text,
                manufacturer,
                lot_number,
                added_erroneously,
                administered_by_id,
                administered_by,
                education_date,
                note,
                create_date,
                amount_administered,
                amount_administered_unit,
                expiration_date,
                route,
                administration_site,
                site.title as site_display,
                site.notes as site_code,
                completion_status,
                refusal_reason,
                IF(
                    IF(
                        information_source = 'new_immunization_record' AND
                        IF(administered_by IS NOT NULL OR administered_by_id IS NOT NULL, TRUE, FALSE),
                        TRUE,
                        FALSE
                    ) OR
                    IF(
                        information_source = 'other_provider' OR 
                        information_source = 'birth_certificate' OR 
                        information_source = 'school_record' OR 
                        information_source = 'public_agency',
                        TRUE,
                        FALSE
                    ),
                    TRUE,
                    FALSE
                ) as primarySource
                FROM immunizations
                LEFT JOIN patient_data as patient ON immunizations.patient_id = patient.pid
                LEFT JOIN codes as cvx ON cvx.code = immunizations.cvx_code
                LEFT JOIN list_options as site ON site.option_id = immunizations.administration_site";

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
            $row['puuid'] = UuidRegistry::uuidToString($row['puuid']);
            $processingResult->addData($row);
        }

        return $processingResult;
    }

    /**
     * Returns a single immunization record by id.
     * @param $uuid - The immunization uuid identifier in string format.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid)
    {
        $processingResult = new ProcessingResult();

        $isValid = $this->immunizationValidator->validateId("uuid", "immunizations", $uuid, true);
        if ($isValid !== true) {
            $validationMessages = [
                'uuid' => ["invalid or nonexisting value" => " value " . $uuid]
            ];
            $processingResult->setValidationMessages($validationMessages);
            return $processingResult;
        }

        $sql = "SELECT immunizations.id,
                        immunizations.uuid,
                        patient.uuid as puuid,
                        administered_date,
                        cvx_code,
                        cvx.code_text as cvx_code_text,
                        manufacturer,
                        lot_number,
                        added_erroneously,
                        administered_by_id,
                        administered_by,
                        education_date,
                        note,
                        create_date,
                        amount_administered,
                        amount_administered_unit,
                        expiration_date,
                        route,
                        administration_site,
                        site.title as site_display,
                        site.notes as site_code,
                        completion_status,
                        refusal_reason,
                        IF(
                            IF(
                                information_source = 'new_immunization_record' AND
                                IF(administered_by IS NOT NULL OR administered_by_id IS NOT NULL, TRUE, FALSE),
                                TRUE,
                                FALSE
                            ) OR
                            IF(
                                information_source = 'other_provider' OR 
                                information_source = 'birth_certificate' OR 
                                information_source = 'school_record' OR 
                                information_source = 'public_agency',
                                TRUE,
                                FALSE
                            ),
                            TRUE,
                            FALSE
                        ) as primarySource
                        FROM immunizations
                        LEFT JOIN patient_data as patient ON immunizations.patient_id = patient.pid
                        LEFT JOIN codes as cvx ON cvx.code = immunizations.cvx_code
                        LEFT JOIN list_options as site ON site.option_id = immunizations.administration_site
                        WHERE immunizations.uuid = ?";

        $uuidBinary = UuidRegistry::uuidToBytes($uuid);
        $sqlResult = sqlQuery($sql, [$uuidBinary]);
        $sqlResult['uuid'] = UuidRegistry::uuidToString($sqlResult['uuid']);
        $sqlResult['puuid'] = UuidRegistry::uuidToString($sqlResult['puuid']);
        $processingResult->addData($sqlResult);
        return $processingResult;
    }


    /**
     * Inserts a new immunization record.
     *
     * @param $data The immunization fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function insert($data)
    {
    }


    /**
     * Updates an existing immunization record.
     *
     * @param $uuid - The immunization uuid identifier in string format used for update.
     * @param $data - The updated immunization data fields
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function update($uuid, $data)
    {
    }
}
