<?php

/**
 * Validation and server-side stamping for native QuestionnaireResponse saves.
 *
 * Extracted from interface/forms/questionnaire_assessments/native_save.php so the
 * deterministic guards (payload validation, status whitelist, id normalization) and the
 * server-authoritative stamping (subject, canonical, authored, id/meta stripping) can be
 * unit tested without a live request, session, or database. The endpoint keeps the stateful
 * concerns — CSRF, ACL, patient-session pid, repository lookups, persistence — and delegates
 * the rest here.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Services\InvalidQuestionnaireResponseException;

class NativeQuestionnaireResponseProcessor
{
    /**
     * Maximum accepted QuestionnaireResponse payload. Large SDC responses are well under this;
     * anything bigger is either malformed or hostile.
     */
    public const MAX_PAYLOAD_BYTES = 2097152; // 2 MiB

    /** QuestionnaireResponse.status values this workspace is allowed to persist. */
    public const ALLOWED_STATUSES = ['in-progress', 'completed', 'amended'];

    /**
     * Normalize a value to a non-negative int, or null when it is not one.
     *
     * Accepts an int directly or a string of digits; rejects negatives, non-digit strings,
     * empty strings, floats, and everything else. Used to compare untrusted stored/session
     * values (patient_id, encounter, foreign ids) against known-good ints without loose
     * coercion surprises.
     */
    public function normalizeNonNegativeInt(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value) || $value === '' || !ctype_digit($value)) {
            return null;
        }

        $validated = filter_var($value, FILTER_VALIDATE_INT);
        return is_int($validated) && $validated >= 0 ? $validated : null;
    }

    /**
     * Validate the raw QuestionnaireResponse payload string and decode it.
     *
     * Enforces: non-empty, within MAX_PAYLOAD_BYTES, valid JSON, an object with
     * resourceType === 'QuestionnaireResponse', and an allowed status.
     *
     * @param string $rawJson the untrusted request body
     * @return array<string, mixed> the decoded, shape-validated resource
     * @throws InvalidQuestionnaireResponseException on any guard failure (safe user-facing message)
     */
    public function decodeAndValidateResponse(string $rawJson): array
    {
        if (trim($rawJson) === '') {
            throw new InvalidQuestionnaireResponseException(xlt('QuestionnaireResponse data is missing.'));
        }
        if (strlen($rawJson) > self::MAX_PAYLOAD_BYTES) {
            throw new InvalidQuestionnaireResponseException(xlt('QuestionnaireResponse data exceeds the maximum allowed size.'));
        }

        $decoded = json_decode($rawJson, true);
        if (!is_array($decoded) || ($decoded['resourceType'] ?? null) !== 'QuestionnaireResponse') {
            throw new InvalidQuestionnaireResponseException(xlt('FHIR QuestionnaireResponse data is invalid.'));
        }

        $status = $decoded['status'] ?? null;
        if (!is_string($status) || !in_array($status, self::ALLOWED_STATUSES, true)) {
            throw new InvalidQuestionnaireResponseException(xlt('FHIR QuestionnaireResponse status is invalid.'));
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * Validate the server-side Questionnaire snapshot the response links to.
     *
     * @param string $questionnaireJson the repository/stored snapshot JSON (server-sourced)
     * @return array<string, mixed> the decoded Questionnaire
     * @throws InvalidQuestionnaireResponseException when the snapshot is not a valid Questionnaire
     */
    public function decodeAndValidateQuestionnaire(string $questionnaireJson): array
    {
        $decoded = json_decode($questionnaireJson, true);
        if (!is_array($decoded) || ($decoded['resourceType'] ?? null) !== 'Questionnaire') {
            throw new InvalidQuestionnaireResponseException(xlt('FHIR Questionnaire data is invalid.'));
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * QuestionnaireResponse fields whose value the server owns and the client is never trusted
     * for. stampResponse() removes every one of these from the incoming resource; those the
     * server sets (subject, authored) are then re-applied with server-authoritative values, and
     * those the server does not set here (id, meta, questionnaire, encounter) stay removed so a
     * downstream owner is the sole authority for them.
     *
     * The reason each is server-owned:
     *  - id, meta:      identity/versioning are assigned on persist; a client id could also be
     *                   adopted as the response_id on a new save.
     *  - subject:       must be the session patient, never a client-claimed reference.
     *  - authored:      stamped server-side so it cannot be forged or drift with client clocks.
     *  - questionnaire: QuestionnaireResponseService::saveQuestionnaireResponse() rebuilds this
     *                   canonical from the questionnaire id, so any stamped value is discarded;
     *                   keeping it out avoids a misleading second source of truth.
     *  - encounter:     this workspace persists patient-context records (encounter 0), and the
     *                   save service skips setEncounter() for a zero/empty encounter, so a
     *                   client-supplied encounter would otherwise survive and make the resource
     *                   falsely claim an encounter when later read through the FHIR path.
     *
     * When adding support for a field the server must own, add it here rather than scattering
     * unset() calls, so the complete client-untrusted policy stays visible in one place.
     */
    public const SERVER_OWNED_FIELDS = ['id', 'meta', 'subject', 'authored', 'questionnaire', 'encounter'];

    /**
     * Apply server-authoritative stamping to a validated QuestionnaireResponse.
     *
     * The DB row linkage is authoritative, so the persisted resource must agree with the server
     * rather than trusting the browser. Every field in SERVER_OWNED_FIELDS is first removed from
     * the client-supplied resource; the server then re-applies the ones it sets here (subject,
     * authored). The rest (id, meta, questionnaire, encounter) stay removed so their downstream
     * owner is the sole authority. These values flow out through the FHIR API and CCDA, where
     * consumers trust the resource.
     *
     * @param array<string, mixed> $response      the validated QuestionnaireResponse
     * @param string               $patientUuid   the resolved session patient UUID (string form)
     * @param string|null          $authored      ISO-8601 authoring time; defaults to now when null
     * @return array<string, mixed> the stamped resource ready to persist
     * @throws InvalidQuestionnaireResponseException when the patient UUID is empty
     */
    public function stampResponse(array $response, string $patientUuid, ?string $authored = null): array
    {
        if ($patientUuid === '') {
            throw new InvalidQuestionnaireResponseException(xlt('Unable to resolve the patient identity for this QuestionnaireResponse.'));
        }

        // Remove every client-supplied server-owned field, then re-apply the ones the server sets.
        foreach (self::SERVER_OWNED_FIELDS as $field) {
            unset($response[$field]);
        }

        // Subject must be the session patient regardless of what the client claimed.
        $response['subject'] = ['reference' => 'Patient/' . $patientUuid];

        // Authoring time is stamped by the server so it cannot be forged or drift with client clocks.
        $response['authored'] = $authored ?? date('c');

        return $response;
    }
}
