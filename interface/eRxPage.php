<?php

/**
 * interface/eRxPage.php Functions for redirecting to NewCrop pages.
 *
 * Copyright (C) 2015 Sam Likins <sam.likins@wsi-services.com>
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

class eRxPage {

	const DEBUG_XML    = 1;
	const DEBUG_RESULT = 2;

	private $xmlBuilder;
	private $authUserId;
	private $destination;
	private $patientId;
	private $prescriptionIds;
	private $prescriptionCount;

	public function __construct($xmlBuilder = null) {
		if($xmlBuilder) {
			$this->setXMLBuilder($xmlBuilder);
		}
	}

	/**
	 * Set XMLBuilder to handle eRx XML
	 * @param  object  $xmlBuilder The eRx XMLBuilder object to use for processing
	 * @return eRxPage             This object is returned for method chaining
	 */
	public function setXMLBuilder($xmlBuilder) {
		$this->xmlBuilder = $xmlBuilder;

		return $this;
	}

	/**
	 * Get XMLBuilder for handling eRx XML
	 * @return object The eRx XMLBuilder object to use for processing
	 */
	public function getXMLBuilder() {
		return $this->xmlBuilder;
	}

	/**
	 * Set the Id of the authenticated user
	 * @param  integer $userId The Id for the authenticated user
	 * @return eRxPage         This object is returned for method chaining
	 */
	public function setAuthUserId($userId) {
		$this->authUserId = $userId;

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
	 * Set the destination for the page request
	 * @param  string  $destination The destination for the page request
	 * @return eRxPage              This object is returned for method chaining
	 */
	public function setDestination($destination){
		$this->destination = $destination;

		return $this;
	}

	/**
	 * Get the destination for the page request
	 * @return string The destination for the page request
	 */
	public function getDestination(){
		return $this->destination;
	}

	/**
	 * Set the Patient Id for the page request
	 * @param  integer $patientId The Patient Id for the page request
	 * @return eRxPage            This object is returned for method chaining
	 */
	public function setPatientId($patientId){
		$this->patientId = $patientId;

		return $this;
	}

	/**
	 * Get the Patient Id for the page request
	 * @return string The Patient Id for the page request
	 */
	public function getPatientId(){
		return $this->patientId;
	}

	/**
	 * Set the Prescription Ids to send with page request
	 * @param  string  $prescriptionIds The Prescription Ids for the page request
	 * @return eRxPage                  This object is returned for method chaining
	 */
	public function setPrescriptionIds($prescriptionIds){
		$this->prescriptions = explode(':', $prescriptionIds);

		return $this;
	}

	/**
	 * Get the Prescription Ids for the page request
	 * @return string The Prescription Ids for the page request
	 */
	public function getPrescriptionIds(){
		$this->prescriptionIds;
	}

	/**
	 * Set the prescription count for the page request
	 * @param  string  $count The prescription count for the page request
	 * @return eRxPage        This object is returned for method chaining
	 */
	public function setPrescriptionCount($count){
		$this->prescriptionCount = $count;

		return $this;
	}

	/**
	 * Get the prescription count for the page request
	 * @return string The prescription count for the page request
	 */
	public function getPrescriptionCount(){
		return $this->prescriptionCount;
	}

	/**
	 * Check for required PHP extensions, return array of messages for missing extensions
	 * @return array Array of messages for missing extensions
	 */
	public function checkForMissingExtensions() {
		$extensions = array(
			'XML',
			'SOAP',
			'cURL',
			'OpenSSL',
		);

		$messages = array();

		foreach ($extensions as $extension) {
			if(!extension_loaded(strtolower($extension))) {
				$messages[] =
					xl('Enable Extension').' '.
					htmlspecialchars(
						$extension,
						ENT_QUOTES
					);
			}
		}

		return $messages;
	}

	/**
	 * Construct the XML document
	 * @return eRxPage This object is returned for method chaining
	 */
	public function buildXML() {
		$XMLBuilder = $this->getXMLBuilder();
		$NCScript = $XMLBuilder->getNCScript();
		$Store = $XMLBuilder->getStore();
		$authUserId = $this->getAuthUserId();
		$destination = $this->getDestination();
		$patientId = $this->getPatientId();

		$NCScript->appendChild($XMLBuilder->getCredentials());
		$NCScript->appendChild($XMLBuilder->getUserRole($authUserId));
		$NCScript->appendChild($XMLBuilder->getDestination($authUserId, $destination));
		$NCScript->appendChild($XMLBuilder->getAccount());
		$XMLBuilder->appendChildren($NCScript, $XMLBuilder->getStaffElements($authUserId, $destination));
		$XMLBuilder->appendChildren($NCScript, $XMLBuilder->getPatientElements($patientId, $this->getPrescriptionCount(), $this->getPrescriptionIds()));

		return array(
			'demographics' => $XMLBuilder->getDemographicsCheckMessages(),
			'empty' => $XMLBuilder->getFieldEmptyMessages(),
			'warning' => $XMLBuilder->getWarningMessages(),
		);
	}

	/**
	 * Return a string version of the constructed XML cleaned-up for NewCrop
	 * @return string NewCrop ready string of the constructed XML.
	 *
	 * XML has had double-quotes converted to single-quotes and \r and \t has been removed.
	 */
	public function getXML() {
		return preg_replace(
			'/\t/',
			'',
			preg_replace(
				'/&#xD;/',
				'',
				preg_replace(
					'/"/',
					'\'',
					$this->getXMLBuilder()->getDocument()->saveXML()
				)
			)
		);
	}

	protected function errorLog($message) {
		$date = date('Y-m-d');
		$path = $this->getXMLBuilder()->getGlobals()
			->getOpenEMRSiteDirectory().'/documents/erx_error';

		if(!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$fileHandler = fopen($path.'/erx_error'.'-'.$date.'.log', 'a');

		fwrite($fileHandler, date('Y-m-d H:i:s').' ==========> '.$message.PHP_EOL);

		fclose($fileHandler);
	}

	public function checkError($xml) {
		$XMLBuilder = $this->getXMLBuilder();

		$result = $XMLBuilder->checkError($xml);

		preg_match('/<textarea.*>(.*)Original XML:/is', $result, $errorMessage);

		if(count($errorMessage) > 0) {
			$errorMessages = explode('Error', $errorMessage[1]);
			array_shift($errorMessages);
		} else {
			$errorMessages = array();
		}

		if(strpos($result, 'RxEntry.aspx')) {
			$this->errorLog($xml);
			$this->errorLog($result);

			if(!count($errorMessages)) {
				$errorMessages[] = xl('An undefined error occoured, please contact your systems administrator.');
			}
		} elseif($XMLBuilder->getGlobals()->getDebugSetting() !== 0) {
			$debugString = '( '.xl('DEBUG OUTPUT').' )'.PHP_EOL;

			if($XMLBuilder->getGlobals()->getDebugSetting() & self::DEBUG_XML) {
				$this->errorLog($debugString.$xml);
			}

			if($XMLBuilder->getGlobals()->getDebugSetting() & self::DEBUG_RESULT) {
				$this->errorLog($debugString.$result);
			}
		}

		return $errorMessages;
	}

	public function updatePatientData() {
		$XMLBuilder = $this->getXMLBuilder();
		$Store = $XMLBuilder->getStore();
		$page = $this->getDestination();
		$patientId = $this->getPatientId();

		if($page == 'compose') {
			$Store->updatePatientImportStatusByPatientId($patientId, 1);
		} elseif($page == 'medentry') {
			$Store->updatePatientImportStatusByPatientId($patientId, 3);
		}

		$allergyIds = $XMLBuilder->getSentAllergyIds();
		if(count($allergyIds)) {
			foreach($allergyIds as $allergyId) {
				$Store->updateAllergyUploadedByPatientIdAllergyId(1, $patientId, $allergyId);
			}
		}

		$prescriptionIds = $XMLBuilder->getSentPrescriptionIds();
		if(count($prescriptionIds)) {
			foreach($prescriptionIds as $prescriptionId) {
				$Store->updatePrescriptionsUploadActiveByPatientIdPrescriptionId(1, 0, $patientId, $prescriptionId);
			}
		}

		$medicationIds = $XMLBuilder->getSentMedicationIds();
		if(count($medicationIds)) {
			foreach($medicationIds as $medicationId) {
				$Store->updateErxUploadedByListId($medicationId, 1);
			}
		}
	}

}