<?php

/**
 * What a FHIR Task create or update may say (part 2 of #14521): the R4 code
 * sets, the resource types focus, owner and requester may point at, and the
 * statuses a task can't leave. Reads a Task body into a task row's fields and
 * says what's wrong, element by element.
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

    /** http://hl7.org/fhir/CodeSystem/task-code */
    public const CODES = ['approve', 'fulfill', 'abort', 'replace', 'change', 'suspend', 'resume'];

    /** The two workflows #14521 builds for: a referral being worked, and a questionnaire assigned to a patient. */
    public const FOCUS_TYPES = ['ServiceRequest', 'Questionnaire'];

    /** Who does the work: a community organization, a clinician, or the patient. */
    public const OWNER_TYPES = ['Organization', 'Practitioner', 'PractitionerRole', 'Patient'];

    /** Who asked for it. */
    public const REQUESTER_TYPES = ['Practitioner', 'PractitionerRole', 'Organization', 'Patient'];

    /**
     * A Task body as task row fields, the patient it is for (by uuid), and what's wrong with
     * it by element. id, meta and lastModified are the server's and aren't read; authoredOn
     * is kept as given (the caller sets it when it isn't). Elements the table doesn't hold
     * yet (input, output, note) are left out.
     *
     * @param array<array-key, mixed> $json the Task as JSON
     * @return array{fields: array<string, string|null>, puuid: ?string, errors: array<string, string>}
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
        if (isset($json['code'])) {
            $codes = FhirPayloadReader::codingCodes($json['code'], FhirTaskService::TASK_CODE_SYSTEM);
            $code = $codes[0] ?? null;
            if (!is_string($code) || !in_array($code, self::CODES, true)) {
                $errors['code'] = 'Task.code must be a coding from ' . FhirTaskService::TASK_CODE_SYSTEM . ': ' . implode(', ', self::CODES);
            } else {
                $fields['code'] = $code;
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

        return ['fields' => $fields, 'puuid' => $puuid, 'errors' => $errors];
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
