<?php

/**
 * The FHIR Task resource, read and search, over the task table (#14521; D9
 * and C7 of #13266). Create and update come in a later change.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRProvenance;
use OpenEMR\FHIR\R4\FHIRDomainResource\FHIRTask;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRTaskIntent;
use OpenEMR\FHIR\R4\FHIRElement\FHIRTaskStatus;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\TaskService;
use OpenEMR\Validators\ProcessingResult;

class FhirTaskService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    use PatientSearchTrait;

    public const TASK_CODE_SYSTEM = 'http://hl7.org/fhir/CodeSystem/task-code';

    private ?TaskService $taskService = null;

    private function getTaskService(): TaskService
    {
        return $this->taskService ??= new TaskService();
    }

    /**
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'patient' => $this->getPatientContextSearchField(),
            'status' => new FhirSearchParameterDefinition('status', SearchFieldType::TOKEN, ['status']),
            'code' => new FhirSearchParameterDefinition('code', SearchFieldType::TOKEN, ['code']),
            'focus' => self::referenceSearch('focus', 'focus_uuid'),
            'owner' => self::referenceSearch('owner', 'owner_uuid'),
            'requester' => self::referenceSearch('requester', 'requester_uuid'),
            'part-of' => self::referenceSearch('part-of', 'part_of_uuid'),
            'authored-on' => new FhirSearchParameterDefinition('authored-on', SearchFieldType::DATETIME, ['authored_on']),
            '_lastUpdated' => $this->getLastModifiedSearchField(),
        ];
    }

    private static function referenceSearch(string $name, string $column): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition($name, SearchFieldType::REFERENCE, [new ServiceField($column, ServiceField::TYPE_UUID)]);
    }

    public function getLastModifiedSearchField(): FhirSearchParameterDefinition
    {
        return new FhirSearchParameterDefinition('_lastUpdated', SearchFieldType::DATETIME, ['last_modified']);
    }

    /**
     * @param array<mixed> $openEMRSearchParameters
     */
    protected function searchForOpenEMRRecords($openEMRSearchParameters): ProcessingResult
    {
        return $this->getTaskService()->search(self::searchFields($openEMRSearchParameters));
    }

    /**
     * With _count, _offset or both, the page is read in the query rather than trimmed afterwards.
     *
     * @param array<mixed> $openEMRSearchParameters
     */
    protected function searchForOpenEMRRecordsWithConfig(array $openEMRSearchParameters, SearchQueryConfig $searchConfig): ProcessingResult
    {
        return $this->getTaskService()->search(self::searchFields($openEMRSearchParameters), true, $searchConfig);
    }

    /**
     * @param array<mixed> $openEMRSearchParameters
     * @return array<string, ISearchField>
     */
    private static function searchFields(array $openEMRSearchParameters): array
    {
        $search = [];
        foreach ($openEMRSearchParameters as $name => $field) {
            if (is_string($name) && $field instanceof ISearchField) {
                $search[$name] = $field;
            }
        }
        return $search;
    }

    /**
     * A row from TaskService::search() as a FHIR Task. Always the resource
     * itself: no caller asks for it encoded.
     *
     * @param array<mixed> $dataRecord
     * @param bool $encode
     */
    public function parseOpenEMRRecord($dataRecord = [], $encode = false): FHIRTask
    {
        $task = new FHIRTask();
        $lastModified = self::text($dataRecord, 'last_modified');
        $lastModified = $lastModified === null ? UtilsService::getDateFormattedAsUTC() : self::utc($lastModified);
        $task->setMeta(UtilsService::createFhirMeta('1', $lastModified));

        $id = new FHIRId();
        $id->setValue(self::text($dataRecord, 'uuid') ?? '');
        $task->setId($id);

        $task->setStatus(new FHIRTaskStatus(['value' => self::text($dataRecord, 'status') ?? 'requested']));
        $task->setIntent(new FHIRTaskIntent(['value' => self::text($dataRecord, 'intent') ?? 'order']));
        $statusReason = self::text($dataRecord, 'status_reason');
        if ($statusReason !== null) {
            $task->setStatusReason(self::textConcept($statusReason));
        }
        $businessStatus = self::text($dataRecord, 'business_status');
        if ($businessStatus !== null) {
            $task->setBusinessStatus(self::textConcept($businessStatus));
        }
        $priority = self::text($dataRecord, 'priority');
        if ($priority !== null) {
            $task->setPriority(new FHIRRequestPriority(['value' => $priority]));
        }
        $code = self::text($dataRecord, 'code');
        if ($code !== null) {
            $concept = new FHIRCodeableConcept();
            $concept->addCoding(UtilsService::createCoding($code, $code, self::TASK_CODE_SYSTEM));
            $task->setCode($concept);
        }
        $description = self::text($dataRecord, 'description');
        if ($description !== null) {
            $task->setDescription(new FHIRString($description));
        }

        $puuid = self::text($dataRecord, 'puuid');
        if ($puuid !== null) {
            $task->setFor(UtilsService::createRelativeReference('Patient', $puuid));
        }
        $focus = self::reference($dataRecord, 'focus');
        if ($focus !== null) {
            $task->setFocus($focus);
        }
        $requester = self::reference($dataRecord, 'requester');
        if ($requester !== null) {
            $task->setRequester($requester);
        }
        $owner = self::reference($dataRecord, 'owner');
        if ($owner !== null) {
            $task->setOwner($owner);
        }
        $partOf = self::text($dataRecord, 'part_of_uuid');
        if ($partOf !== null) {
            $task->addPartOf(UtilsService::createRelativeReference('Task', $partOf));
        }

        $authoredOn = self::text($dataRecord, 'authored_on');
        if ($authoredOn !== null) {
            $task->setAuthoredOn(new FHIRDateTime(self::utc($authoredOn)));
        }
        $task->setLastModified(new FHIRDateTime($lastModified));
        return $task;
    }

    /**
     * Task is read-only until its create and update change: nothing reaches
     * the methods below, because no write route exists.
     *
     * @return array<string, mixed>
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
     * @param string $fhirResourceId
     * @param array<mixed> $updatedOpenEMRRecord
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        return self::readOnly();
    }

    /**
     * @param mixed $dataRecord
     * @param bool $encode
     */
    public function createProvenanceResource($dataRecord, $encode = false): ?FHIRProvenance
    {
        return null;
    }

    private static function readOnly(): ProcessingResult
    {
        $result = new ProcessingResult();
        $result->setValidationMessages(['Task' => 'Task is read-only']);
        return $result;
    }

    /**
     * A non-empty string column, or null.
     *
     * @param array<mixed> $record
     */
    private static function text(array $record, string $column): ?string
    {
        $value = $record[$column] ?? null;
        if (is_int($value)) {
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

    /**
     * A reference stored as a resource type and a uuid (focus, owner, requester).
     *
     * @param array<mixed> $record
     */
    private static function reference(array $record, string $prefix): ?FHIRReference
    {
        $type = self::text($record, $prefix . '_type');
        $uuid = self::text($record, $prefix . '_uuid');
        return $type === null || $uuid === null ? null : UtilsService::createRelativeReference($type, $uuid);
    }

    private static function textConcept(string $text): FHIRCodeableConcept
    {
        $concept = new FHIRCodeableConcept();
        $concept->setText(new FHIRString($text));
        return $concept;
    }
}
