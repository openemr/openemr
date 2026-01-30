<?php

/*
 * QuestionnaireFormFHIRResourceService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Questionnaire;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\IResourceReadableService;
use OpenEMR\Services\FHIR\IResourceSearchableService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;
use JsonException;
use BadMethodCallException;

class FhirQuestionnaireFormService extends FhirServiceBase implements IResourceReadableService, IResourceSearchableService
{
    /**
     * If you'd prefer to keep out the empty methods that are doing nothing uncomment the following helper trait
     */
    use FhirServiceBaseEmptyTrait;

    private ?QuestionnaireService $service;


    public function __construct($fhirApiURL = null)
    {
        parent::__construct($fhirApiURL);
        $this->service = new QuestionnaireService();
    }

    public function getQuestionnaireService(): QuestionnaireService
    {
        if (!isset($this->service)) {
            $this->service = new QuestionnaireService();
        }
        return $this->service;
    }

    public function setQuestionnaireService(QuestionnaireService $service): void
    {
        $this->service = $service;
    }

    /**
     * @param $code
     * @return bool
     */
    public function supportsCode($code): bool
    {
        // we support pretty much any LOINC code
        return true;
    }

    private function parseQuestionnaireItems($dataItem)
    {
        $objItems = [];
        if (!empty($dataItem['item'])) {
            foreach ($dataItem['item'] as $item) {
                if (!empty($item['item'])) {
                    $item['item'] = $this->parseQuestionnaireItems($item);
                }
                $item = new FHIRQuestionnaireItem($item);
                $objItems[] = $item;
            }
        }
        return $objItems;
    }

    /**
     * @param array $dataRecord
     * @param bool $encode
     * @return FHIRQuestionnaire
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRQuestionnaire
    {
        try {
            // parse the json data in dataRecord questionnaire
            $innerData = json_decode((string) $dataRecord['questionnaire'], true, 512, JSON_THROW_ON_ERROR);
            // we have to handle the item properties as Questionnaire only adds data arrays instead of
            // actual object values
            if (!empty($innerData['item'])) {
                $innerData['item'] = $this->parseQuestionnaireItems($innerData);
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
        $fhirResource = new FHIRQuestionnaire($innerData);

        $meta = new FHIRMeta();
        $meta->setVersionId($dataRecord['version'] ?? '1');
        // TODO: @adunsulag use modified_date
        $meta->setLastUpdated(new FHIRInstant(UtilsService::getDateFormattedAsUTC()));
        $fhirResource->setMeta($meta);

        // some of our saved OpenEMR db records have an invalid source_url and so we updated this in the database.
        if (!empty($dataRecord['source_url'])) {
            $fhirResource->setUrl($dataRecord['source_url']);
        }

        $id = new FhirId();
        $id->setValue($dataRecord['uuid']);
        $fhirResource->setId($id);

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
        return  [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)])
            // note what we store in the database is the Title of the questionnaire even thought its called 'name'.  The computable name is stored only in the json
            // TODO: @adunsulag look at adding a database field for the computable name and store it in the database
            ,'title' => new FhirSearchParameterDefinition('title', SearchFieldType::STRING, [new ServiceField('name', ServiceField::TYPE_STRING)])
            ,'questionnaire-code' => new FhirSearchParameterDefinition('questionnaire-code', SearchFieldType::TOKEN, [new ServiceField('code', ServiceField::TYPE_STRING)])
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
}
