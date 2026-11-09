<?php

/**
 * Builds the NCScript XML that interface/eRx.php posts to launch Ensora eRx.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sam Likins <sam.likins@wsi-services.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2015 Sam Likins <sam.likins@wsi-services.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx\Ensora;

use DOMDocument;
use DOMElement;
use DOMNode;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use OpenEMR\Encryption\Plaintext;
use RuntimeException;
use SensitiveParameter;
use UnexpectedValueException;

class ErxXmlBuilder
{
    private ?DOMDocument $document = null;
    private ?DOMElement $ncScript = null;
    private ?Plaintext $sentPassword = null;

    /** @var list<int> */
    private array $sentAllergyIds = [];
    /** @var list<int> */
    private array $sentMedicationIds = [];
    /** @var list<int> */
    private array $sentPrescriptionIds = [];

    /** @var list<string> */
    private array $fieldEmptyMessages = [];
    /** @var list<string> */
    private array $demographicsCheckMessages = [];
    /** @var list<string> */
    private array $warningMessages = [];

    public function __construct(
        private readonly ErxGlobalsInterface $globals,
        private readonly ErxStoreInterface $store,
        private readonly ClientInterface $httpClient,
        // http_verify_ssl: whether the check request verifies the eRx server's certificate
        private readonly bool $verifySsl,
    ) {
    }

    public function getGlobals(): ErxGlobalsInterface
    {
        return $this->globals;
    }

    public function getStore(): ErxStoreInterface
    {
        return $this->store;
    }

    protected function trimData(string $string, int $length): string
    {
        return substr($string, 0, $length - 1);
    }

    protected function stripSpecialCharacter(string $string): string
    {
        return self::regexRemove('/[^a-zA-Z0-9 \'().,#:\/\-@_%]/', $string);
    }

    /**
     * Post the request server-side and return the page, which
     * ErxPage::checkError() searches for validation errors.
     *
     * @throws GuzzleException when the eRx service cannot be reached
     */
    public function checkError(string $xml): string
    {
        $url = $this->getGlobals()->getPath();
        if ($url === '') {
            throw new UnexpectedValueException('The Ensora eRx path is not configured');
        }

        $response = $this->httpClient->request('POST', $url, [
            RequestOptions::COOKIES => new CookieJar(),
            RequestOptions::FORM_PARAMS => ['RxInput' => $xml],
            RequestOptions::HEADERS => ['User-Agent' => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'],
            // ErxPage::checkError() reads the page whatever its status.
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::VERIFY => $this->verifySsl,
        ]);

        return (string) $response->getBody();
    }

    public function addSentAllergyIds(int $allergyId): void
    {
        $this->sentAllergyIds[] = $allergyId;
    }

    /**
     * @return list<int>
     */
    public function getSentAllergyIds(): array
    {
        return $this->sentAllergyIds;
    }

    public function addSentMedicationIds(int $medicationId): void
    {
        $this->sentMedicationIds[] = $medicationId;
    }

    /**
     * @return list<int>
     */
    public function getSentMedicationIds(): array
    {
        return $this->sentMedicationIds;
    }

    public function addSentPrescriptionId(int $prescriptionId): void
    {
        $this->sentPrescriptionIds[] = $prescriptionId;
    }

    /**
     * @return list<int>
     */
    public function getSentPrescriptionIds(): array
    {
        return $this->sentPrescriptionIds;
    }

    public function fieldEmpty(#[SensitiveParameter] string $value, string $message): void
    {
        if ($value === '') {
            $this->fieldEmptyMessages[] = $message;
        }
    }

    /**
     * @return list<string>
     */
    public function getFieldEmptyMessages(): array
    {
        return $this->fieldEmptyMessages;
    }

    public function demographicsCheck(string $value, string $message): void
    {
        if ($value === '') {
            $this->demographicsCheckMessages[] = $message;
        }
    }

    /**
     * @return list<string>
     */
    public function getDemographicsCheckMessages(): array
    {
        return $this->demographicsCheckMessages;
    }

    public function warningMessage(string $value, string $message): void
    {
        if ($value === '') {
            $this->warningMessages[] = $message;
        }
    }

    /**
     * @return list<string>
     */
    public function getWarningMessages(): array
    {
        return $this->warningMessages;
    }

    public function createElementText(string $name, #[SensitiveParameter] string $value): DOMElement
    {
        $element = $this->getDocument()->createElement($name);
        $element->appendChild($this->getDocument()->createTextNode(self::cleanText($value)));

        return $element;
    }

    /**
     * Drop carriage returns, tabs and the other control characters except line
     * feeds, drop U+FFFE and U+FFFF, and replace invalid UTF-8 with U+FFFD
     * whatever the mbstring substitution setting. DOM writes a value holding a
     * control character XML forbids, or invalid UTF-8, as empty, and writes
     * U+FFFE and U+FFFF raw, which XML parsers reject.
     */
    public static function cleanText(#[SensitiveParameter] string $value): string
    {
        if (!mb_check_encoding($value, 'UTF-8')) {
            // ENT_SUBSTITUTE swaps invalid UTF-8 for U+FFFD, and decoding undoes only the escaping.
            $flags = ENT_XML1 | ENT_NOQUOTES;
            $value = htmlspecialchars_decode(htmlspecialchars($value, $flags | ENT_SUBSTITUTE), $flags);
        }

        // The value is valid UTF-8 here, so \xEF\xBF[\xBE\xBF] is only ever U+FFFE or U+FFFF.
        return self::regexRemove('/[\x00-\x09\x0B-\x1F]|\xEF\xBF[\xBE\xBF]/', $value);
    }

    public function createElementTextFieldEmpty(string $name, #[SensitiveParameter] string $value, string $validationText): DOMElement
    {
        $this->fieldEmpty($value, $validationText);

        return $this->createElementText($name, $value);
    }

    public function createElementTextDemographicsCheck(string $name, string $value, string $validationText): DOMElement
    {
        $this->demographicsCheck($value, $validationText);

        return $this->createElementText($name, $value);
    }

    /**
     * @param list<DOMElement> $children
     */
    public function appendChildren(DOMNode $element, array $children): void
    {
        foreach ($children as $child) {
            $element->appendChild($child);
        }
    }

    public function getDocument(): DOMDocument
    {
        if ($this->document === null) {
            $this->document = new DOMDocument();
            $this->document->formatOutput = true;
        }

        return $this->document;
    }

    public function getXml(): string
    {
        return self::serialize($this->getDocument());
    }

    /**
     * The password as getCredentials() wrote it into the request, so logs can
     * redact it without decrypting it again; null until then.
     */
    public function getSentPassword(): ?Plaintext
    {
        return $this->sentPassword;
    }

    /**
     * The request XML with the password element's text replaced, for logging.
     */
    public function getRedactedXml(): string
    {
        $copy = $this->getDocument()->cloneNode(true);
        if (!$copy instanceof DOMDocument) {
            throw new RuntimeException('Could not copy the Ensora eRx request');
        }

        foreach ($copy->getElementsByTagName('password') as $password) {
            $password->textContent = '[redacted]';
        }

        return self::serialize($copy);
    }

    public function getNCScript(): DOMElement
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

    public function getCredentials(): DOMElement
    {
        $globals = $this->getGlobals();
        $password = $globals->getAccountPassword();
        $this->sentPassword = new Plaintext(self::cleanText($password->bytes));

        $element = $this->getDocument()->createElement('Credentials');
        $element->appendChild($this->createElementTextFieldEmpty('partnerName', $globals->getPartnerName(), xl('Ensora eRx Partner Name')));
        $element->appendChild($this->createElementTextFieldEmpty('name', $globals->getAccountName(), xl('Ensora eRx Account Name')));
        $element->appendChild($this->createElementTextFieldEmpty('password', $password->bytes, xl('Ensora eRx Password')));
        $element->appendChild($this->createElementText('productName', 'OpenEMR'));
        $element->appendChild($this->createElementText('productVersion', $globals->getProductVersion()));

        return $element;
    }

    /** @throws ErxLaunchBlockedException when the user has no eRx role */
    public function getUserRole(int $authUserId): DOMElement
    {
        $eRxUserRole = $this->userRole($authUserId);

        if ($eRxUserRole === '') {
            throw new ErxLaunchBlockedException(ErxLaunchBlock::UnauthorizedRole);
        }

        $eRxUserRole = str_replace('erx', '', $eRxUserRole);

        $newCropUser = match ($eRxUserRole) {
            'admin', 'manager', 'nurse' => 'Staff',
            'doctor' => 'LicensedPrescriber',
            'supervisingDoctor' => 'SupervisingDoctor',
            'midlevelPrescriber' => 'MidlevelPrescriber',
            default => '',
        };

        $element = $this->getDocument()->createElement('UserRole');
        $element->appendChild($this->createElementTextFieldEmpty('user', $newCropUser, xl('Ensora eRx User Role * invalid selection *')));
        $element->appendChild($this->createElementText('role', $eRxUserRole));

        return $element;
    }

    public function getDestination(int $authUserId, string $page = ''): DOMElement
    {
        $eRxUserRole = str_replace('erx', '', $this->userRole($authUserId));

        if ($page === '') {
            $page = match ($eRxUserRole) {
                'admin', 'manager' => $eRxUserRole,
                default => 'compose',
            };
        }

        $element = $this->getDocument()->createElement('Destination');
        $element->appendChild($this->createElementText('requestedPage', $page));

        return $element;
    }

    /**
     * @param array<mixed> $facility `facility` row
     */
    public function getAccountAddress(array $facility): DOMElement
    {
        $postalCode = self::regexRemove('/[^0-9]/', self::field($facility, 'postal_code'));
        $postalCodePostfix = substr($postalCode, 5, 4);
        $postalCode = substr($postalCode, 0, 5);

        if (strlen($postalCode) < 5) {
            $this->fieldEmpty('', xl('Primary Facility Zip Code'));
        }

        $element = $this->getDocument()->createElement('AccountAddress');
        $element->appendChild($this->createElementTextFieldEmpty('address1', $this->trimData($this->stripSpecialCharacter(self::field($facility, 'street')), 35), xl('Primary Facility Street Address')));
        $element->appendChild($this->createElementTextFieldEmpty('city', self::field($facility, 'city'), xl('Primary Facility City')));
        $element->appendChild($this->createElementTextFieldEmpty('state', self::field($facility, 'state'), xl('Primary Facility State')));
        $element->appendChild($this->createElementText('zip', $postalCode));
        if (strlen($postalCodePostfix) === 4) {
            $element->appendChild($this->createElementText('zip4', $postalCodePostfix));
        }

        $element->appendChild($this->createElementTextFieldEmpty('country', substr(self::field($facility, 'country_code'), 0, 2), xl('Primary Facility Country code')));

        return $element;
    }

    /** @throws ErxLaunchBlockedException when the primary business entity has no tax id */
    public function getAccount(): DOMElement
    {
        $facility = $this->getStore()->getFacilityPrimary();

        if (self::field($facility, 'federal_ein') === '') {
            throw new ErxLaunchBlockedException(ErxLaunchBlock::MissingTaxId);
        }

        $element = $this->getDocument()->createElement('Account');
        $element->setAttribute('ID', self::cleanText($this->getGlobals()->getAccountId()));
        $element->appendChild($this->createElementTextFieldEmpty('accountName', $this->trimData($this->stripSpecialCharacter(self::field($facility, 'name')), 35), xl('Facility Name')));
        $element->appendChild($this->createElementText('siteID', self::field($facility, 'federal_ein')));
        $element->appendChild($this->getAccountAddress($facility));
        $element->appendChild($this->createElementTextFieldEmpty('accountPrimaryPhoneNumber', self::regexRemove('/[^0-9]/', self::field($facility, 'phone')), xl('Facility Phone')));
        $element->appendChild($this->createElementTextFieldEmpty('accountPrimaryFaxNumber', self::regexRemove('/[^0-9]/', self::field($facility, 'fax')), xl('Facility Fax')));

        return $element;
    }

    /**
     * @param array<mixed> $facility `facility` row
     */
    public function getLocationAddress(array $facility): DOMElement
    {
        $postalCode = self::regexRemove('/[^0-9]/', self::field($facility, 'postal_code'));
        $postalCodePostfix = substr($postalCode, 5, 4);
        $postalCode = substr($postalCode, 0, 5);

        if (strlen($postalCode) < 5) {
            $this->fieldEmpty('', xl('Facility Zip Code'));
        }

        $street = self::field($facility, 'street');
        $city = self::field($facility, 'city');
        $state = self::field($facility, 'state');
        $countryCode = self::field($facility, 'country_code');

        $element = $this->getDocument()->createElement('LocationAddress');
        if ($street !== '') {
            $element->appendChild($this->createElementText('address1', $this->trimData($this->stripSpecialCharacter($street), 35)));
        }

        if ($city !== '') {
            $element->appendChild($this->createElementText('city', $city));
        }

        if ($state !== '') {
            $element->appendChild($this->createElementText('state', $state));
        }

        $element->appendChild($this->createElementText('zip', $postalCode));
        if (strlen($postalCodePostfix) === 4) {
            $element->appendChild($this->createElementText('zip4', $postalCodePostfix));
        }

        if ($countryCode !== '') {
            $element->appendChild($this->createElementText('country', substr($countryCode, 0, 2)));
        }

        return $element;
    }

    public function getLocation(int $authUserId): DOMElement
    {
        $userFacility = $this->getStore()->getUserFacility($authUserId);
        $phone = self::regexRemove('/[^0-9]/', self::field($userFacility, 'phone'));
        $fax = self::regexRemove('/[^0-9]/', self::field($userFacility, 'fax'));

        $element = $this->getDocument()->createElement('Location');
        $element->setAttribute('ID', self::cleanText(self::field($userFacility, 'id')));
        $element->appendChild($this->createElementText('locationName', $this->trimData($this->stripSpecialCharacter(self::field($userFacility, 'name')), 35)));
        $element->appendChild($this->getLocationAddress($userFacility));
        if (self::field($userFacility, 'phone') !== '') {
            $element->appendChild($this->createElementText('primaryPhoneNumber', $phone));
        }

        if (self::field($userFacility, 'fax') !== '') {
            $element->appendChild($this->createElementText('primaryFaxNumber', $fax));
        }

        if (self::field($userFacility, 'phone') !== '') {
            $element->appendChild($this->createElementText('pharmacyContactNumber', $phone));
        }

        return $element;
    }

    public function getLicensedPrescriber(int $authUserId): DOMElement
    {
        return $this->getPrescriber('LicensedPrescriber', $authUserId, xl('Licensed Prescriber'), 'Licensed Prescriber DEA', xl('Licensed Prescriber NPI'));
    }

    /**
     * @param array<mixed> $user `users` row
     */
    public function getStaffName(array $user): DOMElement
    {
        $element = $this->getDocument()->createElement('StaffName');
        $element->appendChild($this->createElementText('last', $this->stripSpecialCharacter(self::field($user, 'lname'))));
        $element->appendChild($this->createElementText('first', $this->stripSpecialCharacter(self::field($user, 'fname'))));
        $element->appendChild($this->createElementText('middle', $this->stripSpecialCharacter(self::field($user, 'mname'))));

        return $element;
    }

    public function getStaff(int $authUserId): DOMElement
    {
        $userDetails = $this->getStore()->getUserById($authUserId);

        $element = $this->getDocument()->createElement('Staff');
        $element->setAttribute('ID', self::cleanText(self::field($userDetails, 'username')));
        $element->appendChild($this->getStaffName($userDetails));
        $element->appendChild($this->createElementText('license', self::field($userDetails, 'state_license_number')));

        return $element;
    }

    /**
     * @param array<mixed> $user `users` row
     */
    public function getLicensedPrescriberName(array $user, string $prescriberType, bool $prefix = false): DOMElement
    {
        $title = self::field($user, 'title');

        $element = $this->getDocument()->createElement('LicensedPrescriberName');
        $element->appendChild($this->createElementTextFieldEmpty('last', $this->stripSpecialCharacter(self::field($user, 'lname')), $prescriberType . ' ' . xl('Licensed Prescriber Last Name')));
        $element->appendChild($this->createElementTextFieldEmpty('first', $this->stripSpecialCharacter(self::field($user, 'fname')), $prescriberType . ' ' . xl('Licensed Prescriber First Name')));
        $element->appendChild($this->createElementText('middle', $this->stripSpecialCharacter(self::field($user, 'mname'))));
        if ($prefix && $title !== '') {
            $element->appendChild($this->createElementTextFieldEmpty('prefix', $title, $prescriberType . ' ' . xl('Licensed Prescriber Title (Prefix)')));
        }

        return $element;
    }

    public function getSupervisingDoctor(int $authUserId): DOMElement
    {
        return $this->getPrescriber('SupervisingDoctor', $authUserId, xl('Supervising Doctor'), xl('Supervising Doctor DEA'), xl('Supervising Doctor NPI'));
    }

    public function getMidlevelPrescriber(int $authUserId): DOMElement
    {
        return $this->getPrescriber('MidlevelPrescriber', $authUserId, xl('Midlevel Prescriber'), xl('Midlevel Prescriber DEA'), xl('Midlevel Prescriber NPI'), true);
    }

    /**
     * @return list<DOMElement>
     */
    public function getStaffElements(int $authUserId, string $destination): array
    {
        $userRole = str_replace('erx', '', $this->userRole($authUserId));

        $elements = [];

        if ($userRole !== 'manager') {
            $elements[] = $this->getLocation($authUserId);
        }

        if ($userRole === 'doctor' || $destination === 'renewal') {
            $elements[] = $this->getLicensedPrescriber($authUserId);
        }

        if (in_array($userRole, ['manager', 'admin', 'nurse'], true)) {
            $elements[] = $this->getStaff($authUserId);
        } elseif ($userRole === 'supervisingDoctor') {
            $elements[] = $this->getSupervisingDoctor($authUserId);
        } elseif ($userRole === 'midlevelPrescriber') {
            $elements[] = $this->getMidlevelPrescriber($authUserId);
        }

        return $elements;
    }

    /**
     * @param array<mixed> $patient `patient_data` row
     */
    public function getPatientName(array $patient): DOMElement
    {
        $element = $this->getDocument()->createElement('PatientName');
        $element->appendChild($this->createElementTextDemographicsCheck('last', $this->trimData($this->stripSpecialCharacter(self::field($patient, 'lname')), 35), xl('Patient Last Name')));
        $element->appendChild($this->createElementTextDemographicsCheck('first', $this->trimData($this->stripSpecialCharacter(self::field($patient, 'fname')), 35), xl('Patient First Name')));
        $element->appendChild($this->createElementText('middle', $this->trimData($this->stripSpecialCharacter(self::field($patient, 'mname')), 35)));

        return $element;
    }

    /**
     * @param array<mixed> $patient `patient_data` row
     */
    public function getPatientAddress(array $patient): DOMElement
    {
        $street = $this->trimData($this->stripSpecialCharacter(self::field($patient, 'street')), 35);
        $this->warningMessage($street, xl('Patient Street Address'));

        $countryCode = self::field($patient, 'country_code');
        if (trim($countryCode) === '') {
            $eRxDefaultPatientCountry = $this->getGlobals()->getDefaultPatientCountry();

            if ($eRxDefaultPatientCountry === '') {
                $this->demographicsCheck('', xl('Global Default Patient Country'));
            } else {
                $countryCode = $eRxDefaultPatientCountry;
            }

            $this->demographicsCheck('', xl('Patient Country'));
        }

        $state = self::field($patient, 'state');
        $postalCode = self::field($patient, 'postal_code');

        $element = $this->getDocument()->createElement('PatientAddress');
        $element->appendChild($this->createElementTextFieldEmpty('address1', $street, xl('Patient Street Address')));
        $element->appendChild($this->createElementTextDemographicsCheck('city', self::field($patient, 'city'), xl('Patient City')));
        if ($state !== '') {
            $element->appendChild($this->createElementText('state', $state));
        }

        if ($postalCode !== '') {
            $element->appendChild($this->createElementText('zip', $postalCode));
        }

        $element->appendChild($this->createElementText('country', substr($countryCode, 0, 2)));

        return $element;
    }

    /**
     * @param array<mixed> $patient `patient_data` row
     */
    public function getPatientContact(array $patient): DOMElement
    {
        $element = $this->getDocument()->createElement('PatientContact');
        $contact = PatientContact::fromPatientRow($patient);
        // ContactType is an xs:sequence: homeTelephone must precede cellularTelephone.
        $children = [];
        if ($contact->homeTelephone !== null) {
            $children[] = $this->createElementText('homeTelephone', $contact->homeTelephone);
        }
        if ($contact->cellularTelephone !== null) {
            $children[] = $this->createElementText('cellularTelephone', $contact->cellularTelephone);
        }
        $this->appendChildren($element, $children);

        return $element;
    }

    /**
     * @param array<mixed> $patient `patient_data` row
     */
    public function getPatientCharacteristics(int $patientId, array $patient): DOMElement
    {
        $dateOfBirth = self::field($patient, 'date_of_birth');
        $sex = self::field($patient, 'sex');
        $hasDateOfBirth = trim($dateOfBirth) !== '' && $dateOfBirth !== '00000000';

        if (!$hasDateOfBirth) {
            $this->warningMessage('', xl('Patient Date Of Birth'));
        }

        $this->warningMessage(trim($sex), xl('Patient Gender'));

        $element = $this->getDocument()->createElement('PatientCharacteristics');
        if ($hasDateOfBirth) {
            $element->appendChild($this->createElementText('dob', $dateOfBirth));
        }

        if ($sex !== '') {
            $element->appendChild($this->createElementText('gender', substr($sex, 0, 1)));
        }

        $vitals = $this->getStore()->getPatientVitalsByPatientId($patientId);
        $age = getPatientAgeYMD($dateOfBirth);
        $years = $age['age'] ?? null;
        $underNineteen = (!is_int($years) && !is_float($years)) || $years < 19;

        $height = self::field($vitals, 'height');
        $heightUnits = self::field($vitals, 'height_units');
        if ($height !== '' && $heightUnits !== '') {
            $element->appendChild($this->createElementText('height', $height));
            $element->appendChild($this->createElementText('heightUnits', $heightUnits));
        } elseif ($underNineteen) {
            $this->warningMessage('', xl('Patient Height Vital is required under age 19'));
        }

        $weight = self::field($vitals, 'weight');
        $weightUnits = self::field($vitals, 'weight_units');
        if ($weight !== '' && $weightUnits !== '') {
            $element->appendChild($this->createElementText('weight', $weight));
            $element->appendChild($this->createElementText('weightUnits', $weightUnits));
        } elseif ($underNineteen) {
            $this->warningMessage('', xl('Patient Weight Vital is required under age 19'));
        }

        return $element;
    }

    /**
     * @return list<DOMElement>
     */
    public function getPatientFreeformHealthplans(int $patientId): array
    {
        $elements = [];

        foreach ($this->getStore()->getPatientHealthplansByPatientId($patientId) as $healthplan) {
            $element = $this->getDocument()->createElement('PatientFreeformHealthplans');
            $element->appendChild($this->createElementText('healthplanName', $this->trimData($this->stripSpecialCharacter(self::field($healthplan, 'name')), 35)));

            $elements[] = $element;
        }

        return $elements;
    }

    /**
     * @return list<DOMElement>
     */
    public function getPatientFreeformAllergy(int $patientId): array
    {
        $elements = [];

        foreach ($this->getStore()->getPatientAllergiesByPatientId($patientId) as $allergy) {
            $allergyId = self::id($allergy);
            $name = self::field($allergy, 'title1');
            $severity = self::field($allergy, 'title2');
            $comments = self::field($allergy, 'comments');

            $element = $this->getDocument()->createElement('PatientFreeformAllergy');
            $element->setAttribute('ID', (string) $allergyId);

            if ($name !== '') {
                $element->appendChild($this->createElementText('allergyName', $this->trimData($this->stripSpecialCharacter($name), 70)));
            }

            if (in_array($severity, ['Mild', 'Moderate', 'Severe'], true)) {
                $element->appendChild($this->createElementText('allergySeverityTypeID', $severity));
            }

            if ($comments !== '') {
                $element->appendChild($this->createElementText('allergyComment', $this->trimData($this->stripSpecialCharacter($comments), 200)));
            }

            $elements[] = $element;

            $this->addSentAllergyIds($allergyId);
        }

        return $elements;
    }

    /**
     * @return list<DOMElement>
     */
    public function getPatientDiagnosis(int $patientId): array
    {
        $rows = $this->getStore()->getPatientDiagnosisByPatientId($patientId);

        $elements = [];
        foreach (PatientDiagnosisList::fromProblemRows($rows) as $diagnosis) {
            $element = $this->getDocument()->createElement('PatientDiagnosis');
            $element->appendChild($this->createElementText('diagnosisID', $diagnosis->code));
            $element->appendChild($this->createElementText('diagnosisType', 'ICD10'));
            if ($diagnosis->onsetDate !== null) {
                $element->appendChild($this->createElementText('onsetDate', $diagnosis->onsetDate));
            }
            if ($diagnosis->name !== null) {
                $element->appendChild($this->createElementText('diagnosisName', $diagnosis->name));
            }
            if ($diagnosis->recordedDate !== null) {
                $element->appendChild($this->createElementText('recordedDate', $diagnosis->recordedDate));
            }
            $elements[] = $element;
        }

        return $elements;
    }

    public function getPatient(int $patientId): DOMElement
    {
        $patientData = $this->getStore()->getPatientByPatientId($patientId);

        $element = $this->getDocument()->createElement('Patient');
        $element->setAttribute('ID', self::cleanText(self::field($patientData, 'pid')));
        $element->appendChild($this->getPatientName($patientData));
        $element->appendChild($this->getPatientAddress($patientData));
        $element->appendChild($this->getPatientContact($patientData));
        $element->appendChild($this->getPatientCharacteristics($patientId, $patientData));
        $this->appendChildren($element, $this->getPatientDiagnosis($patientId));
        $this->appendChildren($element, $this->getPatientFreeformHealthplans($patientId));
        $this->appendChildren($element, $this->getPatientFreeformAllergy($patientId));

        return $element;
    }

    /**
     * @param array{
     *   externalId: string,
     *   date: string,
     *   doctorName: string,
     *   drug: string,
     *   dispenseNumber: string,
     *   sig: string,
     *   refillCount: string,
     *   prescriptionType: string,
     * } $prescription
     */
    public function getOutsidePrescription(array $prescription): DOMElement
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

    /**
     * Ids that are not one of the patient's prescriptions are skipped.
     *
     * @param list<int> $prescriptionIds
     * @return list<DOMElement>
     */
    public function getPatientPrescriptions(int $patientId, array $prescriptionIds): array
    {
        $elements = [];

        foreach ($prescriptionIds as $prescriptionId) {
            $prescription = $this->getStore()->getPrescriptionById($prescriptionId, $patientId);
            if ($prescription === []) {
                continue;
            }

            $quantity = self::field($prescription, 'quantity');
            // Like eRx_xml.php, prefix the size with only the text after the quantity's leading digits, such as a unit.
            $quantityText = is_numeric($quantity) ? '' : self::regexRemove('/^[0-9]+/', $quantity);
            $sig = $quantityText . self::field($prescription, 'size') . ' ' . self::field($prescription, 'title4') . ' '
                . self::field($prescription, 'dosage') . ' In ' . self::field($prescription, 'title1') . ' '
                . self::field($prescription, 'title2') . ' ' . self::field($prescription, 'title3');

            $elements[] = $this->getOutsidePrescription([
                'externalId'        => self::field($prescription, 'prescid'),
                'date'              => self::field($prescription, 'date_added'),
                'doctorName'        => self::field($prescription, 'docname'),
                'drug'              => $this->trimData($this->stripSpecialCharacter(self::field($prescription, 'drug')), 80),
                'dispenseNumber'    => (string) intval($quantity),
                'sig'               => $this->trimData($this->stripSpecialCharacter($sig), 140),
                'refillCount'       => (string) intval(self::field($prescription, 'per_refill')),
                'prescriptionType'  => 'reconcile',
            ]);

            $this->addSentPrescriptionId($prescriptionId);
        }

        return $elements;
    }

    /**
     * @return list<DOMElement>
     */
    public function getPatientMedication(int $patientId, int $uploadActive, int $count): array
    {
        $elements = [];

        foreach ($this->getStore()->selectMedicationsNotUploadedByPatientId($patientId, $uploadActive, $count) as $medication) {
            $medicationId = self::id($medication);

            $elements[] = $this->getOutsidePrescription([
                'externalId'        => (string) $medicationId,
                'date'              => self::field($medication, 'begdate'),
                'doctorName'        => '',
                'drug'              => $this->trimData(self::field($medication, 'title'), 80),
                'dispenseNumber'    => '',
                'sig'               => '',
                'refillCount'       => '',
                'prescriptionType'  => 'reconcile',
            ]);

            $this->addSentMedicationIds($medicationId);
        }

        return $elements;
    }

    /**
     * Not-uploaded prescriptions only shorten the medication list. They are
     * never sent themselves: before the page parsed its ids, no request could
     * reach the branch that sent them.
     *
     * @param list<int> $requestedPrescriptionIds
     * @return list<DOMElement>
     */
    public function getPatientElements(int $patientId, int $totalCount, array $requestedPrescriptionIds): array
    {
        if ($patientId === 0) {
            return [];
        }

        $uploadActive = $this->getGlobals()->getUploadActive() ? 1 : 0;

        $elements = [$this->getPatient($patientId)];
        $elements = array_merge($elements, $this->getPatientPrescriptions($patientId, $requestedPrescriptionIds));

        $notUploadedCount = count($this->getStore()->selectPrescriptionIdsNotUploadedByPatientId($patientId, $uploadActive, $totalCount));
        if ($notUploadedCount < $totalCount) {
            $elements = array_merge($elements, $this->getPatientMedication($patientId, $uploadActive, $totalCount - $notUploadedCount));
        }

        return $elements;
    }

    private function userRole(int $authUserId): string
    {
        return self::field($this->getStore()->getUserById($authUserId), 'newcrop_user_role');
    }

    private function getPrescriber(string $tagName, int $authUserId, string $prescriberType, string $deaText, string $npiText, bool $prefix = false): DOMElement
    {
        $userDetails = $this->getStore()->getUserById($authUserId);
        $npi = self::field($userDetails, 'npi');
        $upin = self::field($userDetails, 'upin');

        $element = $this->getDocument()->createElement($tagName);
        $element->setAttribute('ID', self::cleanText($npi));
        $element->appendChild($this->getLicensedPrescriberName($userDetails, $prescriberType, $prefix));
        $element->appendChild($this->createElementTextFieldEmpty('dea', self::field($userDetails, 'federaldrugid'), $deaText));
        if ($upin !== '') {
            $element->appendChild($this->createElementText('upin', $upin));
        }

        $element->appendChild($this->createElementText('licenseNumber', self::field($userDetails, 'state_license_number')));
        $element->appendChild($this->createElementTextFieldEmpty('npi', $npi, $npiText));

        return $element;
    }

    /**
     * @param array<mixed> $row
     */
    private static function field(array $row, int|string $key): string
    {
        return self::scalar($row[$key] ?? null);
    }

    /**
     * @param array<mixed> $row
     */
    private static function id(array $row): int
    {
        $id = $row['id'] ?? null;
        if (is_string($id) && ctype_digit($id)) {
            return (int) $id;
        }
        if (is_int($id)) {
            return $id;
        }

        throw new UnexpectedValueException('Ensora eRx rows must have a numeric id');
    }

    /**
     * A database or configuration value as text; null is empty.
     */
    private static function scalar(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        throw new UnexpectedValueException('Ensora eRx values must be strings or numbers');
    }

    private static function serialize(DOMDocument $document): string
    {
        $xml = $document->saveXML();
        if ($xml === false) {
            throw new RuntimeException('Could not serialize the Ensora eRx request');
        }

        return $xml;
    }

    private static function regexRemove(string $pattern, #[SensitiveParameter] string $subject): string
    {
        $result = preg_replace($pattern, '', $subject);
        if ($result === null) {
            throw new RuntimeException('Could not clean an Ensora eRx value');
        }

        return $result;
    }
}
