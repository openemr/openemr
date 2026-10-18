<?php

/**
 * What a FHIR Task create or update may say (parts 2 and 3 of #14521): the
 * code sets, the resource types focus, owner, requester and output may point
 * at, and the statuses a task can't leave. Reads a Task body into a task row's
 * fields and outputs, and says what's wrong, element by element.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\FHIR;

use OpenEMR\Common\Uuid\UuidRegistry;

/**
 * @phpstan-type TaskOutputRow array{type_system: ?string, type_code: ?string, type_text: ?string, value_type: string, value_uuid: string}
 */
final class FhirTaskWriteRules
{
    /** http://hl7.org/fhir/R4/valueset-task-status.html */
    public const STATUSES = [
        'draft', 'requested', 'received', 'accepted', 'rejected', 'ready', 'cancelled',
        'in-progress', 'on-hold', 'failed', 'completed', 'entered-in-error',
    ];

    /**
     * The ends of R4's task state machine: a task there stays there. A task can still be marked
     * entered-in-error from any status, as a correction rather than a step in the work.
     */
    public const FINAL_STATUSES = ['rejected', 'cancelled', 'failed', 'completed', 'entered-in-error'];

    /** http://hl7.org/fhir/R4/valueset-task-intent.html */
    public const INTENTS = [
        'unknown', 'proposal', 'plan', 'order', 'original-order', 'reflex-order', 'filler-order', 'instance-order', 'option',
    ];

    /** http://hl7.org/fhir/R4/valueset-request-priority.html */
    public const PRIORITIES = ['routine', 'urgent', 'asap', 'stat'];

    /**
     * Task.code by system: R4's task-code (http://hl7.org/fhir/CodeSystem/task-code), and the SDC's
     * code for a task to fill in a questionnaire, which SDOH Clinical Care's TaskForPatient also uses.
     */
    public const CODES = [
        FhirTaskService::TASK_CODE_SYSTEM => ['approve', 'fulfill', 'abort', 'replace', 'change', 'suspend', 'resume'],
        FhirTaskService::SDC_TEMP_SYSTEM => ['complete-questionnaire'],
    ];

    /** The two workflows #14521 builds for: a referral being worked, and a questionnaire assigned to a patient. */
    public const FOCUS_TYPES = ['ServiceRequest', 'Questionnaire'];

    /** Who does the work: a community organization, a clinician, or the patient. */
    public const OWNER_TYPES = ['Organization', 'Practitioner', 'PractitionerRole', 'Patient'];

    /** Who asked for it. */
    public const REQUESTER_TYPES = ['Practitioner', 'PractitionerRole', 'Organization', 'Patient'];

    /** What the work produced: a filled-in questionnaire, a result, a service delivered. */
    public const OUTPUT_TYPES = ['QuestionnaireResponse', 'Observation', 'Procedure'];

    /** Each output is looked up when the task is written, so a task holds a bounded number. */
    public const MAX_OUTPUTS = 50;

