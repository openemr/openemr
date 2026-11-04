<?php

/**
 * FhirOrganizationInsuranceService.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Organization;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\ValueObjects\PhoneNumber;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIROrganization;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAddress;
use OpenEMR\FHIR\R4\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPoint;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointSystem;
use OpenEMR\FHIR\R4\FHIRElement\FHIRContactPointUse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUri;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\INonPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\InsuranceCompanyService;
use OpenEMR\Services\PhoneNumberService;
use OpenEMR\Services\PhoneType;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirOrganizationInsuranceService extends FhirServiceBase implements INonPatientCompartmentResourceService
{
    use FhirServiceBaseEmptyTrait;

    /**
     * @var InsuranceCompanyService
     */
    private $insuranceService;

    public function __construct(
        $fhirApiURL = null,
        private readonly PhoneNumberService $phoneNumberService = new PhoneNumberService(),
    ) {
        parent::__construct($fhirApiURL);
        $this->insuranceService = new InsuranceCompanyService();
    }

    /**
     * Returns an array mapping FHIR Organization Resource search parameters to OpenEMR Organization search parameters
     *
     * @return array The search parameters
     */
    protected function loadSearchParameters(): array
    {
        return  [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'address' => new FhirSearchParameterDefinition('address', SearchFieldType::STRING, ["line1", "line2", "zip", "city", "state", "country"]),
            'address-city' => new FhirSearchParameterDefinition('address-city', SearchFieldType::STRING, ['city']),
            'address-postalcode' => new FhirSearchParameterDefinition('address-postalcode', SearchFieldType::STRING, ["zip"]),
            'address-state' => new FhirSearchParameterDefinition('address-state', SearchFieldType::STRING, ['state']),
            'name' => new FhirSearchParameterDefinition('name', SearchFieldType::STRING, ['name']),
            '_lastUpdated' => $this->getLastModifiedSearchField()
        ];
    }

    public function getLastModifiedSearchField(): ?FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_updated']);
    }

    /**
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        if (!isset($openEMRSearchParameters['name'])) {
            // make sure we only return records that have a name or an identifier, otherwise its invalid for FHIR

            $name = new TokenSearchField('name', [new TokenSearchValue(false)]);
            $name->setModifier(SearchModifier::MISSING);
            $cms = new TokenSearchField('cms_id', [new TokenSearchValue(false)]);
            $cms->setModifier(SearchModifier::MISSING);
            $cmsAlt = new TokenSearchField('alt_cms_id', [new TokenSearchValue(false)]);
            $cmsAlt->setModifier(SearchModifier::MISSING);
            $openEMRSearchParameters['identifier-name'] = new CompositeSearchField('identifier-name', [], false);
            $openEMRSearchParameters['identifier-name']->setChildren([$name, $cms, $cmsAlt]);
        }
        return $this->insuranceService->search($openEMRSearchParameters);
    }

    /**
     * Parses an OpenEMR organization record, returning the equivalent FHIR Organization Resource
     *
     * @param  array   $dataRecord The source OpenEMR data record
     * @param bool $encode Indicates if the returned resource is encoded into a string. Defaults to false.
     * @return FHIROrganization
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false)
    {
        $organizationResource = new FHIROrganization();

        $meta = new FHIRMeta();
        $meta->setVersionId('1');
        if (!empty($dataRecord['last_updated'])) {
            $meta->setLastUpdated(UtilsService::getLocalDateAsUTC($dataRecord['last_updated']));
        } else {
            $meta->setLastUpdated(UtilsService::getDateFormattedAsUTC());
        }
        $organizationResource->setMeta($meta);
        $organizationResource->setActive($dataRecord['inactive'] == '0');

        $narrativeText = trim($dataRecord['name'] ?? "");
        if (!empty($narrativeText)) {
            $text = [
                'status' => 'generated',
                'div' => '<div xmlns="http://www.w3.org/1999/xhtml"> <p>' . $narrativeText . '</p></div>'
            ];
            $organizationResource->setText($text);
        }

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $organizationResource->setId($id);

        $identifiers = [
            'cms_id' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE
            ,'alt_cms_id' => FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE
        ];
        foreach ($identifiers as $id => $system) {
            if (!empty($dataRecord[$id])) {
                $identifier = new FHIRIdentifier();
                $identifier->setSystem($system);
                $identifier->setValue($dataRecord[$id]);
                $organizationResource->addIdentifier($identifier);
            }
        }

        if (isset($dataRecord['name'])) {
            $organizationResource->setName($dataRecord['name']);
        } else {
//             if the name is missing we HAVE to have this
            $organizationResource->setName(UtilsService::createDataMissingExtension());
        }

        // createAddressFromRecord() reads postal_code and country_code; an insurance company's
        // address has zip and country. A country alone isn't an address: the admin page stores
        // its default, USA, for a company saved without one. A company with no address has none to add.
        $streetParts = array_filter(
            [$dataRecord['line1'] ?? null, $dataRecord['line2'] ?? null, $dataRecord['city'] ?? null, $dataRecord['state'] ?? null, $dataRecord['zip'] ?? null],
            static fn(mixed $part): bool => is_string($part) && $part !== ''
        );
        $address = UtilsService::createAddressFromRecord($dataRecord + [
            'postal_code' => $dataRecord['zip'] ?? null,
            'country_code' => $streetParts !== [] ? ($dataRecord['country'] ?? null) : null,
        ]);
        if ($address !== null) {
            $organizationResource->addAddress($address);
        }

        $contactPoints = ['work_number' => 'phone', 'fax_number' => 'fax'];
        foreach ($contactPoints as $field => $contact) {
            if (!empty($dataRecord[$field])) {
                $organizationResource->addTelecom(UtilsService::createContactPoint(
                    $dataRecord[$field],
                    $contact,
                    'work'
                ));
            }
        }

        $organizationResource->addType(UtilsService::createCodeableConcept(['ins' => [
            'code' => 'ins', 'description' => "Insurance Company"
            , 'system' => FhirCodeSystemConstants::HL7_ORGANIZATION_TYPE]
        ]));

        if ($encode) {
            return json_encode($organizationResource);
        } else {
            return $organizationResource;
        }
    }

    /**
     * Stores the insurance company parseFhirResource() mapped, once the insurance company
     * validator accepts it, and answers with its id and uuid, as a facility POST does.
     */
    protected function insertOpenEMRRecord(mixed $openEmrRecord): ProcessingResult
    {
        // FHIR carries none of these three; InsuranceCompanyService::insert() reads each one.
        $record = FhirPayloadReader::stringKeyed($openEmrRecord)
            + ['attn' => null, 'ins_type_code' => null, 'x12_receiver_id' => null];

        $processingResult = $this->insuranceService->validate($record);
        if (!$processingResult->isValid()) {
            return $processingResult;
        }
        $unstorable = self::unstorableParts($record);
        if ($unstorable !== []) {
            $processingResult->setValidationMessages($unstorable);
            return $processingResult;
        }
        // PhoneNumberService splits a number by pattern, which reads +15552003003 as 155 520 0300;
        // it gets the ten digits instead.
        foreach (['phone', 'fax'] as $field) {
            $number = $record[$field] ?? null;
            if (is_string($number)) {
                $record[$field] = self::northAmericanDigits($number);
            }
        }
        // The company, its address and its phones are stored together or not at all.
        $id = QueryUtils::inTransaction(function () use ($record): int|string {
            $id = $this->insuranceService->insert($record);
            // The fax is stored here, once unstorableParts() has accepted it, rather than by
            // InsuranceCompanyService::insert(), which POST /api/insurance_company also calls.
            $fax = $record['fax'] ?? null;
            if (is_string($fax) && $fax !== '') {
                $this->phoneNumberService->insert(['phone' => $fax], $id, PhoneType::FAX);
            }
            return $id;
        });
        $stored = $this->insuranceService->getOneById($id);
        $processingResult->addData(['id' => $id, 'uuid' => is_array($stored) ? ($stored['uuid'] ?? null) : null]);
        return $processingResult;
    }

    /**
     * What InsuranceCompanyService::insert() would drop without a word: an address it stores only
     * with a city and a state, and a phone or fax it can split only as a NANP number.
     *
     * @param array<string, mixed> $record
     * @return array<string, string> validation messages by field
     */
    private static function unstorableParts(array $record): array
    {
        $messages = [];
        $addressGiven = false;
        foreach (['line1', 'line2', 'zip', 'city', 'state', 'country'] as $field) {
            $addressGiven = $addressGiven || ($record[$field] ?? null) !== null;
        }
        if ($addressGiven && (($record['city'] ?? null) === null || ($record['state'] ?? null) === null)) {
            $messages['address'] = 'An insurance company address needs a city and a state';
        }
        foreach (['phone' => 'telecom phone', 'fax' => 'telecom fax'] as $field => $label) {
            $number = $record[$field] ?? null;
            if (is_string($number) && self::northAmericanDigits($number) === null) {
                $messages[$field] = "The $label must be a 10-digit North American number";
            }
        }
        return $messages;
    }

    /**
     * The ten digits of a North American (+1) number, the parts phone_numbers stores (area code,
     * prefix, number); null for any other number, such as +354 555 1234, whose ten digits
     * include its country code.
     */
    private static function northAmericanDigits(string $number): ?string
    {
        $parsed = PhoneNumber::tryParse($number, 'US');
        if ($parsed === null || $parsed->getCountryCode() !== 1 || !$parsed->isPossible()) {
            return null;
        }
        return $parsed->getNationalDigits();
    }

    /**
     * Parses a FHIR Organization Resource, returning the equivalent OpenEMR insurance company
     * record, keyed as InsuranceCompanyService::insert() reads it.
     *
     * The resource may come from FhirOrganizationSerializer::deserialize(), which leaves name,
     * the address parts, the telecom parts and the identifier parts as the plain strings the
     * request's JSON held, or from code that sets the typed elements: both are read.
     *
     * @param  mixed $fhirResource The source FHIR resource, an FHIROrganization
     * @return array<string, mixed> a mapped OpenEMR data record
     */
    public function parseFhirResource($fhirResource = []): array
    {
        if (!$fhirResource instanceof FHIROrganization) {
            // get_debug_type handles both objects and arrays; avoids `::class` on non-object.
            throw new \BadMethodCallException("Resource expected to be of type " . FHIROrganization::class . " but instead was of type " . get_debug_type($fhirResource));
        }

        $data = [];

        $data['uuid'] = $fhirResource->getId() ?? null;
        $data['name'] = self::primitiveText($fhirResource->getName());
        $data['cms_id'] = null;
        $data['alt_cms_id'] = null;
        // R4 Organization.active defaults to true when absent.
        $data['inactive'] = self::isFalse($fhirResource->getActive()) ? 1 : 0;

        $activeAddress = self::activeAddress($fhirResource);
        if ($activeAddress !== null) {
            $lineValues = array_values(array_filter(
                array_map(self::primitiveText(...), $activeAddress->getLine()),
                is_string(...)
            ));
            // an insurance company's address has two lines: a third or later line joins the second
            $data['line1'] = $lineValues[0] ?? null;
            $data['line2'] = count($lineValues) > 1 ? implode(", ", array_slice($lineValues, 1)) : null;
            $data['zip'] = self::primitiveText($activeAddress->getPostalCode());
            $data['city'] = self::primitiveText($activeAddress->getCity());
            $data['state'] = self::primitiveText($activeAddress->getState());
            $data['country'] = self::primitiveText($activeAddress->getCountry());
        }

        foreach ($fhirResource->getTelecom() as $contactPoint) {
            if (!self::isCurrent($contactPoint)) {
                continue;
            }
            // 'work' is what this service emitted for the phone before it used 'phone'.
            $field = match (self::primitiveText($contactPoint->getSystem())) {
                'phone', 'work' => 'phone',
                'fax' => 'fax',
                default => null,
            };
            if ($field !== null && !isset($data[$field])) {
                $data[$field] = self::primitiveText($contactPoint->getValue());
            }
        }

        foreach ($fhirResource->getIdentifier() as $identifier) {
            if (self::primitiveText($identifier->getSystem()) === FhirCodeSystemConstants::HL7_IDENTIFIER_TYPE_TABLE) {
                // The company holds two: the first is its CMS id, the second its alternate.
                if ($data['cms_id'] === null) {
                    $data['cms_id'] = self::primitiveText($identifier->getValue());
                } elseif ($data['alt_cms_id'] === null) {
                    $data['alt_cms_id'] = self::primitiveText($identifier->getValue());
                }
            }
        }
        return $data;
    }

    /**
     * The address an insurance company keeps: the last one that hasn't ended, or, when every
     * address has ended, the one that ended last. Before, an ended address listed after the
     * current one replaced it whenever an ended one came first.
     */
    private static function activeAddress(FHIROrganization $organization): ?FHIRAddress
    {
        $current = null;
        $lastEnded = null;
        $lastEnd = null;
        foreach ($organization->getAddress() as $address) {
            $end = self::endedAt($address->getPeriod());
            if ($end === null) {
                $current = $address;
            } elseif ($lastEnd === null || $end > $lastEnd) {
                $lastEnd = $end;
                $lastEnded = $address;
            }
        }
        return $current ?? $lastEnded;
    }

    /**
     * Whether a contact point is still in use: not marked old, and its period not ended.
     */
    private static function isCurrent(FHIRContactPoint $contactPoint): bool
    {
        if (self::primitiveText($contactPoint->getUse()) === 'old') {
            return false;
        }
        return self::endedAt($contactPoint->getPeriod()) === null;
    }

    /**
     * When a period ended; null while it hasn't: no end, an end that doesn't parse, or an end
     * still to come.
     */
    private static function endedAt(?FHIRPeriod $period): ?int
    {
        $end = UtilsService::getPeriodTimestamps($period)['end'];
        return is_int($end) && $end <= time() ? $end : null;
    }

    /**
     * Whether a FHIR boolean, typed or as the bare value the serializer leaves, is false.
     */
    private static function isFalse(mixed $element): bool
    {
        if ($element instanceof FHIRBoolean) {
            $element = $element->getValue();
        }
        return $element === false || $element === 'false';
    }

    /**
     * The text of a FHIR primitive as the R4 classes hold it: a typed element, or the bare
     * string FhirOrganizationSerializer::deserialize() leaves. Blank or absent is null.
     */
    private static function primitiveText(mixed $element): ?string
    {
        if (
            $element instanceof FHIRString || $element instanceof FHIRUri
            || $element instanceof FHIRContactPointSystem || $element instanceof FHIRContactPointUse
        ) {
            $element = $element->getValue();
        }
        return is_string($element) && $element !== '' ? $element : null;
    }
}
