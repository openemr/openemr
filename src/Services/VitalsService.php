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
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Forms\FormVitalDetails;
use OpenEMR\Common\Forms\FormVitals;
use OpenEMR\Common\Utils\MeasurementUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Events\Services\ServiceSaveEvent;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\NumberSearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;
use Symfony\Component\EventDispatcher\EventDispatcher;

class VitalsService extends BaseService
{
    const MEASUREMENT_METRIC_ONLY = 4;
    const MEASUREMENT_USA_ONLY = 3;
    const MEASUREMENT_PERSIST_IN_METRIC = 2;
    const MEASUREMENT_PERSIST_IN_USA = 1;

    public const TABLE_VITALS = "form_vitals";

    /**
     * @var boolean whether vital measurement for records retrieved should be converted based upon global settings.
     */
    private $shouldConvertVitalMeasurements;

    private $dispatcher;

    public function __construct(?int $units_of_measurement = null)
    {
        parent::__construct(self::TABLE_VITALS);
        UuidRegistry::createMissingUuidsForTables([self::TABLE_VITALS]);
        $this->shouldConvertVitalMeasurements = true;
        if (isset($units_of_measurement)) {
            $this->units_of_measurement = $units_of_measurement;
        } else {
            $this->units_of_measurement = $GLOBALS['units_of_measurement'];
        }
        if (!empty($GLOBALS['kernel'])) {
            $this->dispatcher = $GLOBALS['kernel']->getEventDispatcher();
        } else {
            $this->dispatcher = new EventDispatcher();
        }
    }

    public function setEventDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * Sets whether the vital measurement records returned should be converted to metric / usa format based on the
     * current unit of measurements setting in the service (either the global flag or what the service was initially created
     * with).
     * @param bool $shouldConvert True if the measurements should be converted, false if they should be disabled.
     */
    public function setShouldConvertVitalMeasurementsFlag(bool $shouldConvert)
    {
        $this->shouldConvertVitalMeasurements = $shouldConvert;
    }

