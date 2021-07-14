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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
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
        $sql = "
            SELECT
                vitals.id
                ,vitals.uuid
                ,vitals.date
                ,vitals.bps
                ,vitals.bpd
                ,vitals.weight
                ,vitals.height
                ,vitals.temperature
                ,vitals.temp_method
                ,vitals.pulse
                ,vitals.respiration
                ,vitals.BMI
                ,vitals.BMI_status
                ,vitals.waist_circ
                ,vitals.head_circ
                ,vitals.oxygen_saturation
                ,vitals.oxygen_flow_rate
                ,vitals.external_id
                ,vitals.note
                ,vitals.ped_weight_height
                ,vitals.ped_bmi
                ,vitals.ped_head_circ
                ,patients.pid
                ,patients.puuid
                ,encounters.eid
                ,encounters.euuid
                ,users.user_uuid
            FROM
            form_vitals vitals
            JOIN (
                select
                    form_id
                    ,encounter
                    ,pid
                    ,`user`
                FROM
                    forms
            ) forms ON vitals.id = forms.form_id
            LEFT JOIN (
                select
                    encounter AS eid
                    ,uuid AS euuid
                FROM
                    form_encounter
            ) encounters ON encounters.eid = forms.encounter
            LEFT JOIN
            (
                SELECT
                    uuid AS puuid
                    ,pid
                    FROM patient_data
            ) patients ON vitals.pid = patients.pid
            LEFT JOIN
            (
                SELECT
                    uuid AS user_uuid
                    ,username
                    ,id AS uid
                    FROM users
            ) users ON vitals.`user` = users.username
        ";
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

    protected function createResultRecordFromDatabaseResult($row)
    {
        $record = parent::createResultRecordFromDatabaseResult($row);


        // add in all the measurement fields

        $kgToLb = function ($val) {
            return number_format($val *  0.45359237, 2);
        };
        $cmToInches = function ($val) {
            return round(number_format($val * 2.54, 2), 1);
        };
        $fhToCelsius = function ($val) {
            return round(number_format(($val - 32) * (5 / 9), 1));
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
            $convertArrayValue('height', $cmToInches, 'cm', $record);
            $convertArrayValue('head_circ', $cmToInches, 'cm', $record);
            $convertArrayValue('waist_circ', $cmToInches, 'cm', $record);
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
}
