<?php

/*
 * FhirQuestionnaireResponseFormService.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\QuestionnaireResponse;

use BadMethodCallException;
use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use JsonException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\DomainModels\OpenEMRFhirQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuestionnaireResponseStatus;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceCreatableService;
use OpenEMR\Services\FHIR\IResourceReadableService;
use OpenEMR\Services\FHIR\IResourceSearchableService;
use OpenEMR\Services\FHIR\IResourceUpdateableService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Validators\ProcessingResult;

class FhirQuestionnaireResponseFormService extends FhirServiceBase implements
    IResourceReadableService,
    IResourceSearchableService,
    IResourceCreatableService,
    IResourceUpdateableService,
    IPatientCompartmentResourceService
{
    /**
     * If you'd prefer to keep out the empty methods that are doing nothing uncomment the following helper trait
     */
    use FhirServiceBaseEmptyTrait;
    use PatientSearchTrait;

    /**
     * @var QuestionnaireResponseService
     */
    private QuestionnaireResponseService $service;

    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new QuestionnaireResponseService();
    }

    /**
     * Maps an inbound FHIR QuestionnaireResponse onto the columns
     * QuestionnaireResponseService::saveQuestionnaireResponse() works from.
     *
     * The payload is read out of the serialized resource rather than off the element
     * getters: the write controller hydrates the resource with `new FHIR<Type>($json)`
     * and the R4 models keep their top-level members as the raw decoded values, so
     * `getMeta()->getVersionId()` and friends are reading a plain array on this path.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        if (!($fhirResource instanceof FHIRQuestionnaireResponse)) {
            throw new InvalidArgumentException("resource must be of type " . FHIRQuestionnaireResponse::class);
        }

        $json = self::toPayloadArray($fhirResource);
        $parsedResource = [];

        $responseId = FhirPayloadReader::getString($json, 'id');
        if ($responseId !== null) {
            $parsedResource['response_id'] = $responseId;
        }

        // questionnaire is 1..1 in R4 and the row is unusable without the link, so an
        // absent or foreign canonical is rejected rather than stored unresolved.
        $canonical = self::canonicalValue($json['questionnaire'] ?? null);
        if ($canonical === null) {
            throw new InvalidArgumentException("QuestionnaireResponse.questionnaire is required");
        }
        $parsedReference = UtilsService::parseReferenceString($canonical, 'Questionnaire');
        if (empty($parsedReference['uuid'])) {
            throw new InvalidArgumentException("Questionnaire does not exist on local server. Cannot save QuestionnaireResponse.");
        }
        $parsedResource['questionnaire_id'] = $parsedReference['uuid'];
        // A pinned |version is carried through rather than dropped. The repository updates a
        // questionnaire in place and bumps `version`, so storing answers against "whatever the
        // row says now" would silently attach them to a different set of questions than the
        // client filled in. fetchQuestionnaireContent() rejects a mismatch.
        $parsedResource['questionnaire_version'] = self::canonicalVersion($json['questionnaire'] ?? null);

        // our subjects at this point should really only be the patient...
        $subject = UtilsService::parseReferenceString(FhirPayloadReader::reference($json['subject'] ?? null), 'Patient');
        if (empty($subject['uuid'])) {
            throw new InvalidArgumentException("QuestionnaireResponse.subject must reference a Patient on this server.");
        }
        $parsedResource['puuid'] = $subject['uuid'];

        if (isset($json['encounter'])) {
            $encounter = UtilsService::parseReferenceString(FhirPayloadReader::reference($json['encounter']), 'Encounter');
            if (empty($encounter['uuid'])) {
                throw new InvalidArgumentException("Encounter does not exist on local server. Cannot save QuestionnaireResponse.");
            }
            $parsedResource['encounter_uuid'] = $encounter['uuid'];
        }

        if (isset($json['source'])) {
            $source = UtilsService::parseReferenceString(FhirPayloadReader::reference($json['source']));
            if (empty($source['uuid'])) {
                throw new InvalidArgumentException("Source does not exist on local server. Cannot save QuestionnaireResponse.");
            }
            if ($source['type'] === 'Practitioner') {
                $parsedResource['creator_user_uuid'] = $source['uuid'];
            }
            // on else clause handle something different here... if we are working with organization or anything
        }

        $status = FhirPayloadReader::getString($json, 'status');
        if ($status === null) {
            throw new InvalidArgumentException("QuestionnaireResponse.status is required");
        }
        // OpenEMR keeps its own vocabulary for an unfinished response
        $parsedResource['status'] = $status === 'in-progress' ? 'incomplete' : $status;

        $parsedResource['questionnaire_response'] = $json;

        $version = FhirPayloadReader::get(FhirPayloadReader::get($json, 'meta'), 'versionId');
        if (is_numeric($version)) {
            $parsedResource['version'] = (int)$version;
        }

        return $parsedResource;
    }

    /**
     * Serializes a hydrated resource back into the payload array the write mapping reads.
     *
     * @return array<array-key, mixed>
     */
    private static function toPayloadArray(FHIRDomainResource $fhirResource): array
    {
        try {
            $json = json_decode(json_encode($fhirResource, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException("QuestionnaireResponse could not be serialized", 0, $exception);
        }
        if (!is_array($json)) {
            throw new InvalidArgumentException("QuestionnaireResponse could not be serialized");
        }
        return $json;
    }

    /**
     * Reads a FHIR canonical, which arrives either as the bare url or, when the sender
     * decorates it with extensions, as an element carrying the url in `value`. A canonical
     * may also pin a version with a `|version` suffix, which is not part of the resource
     * reference the repository is keyed by.
     */
    private static function canonicalValue(mixed $canonical): ?string
    {
        if (!is_string($canonical) || $canonical === '') {
            $canonical = FhirPayloadReader::getString($canonical, 'value');
        }
        if (!is_string($canonical) || $canonical === '') {
            return null;
        }
        $versionSeparator = strpos($canonical, '|');

        return $versionSeparator === false ? $canonical : substr($canonical, 0, $versionSeparator);
    }

    /**
     * Returns the `|version` a canonical pins, or null when it pins none.
     *
     * Split from canonicalValue() because the reference and the version are wanted in different
     * places: the reference resolves the row, the version says which revision of it the answers
     * were given against.
     */
    private static function canonicalVersion(mixed $canonical): ?string
    {
        if (!is_string($canonical) || $canonical === '') {
            $canonical = FhirPayloadReader::getString($canonical, 'value');
        }
        if (!is_string($canonical) || $canonical === '') {
            return null;
        }
        $versionSeparator = strpos($canonical, '|');
        if ($versionSeparator === false) {
            return null;
        }
        $version = substr($canonical, $versionSeparator + 1);

        return $version === '' ? null : $version;
    }

    /**
     * @param array $dataRecord
     * @param bool $encode
     * @return OpenEMRFhirQuestionnaireResponse
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): OpenEMRFhirQuestionnaireResponse
    {
        // US Core 8.0 requires the following fields
        // identifier 0..1
        // questionnaire 1..1
        //   extension:questionnaireDisplay 0..1 Display name for Canonical Reference (http://hl7.org/fhir/StructureDefinition/display)
        //   extension:url 0..1 URL The location where a non-FHIR questionnaire/survey form can be found. (http://hl7.org/fhir/us/core/StructureDefinition/us-core-extension-questionnaire-uri)
        // status 1..1  in-progress | completed | amended | entered-in-error | stopped (http://hl7.org/fhir/ValueSet/questionnaire-answers-status)
        // subject 1..1 The subject of the questions
        // authored 1..1 DateTime Date the answers were gathered
        // author 0..1 The person who received and recorded the answers  (Required to support US Core Practitioner, but others are optional)
        // item 0..* The groups and questions that were part of the questionnaire
        //   linkId 1..1
        //   text 0..1
        //   answer 0..*
        //     value[x] 0..1
        //       valueDecimal 0..1
        //       valueString 0..1
        //       valueCoding 0..1
        //     item 0..* (nested groups and questions)
        //   item 0..* (nested questionnaire response items)

        // would appear that the extensions won't be covered by the QuestionnaireResponse Form as we don't support Non FHIR Questionnaire's in this form
        // will have to be covered by a different service to support that functionality.
        try {
            // parse the json data in dataRecord questionnaire
            $innerData = json_decode((string) $dataRecord['questionnaire_response'], true, 512, JSON_THROW_ON_ERROR);
            if (!isset($innerData['_questionnaire']) && isset($dataRecord['questionnaire_name'])) {
                // if we don't have an US Core 8.0 compliant questionnaire response then we will fallback on the questionnaire_title if we have one
                $innerData['_questionnaire'] = [
                    'extension' => [
                        [
                            'url' => 'http://hl7.org/fhir/StructureDefinition/display',
                            'valueString' => $dataRecord['questionnaire_name']
                        ],
                    ]
                ];
            }
        } catch (JsonException $exception) {
            // log the error and move on
            $innerData = []; // nothing we can do here, but skip the questionnaire data as its invalid
            ServiceContainer::getLogger()->error(
                "Unable to parse questionnaire json",
                ['exception' => $exception, 'uuid' => $dataRecord['uuid'] ?? '']
            );
        }
        $fhirResource = new OpenEMRFhirQuestionnaireResponse($innerData);

        $meta = new FHIRMeta();
        $meta->setVersionId($dataRecord['version'] ?? '1');
        // TODO: @adunsulag use modified_date
        $meta->setLastUpdated(new FHIRInstant(UtilsService::getDateFormattedAsUTC()));
        $fhirResource->setMeta($meta);

        $id = new FHIRId();
        $id->setValue($dataRecord['questionnaire_response_uuid']);
        $fhirResource->setId($id);

        // we trust the db records rather than the JSON as our master record if we have it.
        if (!empty($dataRecord['questionnaire_id'])) {
            // TODO: @adunsulag how do we want to handle non-standard Questionnaires that are via the _questionnaire attribute
            // currently we have nothing in OpenEMR that links to a pdf type survery.  I suppose a Document could be referenced
            // as a questionnaire result... but then how to populate all the answers?
            $questionnaire = UtilsService::createCanonicalUrlForResource('Questionnaire', $dataRecord['questionnaire_id']);
            if (!empty($dataRecord['questionnaire_name'])) {
                $questionnaire->addExtension(new FHIRExtension([
                    'valueString' => $dataRecord['questionnaire_name']
                    ,'url' => 'http://hl7.org/fhir/StructureDefinition/display'
                ]));
            }
            $fhirResource->setQuestionnaire($questionnaire);
        }

        if (!empty($dataRecord['encounter_uuid'])) {
            $fhirResource->setEncounter(UtilsService::createRelativeReference('Encounter', $dataRecord['encounter_uuid']));
        }
        if (!empty($dataRecord['puuid'])) {
            $fhirResource->setSubject(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
            if (empty($dataRecord['creator_user_id'])) {
                $fhirResource->setSource(UtilsService::createRelativeReference('Patient', $dataRecord['puuid']));
            }
        }
        if (!empty($dataRecord['creator_user_uuid'])) {
            $fhirResource->setSource(UtilsService::createRelativeReference('Practitioner', $dataRecord['creator_user_uuid']));
        }
        // TODO: @adunsulag this is a required field
        if (!empty($dataRecord['create_time'])) {
            $fhirResource->setAuthored(new FHIRDateTime(DateTime::createFromFormat("Y-m-d H:i:s", $dataRecord['create_time'])->format(DateTimeInterface::ATOM)));
        }
        $responseStatus = new FHIRQuestionnaireResponseStatus();
        if (!empty($dataRecord['status'])) {
            // map the statii
            $responseStatus->setValue(match ($dataRecord['status']) {
                'completed','amended','entered-in-error','stopped' => $dataRecord['status'],
                'incomplete','active' => 'in-progress',
                default => 'in-progress'
            });
        } elseif (is_string($fhirResource->getStatus())) {
            // otherwise we use the status in the original questionnaire response status
            $responseStatus = new FHIRQuestionnaireResponseStatus();
            $responseStatus->setValue($fhirResource->getStatus());
        } else {
            $responseStatus = new FHIRQuestionnaireResponseStatus();
            $responseStatus->setValue('in-progress');
        }
        $fhirResource->setStatus($responseStatus);


        return $fhirResource;
    }

    /**
     * This method returns the FHIR search definition objects that are used to map FHIR search fields to OpenEMR fields.
     * Since the mapping can be one FHIR search object to many OpenEMR fields, we use the search definition objects.
     * Search fields can be combined as Composite fields and represent a host of search options.
     * @see https://www.hl7.org/fhir/search.html to see the types of search operations, and search types that are available
     * for use.
     * @return array
     */
    protected function loadSearchParameters(): array
    {
        // US CORE 8.0 requires the following
        // _id, patient
        // optional search are the following:
        // patient+status
        // patient+authored
        // patient+questionnaire
        return  [
            '_id' => new FhirSearchParameterDefinition(
                '_id',
                SearchFieldType::TOKEN,
                [new ServiceField('questionnaire_response_uuid', ServiceField::TYPE_UUID)]
            )
            ,'questionnaire' => new FhirSearchParameterDefinition(
                'questionnaire',
                SearchFieldType::REFERENCE,
                [new ServiceField('questionnaire_uuid', ServiceField::TYPE_UUID)]
            )
            ,'patient' => $this->getPatientContextSearchField()
            ,'authored' => new FhirSearchParameterDefinition(
                'authored',
                SearchFieldType::DATETIME,
                [new ServiceField('create_time', ServiceField::TYPE_STRING)]
            )
        ];
    }

    /**
     * @param array<string, ISearchField> $openEMRSearchParameters OpenEMR search fields
     * @return ProcessingResult
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->service->search($openEMRSearchParameters);
    }

    /**
     * Healthcare resources often need to provide an AUDIT trail of who last touched a resource and when was it modified.
     * The ownership and AUDIT trail in FHIR is done via the Provenance record.
     * @param FHIRDomainResource $dataRecord The record we are generating a provenance from
     * @param bool $encode Whether to serialize the record or not
     */
    public function createProvenanceResource($dataRecord, $encode = false): FHIRProvenance|string|false
    {
        // we don't return any provenance authorship for this custom resource
        // if we did return it, we would fill out the following record
//        $provenance = new FHIRProvenance();
        if (!($dataRecord instanceof OpenEMRFhirQuestionnaireResponse)) {
            throw new BadMethodCallException("Data record should be correct instance class");
        }
        // provenance will just be the organization as we don't keep track of the user at the individual FHIR resource level
        // note we do track this internally in OpenEMR but FHIR R4 doesn't expose this as far as I can tell.
        $fhirProvenance = $this->getFhirProvenanceService()->createProvenanceForDomainResource($dataRecord);
        if ($fhirProvenance === null) {
            // Provenance can legitimately be unavailable (e.g. no resolvable organization/author
            // reference); FhirServiceBase::getAll() treats a falsy return as "no provenance
            // available" and continues (see issue #13054).
            return false;
        }
        return $encode ? json_encode($fhirProvenance) : $fhirProvenance;
    }

    /**
     * Seam so unit tests can substitute the provenance factory.
     */
    protected function getFhirProvenanceService(): FhirProvenanceService
    {
        return new FhirProvenanceService();
    }

    public function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        $patientId = $this->resolvePatientId($openEmrRecord['puuid'] ?? null);
        $encounterId = $this->resolveEncounterId($openEmrRecord['encounter_uuid'] ?? null);
        $questionnaire = $this->fetchQuestionnaireContent(
            $openEmrRecord['questionnaire_id'] ?? null,
            FhirPayloadReader::get($openEmrRecord, 'questionnaire_version')
        );

        // note https://build.fhir.org/http.html#create specification states that an id SHALL be
        // ignored for our create operation, so the client's id is dropped from the payload as
        // well as from the save arguments -- saveQuestionnaireResponse() would otherwise pick
        // it back up off the resource and treat the request as an update.
        $payload = self::toPayload($openEmrRecord['questionnaire_response'] ?? null);
        if (is_array($payload)) {
            unset($payload['id']);
        }

        return $this->saveResponse(
            $payload,
            $patientId,
            $encounterId,
            null,
            $questionnaire,
            is_string($openEmrRecord['questionnaire_id'] ?? null) ? $openEmrRecord['questionnaire_id'] : null,
            false
        );
    }

    /**
     * @param array<array-key, mixed> $updatedOpenEMRRecord
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        $processingResult = new ProcessingResult();
        if (!UuidRegistry::isValidStringUUID($fhirResourceId)) {
            $processingResult->setValidationMessages(['_id' => 'invalid uuid format']);
            return $processingResult;
        }

        $stored = $this->service->fetchQuestionnaireResponseById(0, null, UuidRegistry::uuidToBytes($fhirResourceId));
        if ($stored === []) {
            // an empty, error free result is what the REST layer turns into a 404
            return $processingResult;
        }

        $storedPatientId = self::toInt($stored['patient_id'] ?? null);
        $patientId = $this->resolvePatientId($updatedOpenEMRRecord['puuid'] ?? null);
        if ($storedPatientId !== $patientId) {
            // The subject is not rebindable: `patient_id` is not in the update statement, so
            // honouring a changed subject is impossible. Report it the same way an unknown id
            // is reported rather than silently writing the answers against the stored patient.
            return $processingResult;
        }

        $questionnaireId = $updatedOpenEMRRecord['questionnaire_id'] ?? null;
        if ($questionnaireId !== ($stored['questionnaire_id'] ?? null)) {
            $processingResult->setValidationMessages(
                ['questionnaire' => 'QuestionnaireResponse.questionnaire cannot be changed by an update']
            );
            return $processingResult;
        }

        $storedEncounter = $stored['encounter'] ?? null;
        $encounterUuid = $updatedOpenEMRRecord['encounter_uuid'] ?? null;
        if ($encounterUuid !== null && $encounterUuid !== $storedEncounter) {
            // `encounter` is not in the update statement either, so the same reasoning applies.
            $processingResult->setValidationMessages(
                ['encounter' => 'QuestionnaireResponse.encounter cannot be changed by an update']
            );
            return $processingResult;
        }

        // saveQuestionnaireResponse() resolves the row to update from the response id carried by
        // the payload, so it is pinned to the stored value rather than to whatever the client sent.
        $storedResponseId = $stored['response_id'] ?? null;
        $payload = self::toPayload($updatedOpenEMRRecord['questionnaire_response'] ?? null);
        if (is_array($payload) && is_string($storedResponseId)) {
            $payload['id'] = $storedResponseId;
        }

        // the encounter is left to the payload: the update statement never writes the column,
        // and the reference the client sent was checked against the stored one above.
        return $this->saveResponse(
            $payload,
            $storedPatientId,
            null,
            is_string($storedResponseId) ? $storedResponseId : null,
            self::toPayload($stored['questionnaire'] ?? null),
            is_string($stored['questionnaire_id'] ?? null) ? $stored['questionnaire_id'] : null,
            true
        );
    }

    /**
     * Persists the response and shapes the result the REST layer expects: a create answers with
     * the new record's ids, an update answers with the stored row so FhirServiceBase::update()
     * can re-emit it as a FHIR resource.
     *
     * @param array<array-key, mixed>|string $payload the serialized QuestionnaireResponse
     * @param string|array<array-key, mixed>|null $questionnaire
     */
    private function saveResponse(
        array|string $payload,
        ?int $patientId,
        ?int $encounter,
        ?string $responseId,
        string|array|null $questionnaire,
        ?string $questionnaireId,
        bool $isUpdate
    ): ProcessingResult {
        $processingResult = new ProcessingResult();
        try {
            $saved = $this->service->saveQuestionnaireResponse(
                $payload,
                $patientId,
                $encounter,
                $responseId,
                null,
                $questionnaire,
                $questionnaireId,
                null,
                true // I think we want to always generate a narrative here.
            );
        } catch (\Throwable $exception) {
            ServiceContainer::getLogger()->error(
                "Unable to save QuestionnaireResponse",
                ['exception' => $exception]
            );
            $processingResult->setInternalErrors("Server Error in saving QuestionnaireResponse resource");
            return $processingResult;
        }

        $savedResponseId = is_array($saved) ? ($saved['response_id'] ?? null) : null;
        if (!is_array($saved) || !is_string($savedResponseId)) {
            $processingResult->setInternalErrors("Server Error in saving QuestionnaireResponse resource");
            return $processingResult;
        }

        if (!$isUpdate) {
            // the create response carries the ids of the new record, matching the other FHIR writes
            $processingResult->addData([
                'id' => $saved['id'] ?? null,
                'uuid' => $savedResponseId,
            ]);
            return $processingResult;
        }

        $searchResult = $this->service->search([
            'questionnaire_response_uuid' => new TokenSearchField('questionnaire_response_uuid', [$savedResponseId], true)
        ]);
        $records = ProcessingResult::extractDataArray($searchResult);
        $storedRecord = $records[0] ?? null;
        if ($storedRecord === null) {
            $processingResult->setInternalErrors("QuestionnaireResponse could not be read back after save");
            return $processingResult;
        }
        $processingResult->addData($storedRecord);
        return $processingResult;
    }

    /**
     * Narrows a value read out of an untyped database row or parsed record to an int.
     */
    private static function toInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int)$value : null;
    }

    /**
     * Narrows a stored or parsed FHIR payload to the shapes
     * QuestionnaireResponseService::saveQuestionnaireResponse() accepts.
     *
     * @return array<array-key, mixed>|string
     */
    private static function toPayload(mixed $payload): array|string
    {
        if (is_array($payload) || is_string($payload)) {
            return $payload;
        }
        throw new InvalidArgumentException("QuestionnaireResponse payload is missing or malformed");
    }

    /**
     * Resolves the patient the answers belong to, and refuses the write when the reference
     * does not name a patient on this server.
     */
    private function resolvePatientId(mixed $puuid): int
    {
        if (!is_string($puuid) || $puuid === '') {
            throw new InvalidArgumentException("QuestionnaireResponse.subject must reference a Patient on this server.");
        }
        $patientRecords = ProcessingResult::extractDataArray((new PatientService())->getOne($puuid));
        if ($patientRecords === []) {
            throw new InvalidArgumentException("Patient does not exist");
        }
        $pid = self::toInt($patientRecords[0]['pid'] ?? null);
        if ($pid === null) {
            throw new InvalidArgumentException("Patient does not exist");
        }
        return $pid;
    }

    private function resolveEncounterId(mixed $encounterUuid): ?int
    {
        if (!is_string($encounterUuid) || $encounterUuid === '') {
            return null;
        }
        $encounterRecords = ProcessingResult::extractDataArray((new EncounterService())->getEncounter($encounterUuid));
        if ($encounterRecords === []) {
            throw new InvalidArgumentException("Encounter does not exist");
        }
        $eid = self::toInt($encounterRecords[0]['eid'] ?? null);
        if ($eid === null) {
            throw new InvalidArgumentException("Encounter does not exist");
        }
        return $eid;
    }

    /**
     * The response row keeps its own copy of the questionnaire content, so the repository is
     * only consulted on create.
     */
    private function fetchQuestionnaireContent(mixed $questionnaireId, mixed $requestedVersion = null): string
    {
        if (!is_string($questionnaireId) || $questionnaireId === '') {
            throw new InvalidArgumentException("Questionnaire does not exist");
        }
        $questionnaireService = new QuestionnaireService();
        $tokenSearchValue = new TokenSearchField('uuid', [$questionnaireId], true);
        $questionnaireRecords = ProcessingResult::extractDataArray($questionnaireService->search(['uuid' => $tokenSearchValue]));
        if ($questionnaireRecords === []) {
            throw new InvalidArgumentException("Questionnaire does not exist");
        }
        // A canonical that pins a version has to match the revision actually stored. The
        // repository has no history to fall back on -- saveQuestionnaireResource() overwrites
        // the row and increments `version` -- so the alternative to rejecting is silently
        // answering a different questionnaire than the client was shown.
        if (is_string($requestedVersion) && $requestedVersion !== '') {
            $storedVersion = FhirPayloadReader::getString(
                FhirPayloadReader::get($questionnaireRecords, 0),
                'version'
            );
            if ($storedVersion !== $requestedVersion) {
                throw new InvalidArgumentException(
                    'QuestionnaireResponse.questionnaire pins version ' . $requestedVersion
                    . ' but the stored Questionnaire is version ' . ($storedVersion ?? 'unknown')
                    . '; historical versions are not retained'
                );
            }
        }
        $questionnaire = $questionnaireRecords[0]['questionnaire'] ?? null;
        if (!is_string($questionnaire)) {
            throw new InvalidArgumentException("Questionnaire does not exist");
        }
        return $questionnaire;
    }
}
