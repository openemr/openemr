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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfInvalidException;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

header('Content-Type: application/json; charset=utf-8');

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

    $questionnaireResponse = json_decode($questionnaireResponseJson, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($questionnaireResponse) || ($questionnaireResponse['resourceType'] ?? null) !== 'QuestionnaireResponse') {
        throw new RuntimeException(xlt('FHIR QuestionnaireResponse data is invalid.'));
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
        $questionnaireType = $questionnaireRecord['type'] ?? null;
        if (is_string($questionnaireType) && strtolower($questionnaireType) === 'encounter') {
            throw new RuntimeException(xlt('Encounter questionnaires cannot be saved from the patient assessment workspace.'));
        }
        $questionnaireJson = is_string($questionnaireRecord['questionnaire'] ?? null)
            ? $questionnaireRecord['questionnaire']
            : '';
    }

    $questionnaire = json_decode($questionnaireJson, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($questionnaire) || ($questionnaire['resourceType'] ?? null) !== 'Questionnaire') {
        throw new RuntimeException(xlt('FHIR Questionnaire data is invalid.'));
    }

    $saved = $responseService->saveQuestionnaireResponse(
        response: $questionnaireResponseJson,
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
        'id' => $saved['id'] ?? 0,
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
}
