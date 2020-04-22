<?php

/**
 * interface/eRxStore.php Functions for interacting with NewCrop database.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2013-2015 Sam Likins <sam.likins@wsi-services.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../library/api.inc");

class eRxStore
{

    /**
     * Strip away any non numerical characters
     * @param  string $value Value to sanitize
     * @return string        Value sanitized of all non numerical characters
     */
    public static function sanitizeNumber($value)
    {
        return preg_replace('/[^-0-9.]/', '', $value);
    }

    /**
     * Return the primary business entity
     * @return array Primary business entity
     */
    public function getFacilityPrimary()
    {
        $return = sqlQuery('SELECT `name`, `federal_ein`, `street`, `city`, `state`, `postal_code`, `country_code`, `phone`, `fax`
			FROM `facility`
			WHERE `primary_business_entity` = \'1\';');

        return $return;
    }

    /**
     * Return the Federal EIN established with the primary business entity
     * @return string Federal EIN for the primary business entity
     */
    public function selectFederalEin()
    {
        $return = $this->getFacilityPrimary();

        return $return['federal_ein'];
    }

    /**
     * Return user information using user Id
     * @param  integer $id Id of user to return
     * @return array       Specified user information: index [id, username, lname, fname, mname, title, license, federaldrugid, upin, state_license_number, npi, newcrop_user_role]
     */
    public function getUserById($id)
    {
        return sqlQuery(
            'SELECT id, username, lname, fname, mname, title, federaldrugid, upin, state_license_number, npi, newcrop_user_role
			FROM users
			WHERE id = ?;',
            array($id)
        );
    }

    /**
     * Return user facility business entity
     * @param  integer $id Id of user to return
     * @return array       User facility business entity
     */
    public function getUserFacility($id)
    {
        return sqlQuery(
            'SELECT facility.id, facility.name, facility.street, facility.city, facility.state, facility.postal_code, facility.country_code, facility.phone, facility.fax
			FROM users
				LEFT JOIN facility ON facility.id = users.facility_id
			WHERE users.id = ?;',
            array($id)
        );
    }

    /**
     * Return patient information using patient Id
     * @param  integer $patientId Id of patient
     * @return array              Specified patient information: index [pid, fname, mname, lname, street, city, state, postal_code, country_code, phone_home, date_of_birth, sex]
     */
    public function getPatientByPatientId($patientId)
    {
        return sqlQuery(
            'SELECT pid, fname, mname, lname, street, city, state, postal_code, country_code, phone_home, DATE_FORMAT(DOB,\'%Y%m%d\') AS date_of_birth, sex
			FROM patient_data
			WHERE pid = ?;',
            array($patientId)
        );
    }

    public function getPatientVitalsByPatientId($patientId)
    {
        $result = sqlQuery(
            "SELECT FORM_VITALS.date, FORM_VITALS.id 
            FROM form_vitals AS FORM_VITALS LEFT JOIN forms AS FORMS ON FORM_VITALS.id = FORMS.form_id 
            WHERE FORM_VITALS.pid=? AND FORMS.deleted != '1' 
            ORDER BY FORM_VITALS.date DESC",
            array($patientId)
        );

        $data = formFetch("form_vitals", $result['id']);

        $weight = number_format($data['weight'] * 0.45359237, 2);
        $height = round(number_format($data['height'] * 2.54, 2), 1);

        return [
            'height' => $height,
            'height_units' => 'cm',
            'weight' => $weight,
            'weight_units' => 'kg'
        ];
    }

    public function getPatientHealthplansByPatientId($patientId)
    {
        return sqlStatement(
            'SELECT `ins`.`name`
			FROM (
				SELECT
					`id`.`type`,
					`ic`.`name`
				FROM `insurance_data` AS `id`
					LEFT JOIN `insurance_companies` AS `ic` ON `ic`.`id` = `id`.`provider`
				WHERE `id`.`pid` = ?
					AND `id`.`subscriber_relationship` = \'self\'
					AND `id`.`provider` > 0
				ORDER BY `id`.`date` DESC
			) AS `ins`
			GROUP BY `ins`.`type`;',
            array($patientId)
        );
    }

    public function getPatientAllergiesByPatientId($patientId)
    {
        return sqlStatement(
            'SELECT id, lists.title as title1, list_options.title as title2, comments
			FROM lists
				LEFT JOIN list_options ON lists.outcome = list_options.option_id
					AND list_options.list_id = \'outcome\'
			WHERE `type` = \'allergy\'
				AND pid = ?
				AND erx_source = \'0\'
				AND erx_uploaded = \'0\'
				AND (
					enddate is NULL
					OR enddate = \'\'
					OR enddate = \'0000-00-00\'
				);',
            array($patientId)
        );
    }

    /**
     * Return TTL timestamp for provided patient Id and process
     * @param  string         $process   SOAP process to check
     * @param  integer        $patientId Patient Id to check
     * @return string|boolean            TTL timestamp of last SOAP call for provided patient Id and process
     */
    public function getLastSOAP($process, $patientId)
    {
        $return = sqlQuery(
            'SELECT updated
			FROM erx_ttl_touch
			WHERE patient_id = ?
				AND process = ?;',
            array(
                $patientId,
                $process
            )
        );
        if ($return === false) {
            return false;
        }

        return $return['updated'];
    }

    /**
     * Set TTL timestamp for provided patient Id and process
     * @param  string  $process   SOAP process to update
     * @param  integer $patientId Patient Id to update
     */
    public function setLastSOAP($process, $patientId)
    {
        sqlQuery(
            'REPLACE INTO erx_ttl_touch
			SET patient_id = ?,
				process = ?,
				updated = NOW();',
            array(
                $patientId,
                $process
            )
        );
    }

    /**
     * Update external sourced prescripts active status for provided patient Id
     * @param  integer $patientId Patient Id to update
     * @param  integer $active    Active status to set for provided patient
     */
    public function updatePrescriptionsActiveByPatientId($patientId, $active = 0)
    {
        sqlQuery(
            'UPDATE prescriptions
			SET active = ?
			WHERE patient_id = ?
				AND erx_source=\'1\'',
            array(
                ($active == 1 ? 1 : 0),
                $patientId
            )
        );
    }

    public function updatePrescriptionsUploadActiveByPatientIdPrescriptionId($upload, $active, $patientId, $prescriptionId)
    {
        sqlQuery(
            'UPDATE prescriptions
			SET erx_uploaded = ?,
				active = ?
			WHERE patient_id = ?
				AND id = ?;',
            array(
                $upload,
                $active,
                $patientId,
                $prescriptionId
            )
        );
    }

    /**
     * Return prescription specified
     * @param  integer $prescriptionId Id of the prescription to return
     * @return array                   Prescription information specified
     */
    public function getPrescriptionById($prescriptionId)
    {
        return sqlQuery(
            'SELECT p.note, p.dosage, p.substitute, p.per_refill, p.form, p.route, p.size, p.interval, p.drug, p.quantity,
			p.id AS prescid, l1.title AS title1, l2.title AS title2, l3.title AS title3, l4.title AS title4,
			DATE_FORMAT(date_added,\'%Y%m%d\') AS date_added, CONCAT_WS(fname, \' \', mname, \' \', lname) AS docname
			FROM prescriptions AS p
			LEFT JOIN users AS u ON p.provider_id = u.id
			LEFT JOIN list_options AS l1 ON l1.list_id = \'drug_form\'
				AND l1.option_id = p.form
			LEFT JOIN list_options AS l2 ON l2.list_id = \'drug_route\'
				AND l2.option_id = p.route
			LEFT JOIN list_options AS l3 ON l3.list_id = \'drug_interval\'
				AND l3.option_id = p.interval
			LEFT JOIN list_options AS l4 ON l4.list_id = \'drug_units\'
				AND l4.option_id = p.unit
			WHERE p.drug <> \'\'
				AND p.id = ?;',
            array($prescriptionId)
        );
    }


    public function selectMedicationsNotUploadedByPatientId($patientId, $uploadActive, $limit)
    {
        return sqlStatement(
            'SELECT id, begdate, title
			FROM lists
			WHERE type = \'medication\'
				AND pid = ?
				AND title <> \'\'
				AND erx_uploaded = \'0\'
				AND (? = 0
					OR (enddate IS NULL
						OR enddate = \'\'
						OR enddate = \'0000-00-00\'
						)
					)
			ORDER BY enddate
			LIMIT 0, ?;',
            array(
                $patientId,
                $uploadActive,
                $limit
            )
        );
    }

    public function selectPrescriptionIdsNotUploadedByPatientId($patientId, $uploadActive, $limit)
    {
        return sqlStatement(
            'SELECT id
			FROM prescriptions
			WHERE patient_id = ?
				AND erx_source = \'0\'
				AND erx_uploaded = \'0\'
				AND (? = 0
					OR active = 1
				) LIMIT 0, ?;',
            array(
                $patientId,
                $uploadActive,
                $limit,
            )
        );
    }

    /**
     * Return option Id for title text of specified list
     * @param  string $listId Id of list to reference
     * @param  string $title  Title text to find
     * @return string         Option Id of selected list item
     */
    public function selectOptionIdByTitle($listId, $title)
    {
        $return = sqlQuery(
            'SELECT option_id
			FROM list_options
			WHERE list_id = ? AND activity = 1
				AND title = ?;',
            array(
                $listId,
                $title
            )
        );

        if (is_array($return)) {
            $return = $return['option_id'];
        }

        return $return;
    }

    /**
     * Return highest option Id for provided list Id
     * @param  string $listId  Id of list to reference
     * @return integer         Highest option Id for provided list Id
     */
    public function selectOptionIdsByListId($listId)
    {
        $return = sqlQuery(
            'SELECT option_id
			FROM list_options
			WHERE list_id = ? AND activity = 1
			ORDER BY ABS(option_id) DESC
			LIMIT 1;',
            array($listId)
        );

        if (is_array($return)) {
            $return = $return['option_id'];
        }

        return $return;
    }

    /**
     * Return user Id by user name
     * @param  string  $name Name of user to reference
     * @return integer       Id of provided user name
     */
    public function selectUserIdByUserName($name)
    {
        $return = sqlQuery(
            'SELECT id
			FROM users
			WHERE username = ?;',
            array($name)
        );

        return $return['id'];
    }

    /**
     * Insert new option to specified list
     * @param  string $listId   Id of list to add option to
     * @param  string $optionId Option Id to add to referenced list
     * @param  string $title    Title of option to add to new option
     */
    public function insertListOptions($listId, $optionId, $title)
    {
        sqlQuery(
            'INSERT INTO list_options
				(list_id, option_id, title, seq)
			VALUES
				(?, ?, ?, ?);',
            array(
                $listId,
                $optionId,
                $title,
                $optionId
            )
        );
    }

    /**
     * Return Id of prescription selected by GUID and patient Id
     * @param  string   $prescriptionGuid GUID of prescription
     * @param  integer  $patientId        Id of patient
     * @return resource                   Prescription Id of specified GUID for selected patient, this resource comes from a call to mysql_query()
     */
    public function selectPrescriptionIdByGuidPatientId($prescriptionGuid, $patientId)
    {
        return sqlStatement(
            'SELECT id
			FROM prescriptions
			WHERE prescriptionguid = ?
				AND prescriptionguid IS NOT NULL
				AND patient_id = ?;',
            array(
                $prescriptionGuid,
                $patientId
            )
        );
    }

    /**
     * Insert new prescription as external sourced
     * @param  array   $prescriptionData Information for creating prescription: [PrescriptionDate, DrugName, DrugID, DrugInfo, DosageNumberDescription, Strength, Refills, PrescriptionNotes, SiteID, rxcui, PrescriptionGuid, ExternalPatientID]
     * @param  integer $encounter        Id of encounter for prescription
     * @param  integer $providerId       Id of provider for prescription
     * @param  string  $authUserId       Id of user creating prescription
     * @param  integer $formOptionId     Option Id for prescription form
     * @param  integer $routeOptionId    Option Id for prescription route
     * @param  integer $unitsOptionId    Option Id for prescription units
     * @param  integer $intervalOptionId Option Id for prescription interval
     * @return integer                   Id of newly created prescription
     */
    public function insertPrescriptions($prescriptionData, $encounter, $providerId, $authUserId, $formOptionId, $routeOptionId, $unitsOptionId, $intervalOptionId)
    {
        return sqlInsert(
            'INSERT INTO `prescriptions`
				(
					`datetime`,
					`erx_source`,
					`encounter`,
					`date_added`,
					`user`,
					`provider_id`,
					`form`,
					`unit`,
					`route`,
					`interval`,
					`drug`,
					`drug_id`,
					`drug_info_erx`,
					`dosage`,
					`size`,
					`refills`,
					`note`,
					`site`,
					`rxnorm_drugcode`,
					`prescriptionguid`,
					`patient_id`
				)
			VALUES
				(
					NOW(), \'1\', ?, ?, ?,
					?, ?, ?, ?, ?, ?, ?, ?,
					?, ?, ?, ?, ?, ?, ?, ?
				);',
            array(
                $encounter,
                substr($prescriptionData['PrescriptionDate'], 0, 10),
                $authUserId,
                $providerId,
                $formOptionId,
                $unitsOptionId,
                $routeOptionId,
                $intervalOptionId,
                $prescriptionData['DrugName'],
                $prescriptionData['DrugID'],
                $prescriptionData['DrugInfo'],
                $prescriptionData['DosageNumberDescription'],
                self::sanitizeNumber($prescriptionData['Strength']),
                $prescriptionData['Refills'],
                $prescriptionData['PrescriptionNotes'],
                $prescriptionData['SiteID'],
                $prescriptionData['rxcui'],
                $prescriptionData['PrescriptionGuid'],
                $prescriptionData['ExternalPatientID']
            )
        );
    }

    /**
     * Update prescription information as external sourced
     * @param  array   $prescriptionData Information for creating prescription: [DrugName, DrugID, DrugInfo, DosageNumberDescription, Strength, Refills, PrescriptionNotes, SiteID, rxcui, PrescriptionGuid, ExternalPatientID]
     * @param  integer $providerId       Id of provider for prescription
     * @param  string  $authUserId       Id of user creating prescription
     * @param  integer $formOptionId     Option Id for prescription form
     * @param  integer $routeOptionId    Option Id for prescription route
     * @param  integer $unitsOptionId    Option Id for prescription units
     * @param  integer $intervalOptionId Option Id for prescription interval
     */
    public function updatePrescriptions($prescriptionData, $providerId, $authUserId, $formOptionId, $routeOptionId, $unitsOptionId, $intervalOptionId)
    {
        sqlQuery(
            'UPDATE prescriptions SET
				`datetime` = NOW(),
				`erx_source` = \'1\',
				`active` = \'1\',
				`user` = ?,
				`provider_id` = ?,
				`form` = ?,
				`unit` = ?,
				`route` = ?,
				`interval` = ?,
				`drug` = ?,
				`drug_id` = ?,
				`drug_info_erx` = ?,
				`dosage` = ?,
				`size` = ?,
				`refills` = ?,
				`note` = ?,
				`site` = ?,
				`rxnorm_drugcode` = ?
			WHERE prescriptionguid = ?
				AND patient_id = ?;',
            array(
                $authUserId,
                $providerId,
                $formOptionId,
                $unitsOptionId,
                $routeOptionId,
                $intervalOptionId,
                $prescriptionData['DrugName'],
                $prescriptionData['DrugID'],
                $prescriptionData['DrugInfo'],
                $prescriptionData['DosageNumberDescription'],
                self::sanitizeNumber($prescriptionData['Strength']),
                $prescriptionData['Refills'],
                $prescriptionData['PrescriptionNotes'],
                $prescriptionData['SiteID'],
                $prescriptionData['rxcui'],
                $prescriptionData['PrescriptionGuid'],
                $prescriptionData['ExternalPatientID']
            )
        );
    }

    /**
     * Return eRx source of specified active allergy for selected patient
     * @param  integer $patientId Id of patient to select
     * @param  string  $name      Name of active allergy to return
     * @return integer            eRx source flag of specified allergy for selected patient: [0 = OpenEMR, 1 = External]
     */
    public function selectAllergyErxSourceByPatientIdName($patientId, $name)
    {
        $return = sqlQuery(
            'SELECT erx_source
			FROM lists
			WHERE pid = ?
				AND type = \'allergy\'
				AND title = ?
				AND (
					enddate IS NULL
					OR enddate = \'\'
					OR enddate = \'0000-00-00\'
				);',
            array(
                $patientId,
                $name
            )
        );

        if (is_array($return)) {
            $return = $return['erx_source'];
        }

        return $return;
    }

    /**
     * Insert new allergy as external sourced
     * @param  string  $name       Allergy name to insert
     * @param  integer $allergyId  External allergy Id
     * @param  integer $patientId  Patient Id
     * @param  integer $authUserId User Id
     * @param  integer $outcome    Allergy option Id
     */
    public function insertAllergy($name, $allergyId, $patientId, $authUserId, $outcome)
    {
        sqlQuery(
            'INSERT INTO lists
				(
					date, type, erx_source, begdate,
					title, external_allergyid, pid, user, outcome
				)
			VALUES
				(
					NOW(), \'allergy\', \'1\', NOW(),
					?, ?, ?, ?, ?
				);',
            array(
                $name,
                $allergyId,
                $patientId,
                $authUserId,
                $outcome
            )
        );

        setListTouch($patientId, 'allergy');
    }

    /**
     * Update allergy outcome and external Id as external sourced using patient Id and allergy name
     * @param  integer $outcome    Allergy outcome Id to set
     * @param  integer $externalId External allergy Id to set
     * @param  integer $patientId  Patient Id to select
     * @param  string  $name       Allergy name to select
     */
    public function updateAllergyOutcomeExternalIdByPatientIdName($outcome, $externalId, $patientId, $name)
    {
        sqlQuery(
            'UPDATE lists
			SET outcome = ?,
				erx_source = \'1\',
				external_allergyid = ?
			WHERE pid = ?
				AND title = ?;',
            array(
                $outcome,
                $externalId,
                $patientId,
                $name
            )
        );
    }

    /**
     * Update external sourced allergy outcome using patient Id, external Id, and allergy name
     * @param  integer $outcome    Allergy outcome Id to set
     * @param  integer $patientId  Patient Id to select
     * @param  integer $externalId External allergy Id to select
     * @param  string  $name       Allergy name to select
     */
    public function updateAllergyOutcomeByPatientIdExternalIdName($outcome, $patientId, $externalId, $name)
    {
        sqlQuery(
            'UPDATE lists
			SET outcome = ?
			WHERE pid = ?
				AND erx_source = \'1\'
				AND external_allergyid = ?
				AND title = ?;',
            array(
                $outcome,
                $patientId,
                $externalId,
                $name
            )
        );
    }

    public function updateAllergyUploadedByPatientIdAllergyId($uploaded, $patientId, $allergyId)
    {
        sqlQuery(
            'UPDATE lists
			SET erx_uploaded = ?
			WHERE type = \'allergy\'
				AND pid = ?
				AND id = ?;',
            array(
                $uploaded,
                $patientId,
                $allergyId
            )
        );
    }

    /**
     * Return all external sourced active allergies for patient using patient Id
     * @param  integer  $patientId Patient Id to select
     * @return resource            Patients active allergies, this resource comes from a call to mysql_query()
     */
    public function selectActiveAllergiesByPatientId($patientId)
    {
        return sqlStatement(
            'SELECT id, title
			FROM lists
			WHERE pid = ?
				AND type = \'allergy\'
				AND erx_source = \'1\'
				AND (
					enddate IS NULL
						OR enddate = \'\'
						OR enddate = \'0000-00-00\'
				);',
            array($patientId)
        );
    }

    /**
     * Update allergy end date for specified patient Id and list Id
     * @param  integer $patientId Id of patient to lookup
     * @param  integer $listId    Id of allergy to update
     */
    public function updateAllergyEndDateByPatientIdListId($patientId, $listId)
    {
        sqlQuery(
            'UPDATE lists
			SET enddate = now()
			WHERE pid = ?
				AND id = ?
				AND type = \'allergy\';',
            array(
                $patientId,
                $listId
            )
        );
    }

    /**
     * Update eRx uploaded status using list Id
     * @param  integer $listId Id of list item
     * @param  integer $erx    [optional - defaults to 0] Upload status to set: [0 = Pending NewCrop upload, 1 = Uploaded TO NewCrop]
     */
    public function updateErxUploadedByListId($listId, $erx = 0)
    {
        sqlQuery(
            'UPDATE lists
			SET erx_uploaded = ?
			WHERE id = ?;',
            array(
                $erx,
                $listId
            )
        );
    }

    /**
     * Return patient import status using patient Id
     * @param  integer $patientId Id of patient
     * @return integer            Import status for specified patient: [1 = Prescription Press, 2 = Prescription Import, 3 = Allergy Press, 4 = Allergy Import]
     */
    public function getPatientImportStatusByPatientId($patientId)
    {
        $return = sqlquery(
            'SELECT soap_import_status
			FROM patient_data
			WHERE pid = ?;',
            array($patientId)
        );
        return $return['soap_import_status'];
    }

    /**
     * Update patient import status using patient Id
     * @param  integer $patientId Id of patient to update
     * @param  integer $status    Import status to update specified patient: [1 = Prescription Press, 2 = Prescription Import, 3 = Allergy Press, 4 = Allergy Import]
     */
    public function updatePatientImportStatusByPatientId($patientId, $status)
    {
        sqlQuery(
            'UPDATE patient_data
			SET soap_import_status = ?
			WHERE pid = ?;',
            array(
                $status,
                $patientId
            )
        );
    }
}
