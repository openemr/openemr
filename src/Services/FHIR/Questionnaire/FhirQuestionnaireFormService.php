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
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRQuestionnaire;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRQuestionnaire\FHIRQuestionnaireItem;
use OpenEMR\Services\FHIR\FhirPayloadReader;
use OpenEMR\Services\FHIR\FhirProvenanceService;
use OpenEMR\Services\FHIR\FhirServiceBase;
use OpenEMR\Services\FHIR\INonPatientCompartmentResourceService;
use OpenEMR\Services\FHIR\IResourceCreatableService;
use OpenEMR\Services\FHIR\IResourceReadableService;
use OpenEMR\Services\FHIR\IResourceSearchableService;
use OpenEMR\Services\FHIR\IResourceUpdateableService;
use OpenEMR\Services\FHIR\Traits\FhirServiceBaseEmptyTrait;
use OpenEMR\Services\FHIR\UtilsService;
use OpenEMR\Services\QuestionnaireService;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Validators\ProcessingResult;

class FhirQuestionnaireFormService extends FhirServiceBase implements
    IResourceReadableService,
    IResourceSearchableService,
    IResourceCreatableService,
    IResourceUpdateableService,
    INonPatientCompartmentResourceService
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
        $this->service ??= new QuestionnaireService();
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

    /**
     * Repair a raw questionnaire item so the strict generated model constructor
     * accepts it: decode double-encoded array fields where possible, drop them
     * with a warning where not. Dropping a field (e.g. enableWhen) degrades to a
     * less conditional form rather than failing the whole API response.
     * Shared logic lives in QuestionnaireItemNormalizer; the import path uses
     * the same class in strict mode so new data can't need this tolerance.
     *
     * @param array<mixed> $item
     * @return array<mixed>
     */
    private static function normalizeQuestionnaireItem(array $item): array
    {
        [$item, , $unrepairable] = QuestionnaireItemNormalizer::normalizeItem($item);
        foreach ($unrepairable as $field) {
            ServiceContainer::getLogger()->warning(
                "Dropping malformed questionnaire item field",
                ['field' => $field, 'linkId' => $item['linkId'] ?? '', 'type' => gettype($item[$field])]
            );
            unset($item[$field]);
        }
        return $item;
    }

    /**
     * @param array<mixed> $dataItem
     * @return list<FHIRQuestionnaireItem>
     */
    private function parseQuestionnaireItems(array $dataItem): array
    {
        $objItems = [];
        if (!empty($dataItem['item'])) {
            foreach ($dataItem['item'] as $item) {
                if (!is_array($item)) {
                    ServiceContainer::getLogger()->warning("Dropping malformed questionnaire item", ['type' => gettype($item)]);
                    continue;
                }
                $item = self::normalizeQuestionnaireItem($item);
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
            if (!is_array($innerData)) {
                throw new \InvalidArgumentException("Stored questionnaire json is not an object");
            }
            // we have to handle the item properties as Questionnaire only adds data arrays instead of
            // actual object values
            if (isset($innerData['item']) && is_array($innerData['item']) && $innerData['item'] !== []) {
                $innerData['item'] = $this->parseQuestionnaireItems($innerData);
            }
            $fhirResource = new FHIRQuestionnaire($innerData);
        } catch (JsonException | \InvalidArgumentException $exception) {
            // log the error and move on with a bare resource; a single malformed
            // stored questionnaire must not fail the whole collection response.
            // InvalidArgumentException comes from the strict generated model
            // constructors when stored data has an unexpected shape.
            ServiceContainer::getLogger()->error(
                "Unable to parse questionnaire json",
                ['exception' => $exception, 'uuid' => $dataRecord['uuid'] ?? '']
            );
            $fhirResource = new FHIRQuestionnaire();
        }

        $meta = new FHIRMeta();
        $meta->setVersionId($dataRecord['version'] ?? '1');
        $meta->setLastUpdated(new FHIRInstant(UtilsService::getDateFormattedAsUTC()));
        $fhirResource->setMeta($meta);

        if (!empty($dataRecord['source_url'])) {
            $fhirResource->setUrl($dataRecord['source_url']);
        }

        $id = new FHIRId();
        $id->setValue($dataRecord['uuid']);
        $fhirResource->setId($id);

        return $fhirResource;
    }


    /**
     * Maps a FHIR Questionnaire onto the columns QuestionnaireService::saveQuestionnaireResource()
     * expects. The questionnaire itself is stored as the serialized resource in
     * `questionnaire_repository`.`questionnaire`, so the parsed record carries the whole
     * resource rather than a column-per-element breakdown.
     *
     * @param FHIRDomainResource $fhirResource
     * @return array<string, mixed>
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        if (!($fhirResource instanceof FHIRQuestionnaire)) {
            throw new \InvalidArgumentException(
                'Expected FHIRQuestionnaire resource, got ' . $fhirResource::class
            );
        }

        // The write controller hydrates the resource with `new FHIRQuestionnaire($json)` and the
        // R4 model keeps its top-level members as the raw decoded values, so the payload is read
        // back out of the resource rather than off the element getters.
        try {
            $json = json_decode(json_encode($fhirResource, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \InvalidArgumentException('Questionnaire could not be serialized', 0, $exception);
        }
        if (!is_array($json)) {
            throw new \InvalidArgumentException('Questionnaire could not be serialized');
        }

        // FHIR R4 makes Questionnaire.status 1..1, and `questionnaire_repository`.`status`
        // is what the read side re-emits, so a resource without one cannot round-trip.
        $status = FhirPayloadReader::getString($json, 'status');
        if ($status === null) {
            throw new \InvalidArgumentException('Questionnaire.status is required');
        }

        // The repository keys questionnaires by title (stored in `name`), and the read side
        // searches on it, so a questionnaire with neither title nor name is unreachable.
        $title = FhirPayloadReader::getString($json, 'title') ?? FhirPayloadReader::getString($json, 'name');
        if ($title === null || trim($title) === '') {
            throw new \InvalidArgumentException('Questionnaire.title (or name) is required');
        }

        // Item shapes are normalized here rather than inside the repository save so that a
        // malformed item is reported as a 400 instead of escaping the service layer as a bare
        // Exception. The pass is idempotent, so the save's own strict pass then finds nothing.
        [$json] = QuestionnaireItemNormalizer::normalizeQuestionnaire($json, true);

        return [
            'questionnaire' => $json,
            'name' => trim($title),
            'status' => $status,
        ];
    }

    /**
     * @param mixed $openEmrRecord
     */
    public function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        if (!is_array($openEmrRecord)) {
            throw new \InvalidArgumentException('Expected a parsed OpenEMR Questionnaire record array');
        }
        $result = new ProcessingResult();
        $name = $this->parsedTitle($openEmrRecord);

        // saveQuestionnaireResource() resolves an existing row by title, so a create carrying a
        // title already in the repository would overwrite that questionnaire instead of adding
        // one. Titles are the repository's key, so the collision is reported rather than merged.
        if ($this->getQuestionnaireService()->getQuestionnaireIdAndVersion($name) !== []) {
            $result->setValidationMessages(
                ['title' => 'A Questionnaire with this title already exists; update it with PUT instead']
            );
            return $result;
        }

        // https://build.fhir.org/http.html#create -- a client supplied id SHALL be ignored on
        // create; the repository assigns the id and the canonical url it publishes.
        $questionnaire = $this->parsedQuestionnaire($openEmrRecord);
        unset($questionnaire['id'], $questionnaire['url']);

        $rowId = $this->save($questionnaire, $name, null);
        $stored = $this->getQuestionnaireService()->fetchQuestionnaireById($rowId);
        if ($stored === []) {
            $result->addInternalError('Questionnaire could not be read back after save');
            return $result;
        }

        // the create response carries the ids of the new record, matching the other FHIR writes
        $result->addData(['id' => $rowId, 'uuid' => $stored['uuid'] ?? null]);
        return $result;
    }

    /**
     * @param array<array-key, mixed> $updatedOpenEMRRecord
     */
    public function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        $result = new ProcessingResult();
        if (!UuidRegistry::isValidStringUUID($fhirResourceId)) {
            $result->setValidationMessages(['_id' => 'invalid uuid format']);
            return $result;
        }

        $service = $this->getQuestionnaireService();
        $existing = $service->fetchQuestionnaireById(0, UuidRegistry::uuidToBytes($fhirResourceId));
        $recordId = $existing['id'] ?? null;
        if (!is_numeric($recordId)) {
            // an empty, error free result is what the REST layer turns into a 404
            return $result;
        }

        // The repository owns the resource id and the canonical url of a stored questionnaire;
        // neither is rebindable by an update, so both are pinned to the stored values.
        $questionnaire = $this->parsedQuestionnaire($updatedOpenEMRRecord);
        $questionnaire['id'] = $existing['questionnaire_id'] ?? null;
        $questionnaire['url'] = $existing['source_url'] ?? null;

        // saveQuestionnaireResource() bumps `version` when handed an existing row, so a PUT
        // updates the stored questionnaire in place rather than creating a second repository
        // row under the same title.
        $rowId = $this->save($questionnaire, $this->parsedTitle($updatedOpenEMRRecord), (int)$recordId);

        $stored = $service->fetchQuestionnaireById($rowId);
        if ($stored === []) {
            $result->addInternalError('Questionnaire could not be read back after save');
            return $result;
        }

        $result->addData($stored);
        return $result;
    }

    /**
     * @param array<array-key, mixed> $questionnaire
     */
    private function save(array $questionnaire, string $name, ?int $recordId): int
    {
        try {
            $savedId = $this->getQuestionnaireService()->saveQuestionnaireResource(
                $questionnaire,
                $name,
                $recordId,
                null,
                null,
                'Questionnaire'
            );
        } catch (\Throwable $exception) {
            // saveQuestionnaireResource() reports every failure as a bare Exception, which the
            // REST layer would otherwise let reach the global handler with its raw message.
            // Re-thrown rather than swallowed, so the failure still reaches the handler --
            // as a type the write controller recognises and answers with a generic 500.
            throw new \RuntimeException('Questionnaire could not be saved', 0, $exception);
        }

        // an update returns the row id it was handed; an insert returns the new row id
        $rowId = $recordId ?? (is_numeric($savedId) ? (int)$savedId : 0);
        if ($rowId <= 0) {
            throw new \RuntimeException('Questionnaire could not be saved');
        }
        return $rowId;
    }

    /**
     * @param array<array-key, mixed> $openEmrRecord
     * @return array<array-key, mixed>
     */
    private function parsedQuestionnaire(array $openEmrRecord): array
    {
        $questionnaire = $openEmrRecord['questionnaire'] ?? null;
        if (!is_array($questionnaire)) {
            throw new \InvalidArgumentException('Questionnaire payload is missing or malformed');
        }
        return $questionnaire;
    }

    /**
     * @param array<array-key, mixed> $openEmrRecord
     */
    private function parsedTitle(array $openEmrRecord): string
    {
        $name = $openEmrRecord['name'] ?? null;
        if (!is_string($name) || $name === '') {
            throw new \InvalidArgumentException('Questionnaire.title (or name) is required');
        }
        return $name;
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
                [new ServiceField('uuid', ServiceField::TYPE_UUID)]
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
    public function createProvenanceResource($dataRecord, $encode = false): FHIRProvenance|string|false
    {
        if (!($dataRecord instanceof FHIRQuestionnaire)) {
            throw new BadMethodCallException("Data record should be correct instance class");
        }
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
}