    /**
     * A Task body as task row fields, its outputs, the patient it is for (by uuid), and what's
     * wrong with it by element. id, meta and lastModified are the server's and aren't read;
     * authoredOn is kept as given (the caller sets it when it isn't). Elements the table doesn't
     * hold yet (input, note) are left out. Outputs are keyed by their place in Task.output, so
     * a fault found later can name the same one.
     *
     * @param array<array-key, mixed> $json the Task as JSON
     * @return array{fields: array<string, string|null>, outputs: array<int, TaskOutputRow>, puuid: ?string, errors: array<string, string>}
     */
    public static function read(array $json): array
    {
        $fields = [];
        $errors = [];

        $status = $json['status'] ?? null;
        if (!is_string($status) || !in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Task.status must be one of ' . implode(', ', self::STATUSES);
        } else {
            $fields['status'] = $status;
        }
        $intent = $json['intent'] ?? null;
        if (!is_string($intent) || !in_array($intent, self::INTENTS, true)) {
            $errors['intent'] = 'Task.intent must be one of ' . implode(', ', self::INTENTS);
        } else {
            $fields['intent'] = $intent;
        }
        $priority = $json['priority'] ?? null;
        if ($priority !== null && (!is_string($priority) || !in_array($priority, self::PRIORITIES, true))) {
            $errors['priority'] = 'Task.priority must be one of ' . implode(', ', self::PRIORITIES);
        } else {
            $fields['priority'] = is_string($priority) ? $priority : null;
        }

        $fields['code'] = null;
        $fields['code_system'] = null;
        if (isset($json['code'])) {
            $coding = self::knownCoding($json['code']);
            if ($coding === null) {
                $errors['code'] = 'Task.code must be a coding from ' . implode(
                    ' or ',
                    array_map(static fn(string $system, array $codes): string => $system . ' (' . implode(', ', $codes) . ')', array_keys(self::CODES), self::CODES)
                );
            } else {
                [$fields['code_system'], $fields['code']] = $coding;
            }
        }
        $description = $json['description'] ?? null;
        $fields['description'] = is_string($description) && trim($description) !== '' ? $description : null;
        $fields['status_reason'] = self::conceptText($json['statusReason'] ?? null);
        $fields['business_status'] = self::conceptText($json['businessStatus'] ?? null);

        $puuid = null;
        $for = self::reference($json['for'] ?? null, ['Patient']);
        if ($for === null) {
            $errors['for'] = 'Task.for must reference a Patient on this server (Patient/{uuid})';
        } else {
            $puuid = $for['uuid'];
        }

        foreach (['focus' => self::FOCUS_TYPES, 'owner' => self::OWNER_TYPES, 'requester' => self::REQUESTER_TYPES] as $element => $types) {
            $fields[$element . '_type'] = null;
            $fields[$element . '_uuid'] = null;
            if (!isset($json[$element])) {
                continue;
            }
            $reference = self::reference($json[$element], $types);
            if ($reference === null) {
                $errors[$element] = "Task.$element must reference one of " . implode(', ', $types) . ' on this server ({type}/{uuid})';
                continue;
            }
            $fields[$element . '_type'] = $reference['type'];
            $fields[$element . '_uuid'] = $reference['uuid'];
        }
        // A patient can be given their own task, not someone else's.
        if ($fields['owner_type'] === 'Patient' && $puuid !== null && strcasecmp((string)$fields['owner_uuid'], $puuid) !== 0) {
            $errors['owner'] = 'A Patient owner must be the patient the task is for';
        }

        $fields['part_of_uuid'] = null;
        $partOf = $json['partOf'] ?? null;
        if ($partOf !== null) {
            $parents = is_array($partOf) && array_is_list($partOf) ? $partOf : null;
            if ($parents === null || count($parents) > 1) {
                $errors['partOf'] = 'Task.partOf takes one parent task';
            } elseif ($parents !== []) {
                $parent = self::reference($parents[0], ['Task']);
                if ($parent === null) {
                    $errors['partOf'] = 'Task.partOf must reference a Task on this server (Task/{uuid})';
                } else {
                    $fields['part_of_uuid'] = $parent['uuid'];
                }
            }
        }

        // A bad authoredOn is named like any other fault; an update ignores it (it keeps the stored one).
        try {
            $fields['authored_on'] = FhirDateTimeParser::toDbDateTime($json['authoredOn'] ?? null, 'Task.authoredOn');
        } catch (\InvalidArgumentException) {
            $fields['authored_on'] = null;
            $errors['authoredOn'] = 'Task.authoredOn must be a FHIR dateTime with a time zone, such as 2026-10-18T09:30:00-05:00';
        }

        $outputs = [];
        $output = $json['output'] ?? null;
        if ($output !== null) {
            if (!is_array($output) || !array_is_list($output)) {
                $errors['output'] = 'Task.output must be a list';
            } elseif (count($output) > self::MAX_OUTPUTS) {
                $errors['output'] = 'Task.output takes at most ' . self::MAX_OUTPUTS . ' outputs';
            } else {
                foreach ($output as $i => $entry) {
                    $row = self::output($entry);
                    if (is_string($row)) {
                        $errors["output[$i]"] = "Task.output[$i] $row";
                    } else {
                        $outputs[$i] = $row;
                    }
                }
            }
        }

        return ['fields' => $fields, 'outputs' => $outputs, 'puuid' => $puuid, 'errors' => $errors];
    }

