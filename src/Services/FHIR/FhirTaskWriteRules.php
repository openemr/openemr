<?php

/**
 * What a FHIR Task create or update may say (parts 2 and 3 of #14521): the
 * code sets, the resource types focus, owner, requester, input and output may
 * point at, and the statuses a task can't leave. Reads a Task body into a task
 * row's fields, inputs and outputs, and says what's wrong, element by element.
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
 * @phpstan-type TaskParameterRow array{type_system: ?string, type_code: ?string, type_display: ?string, type_text: ?string, value_element: string, value_type: ?string, value_uuid: ?string, value_system: ?string, value_code: ?string, value_display: ?string, value_text: ?string}
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
     * Task.code by system: R4's task-code (http://hl7.org/fhir/CodeSystem/task-code), and the codes
     * SDOH Clinical Care's TaskForPatient binds Task.code to: the SDC's complete-questionnaire, and
     * its own make-contact, review-material and general-information.
     */
    public const CODES = [
        FhirTaskService::TASK_CODE_SYSTEM => ['approve', 'fulfill', 'abort', 'replace', 'change', 'suspend', 'resume'],
        FhirTaskService::SDC_TEMP_SYSTEM => ['complete-questionnaire'],
        FhirTaskService::SDOHCC_TEMP_SYSTEM => ['make-contact', 'review-material', 'general-information'],
    ];

    /** The two workflows #14521 builds for: a referral being worked, and a questionnaire assigned to a patient. */
    public const FOCUS_TYPES = ['ServiceRequest', 'Questionnaire'];

    /** Who does the work: a community organization, a clinician, the patient, or someone for them (a parent). */
    public const OWNER_TYPES = ['Organization', 'Practitioner', 'PractitionerRole', 'Patient', 'RelatedPerson'];

    /** Who asked for it: a patient asks for their own task. */
    public const REQUESTER_TYPES = ['Practitioner', 'PractitionerRole', 'Organization', 'Patient'];

    /** What the work needs: the questionnaire or the PDF to fill in, and whom to contact. */
    public const INPUT_TYPES = ['Questionnaire', 'DocumentReference', 'Organization', 'Practitioner', 'PractitionerRole'];

    /** The values an input may be: SDOH Clinical Care's questionnaire (canonical), its PDF, its url, its category, contacts. */
    public const INPUT_VALUES = ['valueReference', 'valueCanonical', 'valueUrl', 'valueString', 'valueMarkdown', 'valueCodeableConcept'];

    /** What the work produced: a filled-in questionnaire or its PDF, a result, a service delivered. */
    public const OUTPUT_TYPES = ['QuestionnaireResponse', 'Observation', 'Procedure', 'DocumentReference'];

    /** The values an output may be: a record, an answer in words (the contact chosen), or a code for what was done. */
    public const OUTPUT_VALUES = ['valueReference', 'valueString', 'valueMarkdown', 'valueCodeableConcept'];

    /** References into the patient's record are looked up when the task is written, so a task holds a bounded number of each. */
    public const MAX_PARAMETERS = 50;

    /** The longest string, markdown, url or canonical kept, in characters: well inside the TEXT column's 65,535 bytes. */
    public const MAX_TEXT = 10000;

    /** The longest coding system, display and type text kept, in characters: the varchar(255) columns of task_input and task_output. */
    public const MAX_SYSTEM = 255;

    /** The longest code kept, in characters: the varchar(63) columns. */
    public const MAX_CODE = 63;

    /** The elements a write reads. FHIR JSON has no nulls, so a null in one of them is a fault, not an element left out. */
    private const ELEMENTS = [
        'status', 'intent', 'priority', 'code', 'description', 'statusReason', 'businessStatus', 'for', 'focus', 'owner',
        'requester', 'partOf', 'authoredOn', 'input', 'output',
    ];

    /**
     * A Task body as task row fields, its inputs and outputs, the patient it is for (by uuid),
     * and what's wrong with it by element. id, meta and lastModified are the server's and aren't
     * read; authoredOn is kept as given (the caller sets it when it isn't). Elements the table
     * doesn't hold (note, encounter, reasonCode and the rest) are left out, as other FHIR writes
     * here leave them. Inputs and outputs are keyed by their place in the list, so a fault found
     * later can name the same one.
     *
     * @param array<array-key, mixed> $json the Task as JSON
     * @return array{fields: array<string, string|null>, inputs: array<int, TaskParameterRow>, outputs: array<int, TaskParameterRow>, puuid: ?string, errors: array<string, string>}
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
            if (count(FhirPayloadReader::codings($json['code'])) > 1) {
                $errors['code'] = 'Task.code takes one coding: the task keeps one system and code';
            } elseif ($coding === null) {
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
        // A patient can be given their own task, and ask for one, but not someone else's.
        foreach (['owner', 'requester'] as $element) {
            if ($fields[$element . '_type'] === 'Patient' && $puuid !== null && strcasecmp((string)$fields[$element . '_uuid'], $puuid) !== 0) {
                $errors[$element] = "A Patient $element must be the patient the task is for";
            }
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

        $inputs = self::parameters($json, 'input', self::INPUT_VALUES, self::INPUT_TYPES, $errors);
        $outputs = self::parameters($json, 'output', self::OUTPUT_VALUES, self::OUTPUT_TYPES, $errors);

        // Said last, so it replaces whatever the element's own check made of the null.
        foreach (self::ELEMENTS as $element) {
            if (array_key_exists($element, $json) && $json[$element] === null) {
                $errors[$element] = "Task.$element is null: leave it out instead, as FHIR JSON has no nulls";
            }
        }

        return ['fields' => $fields, 'inputs' => $inputs, 'outputs' => $outputs, 'puuid' => $puuid, 'errors' => $errors];
    }

    /**
     * Task.input or Task.output as rows, keyed by place; each fault goes into $errors by its place.
     *
     * @param array<array-key, mixed> $json
     * @param list<string> $values the value[x] elements it may use
     * @param list<string> $types what a valueReference may point at
     * @param array<string, string> $errors
     * @return array<int, TaskParameterRow>
     */
    private static function parameters(array $json, string $element, array $values, array $types, array &$errors): array
    {
        $list = $json[$element] ?? null;
        if ($list === null) {
            return [];
        }
        if (!is_array($list) || !array_is_list($list)) {
            $errors[$element] = "Task.$element must be a list";
            return [];
        }
        if (count($list) > self::MAX_PARAMETERS) {
            $errors[$element] = "Task.$element takes at most " . self::MAX_PARAMETERS;
            return [];
        }
        $rows = [];
        foreach ($list as $i => $entry) {
            $row = self::parameter($entry, $values, $types);
            if (is_string($row)) {
                $errors[$element . "[$i]"] = "Task.$element" . "[$i] $row";
            } else {
                $rows[$i] = $row;
            }
        }
        return $rows;
    }

    /**
     * One input or output as a row, or what's wrong with it. Its type is a coding, its text, or
     * both; its value one of $values: a reference to one of $types on this server, a canonical, a
     * url, a string or markdown, or a CodeableConcept.
     *
     * @param list<string> $values
     * @param list<string> $types
     * @return TaskParameterRow|string
     */
    private static function parameter(mixed $entry, array $values, array $types): array|string
    {
        if (!is_array($entry)) {
            return 'must be an object with a type and a value';
        }
        $type = $entry['type'] ?? null;
        $text = self::filled(FhirPayloadReader::getString($type, 'text'));
        $system = self::filled(FhirPayloadReader::firstCodingValue($type, 'system')) ?? '';
        $code = self::filled(FhirPayloadReader::firstCodingCode($type)) ?? '';
        $display = self::filled(FhirPayloadReader::firstCodingValue($type, 'display')) ?? '';
        // A row holds one coding with its system and code: a second coding, or one without
        // both, would be lost, so it's refused.
        if (count(FhirPayloadReader::codings($type)) > 1) {
            return 'type takes one coding: a row keeps one system and code';
        }
        if (FhirPayloadReader::codings($type) !== [] && ($system === '' || $code === '')) {
            return 'type.coding needs a system and a code';
        }
        if ($text === null && $system === '') {
            return 'needs a type: a coding with a system and a code, or text';
        }
        // Longer than its column, a value would be cut short or fail the insert: refuse it here instead.
        if (($text !== null && mb_strlen($text) > self::MAX_SYSTEM) || mb_strlen($display) > self::MAX_SYSTEM
            || ($system !== '' && $code !== '' && !self::fits($system, $code))) {
            return 'type is too long: its text, coding system and display take at most ' . self::MAX_SYSTEM . ' characters, its code ' . self::MAX_CODE;
        }
        $given = array_values(array_filter(array_keys($entry), static fn(int|string $key): bool => is_string($key) && str_starts_with($key, 'value')));
        if (count($given) !== 1) {
            return 'needs one value, one of ' . implode(', ', $values);
        }
        $element = (string)$given[0];
        if (!in_array($element, $values, true)) {
            return "can't be a $element; it takes " . implode(', ', $values);
        }
        $row = [
            'type_system' => $system !== '' && $code !== '' ? $system : null,
            'type_code' => $system !== '' && $code !== '' ? $code : null,
            'type_display' => $system !== '' && $code !== '' && $display !== '' ? $display : null,
            'type_text' => $text,
            'value_element' => $element,
            'value_type' => null,
            'value_uuid' => null,
            'value_system' => null,
            'value_code' => null,
            'value_display' => null,
            'value_text' => null,
        ];
        $value = $entry[$element];
        if ($element === 'valueReference') {
            $reference = self::reference($value, $types);
            if ($reference === null) {
                return 'must reference one of ' . implode(', ', $types) . ' on this server ({type}/{uuid})';
            }
            $referenceDisplay = self::filled(FhirPayloadReader::getString($value, 'display'));
            if ($referenceDisplay !== null && mb_strlen($referenceDisplay) > self::MAX_SYSTEM) {
                return 'valueReference.display is longer than ' . self::MAX_SYSTEM . ' characters';
            }
            $row['value_type'] = $reference['type'];
            $row['value_uuid'] = $reference['uuid'];
            $row['value_display'] = $referenceDisplay;
            return $row;
        }
        if ($element === 'valueCodeableConcept') {
            $valueText = self::filled(FhirPayloadReader::getString($value, 'text'));
            $valueSystem = self::filled(FhirPayloadReader::firstCodingValue($value, 'system')) ?? '';
            $valueCode = self::filled(FhirPayloadReader::firstCodingCode($value)) ?? '';
            $valueDisplay = self::filled(FhirPayloadReader::firstCodingValue($value, 'display')) ?? '';
            if (count(FhirPayloadReader::codings($value)) > 1) {
                return 'valueCodeableConcept takes one coding: a row keeps one system and code';
            }
            if (FhirPayloadReader::codings($value) !== [] && ($valueSystem === '' || $valueCode === '')) {
                return 'valueCodeableConcept.coding needs a system and a code';
            }
            if ($valueText === null && $valueSystem === '') {
                return 'needs a coding with a system and a code, or text';
            }
            if (($valueSystem !== '' && $valueCode !== '' && !self::fits($valueSystem, $valueCode)) || mb_strlen($valueDisplay) > self::MAX_SYSTEM
                || ($valueText !== null && mb_strlen($valueText) > self::MAX_TEXT)) {
                return 'valueCodeableConcept is too long: its coding system and display take at most ' . self::MAX_SYSTEM . ' characters, its code '
                    . self::MAX_CODE . ', its text ' . self::MAX_TEXT;
            }
            if ($valueSystem !== '' && $valueCode !== '') {
                $row['value_system'] = $valueSystem;
                $row['value_code'] = $valueCode;
                $row['value_display'] = $valueDisplay !== '' ? $valueDisplay : null;
            }
            $row['value_text'] = $valueText;
            return $row;
        }
        if (!is_string($value) || trim($value) === '') {
            return "$element must be a string with something in it";
        }
        if (mb_strlen($value) > self::MAX_TEXT) {
            return "$element is longer than " . self::MAX_TEXT . ' characters';
        }
        if ($element === 'valueUrl' && filter_var($value, FILTER_VALIDATE_URL) === false) {
            return 'valueUrl must be an absolute url';
        }
        if ($element === 'valueCanonical' && preg_match('/^[^\s|]+(\|[^\s|]+)?$/', $value) !== 1) {
            return 'valueCanonical must be a url, with |version or without';
        }
        $row['value_text'] = $value;
        return $row;
    }

    /** Whether a coding fits the system and code columns. */
    private static function fits(string $system, string $code): bool
    {
        return mb_strlen($system) <= self::MAX_SYSTEM && mb_strlen($code) <= self::MAX_CODE;
    }

    /**
     * Task.code's coding, as its system and code, when CODES holds it; otherwise null.
     *
     * @return ?array{0: string, 1: string}
     */
    private static function knownCoding(mixed $concept): ?array
    {
        $coding = FhirPayloadReader::firstCoding($concept);
        $system = FhirPayloadReader::getString($coding, 'system');
        $code = FhirPayloadReader::getString($coding, 'code');
        if ($system === null || $code === null) {
            return null;
        }
        $allowedCodes = self::CODES[$system] ?? [];
        return in_array($code, $allowedCodes, true) ? [$system, $code] : null;
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
        return self::filled(FhirPayloadReader::getString($concept, 'text'))
            ?? self::filled(FhirPayloadReader::firstCodingValue($concept, 'display'))
            ?? self::filled(FhirPayloadReader::firstCodingCode($concept));
    }

    /**
     * A string with something in it, or null. A blank one reads back as nothing
     * (FhirTaskService::text()), so it counts as nothing when written too: a type of " " alone
     * would otherwise be kept, then read back as an empty CodeableConcept.
     */
    private static function filled(?string $value): ?string
    {
        return $value !== null && trim($value) !== '' ? $value : null;
    }
}
