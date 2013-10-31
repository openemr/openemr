<?php

/**
 * interface/eRxSOAP.php Functions for interacting with NewCrop SOAP calls.
 *
 * Copyright (C) 2013 Sam Likins <sam.likins@wsi-services.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option) any
 * later version.  This program is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.  You should have received a copy of the GNU
 * General Public License along with this program.
 * If not, see <http://opensource.org/licenses/gpl-license.php>.
 * 
 * @package    OpenEMR
 * @subpackage NewCrop
 * @author     Sam Likins <sam.likins@wsi-services.com>
 * @link       http://www.open-emr.org
 */

require_once($GLOBALS['fileroot'] . '/interface/eRxStore.php');
require_once($GLOBALS['srcdir'] . '/xmltoarray_parser_htmlfix.php');
require_once($GLOBALS['srcdir'] . '/lists.inc');
require_once($GLOBALS['srcdir'] . '/amc.php');

class eRxSOAP {

	const ACTION_ALLERGIES		= 'allergies';
	const ACTION_MEDICATIONS	= 'medications';

	const FLAG_PRESCRIPTION_PRESS	= '1';
	const FLAG_PRESCRIPTION_IMPORT	= '2';
	const FLAG_ALLERGY_PRESS		= '3';
	const FLAG_ALLERGY_IMPORT		= '4';

	private $store;
	private $authUserId;
	private $authUserDetails;
	private $patientId;
	private $credentials;
	private $soapPath;
	private $soapClient;
	private $soapSettings = array();
	private $accountId;
	private $siteId;

	/**
	 * Repair HTML/XML and return array
	 * @param  string        $xml XML for processing
	 * @return array|boolean      Array on success, false on failure
	 */
	static public function htmlFixXmlToArray($xml) {
		$xmltoarray = new xmltoarray_parser_htmlfix();					//create instance of class

		$xmltoarray->xmlparser_setoption(XML_OPTION_SKIP_WHITE, 1);		//set options same as xml_parser_set_option
		$xmltoarray->xmlparser_setoption(XML_OPTION_CASE_FOLDING, 0);

		$xmltoarray->xmlparser_fix_into_struct(base64_decode($xml));	//fixes html values for XML

		$array = $xmltoarray->createArray();							//creates an array with fixed html values

		foreach($array as $key => $value) {
			$array[$key] = $xmltoarray->fix_html_entities($value);		//returns proper html values
		}

		if(array_key_exists('NewDataSet', $array) && array_key_exists('Table', $array['NewDataSet']))
			$array = $array['NewDataSet']['Table'];
		else
			$array = false;

		return $array;
	}

	/**
	 * Set Store to handle eRx cashed data
	 * @param  object  $store The eRx Store object to use for processing
	 * @return eRxSOAP        This object is returned for method chaining
	 */
	public function setStore($store) {
		$this->store = $store;

		return $this;
	}

	/**
	 * Get Store for handling eRx cashed data
	 * @return object The eRx Store object to use for processing
	 */
	public function getStore() {
		return $this->store;
	}

	/**
	 * Set Account Id for SOAP communications with NewCrop
	 * @param  string  $id The Account Id to send with SOAP requests to NewCrop
	 * @return eRxSOAP     This object is returned for method chaining
	 */
	public function setAccountId($id) {
		$this->accountId = $id;

		return $this;
	}

	/**
	 * Get Account Id set for SOAP communications with NewCrop
	 * @return string The Account Id sent with SOAP requests to NewCrop
	 */
	public function getAccountId() {
		if(null === $this->accountId)
			$this->accountId = $GLOBALS['erx_account_id'];

		return $this->accountId;
	}

	/**
	 * Set SiteId for SOAP communications with NewCrop
	 * @param  string  $id The Site Id to send with SOAP requests to NewCrop
	 * @return eRxSOAP     This object is returned for method chaining
	 */
	public function setSiteId($id) {
		$this->siteId = $id;

		return $this;
	}

	/**
	 * Get Site Id set for SOAP communications with NewCrop
	 * @return string The Site Id sent with SOAP requests to NewCrop
	 */
	public function getSiteId() {
		if(null === $this->siteId)
			$this->siteId = $this->getStore()
				->selectFederalEin();

		return $this->siteId;
	}