    /**
     * One Task.output as a task_output row, or what's wrong with it. Its type is a coding, its
     * text, or both; its value a reference to something the work produced, on this server.
     *
     * @return TaskOutputRow|string
     */
    private static function output(mixed $entry): array|string
    {
        if (!is_array($entry)) {
            return 'must be an object with a type and a valueReference';
        }
        $type = $entry['type'] ?? null;
        $text = FhirPayloadReader::getString($type, 'text');
        $system = FhirPayloadReader::firstCodingValue($type, 'system');
        $code = FhirPayloadReader::firstCodingCode($type);
        if ($text === null && ($system === '' || $code === '')) {
            return 'needs a type: a coding with a system and a code, or text';
        }
        foreach (array_keys($entry) as $key) {
            if (is_string($key) && str_starts_with($key, 'value') && $key !== 'valueReference') {
                return "must be a valueReference, not $key";
            }
        }
        $reference = self::reference($entry['valueReference'] ?? null, self::OUTPUT_TYPES);
        if ($reference === null) {
            return 'must reference one of ' . implode(', ', self::OUTPUT_TYPES) . ' on this server ({type}/{uuid})';
        }
        return [
            'type_system' => $system !== '' && $code !== '' ? $system : null,
            'type_code' => $system !== '' && $code !== '' ? $code : null,
            'type_text' => $text,
            'value_type' => $reference['type'],
            'value_uuid' => $reference['uuid'],
        ];
    }

    /**
     * The first coding in Task.code that CODES holds, as its system and code, or null.
     *
     * @return ?array{0: string, 1: string}
     */
    private static function knownCoding(mixed $concept): ?array
    {
        foreach (FhirPayloadReader::codings($concept) as $coding) {
            $system = FhirPayloadReader::getString($coding, 'system');
            $code = FhirPayloadReader::getString($coding, 'code');
            if ($system !== null && $code !== null && in_array($code, self::CODES[$system] ?? [], true)) {
                return [$system, $code];
            }
        }
        return null;
    }

    /**
     * What's wrong with moving a task from one status to another, or null: nothing leaves a final
     * status except to entered-in-error, and nothing leaves entered-in-error.
     */
    public static function transitionProblem(string $from, string $to): ?string
    {
        if ($from === $to || !in_array($from, self::FINAL_STATUSES, true)) {
            return null;
        }
        if ($to === 'entered-in-error' && $from !== 'entered-in-error') {
            return null;
        }
        return "A task that is $from can't become $to"
            . ($from === 'entered-in-error' ? '' : '; only entered-in-error can correct it');
    }

    /**
     * A reference to one of these types on this server, as its type and uuid, or null.
     *
     * @param list<string> $types
     * @return ?array{type: string, uuid: string}
     */
    private static function reference(mixed $element, array $types): ?array
    {
        $literal = FhirPayloadReader::reference($element);
        if ($literal === null) {
            return null;
        }
        $parsed = UtilsService::parseReferenceString($literal);
        $type = $parsed['type'];
        $uuid = $parsed['uuid'];
        if (!$parsed['localResource'] || !is_string($type) || !in_array($type, $types, true)
            || !is_string($uuid) || !UuidRegistry::isValidStringUUID($uuid)) {
            return null;
        }
        return ['type' => $type, 'uuid' => $uuid];
    }

    /** A CodeableConcept as the text the table keeps: its text, or its first coding's display or code. */
    private static function conceptText(mixed $concept): ?string
    {
        $text = FhirPayloadReader::getString($concept, 'text');
        if ($text !== null) {
            return $text;
        }
        $display = FhirPayloadReader::firstCodingValue($concept, 'display');
        if ($display !== '') {
            return $display;
        }
        $code = FhirPayloadReader::firstCodingCode($concept);
        return $code !== '' ? $code : null;
    }
}
