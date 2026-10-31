<?php

/**
 * ServiceRequest resources for referrals, read from the Transactions screen's referral
 * form (LBTref). FhirServiceRequestService serves orders from procedure_order and
 * routes the referral category here (D8 of #13266).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR\ServiceRequest;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRServiceRequest;
use OpenEMR\FHIR\R4\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestIntent;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\CodeTypesService;
use OpenEMR\Services\FHIR\FhirCodeSystemConstants;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\ReferralService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldException;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Validators\ProcessingResult;

class FhirServiceRequestReferralService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    use PatientSearchTrait;

    /**
     * SNOMED CT 3457005 "Patient referral (procedure)". It is the category, and the code
     * when the referral names no requested service. US Core 6.1 permits a category code
     * outside its ServiceRequest category value set, and 3457005 is a procedure code, so
     * within US Core Procedure Codes for ServiceRequest.code.
     */
    public const CATEGORY_REFERRAL = '3457005';
    public const CATEGORY_REFERRAL_DISPLAY = 'Patient referral';

    private const PROFILE = 'http://hl7.org/fhir/us/core/StructureDefinition/us-core-servicerequest';

    private ?ReferralService $referralService = null;

    private function getReferralService(): ReferralService
    {
        return $this->referralService ??= new ReferralService();
    }

    public function setReferralService(ReferralService $referralService): void
    {
        $this->referralService = $referralService;
    }

    /**
     * FhirServiceRequestService routes a category search here for the referral category.
     */
    public function supportsCategory(string $category): bool
    {
        return $category === self::CATEGORY_REFERRAL;
    }

    /**
     * The same search parameters the order side supports, so no filter is silently
     * ignored when a search reaches both.
     */
    protected function loadSearchParameters(): array
    {
        return [
            'patient' => $this->getPatientContextSearchField(),
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'category' => new FhirSearchParameterDefinition('category', SearchFieldType::TOKEN, ['category']),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'authored' => new FhirSearchParameterDefinition('authored', SearchFieldType::DATETIME, ['authored']),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            'intent' => new FhirSearchParameterDefinition('intent', SearchFieldType::TOKEN, ['intent']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    public function getLastModifiedSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_modified']);
    }

    /**
     * The category is always the referral one, so a category search either keeps every
     * referral or none. The code can be any of a referral's requested services, kept as
     * "TYPE:CODE" text, so it is matched after mapping, against each coding's system and code.
     *
     * @param array<mixed> $openEMRSearchParameters
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        $category = $openEMRSearchParameters['category'] ?? null;
        $code = $openEMRSearchParameters['code'] ?? null;
        // Both are TOKEN parameters, so the search parser hands them over as TokenSearchFields. Anything
        // else is refused (a 400, by name) rather than read as no filter, which would return every referral.
        if ($category !== null && !$category instanceof TokenSearchField) {
            throw new SearchFieldException('category', 'category must be a token search');
        }
        if ($code !== null && !$code instanceof TokenSearchField) {
            throw new SearchFieldException('code', 'code must be a token search');
        }
        if ($category !== null && !self::anyTokenMatches($category, [[FhirCodeSystemConstants::SNOMED_CT, self::CATEGORY_REFERRAL]])) {
            return new ProcessingResult();
        }
        $fields = [];
        foreach ($openEMRSearchParameters as $name => $field) {
            // The referral query has no category or code column: category is answered above, and code
            // after mapping, below. Every other field goes to ReferralService as it is.
            if ($name === 'category' || $name === 'code') {
                continue;
            }
            if (!is_string($name) || !$field instanceof ISearchField) {
                throw new SearchFieldException((string)$name, 'not a search field');
            }
            $fields[$name] = $field;
        }

        $result = $this->getReferralService()->search($fields);
        if (!$result->isValid() || $code === null) {
            return $result;
        }
        $filtered = new ProcessingResult();
        $records = $result->getData();
        foreach (is_array($records) ? $records : [] as $record) {
            if (is_array($record) && self::anyTokenMatches($code, self::codings(self::requestedService($record)))) {
                $filtered->addData($record);
            }
        }
        return $filtered;
    }

    /**
     * A ReferralService::search() record as a US Core ServiceRequest.
     *
     * @param array<mixed> $dataRecord
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRServiceRequest
    {
        $record = $dataRecord;
        $serviceRequest = new FHIRServiceRequest();
        $lastModified = self::text($record, 'last_modified');
        $meta = UtilsService::createFhirMeta('1', $lastModified === null ? UtilsService::getDateFormattedAsUTC() : self::utc($lastModified));
        $meta->addProfile(new FHIRCanonical(self::PROFILE));
        $serviceRequest->setMeta($meta);

        $id = new FHIRId();
        $id->setValue(self::text($record, 'uuid') ?? '');
        $serviceRequest->setId($id);
        $serviceRequest->setStatus(new FHIRRequestStatus(['value' => self::text($record, 'status') ?? 'active']));
        $serviceRequest->setIntent(new FHIRRequestIntent(['value' => 'order']));
        $serviceRequest->addCategory(self::referralConcept());
        $serviceRequest->setCode(self::requestedService($record));

        $puuid = self::text($record, 'puuid');
        if ($puuid !== null) {
            $serviceRequest->setSubject(UtilsService::createRelativeReference('Patient', $puuid));
        }
        // The referral date is a date, which FHIR's dateTime allows as it is; a time would
        // only be invented by converting midnight to UTC.
        $authored = self::text($record, 'authored');
        if ($authored !== null) {
            $serviceRequest->setAuthoredOn(new FHIRDateTime(preg_match('/^\d{4}-\d{2}-\d{2}$/D', $authored) === 1 ? $authored : self::utc($authored)));
        }
        $requester = self::person($record, 'requester');
        if ($requester !== null) {
            $serviceRequest->setRequester($requester);
        }
        $performer = self::person($record, 'performer');
        if ($performer !== null) {
            $serviceRequest->addPerformer($performer);
        }
        foreach (self::reasons(self::text($record, 'refer_diag')) as $reason) {
            $serviceRequest->addReasonCode($reason);
        }
        $body = self::text($record, 'body');
        if ($body !== null) {
            $note = new FHIRAnnotation();
            $note->setText(new FHIRMarkdown($body));
            $serviceRequest->addNote($note);
        }
        return $serviceRequest;
    }

    /**
     * @return string[]
     */
    public function getProfileURIs(): array
    {
        return [self::PROFILE];
    }

    /**
     * Referrals are read-only here: a POST or PUT goes to the order service, so the
     * three write methods are never reached.
     *
     * @return array<mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        throw new \BadMethodCallException('Referral ServiceRequests are read-only');
    }

    /**
     * @param mixed $openEmrRecord
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        throw new \BadMethodCallException('Referral ServiceRequests are read-only');
    }

    /**
     * @param mixed $fhirResourceId
     * @param mixed $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        throw new \BadMethodCallException('Referral ServiceRequests are read-only');
    }

    /**
     * As for orders, with the requester as author when it is a Practitioner reference;
     * otherwise the organization is.
     *
     * @param mixed $dataRecord
     * @param bool $encode unused: FhirServiceBase declares it, and nothing passes it
     */
    public function createProvenanceResource($dataRecord, $encode = false): ?FHIRProvenance
    {
        if (!($dataRecord instanceof FHIRServiceRequest)) {
            throw new \BadMethodCallException('Data record should be correct instance class');
        }
        $requester = $dataRecord->getRequester();
        $author = self::isReference($requester) ? $requester : null;
        $provenance = (new FhirProvenanceService())->createProvenanceForDomainResource($dataRecord, $author);
        return $provenance instanceof FHIRProvenance ? $provenance : null;
    }

    private static function referralConcept(): FHIRCodeableConcept
    {
        return UtilsService::createCodeableConcept([
            self::CATEGORY_REFERRAL => ['code' => self::CATEGORY_REFERRAL, 'description' => self::CATEGORY_REFERRAL_DISPLAY, 'system' => FhirCodeSystemConstants::SNOMED_CT],
        ]);
    }

    /**
     * Referrer Diagnosis as reason codes. Each coded entry (a code type whose system OpenEMR
     * knows, such as ICD10:I10) is a coding. Anything else is the reason's text: on a stock
     * install the field is free text, and "chest pain" is not a code.
     *
     * @return list<FHIRCodeableConcept>
     */
    private static function reasons(?string $diagnosis): array
    {
        $reasons = [];
        $text = [];
        $codeTypes = new CodeTypesService();
        foreach (explode(';', $diagnosis ?? '') as $entry) {
            $entry = trim($entry);
            if ($entry === '') {
                continue;
            }
            $parsed = str_contains($entry, ':') ? $codeTypes->parseCodesIntoCodeableConcepts($entry) : [];
            $code = reset($parsed);
            if (is_array($code) && is_string($code['system'] ?? null) && $code['system'] !== '') {
                $reasons[] = UtilsService::createCodeableConcept($parsed);
            } else {
                $text[] = $entry;
            }
        }
        if ($text !== []) {
            $concept = new FHIRCodeableConcept();
            $concept->setText(new FHIRString(implode('; ', $text)));
            $reasons[] = $concept;
        }
        return $reasons;
    }

    /**
     * The requested services, or the referral code when none is named.
     *
     * @param array<mixed> $record
     */
    private static function requestedService(array $record): FHIRCodeableConcept
    {
        $codes = array_filter(
            (new CodeTypesService())->parseCodesIntoCodeableConcepts(self::text($record, 'refer_related_code') ?? ''),
            static fn(mixed $code): bool => is_array($code) && is_string($code['system'] ?? null) && $code['system'] !== ''
        );
        return $codes === [] ? self::referralConcept() : UtilsService::createCodeableConcept($codes);
    }

    /**
     * A reference to the user as a Practitioner, or an address-book entry as an
     * Organization, when FHIR serves them as one; else their name alone, so the reference
     * never points at a resource that isn't there.
     *
     * @param array<mixed> $record
     */
    private static function person(array $record, string $prefix): ?FHIRReference
    {
        $uuid = self::text($record, $prefix . '_uuid');
        $name = self::text($record, $prefix . '_name');
        if ($uuid !== null && in_array($record[$prefix . '_is_practitioner'] ?? null, [1, '1', true], true)) {
            return UtilsService::createRelativeReference('Practitioner', $uuid, $name);
        }
        if ($uuid !== null && in_array($record[$prefix . '_is_organization'] ?? null, [1, '1', true], true)) {
            return UtilsService::createRelativeReference('Organization', $uuid, $name);
        }
        if ($name === null) {
            return null;
        }
        $reference = new FHIRReference();
        $reference->setDisplay(new FHIRString($name));
        return $reference;
    }

    /**
     * @return list<array{string, string}> [system, code] of each coding
     */
    private static function codings(FHIRCodeableConcept $concept): array
    {
        $out = [];
        foreach ($concept->getCoding() as $coding) {
            $out[] = [(string)$coding->getSystem(), (string)$coding->getCode()];
        }
        return $out;
    }

    /**
     * Whether any searched token matches one of the codings: the code always, and the
     * system when the token names one.
     *
     * @param list<array{string, string}> $codings
     */
    private static function anyTokenMatches(TokenSearchField $field, array $codings): bool
    {
        foreach ($field->getValues() as $value) {
            if (!$value instanceof TokenSearchValue) {
                continue;
            }
            $system = $value->getSystem();
            foreach ($codings as [$codingSystem, $codingCode]) {
                if ((string)$value->getCode() === $codingCode && ($system === null || $system === '' || $system === $codingSystem)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Whether a requester or performer points at a resource, rather than being a name alone
     * or absent (the generated getters return null when the element isn't set).
     */
    private static function isReference(mixed $reference): bool
    {
        return $reference instanceof FHIRReference && trim((string)$reference->getReference()) !== '';
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