	/**
	 * Get the authenticated users ID and NPI
	 * @return array The users ID and NPI
	 */
	public function getAuthUserDetails() {
		if(null === $this->authUserDetails)
			$this->authUserDetails = $this->getStore()
				->getUserById($this->getAuthUserId());

		return $this->authUserDetails;
	}

	/**
	 * Set the Id of the authenticated user
	 * @param  integer $user The Id for the authenticated user
	 * @return eRxSOAP       This object is returned for method chaining
	 */
	public function setAuthUserId($user) {
		$this->authUserId = $user;

		return $this;
	}

	/**
	 * Get the Id of the authenticated user
	 * @return integer The Id of the authenticated user
	 */
	public function getAuthUserId() {
		return $this->authUserId;
	}

	/**
	 * Set the Id of the current patient
	 * @param  integer $id The Id of the current patient
	 * @return eRxSOAP     This object is returned for method chaining
	 */
	public function setPatientId($id) {
		$this->patientId = (integer) $id;

		return $this;
	}

	/**
	 * Get the Id of the current patient
	 * @return integer The Id of the current patient
	 */
	public function getPatientId() {
		if(null === $this->patientId) {
			if(array_key_exists('patient', $_REQUEST))
				$this->patientId = $_REQUEST['patient'];
			elseif(array_key_exists('pid', $GLOBALS))
				$this->patientId = $GLOBALS['pid'];
		}

		return $this->patientId;
	}

	/**
	 * Set the credentials for SOAP requests
	 * @param  array   $credentials Set of credentials: index [0 = Partner Name, 1 = eRx Name, 2 = eRx Password]
	 * @return eRxSOAP              This object is returned for method chaining
	 */
	public function setCredentials($credentials) {
		$this->credentials = $credentials;

		return $this;
	}

	/**
	 * Get the credentials for SOAP requests
	 * @return array Set of credentials: index [0 = Partner Name, 1 = eRx Name, 2 = eRx Password]
	 */
	public function getCredentials() {
		return $this->credentials;
	}

	/**
	 * Set SOAP Web Service Address paths
	 * @param  array   $path Set of paths: index [0 = Update, 1 = Patient]
	 * @return eRxSOAP       This object is returned for method chaining
	 */
	public function setSoapPath($path) {
		$this->soapPath = (array) $path;

		return $this;
	}

	/**
	 * Get SOAP Web Service Address paths
	 * @return array Set of paths: index [0 = Update, 1 = Patient]
	 */
	public function getSoapPath() {
		return $this->soapPath;
	}

	/**
	 * Generate and set a new SOAP client with provided Path Id
	 * @param  integer    $pathId Id for path set with getSoapPath: index [0 = Update, 1 = Patient]
	 * @return SoapClient         Soap Client
	 */
	public function initializeSoapClient($pathId) {
		return $this->setSoapClient(new SoapClient($this->soapPath[(integer) $pathId]));
	}

	/**
	 * Set SOAP client for communication with NewCrop
	 * @param  SoapClient $client SOAP client for communication with NewCrop
	 * @return eRxSOAP            This object is returned for method chaining
	 */
	public function setSoapClient(SoapClient $client) {
		$this->soapClient = $client;

		return $this;
	}

	/**
	 * Get SOAP client for communication with NewCrop
	 * @return SoapClient SOAP client for communication with NewCrop
	 */
	public function getSoapClient() {
		return $this->soapClient;
	}

	/**
	 * Set SOAP call settings for calls to NewCrop
	 * @param  array   $settings [optional] Setting to send with SOAP call to NewCrop
	 * @return eRxSOAP           This object is returned for method chaining
	 */
	public function setSoapSettings($settings = array()) {
		$this->soapSettings = (array) $settings;

		return $this;
	}

	/**
	 * Get SOAP call settings for calls to NewCrop
	 * @return array Settings to send with SOAP call to NewCrop
	 */
	public function &getSoapSettings() {
		return $this->soapSettings;
	}

