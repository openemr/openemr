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

// globals.php sets up the Composer autoloader itself (as early as possible), so no
// separate vendor/autoload.php require is needed here. Unlike save.php and
// questionnaire_assessments.php, this endpoint resolves no portal-session flag before
// globals.php, so there is nothing load-bearing between the two.
require_once(__DIR__ . '/../../globals.php');

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\NativeQuestionnaireResponseProcessor;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

header('Content-Type: application/json; charset=utf-8');

$processor = new NativeQuestionnaireResponseProcessor();

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
    $questionnaireResponse = $processor->decodeAndValidateResponse($questionnaireResponseJson);

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
        if ($processor->normalizeNonNegativeInt($existingResponse['patient_id'] ?? null) !== $pid) {
            throw new RuntimeException(xlt('The QuestionnaireResponse does not belong to the selected patient.'));
        }
        if ($processor->normalizeNonNegativeInt($existingResponse['encounter'] ?? null) !== 0) {
            throw new RuntimeException(xlt('Encounter questionnaires cannot be updated from the patient assessment workspace.'));
        }
        if ($processor->normalizeNonNegativeInt($existingResponse['questionnaire_foreign_id'] ?? null) !== $questionnaireRecordId) {
            throw new RuntimeException(xlt('QuestionnaireResponse repository linkage is invalid.'));
        }

        $questionnaireJson = is_string($existingResponse['questionnaire'] ?? null)
            ? $existingResponse['questionnaire']
            : '';
    } else {
        $questionnaireRecord = $questionnaireService->fetchQuestionnaireById($questionnaireRecordId);
        if ($questionnaireRecord === [] || $processor->normalizeNonNegativeInt($questionnaireRecord['active'] ?? null) !== 1) {
            throw new RuntimeException(xlt('The selected Questionnaire is not active or was not found.'));
        }
        $questionnaireJson = is_string($questionnaireRecord['questionnaire'] ?? null)
            ? $questionnaireRecord['questionnaire']
            : '';
    }

    $questionnaire = $processor->decodeAndValidateQuestionnaire($questionnaireJson);

    // Resolve the session patient's UUID. Ensure only THIS patient has a UUID rather than
    // triggering a whole-table backfill on every save; createMissingUuidForRow is idempotent
    // and no-ops when the row already has one.
    $patientService = new PatientService();
    $puuidBinary = $patientService->getUuid((string) $pid);
    if ($puuidBinary === false) {
        UuidRegistry::createMissingUuidForRow('patient_data', 'pid', $pid);
        $puuidBinary = $patientService->getUuid((string) $pid);
    }
    $puuid = $puuidBinary !== false ? UuidRegistry::uuidToString($puuidBinary) : '';

    // Server-side stamping. The DB row linkage above is authoritative, so the persisted FHIR
    // resource content must agree with it rather than trusting whatever the browser submitted.
    // These values flow out through the FHIR API and CCDA, where consumers trust the resource.
    $questionnaireResponse = $processor->stampResponse($questionnaireResponse, $puuid);

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
        'id' => $saved['id'] ?? null,
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
