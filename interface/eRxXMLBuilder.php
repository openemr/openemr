<?php

/**
 * interface/eRxXMLBuilder.php Functions for building NewCrop XML.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2015 Sam Likins <sam.likins@wsi-services.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../library/patient.inc");

class eRxXMLBuilder
{

    private $globals;
    private $store;

    private $document;
    private $ncScript;

    private $sentAllergyIds = array();
    private $sentMedicationIds = array();
    private $sentPrescriptionIds = array();

    private $fieldEmptyMessages = array();
    private $demographicsCheckMessages = array();
    private $warningMessages = array();

    public function __construct($globals = null, $store = null)
    {
        if ($globals) {
            $this->setGlobals($globals);
        }

        if ($store) {
            $this->setStore($store);
        }
    }

    /**
     * Set Globals for retrieving eRx global configurations
     * @param  object  $globals The eRx Globals object to use for processing
     * @return eRxPage          This object is returned for method chaining
     */
    public function setGlobals($globals)
    {
        $this->globals = $globals;

        return $this;
    }

    /**
     * Get Globals for retrieving eRx global configurations
     * @return object The eRx Globals object to use for processing
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * Set Store to handle eRx cashed data
     * @param  object  $store The eRx Store object to use for processing
     * @return eRxPage        This object is returned for method chaining
     */
    public function setStore($store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Get Store for handling eRx cashed data
     * @return object The eRx Store object to use for processing
     */
    public function getStore()
    {
        return $this->store;
    }

    protected function trimData($string, $length)
    {
        return substr($string, 0, $length - 1);
    }

    protected function stripSpecialCharacter($string)
    {
        return preg_replace('/[^a-zA-Z0-9 \'().,#:\/\-@_%]/', '', $string);
    }

    public function checkError($xml)
    {
        $curlHandler = curl_init($xml);
        $sitePath = $this->getGlobals()->getOpenEMRSiteDirectory();
        $data = array('RxInput' => $xml);

        curl_setopt($curlHandler, CURLOPT_URL, $this->getGlobals()->getPath());
        curl_setopt($curlHandler, CURLOPT_POST, 1);
        curl_setopt($curlHandler, CURLOPT_POSTFIELDS, 'RxInput=' . $xml);
        curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curlHandler, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlHandler, CURLOPT_COOKIESESSION, true);
        curl_setopt($curlHandler, CURLOPT_COOKIEFILE, $sitePath . '/newcrop-cookiefile');
        curl_setopt($curlHandler, CURLOPT_COOKIEJAR, $sitePath . '/newcrop-cookiefile');
        curl_setopt($curlHandler, CURLOPT_COOKIE, session_name() . '=' . session_id());
        curl_setopt($curlHandler, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)');
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($curlHandler) or die(curl_error($curlHandler));

        curl_close($curlHandler);

        return $result;
    }

    public function addSentAllergyIds($allergyIds)
    {
        if (is_array($allergyIds)) {
            foreach ($allergyIds as $allergyId) {
                $this->sentAllergyIds[] = $allergyId;
            }
        } else {
            $this->sentAllergyIds[] = $allergyIds;
        }
    }

    public function getSentAllergyIds()
    {
        return $this->sentAllergyIds;
    }

    public function addSentMedicationIds($medicationIds)
    {
        if (is_array($medicationIds)) {
            foreach ($medicationIds as $medicationId) {
                $this->sentMedicationIds[] = $medicationId;
            }
        } else {
            $this->sentMedicationIds[] = $medicationIds;
        }
    }

    public function getSentMedicationIds()
    {
        return $this->sentMedicationIds;
    }

    public function addSentPrescriptionId($prescriptionIds)
    {
        if (is_array($prescriptionIds)) {
            foreach ($prescriptionIds as $prescriptionId) {
                $this->sentPrescriptionIds[] = $prescriptionId;
            }
        } else {
            $this->sentPrescriptionIds[] = $prescriptionIds;
        }
    }

    public function getSentPrescriptionIds()
    {
        return $this->sentPrescriptionIds;
    }

    public function fieldEmpty($value, $message)
    {
        if (empty($value)) {
            $this->fieldEmptyMessages[] = $message;
        }
    }

    public function getFieldEmptyMessages()
    {
        return $this->fieldEmptyMessages;
    }

    public function demographicsCheck($value, $message)
    {
        if (empty($value)) {
            $this->demographicsCheckMessages[] = $message;
        }
    }

    public function getDemographicsCheckMessages()
    {
        return $this->demographicsCheckMessages;
    }

    public function warningMessage($value, $message)
    {
        if (empty($value)) {
            $this->warningMessages[] = $message;
        }
    }

    public function getWarningMessages()
    {
        return $this->warningMessages;
    }

    public function createElementText($name, $value)
    {
        $element = $this->getDocument()->createElement($name);
        $element->appendChild($this->getDocument()->createTextNode($value));

        return $element;
    }

    public function createElementTextFieldEmpty($name, $value, $validationText)
    {
        $this->fieldEmpty($value, $validationText);

        return $this->createElementText($name, $value);
    }

    public function createElementTextDemographicsCheck($name, $value, $validationText)
    {
        $this->demographicsCheck($value, $validationText);

        return $this->createElementText($name, $value);
    }

    public function appendChildren($element, &$children)
    {
        if (is_a($element, 'DOMNode') && is_array($children) && count($children)) {
            foreach ($children as $child) {
                // if(is_array($child)) {
                //  $this->appendChildren($element, $child);
                // }
                $element->appendChild($child);
            }
        }
    }

    public function getDocument()
    {
        if ($this->document === null) {
            $this->document = new DOMDocument();
            $this->document->formatOutput = true;
        }

        return $this->document;
    }

    public function getNCScript()
    {
        if ($this->ncScript === null) {
            $document = $this->getDocument();

            $this->ncScript = $document->createElement('NCScript');
            $this->ncScript->setAttribute('xmlns', 'http://secure.newcropaccounts.com/interfaceV7');
            $this->ncScript->setAttribute('xmlns:NCStandard', 'http://secure.newcropaccounts.com/interfaceV7:NCStandard');
            $this->ncScript->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

            $document->appendChild($this->ncScript);
        }

        return $this->ncScript;
    }

    public function getCredentials()
    {
        $eRxCredentials = $this->getGlobals()
            ->getCredentials();

        $elemenet = $this->getDocument()->createElement('Credentials');
        $elemenet->appendChild($this->createElementTextFieldEmpty('partnerName', $eRxCredentials['0'], xl('NewCrop eRx Partner Name')));
        $elemenet->appendChild($this->createElementTextFieldEmpty('name', $eRxCredentials['1'], xl('NewCrop eRx Account Name')));
        $elemenet->appendChild($this->createElementTextFieldEmpty('password', $eRxCredentials['2'], xl('NewCrop eRx Password')));
        $elemenet->appendChild($this->createElementText('productName', 'OpenEMR'));
        $elemenet->appendChild($this->createElementText('productVersion', $this->getGlobals()->getOpenEMRVersion()));

        return $elemenet;
    }

    public function getUserRole($authUserId)
    {
        $eRxUserRole = $this->getStore()
            ->getUserById($authUserId);

        $eRxUserRole = $eRxUserRole['newcrop_user_role'];

        $this->fieldEmpty($eRxUserRole, xl('NewCrop eRx User Role'));
        if (!$eRxUserRole) {
            echo xlt('Unauthorized access to ePrescription');
            die;
        }

        $eRxUserRole = preg_replace('/erx/', '', $eRxUserRole);

        switch ($eRxUserRole) {
            case 'admin':
            case 'manager':
            case 'nurse':
                $newCropUser = 'Staff';
                break;
            case 'doctor':
                $newCropUser = 'LicensedPrescriber';
                break;
            case 'supervisingDoctor':
                $newCropUser = 'SupervisingDoctor';
                break;
            case 'midlevelPrescriber':
                $newCropUser = 'MidlevelPrescriber';
                break;
            default:
                $newCropUser = '';
        }

        $element = $this->getDocument()->createElement('UserRole');
        $element->appendChild($this->createElementTextFieldEmpty('user', $newCropUser, xl('NewCrop eRx User Role * invalid selection *')));
        $element->appendChild($this->createElementText('role', $eRxUserRole));

        return $element;
    }

    public function getDestination($authUserId, $page = null)
    {
        $eRxUserRole = $this->getStore()
            ->getUserById($authUserId);

        $eRxUserRole = $eRxUserRole['newcrop_user_role'];

        $eRxUserRole = preg_replace('/erx/', '', $eRxUserRole);

        if (!$page) {
            if ($eRxUserRole == 'admin') {
                $page = 'admin';
            } elseif ($eRxUserRole == 'manager') {
                $page = 'manager';
            } else {
                $page = 'compose';
            }
        }

        $element = $this->getDocument()->createElement('Destination');
        $element->appendChild($this->createElementText('requestedPage', $page));

        return $element;
    }

    public function getAccountAddress($facility)
    {
        $postalCode = preg_replace('/[^0-9]/', '', $facility['postal_code']);
        $postalCodePostfix = substr($postalCode, 5, 4);
        $postalCode = substr($postalCode, 0, 5);

        if (strlen($postalCode) < 5) {
            $this->fieldEmpty('', xl('Primary Facility Zip Code'));
        }

        $element = $this->getDocument()->createElement('AccountAddress');
        $element->appendChild($this->createElementTextFieldEmpty('address1', $this->trimData($this->stripSpecialCharacter($facility['street']), 35), xl('Primary Facility Street Address')));
        $element->appendChild($this->createElementTextFieldEmpty('city', $facility['city'], xl('Primary Facility City')));
        $element->appendChild($this->createElementTextFieldEmpty('state', $facility['state'], xl('Primary Facility State')));
        $element->appendChild($this->createElementText('zip', $postalCode));
        if (strlen($postalCodePostfix) == 4) {
            $element->appendChild($this->createElementText('zip4', $postalCodePostfix));
        }

        $element->appendChild($this->createElementTextFieldEmpty('country', substr($facility['country_code'], 0, 2), xl('Primary Facility Country code')));

        return $element;
    }

    public function getAccount()
    {
        $facility = $this->getStore()
            ->getFacilityPrimary();

        if (!$facility['federal_ein']) {
            echo xlt('Please select a Primary Business Entity facility with \'Tax ID\' as your facility Tax ID. If you are an individual practitioner, use your tax id. This is used for identifying you in the NewCrop system.');
            die;
        }

        $element = $this->getDocument()->createElement('Account');
        $element->setAttribute('ID', $this->getGlobals()->getAccountId());
        $element->appendChild($this->createElementTextFieldEmpty('accountName', $this->trimData($this->stripSpecialCharacter($facility['name']), 35), xl('Facility Name')));
        $element->appendChild($this->createElementText('siteID', $facility['federal_ein'], 'Site ID'));
        $element->appendChild($this->getAccountAddress($facility));
        $element->appendChild($this->createElementTextFieldEmpty('accountPrimaryPhoneNumber', preg_replace('/[^0-9]/', '', $facility['phone']), xl('Facility Phone')));
        $element->appendChild($this->createElementTextFieldEmpty('accountPrimaryFaxNumber', preg_replace('/[^0-9]/', '', $facility['fax']), xl('Facility Fax')));

        return $element;
    }

    public function getLocationAddress($facility)
    {
        $postalCode = preg_replace('/[^0-9]/', '', $facility['postal_code']);
        $postalCodePostfix = substr($postalCode, 5, 4);
        $postalCode = substr($postalCode, 0, 5);

        if (strlen($postalCode) < 5) {
            $this->fieldEmpty('', xl('Facility Zip Code'));
        }

        $element = $this->getDocument()->createElement('LocationAddress');
        if ($facility['street']) {
            $element->appendChild($this->createElementText('address1', $this->trimData($this->stripSpecialCharacter($facility['street']), 35)));
        }

        if ($facility['city']) {
            $element->appendChild($this->createElementText('city', $facility['city']));
        }

        if ($facility['state']) {
            $element->appendChild($this->createElementText('state', $facility['state']));
        }

        $element->appendChild($this->createElementText('zip', $postalCode));
        if (strlen($postalCodePostfix) == 4) {
            $element->appendChild($this->createElementText('zip4', $postalCodePostfix));
        }

        if ($facility['country_code']) {
            $element->appendChild($this->createElementText('country', substr($facility['country_code'], 0, 2)));
        }

        return $element;
    }

    public function getLocation($authUserId)
    {
        $userFacility = $this->getStore()
            ->getUserFacility($authUserId);

        $element = $this->getDocument()->createElement('Location');
        $element->setAttribute('ID', $userFacility['id']);
        $element->appendChild($this->createElementText('locationName', $this->trimData($this->stripSpecialCharacter($userFacility['name']), 35)));
        $element->appendChild($this->getLocationAddress($userFacility));
        if ($userFacility['phone']) {
            $element->appendChild($this->createElementText('primaryPhoneNumber', preg_replace('/[^0-9]/', '', $userFacility['phone'])));
        }

        if ($userFacility['fax']) {
            $element->appendChild($this->createElementText('primaryFaxNumber', preg_replace('/[^0-9]/', '', $userFacility['fax'])));
        }

        if ($userFacility['phone']) {
            $element->appendChild($this->createElementText('pharmacyContactNumber', preg_replace('/[^0-9]/', '', $userFacility['phone'])));
        }

        return $element;
    }

    public function getLicensedPrescriber($authUserId)
    {
        $userDetails = $this->getStore()
            ->getUserById($authUserId);

        $element = $this->getDocument()->createElement('LicensedPrescriber');
        $element->setAttribute('ID', $userDetails['npi']);
        $element->appendChild($this->getLicensedPrescriberName($userDetails, xl('Licensed Prescriber')));
        $element->appendChild($this->createElementTextFieldEmpty('dea', $userDetails['federaldrugid'], 'Licensed Prescriber DEA'));
        if ($userDetails['upin']) {
            $element->appendChild($this->createElementText('upin', $userDetails['upin']));
        }

        $element->appendChild($this->createElementText('licenseNumber', $userDetails['state_license_number']));
        $element->appendChild($this->createElementTextFieldEmpty('npi', $userDetails['npi'], xl('Licensed Prescriber NPI')));

        return $element;
    }

    public function getStaffName($user)
    {
        $element = $this->getDocument()->createElement('StaffName');
        $element->appendChild($this->createElementText('last', $this->stripSpecialCharacter($user['lname'])));
        $element->appendChild($this->createElementText('first', $this->stripSpecialCharacter($user['fname'])));
        $element->appendChild($this->createElementText('middle', $this->stripSpecialCharacter($user['mname'])));

        return $element;
    }

    public function getStaff($authUserId)
    {
        $userDetails = $this->getStore()
            ->getUserById($authUserId);

        $element = $this->getDocument()->createElement('Staff');
        $element->setAttribute('ID', $userDetails['username']);
        $element->appendChild($this->getStaffName($userDetails));
        $element->appendChild($this->createElementText('license', $userDetails['state_license_number']));

        return $element;
    }

    public function getLicensedPrescriberName($user, $prescriberType, $prefix = false)
    {
        $element = $this->getDocument()->createElement('LicensedPrescriberName');
        $element->appendChild($this->createElementTextFieldEmpty('last', $this->stripSpecialCharacter($user['lname']), $prescriberType . ' ' . xl('Licensed Prescriber Last Name')));
        $element->appendChild($this->createElementTextFieldEmpty('first', $this->stripSpecialCharacter($user['fname']), $prescriberType . ' ' . xl('Licensed Prescriber First Name')));
        $element->appendChild($this->createElementText('middle', $this->stripSpecialCharacter($user['mname'])));
        if ($prefix && $user['title']) {
            $element->appendChild($this->createElementTextFieldEmpty('prefix', $user['title'], $prescriberType . ' ' . xl('Licensed Prescriber Title (Prefix)')));
        }

        return $element;
    }

    public function getSupervisingDoctor($authUserId)
    {
        $userDetails = $this->getStore()
            ->getUserById($authUserId);

        $element = $this->getDocument()->createElement('SupervisingDoctor');
        $element->setAttribute('ID', $userDetails['npi']);
        $element->appendChild($this->getLicensedPrescriberName($userDetails, xl('Supervising Doctor')));
        $element->appendChild($this->createElementTextFieldEmpty('dea', $userDetails['federaldrugid'], xl('Supervising Doctor DEA')));
        if ($userDetails['upin']) {
            $element->appendChild($this->createElementText('upin', $userDetails['upin']));
        }

        $element->appendChild($this->createElementText('licenseNumber', $userDetails['state_license_number']));
        $element->appendChild($this->createElementTextFieldEmpty('npi', $userDetails['npi'], xl('Supervising Doctor NPI')));

        return $element;
    }

    public function getMidlevelPrescriber($authUserId)
    {
        $userDetails = $this->getStore()
            ->getUserById($authUserId);

        $element = $this->getDocument()->createElement('MidlevelPrescriber');
        $element->setAttribute('ID', $userDetails['npi']);
        $element->appendChild($this->getLicensedPrescriberName($userDetails, xl('Midlevel Prescriber'), true));
        $element->appendChild($this->createElementTextFieldEmpty('dea', $userDetails['federaldrugid'], xl('Midlevel Prescriber DEA')));
        if ($userDetails['upin']) {
            $element->appendChild($this->createElementText('upin', $userDetails['upin']));
        }

        $element->appendChild($this->createElementText('licenseNumber', $userDetails['state_license_number']));
        $element->appendChild($this->createElementTextFieldEmpty('npi', $userDetails['npi'], xl('Midlevel Prescriber NPI')));

        return $element;
    }

    public function getStaffElements($authUserId, $destination)
    {
        $userRole = $this->getStore()->getUserById($authUserId);
        $userRole = preg_replace('/erx/', '', $userRole['newcrop_user_role']);

        $elements = array();

        if ($userRole != 'manager') {
            $elements[] = $this->getLocation($authUserId);
        }

        if ($userRole == 'doctor' || $destination == 'renewal') {
            $elements[] = $this->getLicensedPrescriber($authUserId);
        }

        if ($userRole == 'manager' || $userRole == 'admin' || $userRole == 'nurse') {
            $elements[] = $this->getStaff($authUserId);
        } elseif ($userRole == 'supervisingDoctor') {
            $elements[] = $this->getSupervisingDoctor($authUserId);
        } elseif ($userRole == 'midlevelPrescriber') {
            $elements[] = $this->getMidlevelPrescriber($authUserId);
        }

        return $elements;
    }

    public function getPatientName($patient)
    {
        $element = $this->getDocument()->createElement('PatientName');
        $element->appendChild($this->createElementTextDemographicsCheck('last', $this->trimData($this->stripSpecialCharacter($patient['lname']), 35), xl('Patient Last Name')));
        $element->appendChild($this->createElementTextDemographicsCheck('first', $this->trimData($this->stripSpecialCharacter($patient['fname']), 35), xl('Patient First Name')));
        $element->appendChild($this->createElementText('middle', $this->trimData($this->stripSpecialCharacter($patient['mname']), 35)));

        return $element;
    }

    public function getPatientAddress($patient)
    {
        $patient['street'] = $this->trimData($this->stripSpecialCharacter($patient['street']), 35);
        $this->warningMessage($patient['street'], xl('Patient Street Address'));


        if (trim($patient['country_code']) == '') {
            $eRxDefaultPatientCountry = $this->getGlobals()->getDefaultPatientCountry();

            if ($eRxDefaultPatientCountry == '') {
                $this->demographicsCheck('', xl('Global Default Patient Country'));
            } else {
                $patient['country_code'] = $eRxDefaultPatientCountry;
            }

            $this->demographicsCheck('', xl('Patient Country'));
        }

        $element = $this->getDocument()->createElement('PatientAddress');
        $element->appendChild($this->createElementTextFieldEmpty('address1', $patient['street'], xl('Patient Street Address')));
        $element->appendChild($this->createElementTextDemographicsCheck('city', $patient['city'], xl('Patient City')));
        if ($patient['state']) {
            $element->appendChild($this->createElementText('state', $patient['state']));
        }

        if ($patient['postal_code']) {
            $element->appendChild($this->createElementText('zip', $patient['postal_code']));
        }

        $element->appendChild($this->createElementText('country', substr($patient['country_code'], 0, 2)));

        return $element;
    }

    public function getPatientContact($patient)
    {
        $element = $this->getDocument()->createElement('PatientContact');
        if ($patient['phone_home']) {
            $element->appendChild($this->createElementText('homeTelephone', preg_replace('/-/', '', $patient['phone_home'])));
        }

        return $element;
    }

    public function getPatientCharacteristics($patient)
    {
        if (trim($patient['date_of_birth']) == '' || $patient['date_of_birth'] == '00000000') {
            $this->warningMessage('', xl('Patient Date Of Birth'));
        }

        $this->warningMessage(trim($patient['sex']), xl('Patient Gender'));

        $element = $this->getDocument()->createElement('PatientCharacteristics');
        if ($patient['date_of_birth'] && $patient['date_of_birth'] != '00000000') {
            $element->appendChild($this->createElementText('dob', $patient['date_of_birth']));
        }

        if ($patient['sex']) {
            $element->appendChild($this->createElementText('gender', substr($patient['sex'], 0, 1)));
        }

        $vitals = $this->getStore()->getPatientVitalsByPatientId($patient['pid']);
        $age = getPatientAgeYMD($patient['date_of_birth']);

        if (
            $vitals['height'] &&
            $vitals['height_units']
        ) {
            $element->appendChild($this->createElementText('height', $vitals['height']));
            $element->appendChild($this->createElementText('heightUnits', $vitals['height_units']));
        } elseif ($age['age'] < 19) {
            $this->warningMessage('', xl('Patient Height Vital is required under age 19'));
        }

        if (
            $vitals['weight'] &&
            $vitals['weight_units']
        ) {
            $element->appendChild($this->createElementText('weight', $vitals['weight']));
            $element->appendChild($this->createElementText('weightUnits', $vitals['weight_units']));
        } elseif ($age['age'] < 19) {
            $this->warningMessage('', xl('Patient Weight Vital is required under age 19'));
        }

        return $element;
    }

    public function getPatientFreeformHealthplans($patientId)
    {
        $healthplans = $this->getStore()
            ->getPatientHealthplansByPatientId($patientId);

        $elements = array();

        while ($healthplan = sqlFetchArray($healthplans)) {
            $element = $this->getDocument()->createElement('PatientFreeformHealthplans');
            $element->appendChild($this->createElementText('healthplanName', $this->trimData($this->stripSpecialCharacter($healthplan['name']), 35)));

            $elements[] = $element;
        }

        return $elements;
    }

    public function getPatientFreeformAllergy($patientId)
    {
        $allergyData = $this->getStore()
            ->getPatientAllergiesByPatientId($patientId);

        $elements = array();

        while ($allergy = sqlFetchArray($allergyData)) {
            $element = $this->getDocument()->createElement('PatientFreeformAllergy');
            $element->setAttribute('ID', $allergy['id']);

            if ($allergy['title1']) {
                $element->appendChild($this->createElementText('allergyName', $this->trimData($this->stripSpecialCharacter($allergy['title1']), 70)));
            }

            if ($allergy['title2'] == 'Mild' || $allergy['title2'] == 'Moderate' || $allergy['title2'] == 'Severe') {
                $element->appendChild($this->createElementText('allergySeverityTypeID', $allergy['title2']));
            }

            if ($allergy['comments']) {
                $element->appendChild($this->createElementText('allergyComment', $this->trimData($this->stripSpecialCharacter($allergy['comments']), 200)));
            }

            $elements[] = $element;

            $this->addSentAllergyIds($allergy['id']);
        }

        return $elements;
    }

    public function getPatient($patientId)
    {
        $patientData = $this->getStore()
            ->getPatientByPatientId($patientId);

        $element = $this->getDocument()->createElement('Patient');
        $element->setAttribute('ID', $patientData['pid']);
        $element->appendChild($this->getPatientName($patientData));
        $element->appendChild($this->getPatientAddress($patientData));
        $element->appendChild($this->getPatientContact($patientData));
        $element->appendChild($this->getPatientCharacteristics($patientData));
        $this->appendChildren($element, $this->getPatientFreeformHealthplans($patientId));
        $this->appendChildren($element, $this->getPatientFreeformAllergy($patientId));

        return $element;
    }

    public function getOutsidePrescription($prescription)
    {
        $element = $this->getDocument()->createElement('OutsidePrescription');
        $element->appendChild($this->createElementText('externalId', $prescription['externalId']));
        $element->appendChild($this->createElementText('date', $prescription['date']));
        $element->appendChild($this->createElementText('doctorName', $prescription['doctorName']));
        $element->appendChild($this->createElementText('drug', $prescription['drug']));
        $element->appendChild($this->createElementText('dispenseNumber', $prescription['dispenseNumber']));
        $element->appendChild($this->createElementText('sig', $prescription['sig']));
        $element->appendChild($this->createElementText('refillCount', $prescription['refillCount']));
        $element->appendChild($this->createElementText('prescriptionType', $prescription['prescriptionType']));

        return $element;
    }

    public function getPatientPrescriptions($prescriptionIds)
    {
        $elements = array();

        foreach ($prescriptionIds as $prescriptionId) {
            if ($prescriptionId) {
                $prescription = $this->getStore()
                    ->getPrescriptionById($prescriptionId);

                $element = $this->getOutsidePrescription(array(
                    'externalId'        => $prescription['prescid'],
                    'date'              => $prescription['date_added'],
                    'doctorName'        => $prescription['docname'],
                    'drug'              => $this->trimData($this->stripSpecialCharacter($prescription['drug']), 80),
                    'dispenseNumber'    => intval($prescription['quantity']),
                    'sig'               => $this->trimData($this->stripSpecialCharacter($prescription['quantity'][1] . $prescription['size'] . ' ' . $prescription['title4'] . ' ' . $prescription['dosage'] . ' In ' . $prescription['title1'] . ' ' . $prescription['title2'] . ' ' . $prescription['title3']), 140),
                    'refillCount'       => intval($prescription['per_refill']),
                    'prescriptionType'  => 'reconcile'
                ));

                $this->addSentPrescriptionId($prescriptionId);

                $elements[] = $element;
            }
        }

        return $elements;
    }

    public function getPatientMedication($patientId, $uploadActive, $count)
    {
        $medications = $this->getStore()
            ->selectMedicationsNotUploadedByPatientId($patientId, $uploadActive, $count);

        $elements = array();

        while ($medication = sqlFetchArray($medications)) {
            $elements[] = $this->getOutsidePrescription(array(
                'externalId'        => $medication['id'],
                'date'              => $medication['begdate'],
                'doctorName'        => '',
                'drug'              => $this->trimData($medication['title'], 80),
                'dispenseNumber'    => '',
                'sig'               => '',
                'refillCount'       => '',
                'prescriptionType'  => 'reconcile'
            ));

            $this->addSentMedicationIds($medication['id']);
        }

        return $elements;
    }

    public function getPatientElements($patientId, $totalCount, $requestedPrescriptionIds)
    {
        $elements = array();

        if ($patientId) {
            $uploadActive = $this->getGlobals()->getUploadActive();

            $elements[] = $this->getPatient($patientId);

            $selectPrescriptionIds = $this->getStore()
                ->selectPrescriptionIdsNotUploadedByPatientId(
                    $patientId,
                    $uploadActive,
                    $totalCount
                );

            $selectPrescriptionIdsCount = sqlNumRows($selectPrescriptionIds);

            $prescriptionIds = array();

            while ($selectPrescriptionId = sqlFetchArray($selectPrescriptionIds)) {
                $prescriptionIds[] = $selectPrescriptionId['id'];
            }

            if (count($requestedPrescriptionIds) > 0) {
                $elements = array_merge($elements, $this->getPatientPrescriptions($requestedPrescriptionIds));
            } elseif (count($prescriptionIds) > 0) {
                $elements = array_merge($elements, $this->getPatientPrescriptions($prescriptionIds));
            } else {
                $this->getPatientPrescriptions(array(0));
            }

            if ($selectPrescriptionIdsCount < $totalCount) {
                $elements = array_merge($elements, $this->getPatientMedication($patientId, $uploadActive, $totalCount - $selectPrescriptionIdsCount));
            }
        }

        return $elements;
    }
}