	/**
	 * Get TTL for provided SOAP process
	 * @param  string         $process SOAP process to retrieve TTL for
	 * @return number|boolean          Number on success, false on failure
	 */
	public function getTTL($process) {
		if(array_key_exists('erx_soap_ttl_'.$process, $GLOBALS))
			return $GLOBALS['erx_soap_ttl_'.$process];
		else
			return false;
	}

	/**
	 * Check if TTL of current patient has elapsed for provided SOAP process
	 * @param  string  $process SOAP process to check against for elapsed TTL of current patient
	 * @return boolean          True if TTL of current patient has elapsed for provided SOAP process, otherwise false
	 */
	public function elapsedTTL($process) {
		$ttl = $this->getTTL($process);
		if(false === $ttl || 0 == $ttl)
			return true;

		$soap = $this->getStore()->getLastSOAP($process, $this->getPatientId());
		if(false === $soap)
			return true;

		return strtotime('-'.$ttl.' seconds') >= strtotime($soap);
	}

	/**
	 * Update provided SOAP process TTL timestamp of current patient 
	 * @param  string  $process SOAP process to update TTL of current patient
	 * @return eRxSOAP          This object is returned for method chaining
	 */
	public function updateTTL($process) {
		$this->getStore()->setLastSOAP($process, $this->getPatientId());

		return $this;
	}

	/**
	 * Check if import status of current patient is set to provided SOAP process(es)
	 * @param  string|array $status SOAP process to check against import status of current patient, optionally an array of SOAP processes can be substituted
	 * @return boolean              True if import status of current patient is set to provided SOAP process(es), otherwise false
	 */
	public function checkPatientImportStatus($status) {
		$currentStatus = $this->getStore()
			->getPatientImportStatusByPatientId(
				$this->getPatientId()
			);

		if(is_array($status))
			$return = in_array($currentStatus, $status);
		else
			$return = ($currentStatus == $status);

		return $return;
	}

	/**
	 * [updatePatientImportStatus description]
	 * @param  string  $status SOAP process to update import status of current patient
	 * @return eRxSOAP         This object is returned for method chaining
	 */
	public function updatePatientImportStatus($status) {
		$this->getStore()
			->updatePatientImportStatusByPatientId(
				$this->getPatientId(),
				$status
			);

		return $this;
	}

	/**
	 * Initialize SOAP settings with the credentials currently set
	 * @return eRxSOAP This object is returned for method chaining
	 */
	public function initializeCredentials() {
		$this->soapSettings['credentials'] = array(
			'PartnerName'	=> $this->credentials['0'],
			'Name'			=> $this->credentials['1'],
			'Password'		=> $this->credentials['2'],
		);

		return $this;
	}

	/**
	 * Initialize SOAP settings with the NewCrop account and site Ids
	 * @return eRxSOAP This object is returned for method chaining
	 */
	public function initializeAccountRequest() {
		$this->soapSettings['accountRequest'] = array(
			'AccountId'	=> $this->getAccountId(),
			'SiteId'	=> $this->getSiteId(),
		);

		return $this;
	}

	/**
	 * Initialize SOAP settings with patient information
	 * @return eRxSOAP This object is returned for method chaining
	 */
	public function initializePatientInformationRequester() {
		$userDetails = $this->getAuthUserDetails();

		$this->soapSettings['patientInformationRequester'] = array(
			'UserId'	=> $userDetails['id'],
			'UserType'	=> 'D',
		);

		return $this;
	}

	/**
	 * Get account status information for current patient
	 * @return object SOAP client response from NewCrop call
	 */
	public function getAccountStatus() {
		$this->setSoapSettings()
			->initializeCredentials()
			->initializeAccountRequest();

		$userDetails = $this->getAuthUserDetails();

		$this->soapSettings['locationId'] = $this->getPatientId();
		$this->soapSettings['userId'] = $userDetails['npi'];
		$this->soapSettings['userType'] = 'P';

		$this->initializeSoapClient(1);

		return $this->getSoapClient()
			->GetAccountStatus($this->soapSettings);
	}

