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
     * Apply server-authoritative stamping to a validated QuestionnaireResponse.
     *
     * The DB row linkage is authoritative, so the persisted resource must agree with the
     * server rather than trusting the browser. This strips client-supplied id/meta, forces the
     * subject to the session patient, and stamps the authoring time. These values flow out
     * through the FHIR API and CCDA, where consumers trust the resource.
     *
     * Note: the questionnaire canonical is intentionally NOT set here. QuestionnaireResponseService
     * ::saveQuestionnaireResponse() owns the canonical and rebuilds it from the questionnaire id
     * against the server FHIR base URL, so any value stamped here would be discarded. Keeping it
     * out avoids a misleading second source of truth.
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

        // Identity and versioning are owned by the server. Stripping any client-supplied id also
        // prevents the save service from adopting a caller-chosen response_id on new saves. The
        // questionnaire reference is likewise stripped so the save service is the sole authority
        // for it rather than a client-supplied value surviving into persistence.
        unset($response['id'], $response['meta'], $response['questionnaire']);

        // Subject must be the session patient regardless of what the client claimed.
        $response['subject'] = ['reference' => 'Patient/' . $patientUuid];

        // Authoring time is stamped by the server so it cannot be forged or drift with client clocks.
        $response['authored'] = $authored ?? date('c');

        return $response;
    }
}
