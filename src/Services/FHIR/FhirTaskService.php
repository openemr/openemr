<?php

/**
 * The FHIR Task resource over the task table (#14521; D9 and C7 of #13266):
 * read and search, and create and update. A write keeps to FhirTaskWriteRules:
 * the patient a task is for doesn't change, focus, owner and requester point
 * at the resource types the workflows use, a Patient owner or requester is the
 * task's own patient and a RelatedPerson owner theirs, a parent task, a
 * ServiceRequest focus and each input and output in the patient's record
 * belong to the same patient, and a task doesn't leave a final status.
 * meta.versionId counts the updates, so an update can ask that nobody changed
 * the task since it was read (If-Match).
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
use OpenEMR\FHIR\R4\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\R4\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\R4\FHIRElement\FHIRId;
use OpenEMR\FHIR\R4\FHIRElement\FHIRMarkdown;
use OpenEMR\FHIR\R4\FHIRElement\FHIRReference;
use OpenEMR\FHIR\R4\FHIRElement\FHIRRequestPriority;
use OpenEMR\FHIR\R4\FHIRElement\FHIRString;
use OpenEMR\FHIR\R4\FHIRElement\FHIRTaskIntent;
use OpenEMR\FHIR\R4\FHIRElement\FHIRTaskStatus;
use OpenEMR\FHIR\R4\FHIRElement\FHIRUrl;
use OpenEMR\FHIR\R4\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTask\FHIRTaskInput;
use OpenEMR\FHIR\R4\FHIRResource\FHIRTask\FHIRTaskOutput;
use OpenEMR\Services\AddressBookOrganizationService;
use OpenEMR\Services\FHIR\Questionnaire\FhirQuestionnaireFormService;
use OpenEMR\Services\FHIR\QuestionnaireResponse\FhirQuestionnaireResponseFormService;
use OpenEMR\Services\FHIR\Traits\PatientSearchTrait;
use OpenEMR\Services\ReferralTaskService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\FhirSearchParameterDefinition;
use OpenEMR\Services\Search\ISearchField;
use OpenEMR\Services\Search\SearchFieldType;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\SearchQueryConfig;
use OpenEMR\Services\Search\ServiceField;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use OpenEMR\Services\TaskService;
use OpenEMR\Validators\ProcessingResult;

class FhirTaskService extends FhirServiceBase implements IPatientCompartmentResourceService
{
    use PatientSearchTrait;

    public const TASK_CODE_SYSTEM = 'http://hl7.org/fhir/CodeSystem/task-code';

    /** Structured Data Capture's temporary codes: complete-questionnaire, and its questionnaire-response output. */
    public const SDC_TEMP_SYSTEM = 'http://hl7.org/fhir/uv/sdc/CodeSystem/temp';

    /** SDOH Clinical Care's temporary codes: its patient task codes, and its input and output types. */
    public const SDOHCC_TEMP_SYSTEM = 'http://hl7.org/fhir/us/sdoh-clinicalcare/CodeSystem/SDOHCC-CodeSystemTemporaryCodes';

    private ?TaskService $taskService = null;

    /** The version an update must find (If-Match), or null for any. */
    private ?int $expectedVersion = null;

    private bool $versionConflict = false;

    /** How far up a parent chain the loop check walks; deeper chains aren't a workflow anyone builds. */
    private const MAX_PARENT_DEPTH = 100;

    /** The types a task's references are looked up as, in its patient's record. */
    private const RECORD_TYPES = ['ServiceRequest', 'RelatedPerson', 'QuestionnaireResponse', 'Observation', 'Procedure', 'DocumentReference'];

    /** The types a task's references are looked up as on this server; they aren't any one patient's. */
    private const SERVER_TYPES = ['Questionnaire', 'Organization', 'Practitioner', 'PractitionerRole'];

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
        // code:missing is read as for any token; a code search reads the system as well.
        if (($search['code'] ?? null) instanceof TokenSearchField && $search['code']->getModifier() !== SearchModifier::MISSING) {
            $search['code'] = self::codeSearch($search['code']);
        }
        return $search;
    }

    /**
     * Task.code is kept as a code and its system, so a code search matches on both, as FHIR's token
     * search reads them: system|code needs both, system| matches any code in that system, a bare
     * code matches it in any system, and |code only where no system was recorded.
     */
    private static function codeSearch(TokenSearchField $field): CompositeSearchField
    {
        $anyOf = new CompositeSearchField('code', [], false);
        foreach ($field->getValues() as $value) {
            if (!$value instanceof TokenSearchValue) {
                continue;
            }
            $match = new CompositeSearchField('code', [], true);
            $code = (string) $value->getCode();
            $system = $value->getSystem();
            // system| (a system and no code) is any code in that system, so only the system is compared.
            if ($code !== '' || !is_string($system) || $system === '') {
                $match->addChild(new StringSearchField('code', [$code], SearchModifier::EXACT));
            }
            if ($system === '') {
                $noSystem = new TokenSearchField('code_system', [new TokenSearchValue(true)]);
                $noSystem->setModifier(SearchModifier::MISSING);
                $match->addChild($noSystem);
            } elseif (is_string($system)) {
                $match->addChild(new StringSearchField('code_system', [$system], SearchModifier::EXACT));
            }
            $anyOf->addChild($match);
        }
        return $anyOf;
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
        $codeSystem = self::text($dataRecord, 'code_system');
        // A write keeps both, and the upgrade gave every earlier code its system, so a code without
        // one isn't rendered: there's no system to put with it that codeSearch() would agree with.
        if ($code !== null && $codeSystem !== null) {
            $concept = new FHIRCodeableConcept();
            $concept->addCoding(UtilsService::createCoding($code, $code, $codeSystem));
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

        foreach (['inputs' => new FHIRTaskInput(), 'outputs' => new FHIRTaskOutput()] as $key => $blank) {
            $rows = $dataRecord[$key] ?? null;
            foreach (is_array($rows) ? $rows : [] as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $parameter = self::parameter(clone $blank, $row);
                if ($parameter instanceof FHIRTaskInput) {
                    $task->addInput($parameter);
                } else {
                    $task->addOutput($parameter);
                }
            }
        }
        return $task;
    }

    /**
     * A task_input or task_output row as Task.input or Task.output: its type, and its value.
     *
     * @param array<mixed> $row
     */
    private static function parameter(FHIRTaskInput|FHIRTaskOutput $parameter, array $row): FHIRTaskInput|FHIRTaskOutput
    {
        $parameter->setType(self::concept(self::text($row, 'type_system'), self::text($row, 'type_code'), self::text($row, 'type_display'), self::text($row, 'type_text')));
        $text = self::text($row, 'value_text');
        $display = self::text($row, 'value_display');
        match (self::text($row, 'value_element')) {
            'valueReference' => $parameter->setValueReference(UtilsService::createRelativeReference(self::text($row, 'value_type') ?? '', self::text($row, 'value_uuid') ?? '', $display)),
            'valueCodeableConcept' => $parameter->setValueCodeableConcept(self::concept(self::text($row, 'value_system'), self::text($row, 'value_code'), $display, $text)),
            'valueCanonical' => $parameter->setValueCanonical(new FHIRCanonical($text ?? '')),
            'valueUrl' => $parameter->setValueUrl(new FHIRUrl($text ?? '')),
            'valueMarkdown' => $parameter->setValueMarkdown(new FHIRMarkdown($text ?? '')),
            default => $parameter->setValueString(new FHIRString($text ?? '')),
        };
        return $parameter;
    }

    /** A CodeableConcept of one coding (with its display, when it had one), its text, or both. */
    private static function concept(?string $system, ?string $code, ?string $display, ?string $text): FHIRCodeableConcept
    {
        $concept = new FHIRCodeableConcept();
        if ($system !== null && $code !== null) {
            $concept->addCoding(UtilsService::createCoding($code, $display, $system));
        }
        if ($text !== null) {
            $concept->setText(new FHIRString($text));
        }
        return $concept;
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
     * A Task body as task fields, its inputs and outputs, the patient's uuid, and what's wrong with it.
     *
     * @return array{fields: array<string, string|null>, inputs: array<int, array<string, string|null>>, outputs: array<int, array<string, string|null>>, puuid: ?string, errors: array<string, string>}
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
        [$fields, $inputs, $outputs, $puuid, $errors] = self::unpack($openEmrRecord);
        $pid = $puuid === null ? null : self::patientId($puuid);
        if ($puuid !== null && $pid === null) {
            $errors['for'] = 'Patient reference could not be resolved: ' . $puuid;
        }
        if ($pid !== null && ($problem = $this->parentProblem($fields['part_of_uuid'] ?? null, $pid, null)) !== null) {
            $errors['partOf'] = $problem;
        }
        if ($pid !== null) {
            $errors += $this->recordProblems($fields, $inputs, $outputs, $puuid);
        }
        if ($errors !== [] || $pid === null) {
            return self::invalid($errors);
        }
        $fields['authored_on'] ??= date('Y-m-d H:i:s');
        $uuid = $this->getTaskService()->insert($fields, $pid, $this->userId(), array_values($inputs), array_values($outputs));
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
        [$fields, $inputs, $outputs, $puuid, $errors] = self::unpack($updatedOpenEMRRecord);
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
        if ($puuid !== null && $pid === null) {
            // Not compared as "no patient": a task with none would take the reference and drop it.
            $errors['for'] = 'Patient reference could not be resolved: ' . $puuid;
        } elseif ($puuid !== null && $pid !== $stored['patient_id']) {
            $errors['for'] = "Task.for can't change: it must name the task's patient";
        } elseif ($puuid !== null) {
            $errors += $this->recordProblems($fields, $inputs, $outputs, $puuid);
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
        // A PUT replaces the task, its inputs and outputs with it.
        if (!$this->getTaskService()->update($fhirResourceId, $fields, $this->userId(), $this->expectedVersion ?? $stored['version'], array_values($inputs), array_values($outputs))) {
            $this->versionConflict = true;
            return self::invalid(['meta.versionId' => 'The task changed while it was being updated: read it again before updating']);
        }
        // A referral's Task: the one who made the referral hears when it's accepted, rejected or completed (#14710).
        if (isset($fields['status'])) {
            (new ReferralTaskService())->taskStatusChanged($fhirResourceId, $stored['status'], $fields['status'], $this->userId());
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
     * @param bool $encode unused: FhirServiceBase declares it, and nothing passes it. #14443
     *                     drops it there and declares ?FHIRProvenance, which this signature
     *                     already keeps to, so it loads under the base as it is and as #14443 makes it.
     */
    public function createProvenanceResource($dataRecord, $encode = false): ?FHIRProvenance
    {
        if (!($dataRecord instanceof FHIRTask)) {
            throw new \BadMethodCallException('Data record should be correct instance class');
        }
        $provenance = $this->getFhirProvenanceService()->createProvenanceForDomainResource($dataRecord, $dataRecord->getRequester());
        return $provenance instanceof FHIRProvenance ? $provenance : null;
    }

    /**
     * Seam so unit tests can substitute the provenance factory.
     */
    protected function getFhirProvenanceService(): FhirProvenanceService
    {
        return new FhirProvenanceService();
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
     * What's wrong with the task's references, by element. Those into the patient's record (a
     * ServiceRequest focus, a RelatedPerson owner, and each input and output that names a
     * QuestionnaireResponse, Observation, Procedure or DocumentReference) have to be on this server
     * and for the task's patient: the resource's own service looks each up inside that patient's
     * compartment, so a task can't name another patient's record. Those outside the record (a
     * Questionnaire, Organization, Practitioner or PractitionerRole, as focus, owner, requester or
     * input) have to be on this server; they aren't any one patient's, so there's no compartment to
     * check. A Practitioner is any user but an address book organization; the others are looked up through their own service, so an
     * Organization is one FHIR serves (a facility, insurance company, procedure provider or address
     * book organization). A Patient owner or requester is checked against Task.for when the body is read.
     *
     * @param array<string, string|null> $fields
     * @param array<int, array<string, string|null>> $inputs
     * @param array<int, array<string, string|null>> $outputs
     * @return array<string, string>
     */
    private function recordProblems(array $fields, array $inputs, array $outputs, string $puuid): array
    {
        $references = [];
        $looked = array_merge(self::RECORD_TYPES, self::SERVER_TYPES);
        foreach (['focus', 'owner', 'requester'] as $element) {
            $type = $fields[$element . '_type'] ?? null;
            if ($type !== null && in_array($type, $looked, true)) {
                $references[$element] = [$type, $fields[$element . '_uuid'] ?? null];
            }
        }
        foreach (['input' => $inputs, 'output' => $outputs] as $element => $rows) {
            foreach ($rows as $i => $row) {
                $type = $row['value_type'] ?? null;
                if ($type !== null && in_array($type, $looked, true)) {
                    $references[$element . "[$i]"] = [$type, $row['value_uuid'] ?? null];
                }
            }
        }
        // One service per type, and one lookup per record however often the task names it.
        $services = [];
        $found = [];
        $problems = [];
        foreach ($references as $element => [$type, $uuid]) {
            $key = $type . '/' . strtolower($uuid ?? '');
            $onServer = in_array($type, self::SERVER_TYPES, true);
            // A Practitioner only has to be a user on this server. FHIR's Practitioner search shows only
            // users with an NPI and a username or a listed address book type, but core names users
            // without one as Practitioners too (Patient.generalPractitioner, CarePlan.author).
            $found[$key] ??= $uuid !== null && ($type === 'Practitioner'
                ? self::isUser($uuid)
                : self::inRecord($services[$type] ??= $this->recordService($type), $uuid, $onServer ? null : $puuid));
            if (!$found[$key]) {
                $problems[$element] = "Task.$element names no $type " . ($onServer ? 'on this server' : "for this task's patient") . ': ' . ($uuid ?? '');
            }
        }
        return $problems;
    }

    /**
     * Whether a user on this server has this uuid, other than an address book organization: those are
     * Organizations (AddressBookOrganizationService), so naming one as a Practitioner is refused.
     */
    private static function isUser(string $uuid): bool
    {
        return UuidRegistry::isValidStringUUID($uuid) && QueryUtils::fetchSingleValue(
            'SELECT id FROM users WHERE uuid = ? AND COALESCE(abook_type, \'\') <> ?',
            'id',
            [UuidRegistry::uuidToBytes($uuid), AddressBookOrganizationService::ABOOK_TYPE]
        ) !== null;
    }

    /** Whether its own service finds the record with this uuid: in the patient's compartment, or with no patient on this server. */
    private static function inRecord(IResourceReadableService $service, string $uuid, ?string $puuid): bool
    {
        $found = $service->getOne($uuid, $puuid);
        return $found->isValid() && $found->hasData();
    }

    /**
     * The service for one of RECORD_TYPES or SERVER_TYPES, built as its read route builds it.
     * QuestionnaireResponse's responses saved from forms, and Questionnaire's forms, are mapped services.
     */
    private function recordService(string $type): IResourceReadableService
    {
        if ($type === 'QuestionnaireResponse') {
            $service = new FhirQuestionnaireResponseService();
            $service->addMappedService(new FhirQuestionnaireResponseFormService());
        } elseif ($type === 'Questionnaire') {
            $service = new FhirQuestionnaireService();
            $service->addMappedService(new FhirQuestionnaireFormService());
        } else {
            $service = match ($type) {
                'ServiceRequest' => new FhirServiceRequestService(),
                'RelatedPerson' => new FhirRelatedPersonService(),
                'Observation' => new FhirObservationService(),
                'Procedure' => new FhirProcedureService(),
                'DocumentReference' => new FhirDocumentReferenceService(),
                'Organization' => new FhirOrganizationService(),
                'Practitioner' => new FhirPractitionerService(),
                'PractitionerRole' => new FhirPractitionerRoleService(),
                default => throw new \LogicException("$type is not looked up"),
            };
        }
        $session = $this->getSession();
        if ($session !== null && $service instanceof FhirServiceBase) {
            $service->setSession($session);
        }
        return $service;
    }

    /**
     * @return array{0: array<string, string|null>, 1: array<int, array<string, string|null>>, 2: array<int, array<string, string|null>>, 3: ?string, 4: array<string, string>}
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
        $rows = is_array($record) ? $record : [];
        return [$fields, self::rows($rows['inputs'] ?? null), self::rows($rows['outputs'] ?? null), $puuid, $errors];
    }

    /**
     * Input or output rows from what parseFhirResource() returned, keyed by place.
     *
     * @return array<int, array<string, string|null>>
     */
    private static function rows(mixed $list): array
    {
        $rows = [];
        foreach (is_array($list) ? $list : [] as $i => $row) {
            if (!is_int($i) || !is_array($row)) {
                continue;
            }
            $rows[$i] = [];
            foreach ($row as $column => $value) {
                if (is_string($column) && ($value === null || is_string($value))) {
                    $rows[$i][$column] = $value;
                }
            }
        }
        return $rows;
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