	/**
	 * Get allergy history for current patient
	 * @return object SOAP client response from NewCrop call
	 */
	public function getPatientAllergyHistoryV3() {
		$this->setSoapSettings()
			->initializeCredentials()
			->initializeAccountRequest()
			->initializePatientInformationRequester();

		$this->soapSettings['patientRequest']['PatientId'] = $this->getPatientId();

		$this->initializeSoapClient(0);

		return $this->getSoapClient()
			->GetPatientAllergyHistoryV3($this->soapSettings);
	}

	/**
	 * Get full medication history for current patient
	 * @return object SOAP client response from NewCrop call
	 */
	public function getPatientFullMedicationHistory6() {
		$this->setSoapSettings()
			->initializeCredentials()
			->initializeAccountRequest()
			->initializePatientInformationRequester();

		$this->soapSettings['patientRequest']['PatientId'] = $this->getPatientId();

		$this->soapSettings['prescriptionHistoryRequest'] = array(
			'StartHistory'				=> '2011-01-01T00:00:00.000',
			'EndHistory'				=> date('Y-m-d').'T23:59:59.000',
			'PrescriptionStatus'		=> 'C',
			'PrescriptionSubStatus'		=> '%',
			'PrescriptionArchiveStatus'	=> 'N',
		);

		$this->soapSettings['patientIdType'] = '';
		$this->soapSettings['includeSchema'] = '';

		$this->initializeSoapClient(0);

		return $this->getSoapClient()
			->GetPatientFullMedicationHistory6($this->soapSettings);
	}

	/**
	 * Get free form allergy history for current patient
	 * @return object SOAP client response from NewCrop call
	 */
	public function getPatientFreeFormAllergyHistory() {
		$this->setSoapSettings()
			->initializeCredentials()
			->initializeAccountRequest()
			->initializePatientInformationRequester();

		$this->soapSettings['patientRequest']['PatientId'] = $this->getPatientId();

		$client = $this->initializeSoapClient(0);

		return $this->getSoapClient()
			->GetPatientFreeFormAllergyHistory($this->soapSettings);
	}

	/**
	 * Insert list option if missing and return the associated option Id
	 * @param  string $listId Id of list to reference
	 * @param  string $title  Title text to find
	 * @return string         Option Id of selected list item
	 */
	public function insertMissingListOptions($listId, $title) {
		$store = $this->getStore();

		$optionId = $store->selectOptionIdByTitle($listId, $title);

		if(false === $optionId) {
			$optionId = 1 + $store->selectOptionIdsByListId($listId);

			$store->insertListOptions($listId, $optionId, $title);
		}

		return $optionId;
	}

	/**
	 * Trigger Allergy History SOAP call to NewCrop for current patient and update local cached data
	 * @return integer Count of newly cached records
	 */
	public function insertUpdateAllergies() {
		$store = $this->getStore();

		$insertedRows = 0;

		$allergyArray = self::htmlFixXmlToArray(
			$this->getPatientAllergyHistoryV3()
				->GetPatientAllergyHistoryV3Result
				->XmlResponse
		);

		if(is_array($allergyArray)) {
			foreach($allergyArray as $allergy) {
				$optionId = $this->insertMissingListOptions(
					'outcome',
					$allergy['AllergySeverityName']
				);

				$allergySource = $store->selectAllergyErxSourceByPatientIdName(
					$this->getPatientId(),
					$allergy['AllergyName']
				);


				if(false === $allergySource) {
					$store->insertAllergy(
						$allergy['AllergyName'],
						$allergy['AllergyId'],
						$this->getPatientId(),
						$this->getAuthUserId(),
						$optionId
					);

					++$insertedRows;
				} elseif(0 == $allergySource) {
					$store->updateAllergyOutcomeExternalIdByPatientIdName(
						$optionId,
						$allergy['AllergyId'],
						$this->getPatientId(),
						$allergy['AllergyName']
					);
				} else {
					$store->updateAllergyOutcomeByPatientIdExternalIdName(
						$optionId,
						$this->getPatientId(),
						$allergy['AllergyId'],
						$allergy['AllergyName']
					);
				}
			}

			$this->updatePatientAllergyEndDate($allergyArray);
		}

		return $insertedRows;
	}