    public function search($search, $isAndCondition = true)
    {
        $sqlSelect = "
                    SELECT patients.pid
                    ,patients.puuid
                    ,encounters.eid
                    ,encounters.euuid
                    ,users.user_uuid
                    ,users.user_npi
                    ,vitals.id
                    ,forms.form_id
                    ,vitals.uuid
                    ,vitals.date
                    ,vitals.external_id
                    ,vitals.note
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
                    ,vitals.ped_weight_height
                    ,vitals.ped_bmi
                    ,vitals.ped_head_circ
                    ,vitals.inhaled_oxygen_concentration
                    ,details.details_id
                    ,details.interpretation_list_id
                    ,details.interpretation_option_id
                    ,details.interpretation_codes
                    ,details.interpretation_title
                    ,details.reason_code
                    ,details.reason_status
                    ,details.reason_description
                    ,details.vitals_column
                FROM
                (
                    SELECT
                        id,uuid,`user`,groupname,authorized,activity,external_id
                         ,`date`,note
                        ,bpd,bps,weight,height,temperature,temp_method,pulse,respiration,BMI,BMI_status,waist_circ
                         ,head_circ,oxygen_saturation,oxygen_flow_rate,inhaled_oxygen_concentration
                         , ped_weight_height,ped_bmi,ped_head_circ
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
                        ,npi AS user_npi
                        ,id AS uid
                        FROM users
                ) users ON vitals.`user` = users.username
                LEFT JOIN
                (
                    SELECT
                        form_id AS details_form_id
                        ,id AS details_id
                        ,interpretation_list_id
                        ,interpretation_option_id
                        ,interpretation_codes
                        ,interpretation_title
                        ,reason_code
                        ,reason_status
                        ,reason_description
                        ,vitals_column
                    FROM
                        form_vital_details
                ) details ON details.details_form_id = vitals.`id`";

        $whereClause = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
        // lets combine our columns, table selects, and the vitals interpretation clauses
        $sql = $sqlSelect . " " . $whereClause->getFragment();
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
                $recordsById[$row['form_id']]["details"][$details['vitals_column']] = $details;
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

        if (
            $this->shouldConvertVitalMeasurements
                && ($this->units_of_measurement == self::MEASUREMENT_PERSIST_IN_METRIC
                    || $this->units_of_measurement == self::MEASUREMENT_METRIC_ONLY)
        ) {
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

        $convertArrayValue('inhaled_oxygen_concentration', $identity, '%', $record);
        $convertArrayValue('oxygen_saturation', $identity, '%', $record);
        $convertArrayValue('oxygen_flow_rate', $identity, 'L/min', $record);
        $convertArrayValue('ped_weight_height', $identity, '%', $record);
        $convertArrayValue('ped_bmi', $identity, '%', $record);
        $convertArrayValue('ped_head_circ', $identity, '%', $record);


        $detailColumns = ['details_id', 'interpretation_codes', 'interpretation_title', 'vitals_column'
            , 'interpretation_list_id', 'interpretation_option_id', 'reason_code', 'reason_status', 'reason_description'];


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
        // TODO: not sure we need this anymore.
    }


    public function save(array $vitals)
    {

        // this makes sure we whitelist only values that can be saved in the form.
        $vitalsForm = new FormVitals();
        $vitalsForm->populate_array($vitals);
        $data = $vitalsForm->get_data_for_save();
        return $this->saveVitalsArray($data);
    }

    /**
     * Saves vital information to the vital's form and corresponding vital_form_details records.
     *
     * @param array $vitalsData
     * @return array
     */
    public function saveVitalsArray(array $vitalsData)
    {
        $vitalsData = $this->dispatchSaveEvent(ServiceSaveEvent::EVENT_PRE_SAVE, $vitalsData);

        // convert any uuids to their proper format for insertion
        foreach ($this->getUuidFields() as $field) {
            if (isset($vitalsData[$field])) {
                $vitalsData[$field] = UuidRegistry::uuidToBytes($vitalsData[$field]);
            }
        }

        // this only works on MySQL/MariaDB variants
        $id = $vitalsData['id'] ?? null;
        $sqlOperation = "UPDATE ";
        if (empty($id)) {
            // verify we have enough to save a form
            if (empty($vitalsData['eid']) && empty($vitalsData['pid'])) {
                throw new \InvalidArgumentException("encounter eid and patient pid must be populated to insert a new vitals form");
            }
            $sqlOperation = "INSERT INTO ";
            // use our generate_id function here for us to create a new id
            $vitalsData['id'] = \generate_id();
            $vitalsData['uuid'] = UuidRegistry::getRegistryForTable(self::TABLE_VITALS)->createUuid();
            \addForm($vitalsData['eid'], "Vitals", $vitalsData['id'], "vitals", $vitalsData['pid'], $vitalsData['authorized']);
        } else {
            unset($vitalsData['id']);
        }
        // clear out encounter and other new form settings
        unset($vitalsData['eid']);
        unset($vitalsData['authorized']);

        $details = $vitalsData['details'] ?? [];
        unset($vitalsData['details']);
        $keys = array_keys($vitalsData);
        $values = array_values($vitalsData);
        $fields = array_map(function ($val) {
            return '`' . $val . '` = ?';
        }, $keys);
        $sqlSet = implode(",", $fields);

        $sql = $sqlOperation . self::TABLE_VITALS . " SET " . $sqlSet;

        if (!empty($id)) {
            $sql .= " WHERE `id` = ? ";
            $values[] = $id;
            $vitalsData['id'] = $id;
        }
        QueryUtils::sqlStatementThrowException($sql, $values);

        // now go through and update all of our vital details

        $updatedDetails = [];
        foreach ($details as $column => $detail) {
            $detail['form_id'] = $vitalsData['id'];
            $updatedDetails[$column] = $this->saveVitalDetails($detail);
        }
        $vitalsData['details'] = $updatedDetails;
        $vitalsData = $this->dispatchSaveEvent(ServiceSaveEvent::EVENT_POST_SAVE, $vitalsData);
        return $vitalsData;
    }

    /**
     * Given a vitals form object save/create the data and any corresponding vital details into the database.
     * @param FormVitals $vitals
     * @return FormVitals
     */
    public function saveVitalsForm(FormVitals $vitals)
    {
        $newForm = empty($vitals->get_id());

        $data = $vitals->get_data_for_save();
        $result = $this->saveVitalsArray($data);
        $vitals->populate_array($result); // populate any database records and other things we may need

        return $vitals;
    }

    public function getUuidFields(): array
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

    /**
     * Retrieves all the vital observation records for the passed in form id.
     * @param $form_id
     * @return array|null
     */
    public function getVitalsForForm($form_id)
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


    /**
     *
     * @param string $type The type of save event to dispatch
     * @param $vitalsData The vitals data to send in the event
     * @return array
     */
    private function dispatchSaveEvent(string $type, $vitalsData)
    {
        $saveEvent = new ServiceSaveEvent($this, $vitalsData);
        $filteredData = $this->getEventDispatcher()->dispatch($saveEvent, $type);
        if ($filteredData instanceof ServiceSaveEvent) { // make sure whoever responds back gives us the right data.
            $vitalsData = $filteredData->getSaveData();
        }
        return $vitalsData;
    }

    /**
     * Given an array of vitals go through and save it to the database.  Return the created/updated record that was saved
     * @param array $vitalDetails
     */
    private function saveVitalDetails(array $vitalDetails)
    {
        $id = $vitalDetails['id'] ?? null;
        $sqlOperation = "UPDATE ";
        if (empty($id)) {
            $sqlOperation = "INSERT INTO ";
        }

        unset($vitalDetails['id']);
        $keys = array_keys($vitalDetails);
        $values = array_values($vitalDetails);
        $fields = array_map(function ($val) {
            return '`' . $val . '` = ?';
        }, $keys);
        $sqlSet = implode(",", $fields);

        $sql = $sqlOperation . FormVitalDetails::TABLE_NAME . " SET " . $sqlSet;
        if (!empty($id)) {
            $sql .= " WHERE `id` = ? ";
            $values[] = $id;
        }
        QueryUtils::sqlStatementThrowException($sql, $values);
    }

    public function getVitalsForPatientEncounter($pid, $eid)
    {
        $search = [];
        $search[] = new StringSearchField('pid', $pid, SearchModifier::EXACT);
        $search[] = new StringSearchField('eid', $eid, SearchModifier::EXACT);
        $search[] = new StringSearchField('deleted', 0, SearchModifier::EXACT);
        $search[] = new StringSearchField('formdir', 'vitals', SearchModifier::EXACT);
        $results = $this->search($search);
        $data = $results->getData();

        if (!empty($data)) {
            return $data;
        }
        return null;
    }
}
