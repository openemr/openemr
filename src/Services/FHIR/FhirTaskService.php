<?php

/**
 * The FHIR Task resource over the task table (#14521; D9 and C7 of #13266):
 * read and search, and create and update. A write keeps to FhirTaskWriteRules:
 * the patient a task is for doesn't change, focus, owner and requester point
 * at the resource types the workflows use, a parent task belongs to the same
 * patient, and a task doesn't leave a final status. meta.versionId counts the
 * updates, so an update can ask that nobody changed the task since it was read
 * (If-Match).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
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

    /** The version an update must find (If-Match), or null for any. */
    private ?int $expectedVersion = null;

    private bool $versionConflict = false;

    /** How far up a parent chain the loop check walks; deeper chains aren't a workflow anyone builds. */
    private const MAX_PARENT_DEPTH = 100;

    private function getTaskService(): TaskService
    {
        return $this->taskService ??= new TaskService();
    }

    /**
     * Puts another TaskService in front of the task table: a test uses it to make the conditional
     * UPDATE lose a race.
     */
    public function setTaskService(TaskService $taskService): void
    {
        $this->taskService = $taskService;
    }

    /**
     * @return array<string, FhirSearchParameterDefinition>
     */
    protected function loadSearchParameters(): array
    {
        return [
            '_id' => new FhirSearchParameterDefinition('_id', SearchFieldType::TOKEN, [new ServiceField('uuid', ServiceField::TYPE_UUID)]),
            'patient' => $this->getPatientContextSearchField(),
            // R4's subject is Task.for, which here is always the patient.
            'subject' => new FhirSearchParameterDefinition('subject', SearchFieldType::REFERENCE, [new ServiceField('puuid', ServiceField::TYPE_UUID)]),
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
        $version = $dataRecord['version'] ?? null;
        $task->setMeta(UtilsService::createFhirMeta(is_numeric($version) ? (string)(int)$version : '1', $lastModified));

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
     * The version the next update must find, from If-Match; null takes any.
     */
    public function expectVersion(?int $version): void
    {
        $this->expectedVersion = $version;
    }

    /**
     * Whether the last update was refused because the task wasn't at the version it expected:
     * the version If-Match named, or the one read before the conditional UPDATE. The controller
     * answers that with 412, not 400.
     */
    public function hadVersionConflict(): bool
    {
        return $this->versionConflict;
    }

    /**
     * A Task body as task fields, the patient's uuid, and what's wrong with it.
     *
     * @return array{fields: array<string, string|null>, puuid: ?string, errors: array<string, string>}
     */
    public function parseFhirResource(FHIRDomainResource $fhirResource): array
    {
        if (!($fhirResource instanceof FHIRTask)) {
            throw new \InvalidArgumentException('Expected FHIRTask resource, got ' . $fhirResource::class);
        }
        // As plain JSON arrays: the model's own serialization nests element objects.
        $json = json_decode((string)json_encode($fhirResource), true);
        return FhirTaskWriteRules::read(is_array($json) ? $json : []);
    }

    /**
     * @param mixed $openEmrRecord what parseFhirResource() returned
     */
    protected function insertOpenEMRRecord($openEmrRecord): ProcessingResult
    {
        [$fields, $puuid, $errors] = self::unpack($openEmrRecord);
        $pid = $puuid === null ? null : self::patientId($puuid);
        if ($puuid !== null && $pid === null) {
            $errors['for'] = 'Patient reference could not be resolved: ' . $puuid;
        }
        if ($pid !== null && ($problem = $this->parentProblem($fields['part_of_uuid'] ?? null, $pid, null)) !== null) {
            $errors['partOf'] = $problem;
        }
        if ($errors !== [] || $pid === null) {
            return self::invalid($errors);
        }
        $fields['authored_on'] ??= date('Y-m-d H:i:s');
        $uuid = $this->getTaskService()->insert($fields, $pid, $this->userId());
        // insert() hands this result to the response as it is, so it carries the FHIR resource.
        $result = new ProcessingResult();
        $record = $this->getTaskService()->getOne($uuid);
        if ($record !== null) {
            $result->addData($this->parseOpenEMRRecord($record));
        }
        return $result;
    }

    /**
     * @param string $fhirResourceId
     * @param array<mixed> $updatedOpenEMRRecord what parseFhirResource() returned, with the uuid
     */
    protected function updateOpenEMRRecord($fhirResourceId, $updatedOpenEMRRecord): ProcessingResult
    {
        [$fields, $puuid, $errors] = self::unpack($updatedOpenEMRRecord);
        $this->versionConflict = false;
        // authoredOn is when the task was made: an update keeps the stored one, so a bad one in the body doesn't matter.
        unset($errors['authoredOn']);
        $stored = UuidRegistry::isValidStringUUID($fhirResourceId) ? $this->getTaskService()->getForWrite($fhirResourceId) : null;
        if ($stored === null) {
            // Nothing to update: handleFhirProcessingResult() answers 404 for an empty result.
            return new ProcessingResult();
        }
        // The patient a task is for doesn't change: the body has to name the one it has, so a
        // task id can't be used to move a task, or write one, under another patient.
        $pid = $puuid === null ? null : self::patientId($puuid);
        if ($puuid !== null && $pid !== $stored['patient_id']) {
            $errors['for'] = "Task.for can't change: it must name the task's patient";
        }
        if (isset($fields['status']) && ($problem = FhirTaskWriteRules::transitionProblem($stored['status'], $fields['status'])) !== null) {
            $errors['status'] = $problem;
        }
        if ($stored['patient_id'] !== null && ($problem = $this->parentProblem($fields['part_of_uuid'] ?? null, $stored['patient_id'], $fhirResourceId)) !== null) {
            $errors['partOf'] = $problem;
        }
        if ($errors !== []) {
            return self::invalid($errors);
        }
        // Only once the body is allowed for this task's patient does the version come into it, so a
        // 412 never tells a caller about a task it couldn't write.
        if ($this->expectedVersion !== null && $this->expectedVersion !== $stored['version']) {
            $this->versionConflict = true;
            return self::invalid(['meta.versionId' => "The task is at version {$stored['version']}, not {$this->expectedVersion}: read it again before updating"]);
        }
        unset($fields['authored_on']);
        if (!$this->getTaskService()->update($fhirResourceId, $fields, $this->userId(), $this->expectedVersion ?? $stored['version'])) {
            $this->versionConflict = true;
            return self::invalid(['meta.versionId' => 'The task changed while it was being updated: read it again before updating']);
        }
        // update() turns the row into the FHIR resource itself.
        $result = new ProcessingResult();
        $record = $this->getTaskService()->getOne($fhirResourceId);
        if ($record !== null) {
            $result->addData($record);
        }
        return $result;
    }

    /**
     * Who made or last changed the task, as the Provenance for its author: the requester when
     * the task names one.
     *
     * @param mixed $dataRecord
     * @param bool $encode
     */
    public function createProvenanceResource($dataRecord, $encode = false): FHIRProvenance|string|null
    {
        if (!($dataRecord instanceof FHIRTask)) {
            throw new \BadMethodCallException('Data record should be correct instance class');
        }
        $provenance = (new FhirProvenanceService())->createProvenanceForDomainResource($dataRecord, $dataRecord->getRequester());
        if (!($provenance instanceof FHIRProvenance)) {
            return null;
        }
        if ($encode) {
            $json = json_encode($provenance);
            return is_string($json) ? $json : null;
        }
        return $provenance;
    }

    /**
     * What's wrong with a parent task, or null: it has to exist, be for the same patient, and not
     * lead back to the task itself through its own parents (A part of B, then B part of A).
     */
    private function parentProblem(?string $parentUuid, int $patientId, ?string $selfUuid): ?string
    {
        if ($parentUuid === null) {
            return null;
        }
        if ($selfUuid !== null && strcasecmp($parentUuid, $selfUuid) === 0) {
            return "A task can't be part of itself";
        }
        $parent = $this->getTaskService()->getForWrite($parentUuid);
        if ($parent === null) {
            return 'Task.partOf names no task: ' . $parentUuid;
        }
        if ($parent['patient_id'] !== $patientId) {
            return 'Task.partOf must name a task for the same patient';
        }
        // A new task has no children yet, so only an update can close a loop. The walk also stops
        // at a loop already stored, and after MAX_PARENT_DEPTH steps.
        if ($selfUuid === null) {
            return null;
        }
        $seen = [strtolower($parentUuid) => true];
        $next = $parent['part_of_uuid'];
        for ($depth = 0; $next !== null && $depth < self::MAX_PARENT_DEPTH; $depth++) {
            if (strcasecmp($next, $selfUuid) === 0) {
                return 'Task.partOf would make a loop: ' . $parentUuid . ' is already part of this task';
            }
            if (isset($seen[strtolower($next)])) {
                return null;
            }
            $seen[strtolower($next)] = true;
            $next = $this->getTaskService()->getForWrite($next)['part_of_uuid'] ?? null;
        }
        return null;
    }

    /**
     * @return array{0: array<string, string|null>, 1: ?string, 2: array<string, string>}
     */
    private static function unpack(mixed $record): array
    {
        $fields = [];
        $errors = [];
        if (is_array($record)) {
            foreach (is_array($record['fields'] ?? null) ? $record['fields'] : [] as $column => $value) {
                if (is_string($column) && ($value === null || is_string($value))) {
                    $fields[$column] = $value;
                }
            }
            foreach (is_array($record['errors'] ?? null) ? $record['errors'] : [] as $element => $message) {
                if (is_string($element) && is_string($message)) {
                    $errors[$element] = $message;
                }
            }
        }
        $puuid = is_array($record) && is_string($record['puuid'] ?? null) ? $record['puuid'] : null;
        return [$fields, $puuid, $errors];
    }

    /** The pid of the patient with this uuid, or null. */
    private static function patientId(string $puuid): ?int
    {
        $pid = QueryUtils::fetchSingleValue('SELECT pid FROM patient_data WHERE uuid = ?', 'pid', [UuidRegistry::uuidToBytes($puuid)]);
        return is_numeric($pid) ? (int)$pid : null;
    }

    private function userId(): ?int
    {
        $id = $this->getSession()?->get('authUserID');
        return is_numeric($id) ? (int)$id : null;
    }

    /**
     * @param array<string, string> $errors
     */
    private static function invalid(array $errors): ProcessingResult
    {
        $result = new ProcessingResult();
        $result->setValidationMessages($errors);
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
