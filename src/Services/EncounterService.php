<?php

/**
 * EncounterService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\EncounterValidator;
use OpenEMR\Validators\ProcessingResult;
use Particle\Validator\Validator;

require_once dirname(__FILE__) . "/../../library/forms.inc.php";
require_once dirname(__FILE__) . "/../../library/encounter.inc.php";

class EncounterService extends BaseService
{
    /**
     * @var EncounterValidator
     */
    private $encounterValidator;

    private const ENCOUNTER_TABLE = "form_encounter";
    private const PATIENT_TABLE = "patient_data";
    private const PROVIDER_TABLE = "users";
    private const FACILITY_TABLE = "facility";

    /**
     * Default class_code from list_options.  Defaults to outpatient ambulatory care.
     */
    const DEFAULT_CLASS_CODE = 'AMB';

    /**
     * Default constructor.
     */
    public function __construct()
    {
        parent::__construct('form_encounter');
        UuidRegistry::createMissingUuidsForTables([self::ENCOUNTER_TABLE, self::PATIENT_TABLE, self::PROVIDER_TABLE,
            self::FACILITY_TABLE]);
        $this->encounterValidator = new EncounterValidator();
    }

    /**
     * Returns a list of encounters matching the encounter identifier.
     *
     * @param  $eid     The encounter identifier of particular encounter
     * @param  $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     *                    payload.
     */
    public function getEncounterById($eid, $puuidBind = null)
    {
        $search = ['eid' => new TokenSearchField('eid', [new TokenSearchValue($eid)])];
        return $this->search($search, true, $puuidBind);
    }

    /**
     * Returns a list of encounters matching the encounter identifier.
     *
     * @param  $euuid     The encounter identifier of particular encounter
     * @param  $puuidBind - Optional variable to only allow visibility of the patient with this puuid.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     *                    payload.
     */
    public function getEncounter($euuid, $puuidBind = null)
    {
        $search = ['euuid' => new TokenSearchField('euuid', [new TokenSearchValue($euuid, null, true)])];
        return $this->search($search, true, $puuidBind);
    }

    /**
     * Returns an encounter matching the patient and encounter identifier.
     *
     * @param  $pid          The legacy identifier of particular patient
     * @param  $encounter_id The identifier of a particular encounter
     * @return array         first row of encounter data
     */
    public function getOneByPidEid($pid, $encounter_id)
    {
        $encounterResult = $this->search(['pid' => $pid, 'eid' => $encounter_id], $options = ['limit' => '1']);
        if ($encounterResult->hasData()) {
            return $encounterResult->getData()[0];
        }
        return [];
    }

    public function getUuidFields(): array
    {
        return ['provider_uuid', 'facility_uuid', 'euuid', 'puuid', 'billing_facility_uuid'
            , 'facility_location_uuid', 'billing_location_uuid', 'referrer_uuid'];
    }

    /**
     * Given a patient pid return the most recent patient encounter for that patient
     * @param $pid The unique public id (pid) for the patient.
     * @return array|null Returns the encounter if found, null otherwise
     */
    public function getMostRecentEncounterForPatient($pid): ?array
    {
        $pid = new TokenSearchField('pid', [new TokenSearchValue($pid, null)]);
        // we discovered that most queries were ordering by encounter id which may NOT be the most recent encounter as
        // an older historical encounter may be entered after a more recent encounter, so ordering be encounter id screws
        // this up.
        $result = $this->search(['pid' => $pid], true, '', ['limit' => 1, 'order' => '`date` DESC']);
        if ($result->hasData()) {
            return array_pop($result->getData());
        }
        return null;
    }

    /**
     * Returns a list of encounters matching optional search criteria.
     * Search criteria is conveyed by array where key = field/column name, value = field value.
     * If no search criteria is provided, all records are returned.
     *
     * @param array  $search         search array parameters
     * @param bool   $isAndCondition specifies if AND condition is used for multiple criteria. Defaults to true.
     * @param string $puuidBindValue - Optional puuid to only allow visibility of the patient with this puuid.
     * @param array  $options        - Optional array of sql clauses like LIMIT, ORDER, etc
     * @return bool|ProcessingResult|true|null ProcessingResult which contains validation messages, internal error messages, and the data
     *                               payload.
     */
    public function search($search = array(), $isAndCondition = true, $puuidBindValue = '', $options = array())
    {
        $limit = $options['limit'] ?? null;
        $sqlBindArray = array();
        $processingResult = new ProcessingResult();

        // Validating and Converting _id to UUID byte
        if (isset($search['uuid'])) {
            $isValidEncounter = $this->encounterValidator->validateId(
                'uuid',
                self::ENCOUNTER_TABLE,
                $search['uuid'],
                true
            );
            if ($isValidEncounter !== true) {
                return $isValidEncounter;
            }
            $search['uuid'] = UuidRegistry::uuidToBytes($search['uuid']);
        }
        // passed in uuid string to bind patient via their uuid.
        // confusing ...
        if (!empty($puuidBindValue)) {
            // code to support patient binding
            $isValidPatient = $this->encounterValidator->validateId('uuid', self::PATIENT_TABLE, $puuidBindValue, true);
            if ($isValidPatient !== true) {
                return $isValidPatient;
            }
            $pid = $this->getIdByUuid(UuidRegistry::uuidToBytes($puuidBindValue), self::PATIENT_TABLE, "pid");
            if (empty($pid)) {
                $processingResult->setValidationMessages("Invalid pid");
                return $processingResult;
            }
            $search['puuid'] = new TokenSearchField('puuid', [new TokenSearchValue($puuidBindValue, null, true)]);
        }

        $sql = "SELECT fe.eid,
                       fe.euuid,
                       fe.date,
                       fe.reason,
                       fe.onset_date,
                       fe.sensitivity,
                       fe.billing_note,
                       fe.pc_catid,
                       fe.last_level_billed,
                       fe.last_level_closed,
                       fe.last_stmt_date,
                       fe.stmt_count,
                       fe.supervisor_id,
                       fe.invoice_refno,
                       fe.referral_source,
                       fe.billing_facility,
                       fe.external_id,
                       fe.pos_code,
                       fe.class_code,
                       class.notes as class_title,
                       opc.pc_catname,

                       patient.pid,
                       patient.puuid,
                       facilities.facility_id,
                       facilities.facility_uuid,
                       facilities.facility_name,
                       facilities.facility_location_uuid,

                       fa.billing_facility_id,
                       fa.billing_facility_uuid,
                       fa.billing_facility_name,
                       fa.billing_location_uuid,

                       fe.provider_id,
                       fe.referring_provider_id,
                       providers.provider_uuid,
                       providers.provider_username,
                       referrers.referrer_uuid,
                       referrers.referrer_username,
                       fe.discharge_disposition,
                       discharge_list.discharge_disposition_text


                       FROM (
                           select
                               encounter as eid,
                               uuid as euuid,
                               `date`,
                               reason,
                               onset_date,
                               sensitivity,
                               billing_note,
                               pc_catid,
                               last_level_billed,
                               last_level_closed,
                               last_stmt_date,
                               stmt_count,
                               provider_id,
                               supervisor_id,
                               invoice_refno,
                               referral_source,
                               billing_facility,
                               external_id,
                               pos_code,
                               class_code,
                               facility_id,
                               discharge_disposition,
                               pid as encounter_pid,
                               referring_provider_id
                           FROM form_encounter
                       ) fe
                       LEFT JOIN openemr_postcalendar_categories as opc
                       ON opc.pc_catid = fe.pc_catid
                       LEFT JOIN list_options as class ON class.option_id = fe.class_code
                       LEFT JOIN (
                           select
                                facility.id AS billing_facility_id
                                ,facility.uuid AS billing_facility_uuid
                                ,facility.`name` AS billing_facility_name
                                ,locations.uuid AS billing_location_uuid
                           from facility
                           LEFT JOIN uuid_mapping AS locations
                               ON locations.target_uuid = facility.uuid AND locations.resource='Location'
                       ) fa ON fa.billing_facility_id = fe.billing_facility
                       LEFT JOIN (
                           select
                                  pid
                                 ,uuid AS puuid
                           FROM patient_data
                       ) patient ON fe.encounter_pid = patient.pid
                       LEFT JOIN (
                           select
                                id AS provider_provider_id
                                ,uuid AS provider_uuid
                                ,`username` AS provider_username
                            FROM users
                            WHERE
                                npi IS NOT NULL and npi != ''
                       ) providers ON fe.provider_id = providers.provider_provider_id
                       LEFT JOIN (
                           select
                                id AS referring_provider_id
                                ,uuid AS referrer_uuid
                                ,`username` AS referrer_username
                            FROM users
                            WHERE
                                npi IS NOT NULL and npi != ''
                       ) referrers ON fe.referring_provider_id = referrers.referring_provider_id
                       LEFT JOIN (
                           select
                                facility.id AS facility_id
                                ,facility.uuid AS facility_uuid
                                ,facility.`name` AS facility_name
                                ,`locations`.`uuid` AS facility_location_uuid
                           from facility
                           LEFT JOIN uuid_mapping AS locations
                               ON locations.target_uuid = facility.uuid AND locations.resource='Location'
                       ) facilities ON facilities.facility_id = fe.facility_id
                       LEFT JOIN (
                           select option_id AS discharge_option_id
                           ,title AS discharge_disposition_text
                           FROM list_options
                           WHERE list_id = 'discharge-disposition'
                       ) discharge_list ON fe.discharge_disposition = discharge_list.discharge_option_id";

        try {
            $whereFragment = FhirSearchWhereClauseBuilder::build($search, $isAndCondition);
            $sql .= $whereFragment->getFragment();

            if (empty($options['order'])) {
                $sql .= " ORDER BY fe.eid DESC";
            } else {
                $sql .= " ORDER BY " . $options['order'];
            }


            if (is_int($limit) && $limit > 0) {
                $sql .= " LIMIT " . $limit;
            }

            $records = QueryUtils::fetchRecords($sql, $whereFragment->getBoundValues());

            if (!empty($records)) {
                foreach ($records as $row) {
                    $resultRecord = $this->createResultRecordFromDatabaseResult($row);
                    $processingResult->addData($resultRecord);
                }
            }
        } catch (SqlQueryException $exception) {
            // we shouldn't hit a query exception
            (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $processingResult->addInternalError("Error selecting data from database");
        } catch (SearchFieldException $exception) {
            (new SystemLogger())->error($exception->getMessage(), ['trace' => $exception->getTraceAsString(), 'field' => $exception->getField()]);
            $processingResult->setValidationMessages([$exception->getField() => $exception->getMessage()]);
        }

        return $processingResult;
    }

    /**
     * Inserts a new Encounter record.
     *
     * @param $puuid The patient identifier of particular encounter
     * @param $data  The encounter fields (array) to insert.
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     *               payload.
     */
    public function insertEncounter($puuid, $data)
    {
        $processingResult = new ProcessingResult();
        $processingResult = $this->encounterValidator->validate(
            array_merge($data, ["puuid" => $puuid]),
            EncounterValidator::DATABASE_INSERT_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $encounter = generate_id();
        $data['encounter'] = $encounter;
        $data['uuid'] = UuidRegistry::getRegistryForTable(self::ENCOUNTER_TABLE)->createUuid();
        if (empty($data['date'])) {
            $data['date'] = date("Y-m-d");
        }
        $puuidBytes = UuidRegistry::uuidToBytes($puuid);
        $data['pid'] = $this->getIdByUuid($puuidBytes, self::PATIENT_TABLE, "pid");
        $query = $this->buildInsertColumns($data);
        $sql = " INSERT INTO form_encounter SET ";
        $sql .= $query['set'];

        $results = sqlInsert(
            $sql,
            $query['bind']
        );

        addForm(
            $encounter,
            "New Patient Encounter",
            $results,
            "newpatient",
            $data['pid'],
            $data["provider_id"],
            $data["date"],
            $data['user'],
            $data['group'],
            $data['referring_provider_id']
        );

        if ($results) {
            $processingResult->addData(array(
                'encounter' => $encounter,
                'uuid' => UuidRegistry::uuidToString($data['uuid']),
            ));
        } else {
            $processingResult->addProcessingError("error processing SQL Insert");
        }

        return $processingResult;
    }

    /**
     * Updates an existing Encounter record.
     *
     * @param $puuid The patient identifier of particular encounter.
     * @param $euuid - The Encounter identifier used for update.
     * @param $data  - The updated Encounter data fields
     * @return ProcessingResult which contains validation messages, internal error messages, and the data
     *               payload.
     */
    public function updateEncounter($puuid, $euuid, $data)
    {
        $processingResult = new ProcessingResult();
        $processingResult = $this->encounterValidator->validate(
            array_merge($data, ["puuid" => $puuid, "euuid" => $euuid]),
            EncounterValidator::DATABASE_UPDATE_CONTEXT
        );

        if (!$processingResult->isValid()) {
            return $processingResult;
        }

        $puuidBytes = UuidRegistry::uuidToBytes($puuid);
        $euuidBytes = UuidRegistry::uuidToBytes($euuid);
        $pid = $this->getIdByUuid($puuidBytes, self::PATIENT_TABLE, "pid");
        $encounter = $this->getIdByUuid($euuidBytes, self::ENCOUNTER_TABLE, "encounter");

        $facilityService = new FacilityService();
        $facilityresult = $facilityService->getById($data["facility_id"]);
        $facility = $facilityresult['name'];
        $result = sqlQuery("SELECT sensitivity FROM form_encounter WHERE encounter = ?", array($encounter));
        if ($result['sensitivity'] && !AclMain::aclCheckCore('sensitivities', $result['sensitivity'])) {
            return "You are not authorized to see this encounter.";
        }

        // See view.php to allow or disallow updates of the encounter date.
        if (!AclMain::aclCheckCore('encounters', 'date_a')) {
            unset($data["date"]);
        }

        $data['facility'] = $facility;

        $query = $this->buildUpdateColumns($data);
        $sql = " UPDATE form_encounter SET ";
        $sql .= $query['set'];
        $sql .= " WHERE encounter = ?";
        $sql .= " AND pid = ?";

        array_push($query['bind'], $encounter);
        array_push($query['bind'], $pid);
        $results = sqlStatement(
            $sql,
            $query['bind']
        );

        if ($results) {
            $processingResult = $this->getEncounter($euuid, $puuid);
        } else {
            $processingResult->addProcessingError("error processing SQL Update");
        }

        return $processingResult;
    }

    public function insertSoapNote($pid, $eid, $data)
    {
        $soapSql = " INSERT INTO form_soap SET";
        $soapSql .= "     date=NOW(),";
        $soapSql .= "     activity=1,";
        $soapSql .= "     pid=?,";
        $soapSql .= "     subjective=?,";
        $soapSql .= "     objective=?,";
        $soapSql .= "     assessment=?,";
        $soapSql .= "     plan=?";

        $soapResults = sqlInsert(
            $soapSql,
            array(
                $pid,
                $data["subjective"],
                $data["objective"],
                $data["assessment"],
                $data["plan"]
            )
        );

        if (!$soapResults) {
            return false;
        }

        $formSql = "INSERT INTO forms SET";
        $formSql .= "     date=NOW(),";
        $formSql .= "     encounter=?,";
        $formSql .= "     form_name='SOAP',";
        $formSql .= "     authorized='1',";
        $formSql .= "     form_id=?,";
        $formSql .= "     pid=?,";
        $formSql .= "     formdir='soap'";

        $formResults = sqlInsert(
            $formSql,
            array(
                $eid,
                $soapResults,
                $pid
            )
        );

        return array($soapResults, $formResults);
    }

    public function updateSoapNote($pid, $eid, $sid, $data)
    {
        $sql = " UPDATE form_soap SET";
        $sql .= "     date=NOW(),";
        $sql .= "     activity=1,";
        $sql .= "     pid=?,";
        $sql .= "     subjective=?,";
        $sql .= "     objective=?,";
        $sql .= "     assessment=?,";
        $sql .= "     plan=?";
        $sql .= "     where id=?";

        return sqlStatement(
            $sql,
            array(
                $pid,
                $data["subjective"],
                $data["objective"],
                $data["assessment"],
                $data["plan"],
                $sid
            )
        );
    }

    public function updateVital($pid, $eid, $vid, $data)
    {
        $data['date'] = date("Y-m-d H:i:s");
        $data['activity'] = 1;
        $data['id'] = $vid;
        $data['pid'] = $pid;
        $data['eid'] = $eid;

        $vitalsService = new VitalsService();
        $updatedRecords = $vitalsService->save($data);
        return $updatedRecords;
    }

    public function insertVital($pid, $eid, $data)
    {
        $data['eid'] = $eid;
        $data['authorized'] = '1';
        $data['pid'] = $pid;
        $vitalsService = new VitalsService();
        $savedVitals = $vitalsService->save($data);

        // need to grab the form record here, not sure why people need this but sure, why not, since the old method returned
        // it we will keep the functionality.
        $vitalsFormId = $savedVitals['id'];
        $formId = intval(QueryUtils::fetchSingleValue('select id FROM forms WHERE form_id = ? ', 'id', [$vitalsFormId]));
        return [$vitalsFormId, $formId];
    }

    public function getVitals($pid, $eid)
    {
        $vitalsService = new VitalsService();
        $vitals = $vitalsService->getVitalsForPatientEncounter($pid, $eid) ?? [];
        return $vitals;
    }

    public function getVital($pid, $eid, $vid)
    {
        $vitalsService = new VitalsService();
        $vitals = $vitalsService->getVitalsForForm($vid);
        if (!empty($vitals) && $vitals['eid'] == $eid && $vitals['pid'] == $pid) {
            return $vitals;
        }
        return null;
    }

    public function getSoapNotes($pid, $eid)
    {
        $sql = "  SELECT fs.*";
        $sql .= "  FROM forms fo";
        $sql .= "  JOIN form_soap fs on fs.id = fo.form_id";
        $sql .= "  WHERE fo.encounter = ?";
        $sql .= "    AND fs.pid = ?";

        $statementResults = sqlStatement($sql, array($eid, $pid));

        $results = array();
        while ($row = sqlFetchArray($statementResults)) {
            array_push($results, $row);
        }

        return $results;
    }

    public function getSoapNote($pid, $eid, $sid)
    {
        $sql = "  SELECT fs.*";
        $sql .= "  FROM forms fo";
        $sql .= "  JOIN form_soap fs on fs.id = fo.form_id";
        $sql .= "  WHERE fo.encounter = ?";
        $sql .= "    AND fs.id = ?";
        $sql .= "    AND fs.pid = ?";

        return sqlQuery($sql, array($eid, $sid, $pid));
    }

    public function validateSoapNote($soapNote)
    {
        $validator = new Validator();

        $validator->optional('subjective')->lengthBetween(2, 65535);
        $validator->optional('objective')->lengthBetween(2, 65535);
        $validator->optional('assessment')->lengthBetween(2, 65535);
        $validator->optional('plan')->lengthBetween(2, 65535);

        return $validator->validate($soapNote);
    }

    public function validateVital($vital)
    {
        $validator = new Validator();

        $validator->optional('temp_method')->lengthBetween(1, 255);
        $validator->optional('note')->lengthBetween(1, 255);
        $validator->optional('BMI_status')->lengthBetween(1, 255);
        $validator->optional('bps')->numeric();
        $validator->optional('bpd')->numeric();
        $validator->optional('weight')->numeric();
        $validator->optional('height')->numeric();
        $validator->optional('temperature')->numeric();
        $validator->optional('pulse')->numeric();
        $validator->optional('respiration')->numeric();
        $validator->optional('BMI')->numeric();
        $validator->optional('waist_circ')->numeric();
        $validator->optional('head_circ')->numeric();
        $validator->optional('oxygen_saturation')->numeric();

        return $validator->validate($vital);
    }

    public function getEncountersForPatientByPid($pid)
    {
        $encounterResult = $this->search(['pid' => $pid]);
        if ($encounterResult->hasData()) {
            return $encounterResult->getData();
        }
        return [];
    }

    /**
     * The result of this function returns the format needed by the frontend with the window.left_nav.setPatientEncounter function
     * @param $pid
     * @return array
     */
    public function getPatientEncounterListWithCategories($pid)
    {
        $encounters = $this->getEncountersForPatientByPid($pid);

        $encounterList = [
            'ids' => []
            ,'dates' => []
            ,'categories' => []
        ];
        foreach ($encounters as $index => $encounter) {
            $encounterList['ids'][$index] = $encounter['eid'];
            $encounterList['dates'][$index] = date("Y-m-d", strtotime($encounter['date']));
            $encounterList['categories'][$index] = $encounter['pc_catname'];
        }
        return $encounterList;
    }

    /**
     * Returns the sensitivity level for the encounter matching the patient and encounter identifier.
     *
     * @param  $pid          The legacy identifier of particular patient
     * @param  $encounter_id The identifier of a particular encounter
     * @return string         sensitivity_level of first row of encounter data
     */
    public function getSensitivity($pid, $encounter_id)
    {
        $encounterResult = $this->search(['pid' => $pid, 'eid' => $encounter_id], $options = ['limit' => '1']);
        if ($encounterResult->hasData()) {
            return $encounterResult->getData()[0]['sensitivity'];
        }
        return [];
    }

    /**
     * Returns the referring provider for the encounter matching the patient and encounter identifier.
     *
     * @param  $pid          The legacy identifier of particular patient
     * @param  $encounter_id The identifier of a particular encounter
     * @return string        referring provider of first row of encounter data (it's an id from the users table)
     */
    public function getReferringProviderID($pid, $encounter_id)
    {
        $encounterResult = $this->search(['pid' => $pid, 'eid' => $encounter_id], $options = ['limit' => '1']);
        if ($encounterResult->hasData()) {
            return $encounterResult->getData()[0]['referring_provider_id'] ?? '';
        }
        return [];
    }
}
