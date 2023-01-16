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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ImmunizationValidator;
use OpenEMR\Validators\ProcessingResult;

class ImmunizationService extends BaseService
{
    private const IMMUNIZATION_TABLE = "immunizations";
    private const PATIENT_TABLE = "patient_data";
    private $immunizationValidator;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct(self::IMMUNIZATION_TABLE);
        UuidRegistry::createMissingUuidsForTables([self::IMMUNIZATION_TABLE, self::PATIENT_TABLE]);
        $this->immunizationValidator = new ImmunizationValidator();
    }

    public function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);

        $record['primarySource'] = $record['primarySource'] === '1';
        $record['amount_administered'] = intval($record['amount_administered']);
        // TODO: @adunsulag check with @brady.miller and @sjpadgeet on if the OpenEMR and mysql instances always in UTC time?
        $dates = ['create_date', 'administered_date', 'expiration_date', 'education_date'];

        foreach ($dates as $date) {
            if (isset($record[$date])) {
                $record[$date] = date('c', strtotime($record[$date]));
            }
        }
        return $record;
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'puuid', 'provider_uuid'];
    }

    public function search($search, $isAndCondition = true)
    {
        $sql = "SELECT immunizations.id,
                immunizations.uuid,
                patient.puuid,
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
                immunizations.reason_code,
                immunizations.refusal_reason,
                refusal_reasons.refusal_reason_codes,
                refusal_reasons.refusal_reason_cdc_nip_code,
                refusal_reasons.refusal_reason_description,
                providers.provider_uuid,
                providers.provider_npi,
                providers.provider_username,
                
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
                LEFT JOIN (
                    SELECT uuid AS puuid
                    ,pid
                    FROM patient_data
                ) patient ON immunizations.patient_id = patient.pid
                LEFT JOIN codes as cvx ON cvx.code = immunizations.cvx_code
                LEFT JOIN list_options as site ON site.option_id = immunizations.administration_site
                LEFT JOIN (
                    select
                        uuid AS provider_uuid
                        ,npi AS provider_npi
                        ,username AS provider_username
                        ,id AS provider_id
                    FROM
                        users
                ) providers ON immunizations.administered_by_id = providers.provider_id
                LEFT JOIN (
                    SELECT option_id as refusal_reason_id,
                           notes AS refusal_reason_cdc_nip_code,
                           codes AS refusal_reason_codes,
                           title AS refusal_reason_description
                   FROM list_options 
                   WHERE list_id = 'immunization_refusal_reason'
               ) refusal_reasons ON immunizations.refusal_reason = refusal_reasons.refusal_reason_id";

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

    /**
     * Returns a list of immunizations matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param  $search search array parameters
     * @param  $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getAll($search = array(), $isAndCondition = true, $puuidBind = null)
    {
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

        if (!empty($puuidBind)) {
            // code to support patient binding
            $isValidEncounter = $this->immunizationValidator->validateId(
                'uuid',
                self::PATIENT_TABLE,
                $puuidBind,
                true
            );
            if ($isValidEncounter !== true) {
                return $isValidEncounter;
            }
        }

        $newSearch = [];
        foreach ($search as $key => $value) {
            if (!$value instanceof ISearchField) {
                $newSearch[] = new StringSearchField($key, [$value], SearchModifier::EXACT);
            } else {
                $newSearch[$key] = $value;
            }
        }

        // override puuid, this replaces anything in search if it is already specified.
        if (isset($puuidBind)) {
            $newSearch['puuid'] = new TokenSearchField('puuid', $puuidBind, true);
        }
        return $this->search($newSearch, $isAndCondition);
    }

    /**
     * Returns a single immunization record by id.
     * @param $uuid - The immunization uuid identifier in string format.
     * @param $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     * payload.
     */
    public function getOne($uuid, $puuidBind = null)
    {
        $search['uuid'] = new TokenSearchField('uuid', $uuid, true);
        if (isset($puuidBind)) {
            $search['patient'] = new TokenSearchField('puuid', $puuidBind, true);
        }
        return $this->search($search);
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
