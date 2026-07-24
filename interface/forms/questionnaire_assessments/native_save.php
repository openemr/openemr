<?php

/**
 * Save a patient-context FHIR QuestionnaireResponse from the native Questionnaire runtime.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

header('Content-Type: application/json; charset=utf-8');

// Maximum accepted QuestionnaireResponse payload. Large SDC responses are well under this;
// anything bigger is either malformed or hostile.
const OE_QR_MAX_PAYLOAD_BYTES = 2097152; // 2 MiB

// QuestionnaireResponse.status values this workspace is allowed to persist.
const OE_QR_ALLOWED_STATUSES = ['in-progress', 'completed', 'amended'];

$nonNegativeInt = static function (mixed $value): ?int {
    if (is_int($value)) {
        return $value >= 0 ? $value : null;
    }

    if (!is_string($value) || $value === '' || !ctype_digit($value)) {
        return null;
    }

    $validated = filter_var($value, FILTER_VALIDATE_INT);
    return is_int($validated) && $validated >= 0 ? $validated : null;
};

try {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: false);

    $pid = PatientSessionUtil::getPid();
    if ($pid < 1 || !AclMain::aclCheckCore('patients', 'med')) {
        throw new RuntimeException(xlt('Not authorized to save patient assessments.'));
    }

    $questionnaireRecordId = filter_input(INPUT_POST, 'questionnaire_id', FILTER_VALIDATE_INT);
    if (!is_int($questionnaireRecordId) || $questionnaireRecordId < 1) {
        throw new RuntimeException(xlt('A Questionnaire repository id is required.'));
    }

    $questionnaireResponseInput = filter_input(
        INPUT_POST,
        'questionnaire_response',
        FILTER_UNSAFE_RAW,
        FILTER_REQUIRE_SCALAR
    );
    $questionnaireResponseJson = is_string($questionnaireResponseInput) ? $questionnaireResponseInput : '';
    if (trim($questionnaireResponseJson) === '') {
        throw new RuntimeException(xlt('QuestionnaireResponse data is missing.'));
    }
    if (strlen($questionnaireResponseJson) > OE_QR_MAX_PAYLOAD_BYTES) {
        throw new RuntimeException(xlt('QuestionnaireResponse data exceeds the maximum allowed size.'));
    }

    $questionnaireResponse = json_decode($questionnaireResponseJson, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($questionnaireResponse) || ($questionnaireResponse['resourceType'] ?? null) !== 'QuestionnaireResponse') {
        throw new RuntimeException(xlt('FHIR QuestionnaireResponse data is invalid.'));
    }

    $responseStatus = $questionnaireResponse['status'] ?? null;
    if (!is_string($responseStatus) || !in_array($responseStatus, OE_QR_ALLOWED_STATUSES, true)) {
        throw new RuntimeException(xlt('FHIR QuestionnaireResponse status is invalid.'));
    }

    $responseIdInput = filter_input(INPUT_POST, 'response_id', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
    $responseId = is_string($responseIdInput) ? trim($responseIdInput) : '';
    $questionnaireService = new QuestionnaireService();
    $responseService = new QuestionnaireResponseService();
    $existingResponse = [];

    if ($responseId !== '') {
        $existingResponse = $responseService->fetchQuestionnaireResponseByResponseId($responseId);
        if ($existingResponse === []) {
            throw new RuntimeException(xlt('The QuestionnaireResponse to update was not found.'));
        }
        if ($nonNegativeInt($existingResponse['patient_id'] ?? null) !== $pid) {
            throw new RuntimeException(xlt('The QuestionnaireResponse does not belong to the selected patient.'));
        }
        if ($nonNegativeInt($existingResponse['encounter'] ?? null) !== 0) {
            throw new RuntimeException(xlt('Encounter questionnaires cannot be updated from the patient assessment workspace.'));
        }
        if ($nonNegativeInt($existingResponse['questionnaire_foreign_id'] ?? null) !== $questionnaireRecordId) {
            throw new RuntimeException(xlt('QuestionnaireResponse repository linkage is invalid.'));
        }

        $questionnaireJson = is_string($existingResponse['questionnaire'] ?? null)
            ? $existingResponse['questionnaire']
            : '';
    } else {
        $questionnaireRecord = $questionnaireService->fetchQuestionnaireById($questionnaireRecordId);
        if ($questionnaireRecord === [] || $nonNegativeInt($questionnaireRecord['active'] ?? null) !== 1) {
            throw new RuntimeException(xlt('The selected Questionnaire is not active or was not found.'));
        }
        $questionnaireJson = is_string($questionnaireRecord['questionnaire'] ?? null)
            ? $questionnaireRecord['questionnaire']
            : '';
    }

    $questionnaire = json_decode($questionnaireJson, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($questionnaire) || ($questionnaire['resourceType'] ?? null) !== 'Questionnaire') {
        throw new RuntimeException(xlt('FHIR Questionnaire data is invalid.'));
    }

    // Server-side stamping. The DB row linkage above is authoritative, so the persisted FHIR
    // resource content must agree with it rather than trusting whatever the browser submitted.
    // These values flow out through the FHIR API and CCDA, where consumers trust the resource.
    UuidRegistry::createMissingUuidsForTables(['patient_data']);
    $patientService = new PatientService();
    $puuidBinary = $patientService->getUuid((string) $pid);
    $puuid = $puuidBinary !== false ? UuidRegistry::uuidToString($puuidBinary) : '';
    if ($puuid === '') {
        throw new RuntimeException(xlt('Unable to resolve the patient identity for this QuestionnaireResponse.'));
    }

    // Identity and versioning are owned by the server. Stripping any client-supplied id also
    // prevents the save service from adopting a caller-chosen response_id on new saves.
    unset($questionnaireResponse['id'], $questionnaireResponse['meta']);

    // Subject must be the session patient regardless of what the client claimed.
    $questionnaireResponse['subject'] = ['reference' => 'Patient/' . $puuid];

    // Canonical questionnaire reference comes from the server-side Questionnaire snapshot.
    $questionnaireUrl = $questionnaire['url'] ?? null;
    $questionnaireIdValue = $questionnaire['id'] ?? null;
    $questionnaireCanonical = '';
    if (is_string($questionnaireUrl) && $questionnaireUrl !== '') {
        $questionnaireCanonical = $questionnaireUrl;
    } elseif (is_string($questionnaireIdValue) && $questionnaireIdValue !== '') {
        $questionnaireCanonical = 'Questionnaire/' . $questionnaireIdValue;
    }
    if ($questionnaireCanonical !== '') {
        $questionnaireResponse['questionnaire'] = $questionnaireCanonical;
    } else {
        unset($questionnaireResponse['questionnaire']);
    }

    // Authoring time is stamped by the server so it cannot be forged or drift with client clocks.
    $questionnaireResponse['authored'] = date('c');

    $stampedResponseJson = json_encode(
        $questionnaireResponse,
        JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    );

    $saved = $responseService->saveQuestionnaireResponse(
        response: $stampedResponseJson,
        pid: $pid,
        encounter: 0,
        qr_id: $responseId !== '' ? $responseId : null,
        qr_record_id: null,
        q: $questionnaireJson,
        q_id: null,
        form_response: null,
        add_report: true,
    );

    if (!is_array($saved)) {
        throw new RuntimeException(xlt('QuestionnaireResponse save failed.'));
    }

    echo json_encode([
        'success' => true,
        'id' => $saved['id'],
        'response_id' => $saved['response_id'] ?? '',
        'new' => $saved['new'] ?? false,
    ], JSON_THROW_ON_ERROR);
} catch (RuntimeException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
} catch (CsrfInvalidException | JsonException) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => xlt('Unable to save QuestionnaireResponse.'),
    ]);
} catch (Throwable $e) {
    // Error/TypeError must not be suppressed (openemr.forbiddenCatchType); log for
    // observability, then let it propagate to the global exception handler.
    ServiceContainer::getLogger()->error(
        'Native QuestionnaireResponse save failed with a fatal error.',
        ['exception' => $e]
    );
    throw $e;
}
