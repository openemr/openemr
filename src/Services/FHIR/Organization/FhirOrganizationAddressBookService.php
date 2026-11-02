<?php

/**
 * External organizations from the address book as FHIR Organizations (#14710), read-only:
 * they are edited in the address book. Each keeps its users.uuid as its id, so a referral's
 * performer or a Task's owner can point at Organization/{uuid}.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\Organization;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\AddressBookOrganizationService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\INonPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirOrganizationAddressBookService extends FhirServiceBase implements INonPatientCompartmentResourceService
{
    private ?AddressBookOrganizationService $organizationService = null;

    private function getOrganizationService(): AddressBookOrganizationService
    {
        return $this->organizationService ??= new AddressBookOrganizationService();
    }

    public function setOrganizationService(AddressBookOrganizationService $organizationService): void
    {
        $this->organizationService = $organizationService;
    }

    /**
     * Every search parameter FhirOrganizationService offers, so that a search doesn't
     * return these organizations unfiltered for want of one.
     */
    protected function loadSearchParameters(): array
    {
        return [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'email' => new FhirSearchParameterDefinition('email', SearchFieldType::TOKEN, ['email']),
            'phone' => new FhirSearchParameterDefinition('phone', SearchFieldType::TOKEN, ['phone']),
            'telecom' => new FhirSearchParameterDefinition('telecom', SearchFieldType::TOKEN, ['email', 'phone']),
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ['street', 'street_line_2', 'postal_code', 'city', 'state']),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ['postal_code']),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['name']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated']);
    }

    /**
     * @param array<mixed> $openEMRSearchParameters
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $fields = [];
        foreach ($openEMRSearchParameters as $name => $field) {
            if (is_string($name) && $field instanceof ISearchField) {
                $fields[$name] = $field;
            }
        }
        return $this->getOrganizationService()->search($fields);
    }

    /**
     * An AddressBookOrganizationService::search() record as an Organization.
     *
     * @param array<mixed> $dataRecord
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIROrganization
    {
        $record = $dataRecord;
        $organization = new FHIROrganization();
        $lastUpdated = self::text($record, 'last_updated');
        $organization->setMeta(UtilsService::createFhirMeta('1', $lastUpdated === null ? UtilsService::getDateFormattedAsUTC() : self::utc($lastUpdated)));

        $id = new FHIRId();
        $id->setValue(self::text($record, 'uuid') ?? '');
        $organization->setId($id);
        $organization->setActive(new FHIRBoolean(in_array($record['active'] ?? null, [1, '1', true], true)));
        $organization->setName(new FHIRString(self::text($record, 'name') ?? ''));

        $npi = self::text($record, 'npi');
        if ($npi !== null) {
            $identifier = new FHIRIdentifier();
            $identifier->setSystem(new FHIRUri(FhirCodeSystemConstants::PROVIDER_NPI));
            $identifier->setValue(new FHIRString($npi));
            $organization->addIdentifier($identifier);
        }
        foreach (['phone' => 'phone', 'fax' => 'fax', 'email' => 'email', 'url' => 'url'] as $column => $system) {
            $value = self::text($record, $column);
            if ($value !== null) {
                $organization->addTelecom(UtilsService::createContactPoint($value, $system, 'work'));
            }
        }
        $address = self::address($record);
        if ($address !== null) {
            $organization->addAddress($address);
        }
        return $organization;
    }

    /**
     * Address book entries are edited in the address book, so the write methods below
     * refuse with a 400 (a PUT for one of these ids reaches this service).
     *
     * @return array<mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        return [];
    }

    /**
     * @param mixed $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        return self::readOnly();
    }

    /**
     * @param mixed $fhirResourceId
     * @param mixed $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        return self::readOnly();
    }

    /**
     * As for the other organization sources: an address book entry has no author to report.
     *
     * @param mixed $dataRecord
     */
    public function createProvenanceResource($dataRecord, $encode = false): null
    {
        return null;
    }

    private static function readOnly(): ProcessingResult
    {
        $result = new ProcessingResult();
        $result->setValidationMessages(['Organization' => 'External organizations are edited in the address book']);
        return $result;
    }

    /**
     * The address as recorded. UtilsService::createAddressFromRecord() would add a period
     * starting a year ago, which an address book entry doesn't record.
     *
     * @param array<mixed> $record
     */
    private static function address(array $record): ?FHIRAddress
    {
        $address = new FHIRAddress();
        $any = false;
        foreach (['street', 'street_line_2'] as $column) {
            $line = self::text($record, $column);
            if ($line !== null) {
                $address->addLine(new FHIRString($line));
                $any = true;
            }
        }
        $city = self::text($record, 'city');
        if ($city !== null) {
            $address->setCity(new FHIRString($city));
        }
        $state = self::text($record, 'state');
        if ($state !== null) {
            $address->setState(new FHIRString($state));
        }
        $postalCode = self::text($record, 'postal_code');
        if ($postalCode !== null) {
            $address->setPostalCode(new FHIRString($postalCode));
        }
        return $any || $city !== null || $state !== null || $postalCode !== null ? $address : null;
    }

    /**
     * @param array<mixed> $record
     */
    private static function text(array $record, string $column): ?string
    {
        $value = $record[$column] ?? null;
        if (is_int($value) || is_float($value)) {
            $value = (string)$value;
        }
        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /** A local date and time from the database, as FHIR's UTC form. */
    private static function utc(string $localDateTime): string
    {
        $utc = UtilsService::getLocalDateAsUTC($localDateTime);
        return is_string($utc) ? $utc : $localDateTime;
    }
}
