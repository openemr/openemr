<?php

/**
 * VitalsService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\MeasurementUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\NumberSearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class VitalsService extends BaseService
{
    private const TABLE_VITALS = "form_vitals";

    public function __construct()
    {
        parent::__construct(self::TABLE_VITALS);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_VITALS]);
    }

    public function search($search, $isAndCondition = true)
    {
        $measurementColumns = [
            'bps', 'bpd', 'weight', 'height', 'temperature', 'pulse', 'respiration', 'BMI', 'waist_circ', 'head_circ'
            , 'oxygen_saturation', 'oxygen_flow_rate', 'ped_weight_height', 'ped_bmi', 'ped_head_circ'
        ];
        $namespacedMeasurementColumns = array_map(function ($val) {
            return "vitals." . $val;
        }, $measurementColumns);

        $selectColumns = array_merge($namespacedMeasurementColumns, []);

        $sqlSelect = "
                    SELECT patients.pid
                    ,patients.puuid
                    ,encounters.eid
                    ,encounters.euuid
                    ,users.user_uuid
                    ,vitals.id
                    ,forms.form_id
                    ,vitals.uuid
                    ,vitals.date
                    ,vitals.external_id
                    ,vitals.note
                    ,details.details_id
                    ,details.interpretation_codes
                    ,details.interpretation_title
                    ,details.vitals_column
                    ";
        $sqlFrom = "
                FROM
                (
                    SELECT
                        id,uuid,`user`,groupname,authorized,activity,external_id
                         ,`date`,note
                        ,bpd,bps,weight,height,temperature,temp_method,pulse,respiration,BMI,BMI_status,waist_circ
                         ,head_circ,oxygen_saturation,oxygen_flow_rate,ped_weight_height,ped_bmi,ped_head_circ
                    FROM
                        form_vitals
                 ) vitals
                JOIN (
                    select 
                        form_id
                        ,encounter
                        ,pid AS form_pid
                        ,`user`
                        ,deleted
                        ,formdir
                    FROM
                        forms
                ) forms ON vitals.id = forms.form_id
                LEFT JOIN (
                    select
                        encounter AS eid
                        ,uuid AS euuid
                        ,`date` AS encounter_date
                    FROM
                        form_encounter
                ) encounters ON encounters.eid = forms.encounter
                LEFT JOIN
                (
                    SELECT 
                        uuid AS puuid
                        ,pid
                        FROM patient_data
                ) patients ON forms.form_pid = patients.pid
                LEFT JOIN
                (
                    SELECT 
                        uuid AS user_uuid
                        ,username
                        ,id AS uid
                        FROM users
                ) users ON vitals.`user` = users.username
                LEFT JOIN
                (
                    SELECT
                        form_id AS details_form_id
                        ,id AS details_id
                        ,interpretation_codes
                        ,interpretation_title
                        ,vitals_column
                    FROM
                        form_vital_details
                ) details ON details.details_form_id = vitals.`id`
        ";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
        $sqlSelect .= "," . implode(",", $selectColumns);
        // lets combine our columns, table selects, and the vitals interpretation clauses
        $sql = $sqlSelect . $sqlFrom
            . " " . $whereClause->getFragment();
        $sql .= " ORDER BY encounter_date DESC, `date` DESC ";

        $sqlBindArray = $whereClause->getBoundValues();
        $statementResults =  QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
        $processingResult = new ProcessingResult();
        $orderedRecords = [];
        $recordsById = [];
        while ($row = sqlFetchArray($statementResults)) {
            $record = $this->createResultRecordFromDatabaseResult($row);
            $details = isset($record['details']) ? array_pop($record['details']) : null;

            if (!isset($recordsById[$row['form_id']])) {
                $orderedRecords[] = $record['form_id'];
                $recordsById[$row['form_id']] = $record;
            }
            if (!empty($details)) {
                $existingRecord = &$recordsById[$row['form_id']];
                $existingRecord["details"][$details['vitals_column']] = $details;
            }
        }
        foreach ($orderedRecords as $formId) {
            $existingRecord = $recordsById[$formId];
            $processingResult->addData($existingRecord);
        }
        return $processingResult;
    }

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);


        // add in all the measurement fields

        $kgToLb = function ($val) {
            if ($val != 0) {
                return MeasurementUtils::lbToKg($val);
            }
        };
        $inchesToCm = function ($val) {
            if ($val != 0) {
                return MeasurementUtils::inchesToCm($val);
            }
        };
        $fhToCelsius = function ($val) {
            if ($val != 0) {
                return MeasurementUtils::fhToCelsius($val);
            }
        };
        $identity = function ($val) {
            return $val;
        };

        $convertArrayValue = function ($index, $converter, $unit, &$array) {
            $array[$index] = $converter($array[$index]);
            $array[$index . "_unit"] = $unit;
        };

        if ($GLOBALS['units_of_measurement'] == 2 || $GLOBALS['units_of_measurement'] == 4) {
            $convertArrayValue('weight', $kgToLb, 'kg', $record);
            $convertArrayValue('height', $inchesToCm, 'cm', $record);
            $convertArrayValue('head_circ', $inchesToCm, 'cm', $record);
            $convertArrayValue('waist_circ', $inchesToCm, 'cm', $record);
            $convertArrayValue('temperature', $fhToCelsius, 'Cel', $record);
        } else {
            $convertArrayValue('weight', $identity, 'lb', $record);
            $convertArrayValue('height', $identity, 'in', $record);
            $convertArrayValue('head_circ', $identity, 'in', $record);
            $convertArrayValue('waist_circ', $identity, 'in', $record);
            $convertArrayValue('temperature', $identity, 'degF', $record);
        }

        $convertArrayValue('pulse', $identity, '/min', $record);
        $convertArrayValue('respiration', $identity, '/min', $record);
        $convertArrayValue('BMI', $identity, 'kg/m2', $record);
        $convertArrayValue('bps', $identity, 'mm[Hg]', $record);
        $convertArrayValue('bpd', $identity, 'mm[Hg]', $record);

        $convertArrayValue('oxygen_saturation', $identity, '%', $record);
        $convertArrayValue('oxygen_flow_rate', $identity, 'L/min', $record);
        $convertArrayValue('ped_weight_height', $identity, '%', $record);
        $convertArrayValue('ped_bmi', $identity, '%', $record);
        $convertArrayValue('ped_head_circ', $identity, '%', $record);


        $detailColumns = ['details_id', 'interpretation_codes', 'interpretation_title', 'vitals_column'];


        // we only set the details record if we actually have a details
        // we run the loop because we still need to clear out the columns that are null
        $details = [];
        foreach ($detailColumns as $column) {
            $details[$column] = $record[$column] ?? null;
            unset($record[$column]);
        }
        if (isset($details['details_id'])) {
            // for anything ORM or active record (such as vitals form) we want the details to use the simplified id
            // instead of the longer named value
            $details['id'] = $details['details_id'];
            unset($details['details_id']);
            $record['details'] = [$details];
        }

        return $record;
    }

    public function create($record)
    {
    }

    public function save()
    {
    }

    function getUuidFields(): array
    {
        // note the uuid here is the uuid_mapping table's uuid since each column in the table has its own distinct uuid
        // in the system.
        return ['puuid', 'euuid', 'uuid', 'user_uuid'];
    }

    /**
     * Retrieves a list of vital records with the passed in vital form excluded.
     * @param $pid
     * @param $excludeVitalFormId
     * @return array
     */
    public function getVitalsHistoryForPatient($pid, $excludeVitalFormId)
    {
        if (empty($pid)) {
            throw new \InvalidArgumentException("patient pid is required");
        }
        $search = [];
        if (isset($excludeVitalFormId)) {
            $search[] = new StringSearchField('id', $excludeVitalFormId, SearchModifier::NOT_EQUALS_EXACT);
        }

        // TODO: @adunsulag when we have NumberSearchField fully implemented change these values over
        $search[] = new StringSearchField('pid', $pid, SearchModifier::EXACT);
        $search[] = new StringSearchField('deleted', 0, SearchModifier::EXACT);
        $search[] = new StringSearchField('formdir', 'vitals', SearchModifier::EXACT);

        $results = $this->search($search);
        return $results->getData();
    }

    public function getVitalsForForm(int $form_id)
    {
        $search = [];
        $search[] = new StringSearchField('id', $form_id, SearchModifier::EXACT);
        $search[] = new StringSearchField('deleted', 0, SearchModifier::EXACT);
        $search[] = new StringSearchField('formdir', 'vitals', SearchModifier::EXACT);
        $results = $this->search($search);
        $data = $results->getData();

        if (!empty($data)) {
            return $data[0];
        }
        return null;
    }
}
