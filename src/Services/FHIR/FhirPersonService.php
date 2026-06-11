<?php

/**
 * FHIR Person Service.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPerson;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRPractitioner;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\Traits\BulkExportSupportAllOperationsTrait;
use OpenEMR\Services\FHIR\Traits\FhirBulkExportDomainResourceTrait;
use OpenEMR\Services\PractitionerService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\UserService;
use OpenEMR\Validators\ProcessingResult;

class FhirPersonService extends FhirServiceBase implements IFhirExportableResourceService
{
    use BulkExportSupportAllOperationsTrait;
    use FhirBulkExportDomainResourceTrait;

    const RESOURCE_NAME = 'Person';

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var PractitionerService
     */
    private $practitionerService;

    /**
     * FHIR identifier system for US-NPI, used both to surface NPI in reads and to
     * extract it from FHIR Person writes.
     */
    private const US_NPI_SYSTEM = 'http://hl7.org/fhir/sid/us-npi';

    public function __construct()
    {
        parent::__construct();
        $this->userService = new UserService();
        $this->practitionerService = new PractitionerService();
    }

    /**
     * Returns an array mapping FHIR Practitioner Resource search parameters to OpenEMR Practitioner search parameters
     * @return array The search parameters
     */
    protected function loadSearchParameters()
    {
        return  [
            // not sure if this a token or not
            'active' => new FhirSearchParameterDefinition('active', SearchFieldType::TOKEN, ['active']),

            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ["phonew1", "phone", "phonecell"]),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ["email", "phone", "phonew1", "phonecell"]),
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ["street", "streetb", "zip", "city", "state"]),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ['zip']),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),

            'family' => new FhirSearchParameterDefinition('family', SearchFieldType::STRING, ["lname"]),
            'given' => new FhirSearchParameterDefinition('given', SearchFieldType::STRING, ["fname", "mname"]),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ["users.title", "fname", "mname", "lname"]),

            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated']);
    }


    /**
     * Parses an OpenEMR user record, returning the equivalent FHIR Person Resource
     *
     * @param array $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIRPractitioner
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $person = new FHIRPerson();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $person->setMeta($meta);

        $person->setActive($dataRecord['active'] == "1" ? true : false);

        $narrativeText = '';
        if (isset($dataRecord['fname'])) {
            $narrativeText = $dataRecord['fname'];
        }
        if (isset($dataRecord['lname'])) {
            $narrativeText .= ' ' . $dataRecord['lname'];
        }
        $text = [
            'status' => 'generated',
            'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
        ];
        $person->setText($text);

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $person->setId($id);

        $person->addName(UtilsService::createHumanNameFromRecord($dataRecord));
        $person->addAddress(UtilsService::createAddressFromRecord($dataRecord));

        if (!empty($dataRecord['phone'])) {
            $person->addTelecom([
                'system' => 'phone',
                'value' => $dataRecord['phone'],
                'use' => 'home'
            ]);
        }

        if (!empty($dataRecord['phonew1'])) {
            $person->addTelecom([
                'system' => 'phone',
                'value' => $dataRecord['phonew1'],
                'use' => 'work'
            ]);
        }

        if (!empty($dataRecord['phonecell'])) {
            $person->addTelecom([
                'system' => 'phone',
                'value' => $dataRecord['phonecell'],
                'use' => 'mobile'
            ]);
        }

        if (isset($dataRecord['email'])) {
            $person->addTelecom([
                'system' => 'email',
                'value' => $dataRecord['email'],
                'use' => 'home'
            ]);
        }

        if ($encode) {
            return json_encode($person);
        } else {
            return $person;
        }
    }

    /**
     * Parses a FHIR Person resource into the OpenEMR users-table shape consumed by
     * PractitionerService::insert/update.
     *
     * FHIR Person reads (FhirPersonService::searchForOpenEMRRecords) come from `users`,
     * and FhirPractitionerService already writes to the same table — so Person writes
     * delegate to PractitionerService for consistency. PractitionerValidator requires
     * an NPI; we extract it from a FHIR `identifier` with system US_NPI_SYSTEM. Without
     * one, insertOpenEMRRecord returns a 422-style ProcessingResult.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource)
    {
        if (!($fhirResource instanceof FHIRPerson) && !($fhirResource instanceof FHIRPractitioner)) {
            throw new \InvalidArgumentException(
                'Expected FHIRPerson resource, got ' . $fhirResource::class
            );
        }

        $json = $fhirResource->jsonSerialize();
        $data = [];

        if (!empty($json['id']) && is_string($json['id'])) {
            $data['uuid'] = $json['id'];
        }

        // identifier[] -> npi (system http://hl7.org/fhir/sid/us-npi)
        foreach (($json['identifier'] ?? []) as $identifier) {
            $system = $identifier['system'] ?? null;
            $value = $identifier['value'] ?? null;
            if ($system === self::US_NPI_SYSTEM && is_string($value) && $value !== '') {
                $data['npi'] = $value;
                break;
            }
        }

        // name[] -> fname / lname / mname / title (prefix). Prefer use=official; otherwise
        // first entry. Mirrors the read-side createHumanNameFromRecord shape.
        $names = is_array($json['name'] ?? null) ? $json['name'] : [];
        $name = null;
        foreach ($names as $candidate) {
            if (is_array($candidate) && ($candidate['use'] ?? null) === 'official') {
                $name = $candidate;
                break;
            }
        }
        if ($name === null && !empty($names) && is_array($names[0])) {
            $name = $names[0];
        }
        if (is_array($name)) {
            if (!empty($name['family']) && is_string($name['family'])) {
                $data['lname'] = $name['family'];
            }
            $given = is_array($name['given'] ?? null) ? $name['given'] : [];
            if (!empty($given[0]) && is_string($given[0])) {
                $data['fname'] = $given[0];
            }
            if (!empty($given[1]) && is_string($given[1])) {
                $data['mname'] = $given[1];
            }
            $prefix = $name['prefix'][0] ?? null;
            if (is_string($prefix) && $prefix !== '') {
                $data['title'] = $prefix;
            }
        }

        // address[0] -> street/city/state/zip
        $address = $json['address'][0] ?? null;
        if (is_array($address)) {
            $line = $address['line'][0] ?? null;
            if (is_string($line) && $line !== '') {
                $data['street'] = $line;
            }
            foreach (['city' => 'city', 'state' => 'state', 'postalCode' => 'zip'] as $fhirKey => $openemrKey) {
                $val = $address[$fhirKey] ?? null;
                if (is_string($val) && $val !== '') {
                    $data[$openemrKey] = $val;
                }
            }
        }

        // telecom[] -> phone (home), phonew1 (work), phonecell (mobile), email
        foreach (($json['telecom'] ?? []) as $telecom) {
            if (!is_array($telecom)) {
                continue;
            }
            $system = $telecom['system'] ?? null;
            $value = $telecom['value'] ?? null;
            $use = $telecom['use'] ?? null;
            if (!is_string($value) || $value === '') {
                continue;
            }
            if ($system === 'phone') {
                $data[match ($use) {
                    'mobile' => 'phonecell',
                    'work' => 'phonew1',
                    default => 'phone',
                }] = $value;
            } elseif ($system === 'email') {
                $data['email'] = $value;
            }
        }

        // gender -> users.title? No — there is no sex column on users. The legacy code wrote
        // 'sex' into the record, but PractitionerService.buildInsertColumns silently drops it
        // because it isn't a users column. We omit it from the write payload to keep PHPStan
        // and reviewers from wondering about unused fields.

        return $data;
    }

    /**
     * Inserts a FHIR Person as a row in the `users` table via PractitionerService.
     *
     * Requires NPI in the FHIR identifier (system http://hl7.org/fhir/sid/us-npi) because
     * PractitionerValidator gates writes on it. The 422-style response calls that out.
     *
     * @param array<string, mixed> $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        if (empty($openEmrRecord['npi'])) {
            $result = new ProcessingResult();
            $result->setValidationMessages([
                'identifier' => 'FHIR Person writes require an identifier with system '
                    . self::US_NPI_SYSTEM,
            ]);
            return $result;
        }

        return $this->practitionerService->insert($openEmrRecord);
    }

    /**
     * Updates an existing `users` row via PractitionerService.
     *
     * @param string $fhirResourceId The users.uuid string
     * @param array<string, mixed> $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        return $this->practitionerService->update($fhirResourceId, $updatedOpenEMRRecord);
    }

    /**
     * Searches for OpenEMR records using OpenEMR search parameters
     *
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->userService->search($openEMRSearchParameters);
    }
    public function createProvenanceResource($dataRecord = [], $encode = false): never
    {
        // TODO: If Required in Future
        throw new \BadMethodCallException("provenance record is not supported in this resource");
    }
}
