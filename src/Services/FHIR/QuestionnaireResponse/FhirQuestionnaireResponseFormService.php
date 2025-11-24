<?php

/*
 * FhirQuestionnaireResponseFormService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\QuestionnaireResponse;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\DomainModels\OpenEMRFhirQuestionnaireResponse;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRElement\FHIRQuestionnaireResponseStatus;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\EncounterService;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IResourceCreatableService;
use OpenEMR\Services\FHIR\IResourceReadableService;
use OpenEMR\Services\FHIR\IResourceSearchableService;
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
use InvalidArgumentException;
use JsonException;
use DateTime;
use DateTimeInterface;
use BadMethodCallException;
use Exception;

class FhirQuestionnaireResponseFormService extends FhirServiceBase implements IResourceReadableService, IResourceSearchableService, IResourceCreatableService
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
     * @param FHIRDomainResource $fhirResource
     * @return array
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        if (!($fhirResource instanceof OpenEMRFhirQuestionnaireResponse)) {
            throw new InvalidArgumentException("resource must be of type " . OpenEMRFhirQuestionnaireResponse::class);
        }

        $parsedResource = [];
        if (!empty($fhirResource->getId())) {
            $parsedResource['response_id'] = $fhirResource->getId()->getValue();
            $parsedResource['uuid'] = UuidRegistry::uuidToBytes($parsedResource['response_id']);
        }
        // required value so should be here
        if (!empty($fhirResource->getQuestionnaire())) {
            $parsedUrl = UtilsService::parseCanonicalUrl($fhirResource->getQuestionnaire());
            if ($parsedUrl['localResource']) {
                $parsedResource['questionnaire_id'] = $parsedUrl['uuid'];
            } else {
                throw new InvalidArgumentException("Questionnaire does not exist on local server. Cannot save QuestionnaireResponse.");
            }
        }
        // our subjects at this point should really only be the patient...
        if (!empty($fhirResource->getSubject())) {
            $parsedReference = UtilsService::parseReference($fhirResource->getSubject());
            if ($parsedReference['localResource']) {
                if (!empty($parsedReference['type']) == 'Patient') {
                    $parsedResource['puuid'] = $parsedReference['uuid'];
                }
                // on else close handle something different here... if we are working with organization or anything
            } else {
                throw new InvalidArgumentException("Subject does not exist on local server. Cannot save QuestionnaireResponse.");
            }
        }
        if (!empty($fhirResource->getEncounter())) {
            $parsedReference = UtilsService::parseReference($fhirResource->getEncounter());
            if ($parsedReference['localResource']) {
                $parsedReference['encounter_uuid'] = $parsedResource['uuid'];
            } else {
                throw new InvalidArgumentException("Subject does not exist on local server. Cannot save QuestionnaireResponse.");
            }
        }
        if (!empty($fhirResource->getSource())) {
            $parsedReference = UtilsService::parseReference($fhirResource->getSource());
            if ($parsedReference['localResource']) {
                if (!empty($parsedReference['type']) == 'Practitioner') {
                    $parsedResource['creator_user_uuid'] = $parsedReference['uuid'];
                }
                // on else clause handle something different here... if we are working with organization or anything
            } else {
                throw new InvalidArgumentException("Subject does not exist on local server. Cannot save QuestionnaireResponse.");
            }
        }
        $status = $fhirResource->getStatus();
        if ($status == 'in-progress') {
            $status = 'incomplete';
        }
        $parsedResource['status'] = $status;

        $parsedResource['questionnaire_response'] = json_encode($fhirResource);
        if (!empty($fhirResource->getMeta())) {
            if (!empty($fhirResource->getMeta()->getId())) {
                $parsedResource['version'] = $fhirResource->getMeta()->getVersionId()->getValue() ?? 1;
            }
        }
        return $parsedResource;
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
            (new SystemLogger())->errorLogCaller(
                "Unable to parse questionnaire json",
                ['uuid' => $dataRecord['uuid'] ?? '', 'message' => $exception->getMessage()
                ,
                'trace' => $exception->getTraceAsString()]
            );
        }
        $fhirResource = new OpenEMRFhirQuestionnaireResponse($innerData);

        $meta = new FHIRMeta();
        $meta->setVersionId($dataRecord['version'] ?? '1');
        // TODO: @adunsulag use modified_date
        $meta->setLastUpdated(new FHIRInstant(UtilsService::getDateFormattedAsUTC()));
        $fhirResource->setMeta($meta);

        $id = new FhirId();
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
        } else if (is_string($fhirResource->getStatus())) {
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
     * @return FHIRProvenance|string
     */
    public function createProvenanceResource($dataRecord, $encode = false): FHIRProvenance|string
    {
        // we don't return any provenance authorship for this custom resource
        // if we did return it, we would fill out the following record
//        $provenance = new FHIRProvenance();
        if (!($dataRecord instanceof FHIRQuestionnaire)) {
            throw new BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        // provenance will just be the organization as we don't keep track of the user at the individual FHIR resource level
        // note we do track this internally in OpenEMR but FHIR R4 doesn't expose this as far as I can tell.
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }

    public function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        /**
         * $response,
        $pid,
        $encounter = null,
        $qr_id = null,
        $qr_record_id = null,
        $q = null,
        $q_id = null,
        $form_response = null,
        $add_report = false,
        $scores = []
         */
        $patientService = new PatientService();
        $patientRecords = ProcessingResult::extractDataArray($patientService->getOne($openEmrRecord['puuid']));
        if (empty($patientRecords)) {
            throw new InvalidArgumentException("Patient does not exist");
        }
        $patientId = $patientRecords[0]['pid'];
        $encounterId = null;
        if (!empty($openEmrRecord['encounter_uuid'])) {
            $encounterService = new EncounterService();
            $encounterRecords = ProcessingResult::extractDataArray($encounterService->getEncounter($openEmrRecord['encounter_uuid']));
            if (empty($encounterRecords)) {
                throw new InvalidArgumentException("Encounter does not exist");
            }
            $encounterId = $encounterRecords[0]['eid'];
        }
        // note https://build.fhir.org/http.html#create specification states that an id SHALL be ignored for our create
        // operation so we ignore any record ids here.
        $qr_id = null;
        $qr_record_id = null; // what is this even used for?
        $questionnaireService = new QuestionnaireService();
        $tokenSearchValue = new TokenSearchField('uuid', [$openEmrRecord['questionnaire_id']], true);
        $questionnaireRecords = ProcessingResult::extractDataArray($questionnaireService->search(['uuid' => $tokenSearchValue]));
        if (empty($questionnaireRecords)) {
            throw new InvalidArgumentException("Questionnaire does not exist");
        }
        $questionnaire = $questionnaireRecords[0];

        $form_response = null; // not sure why we are saving this off...

        try {
            $saved = $this->service->saveQuestionnaireResponse(
                $openEmrRecord['questionnaire_response'],
                $patientId,
                $encounterId,
                $qr_id,
                $qr_record_id,
                $questionnaire['questionnaire'],
                $openEmrRecord['questionnaire_id'],
                $form_response,
                true // I think we want to always generate a narrative here.
            );
            // return the newly created resource id
            $processingResult = new ProcessingResult();
            $processingResult->addData($saved['response_id']);
            return $processingResult;
        } catch (Exception $exception) {
            (new SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
            $processingResult = new ProcessingResult();
            $processingResult->setInternalErrors("Server Error in creating QuestionnaireResponse resource");
            return $processingResult;
        }
    }
}