	/**
	 * Iterate through provided list of allergies and update records with end dates
	 * @param  array   $allergyArray List of allergies
	 * @return eRxSOAP               This object is returned for method chaining
	 */
	public function updatePatientAllergyEndDate($allergyArray) {
		$store = $this->getStore();
		$patientId = $this->getPatientId();

		$resource = $store->selectActiveAllergiesByPatientId($patientId);

		while($row = sqlFetchArray($resource)) {
			$noMatch = true;

			foreach($allergyArray as $allergy) {
				if(array_key_exists('AllergyName', $allergy) && $allergy['AllergyName'] == $row['title']) {
					$noMatch = false;
					break;
				}
			}

			if($noMatch)
				$store->updateAllergyEndDateByPatientIdListId(
					$patientId,
					$row['id']
				);
		}

		return $this;
	}

	/**
	 * Update eRx uploaded status for current patient allergies
	 * @return boolean True on success, false on failure
	 */
	public function updateUploadedErx() {
		$patientFreeFormAllergyHistory = $this
			->getPatientFreeFormAllergyHistory()
			->GetPatientFreeFormAllergyHistoryResult;

		if(0 < $patientFreeFormAllergyHistory->result->RowCount) {
			$response = $patientFreeFormAllergyHistory
				->patientFreeFormAllergyExtendedDetail
				->PatientFreeFormAllergyExtendedDetail;

			if(!is_array($response))
				$response = array($response);

			foreach($response as $response) {
				$this->getStore()
					->updateErxUploadedByListId($response->ExternalId);
			}
		}

		return isset($response);
	}

	/**
	 * Insert or update medications for current patient
	 * @return integer Count of newly cached records
	 */
	public function insertUpdateMedications() {
		$store = $this->getStore();

		$insertedRows = 0;

		$medArray = self::htmlFixXmlToArray(
			$this->getPatientFullMedicationHistory6()
				->GetPatientFullMedicationHistory6Result
				->XmlResponse
		);

		$store->updatePrescriptionsActiveByPatientId($this->getPatientId());

		if(is_array($medArray)) {
			foreach($medArray as $med) {
				if($med['DosageForm'])
					$optionIdDosageForm = $this->insertMissingListOptions(
						'drug_form',
						$med['DosageForm']
					);

				if($med['Route'])
					$optionIdRoute = $this->insertMissingListOptions(
						'drug_route',
						$med['Route']
					);

				if($med['StrengthUOM'])
					$optionIdStrengthUOM = $this->insertMissingListOptions(
						'drug_units',
						$med['StrengthUOM']
					);

				if($med['DosageFrequencyDescription'])
					$optionIdFrequencyDescription = $this->insertMissingListOptions(
						'drug_interval',
						$med['DosageFrequencyDescription']
					);

				$providerId = $store->selectUserIdByUserName($med['ExternalPhysicianID']);

				$check = $store->selectPrescriptionIdByGuidPatientId(
					$med['PrescriptionGuid'],
					$med['ExternalPatientID']
				);

				$prescriptionId = '';

				if(0 == sqlNumRows($check)) {
					$prescriptionId = $store->insertPrescriptions(
						$med,
						$encounter,
						$providerId,
						$this->getAuthUserId(),
						$optionIdDosageForm,
						$optionIdRoute,
						$optionIdStrengthUOM,
						$optionIdFrequencyDescription
					);

					++$insertedRows;

					setListTouch($this->getPatientId(), 'prescription_erx');
				} else {
					$store->updatePrescriptions(
						$med,
						$providerId,
						$this->getAuthUserId(),
						$optionIdDosageForm,
						$optionIdRoute,
						$optionIdStrengthUOM,
						$optionIdFrequencyDescription
					);
				}

				$result = sqlFetchArray($check);
				if($result['id'])
					$prescriptionId = $result['id'];

				processAmcCall(
					'e_prescribe_amc',
					true,
					'add',
					$med['ExternalPatientID'],
					'prescriptions',
					$prescriptionId
				);
			}
		}

		return $insertedRows;
	}

}