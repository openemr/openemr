<?php

/*
 * QuestionnaireFormFHIRResourceService.php
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\FHIR\Questionnaire;

use BadMethodCallException;
use JsonException;
use OpenEMR\BC\ServiceContainer;
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
            $innerData = json_decode((string) $dataRecord['questionnaire'], true, 512, JSON_THROW_ON_ERROR);
            if (!empty($innerData['item'])) {
                $innerData['item'] = $this->parseQuestionnaireItems($innerData);
            }
        } catch (JsonException $exception) {
            $innerData = [];
            ServiceContainer::getLogger()->error(
                "Unable to parse questionnaire json",
                ['exception' => $exception, 'uuid' => $dataRecord['uuid'] ?? '']
            );
        }
        $fhirResource = new FHIRQuestionnaire($innerData);

        $meta = new FHIRMeta();
        $meta->setVersionId($dataRecord['version'] ?? '1');
        $meta->setLastUpdated(new FHIRInstant(UtilsService::getDateFormattedAsUTC()));
        $fhirResource->setMeta($meta);

        if (!empty($dataRecord['source_url'])) {
            $fhirResource->setUrl($dataRecord['source_url']);
        }

        $id = new FHIRId();
        $id->setValue((string)($dataRecord['id'] ?? ''));
        $fhirResource->setId($id);

        return $fhirResource;
    }

    /**
     * @return array
     */
    protected function loadSearchParameters(): array
    {
        return  [
            '_id' => new FhirSearchParameterDefinition(
                '_id',
                SearchFieldType::TOKEN,
                [new ServiceField('id', ServiceField::TYPE_NUMBER)]
            ),
            'title' => new FhirSearchParameterDefinition(
                'title',
                SearchFieldType::STRING,
                [new ServiceField('name', ServiceField::TYPE_STRING)]
            ),
            'questionnaire-code' => new FhirSearchParameterDefinition(
                'questionnaire-code',
                SearchFieldType::TOKEN,
                [new ServiceField('code', ServiceField::TYPE_STRING)]
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
     * @param FHIRDomainResource $dataRecord
     * @param bool $encode
     * @return FHIRProvenance|string
     */
    public function createProvenanceResource($dataRecord, $encode = false): FHIRProvenance|string
    {
        if (!($dataRecord instanceof FHIRQuestionnaire)) {
            throw new BadMethodCallException("Data record should be correct instance class");
        }
        $fhirProvenanceService = new FhirProvenanceService();
        $fhirProvenance = $fhirProvenanceService->createProvenanceForDomainResource($dataRecord);
        if ($encode) {
            return json_encode($fhirProvenance);
        } else {
            return $fhirProvenance;
        }
    }
}
