<?php

/**
 * OpenEMR native FHIR Questionnaire assessment renderer.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . '/../../../vendor/autoload.php');
require_once(__DIR__ . '/../../globals.php');

use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

$pid = PatientSessionUtil::getPid();
if ($pid < 1) {
    AccessDeniedHelper::denyWithTemplate('No patient selected.', xlt('FHIR Assessment'));
}
if (!AclMain::aclCheckCore('patients', 'med')) {
    AccessDeniedHelper::denyWithTemplate('ACL check failed for patient assessments.', xlt('FHIR Assessment'));
}

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$questionnaireService = new QuestionnaireService();
$responseService = new QuestionnaireResponseService();
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

$questionnaireRecordIdInput = filter_input(INPUT_GET, 'questionnaire_id', FILTER_VALIDATE_INT);
$questionnaireRecordId = is_int($questionnaireRecordIdInput) ? $questionnaireRecordIdInput : null;
$responseIdInput = filter_input(INPUT_GET, 'response_id', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
$responseId = is_string($responseIdInput) ? trim($responseIdInput) : '';
$questionnaireRecord = [];
$responseRecord = [];
$questionnaireJson = '';
$questionnaireResponseJson = '';

if ($responseId !== '') {
    $responseRecord = $responseService->fetchQuestionnaireResponseByResponseId($responseId);
    if ($responseRecord === []) {
        throw new RuntimeException(xlt('Questionnaire response was not found.'));
    }
    if ($nonNegativeInt($responseRecord['patient_id'] ?? null) !== $pid) {
        throw new RuntimeException(xlt('Questionnaire response does not belong to the selected patient.'));
    }
    if ($nonNegativeInt($responseRecord['encounter'] ?? null) !== 0) {
        throw new RuntimeException(xlt('Encounter questionnaires cannot be edited from the patient assessment workspace.'));
    }

    $questionnaireRecordId = $nonNegativeInt($responseRecord['questionnaire_foreign_id'] ?? null);
    if ($questionnaireRecordId === null || $questionnaireRecordId < 1) {
        throw new RuntimeException(xlt('Questionnaire repository link is missing from the response.'));
    }

    $questionnaireRecord = $questionnaireService->fetchQuestionnaireById($questionnaireRecordId);
    $questionnaireJson = is_string($responseRecord['questionnaire'] ?? null)
        ? $responseRecord['questionnaire']
        : '';
    if ($questionnaireJson === '') {
        $questionnaireJson = is_string($questionnaireRecord['questionnaire'] ?? null)
            ? $questionnaireRecord['questionnaire']
            : '';
    }
    $questionnaireResponseJson = is_string($responseRecord['questionnaire_response'] ?? null)
        ? $responseRecord['questionnaire_response']
        : '';
} else {
    if (!is_int($questionnaireRecordId) || $questionnaireRecordId < 1) {
        throw new RuntimeException(xlt('A Questionnaire repository id is required.'));
    }
    $questionnaireRecord = $questionnaireService->fetchQuestionnaireById($questionnaireRecordId);
    if ($questionnaireRecord === [] || $nonNegativeInt($questionnaireRecord['active'] ?? null) !== 1) {
        throw new RuntimeException(xlt('The selected Questionnaire is not active or was not found.'));
    }
    $questionnaireType = $questionnaireRecord['type'] ?? null;
    if (is_string($questionnaireType) && strtolower($questionnaireType) === 'encounter') {
        throw new RuntimeException(xlt('Encounter questionnaires cannot be launched from the patient assessment workspace.'));
    }
    $questionnaireJson = is_string($questionnaireRecord['questionnaire'] ?? null)
        ? $questionnaireRecord['questionnaire']
        : '';
}

$questionnaire = json_decode($questionnaireJson, true, 512, JSON_THROW_ON_ERROR);
if (!is_array($questionnaire) || ($questionnaire['resourceType'] ?? null) !== 'Questionnaire') {
    throw new RuntimeException(xlt('The repository record does not contain a FHIR Questionnaire.'));
}

$questionnaireResponse = null;
if ($questionnaireResponseJson !== '') {
    $questionnaireResponse = json_decode($questionnaireResponseJson, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($questionnaireResponse) || ($questionnaireResponse['resourceType'] ?? null) !== 'QuestionnaireResponse') {
        throw new RuntimeException(xlt('The saved response is not a FHIR QuestionnaireResponse.'));
    }
}
$titleValue = $questionnaire['title'] ?? $questionnaire['name'] ?? null;
$title = is_string($titleValue) && $titleValue !== '' ? $titleValue : xlt('FHIR Assessment');
$webRoot = OEGlobalsBag::getInstance()->getWebRoot();
$saveUrl = $webRoot . '/interface/forms/questionnaire_assessments/native_save.php';
$csrfToken = CsrfUtils::collectCsrfToken(session: $session);
$questionnaireForJs = json_encode(
    $questionnaire,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
$responseForJs = json_encode(
    $questionnaireResponse,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
?>
<!doctype html>
<html>
<head>
    <title><?php echo text($title); ?></title>
    <?php Header::setupHeader(); ?>
    <?php require __DIR__ . '/openemr_questionnaire_components.php'; ?>
</head>
<body class="bg-light">
<div class="container-fluid py-2">
    <div id="native-assessment-alert" class="d-none" role="alert"></div>
    <div id="formContainer" class="bg-light text-dark"></div>
</div>
<script>
    const questionnaire = <?php echo $questionnaireForJs ?: 'null'; ?>;
    const questionnaireResponse = <?php echo $responseForJs ?: 'null'; ?>;
    const questionnaireRecordId = <?php echo json_encode($questionnaireRecordId, JSON_THROW_ON_ERROR); ?>;
    const responseId = <?php echo js_escape($responseId); ?>;
    const saveUrl = <?php echo js_escape($saveUrl); ?>;
    const csrfToken = <?php echo js_escape($csrfToken); ?>;
    const alertElement = document.getElementById('native-assessment-alert');

    function showAssessmentAlert(message, type = 'danger') {
        alertElement.className = 'alert alert-' + type;
        alertElement.textContent = message;
    }

    function clearAssessmentAlert() {
        alertElement.className = 'd-none';
        alertElement.textContent = '';
    }

    const runtime = OpenEMRQuestionnaire.mount({
        questionnaire,
        questionnaireResponse,
        container: document.getElementById('formContainer'),
        options: {
            questionLayout: 'vertical',
        },
    });

    window.addEventListener('message', async (event) => {
        if (event.origin !== window.location.origin || event.data?.submitForm !== true) {
            return;
        }

        clearAssessmentAlert();
        const validation = runtime.validate();
        if (!validation.valid) {
            const message = validation.issues.map((issue) => issue.message).join(' ');
            showAssessmentAlert(message || <?php echo xlj('Form validation failed.'); ?>);
            parent.postMessage({assessmentValidationFailed: true}, window.location.origin);
            return;
        }

        const data = new FormData();
        data.append('csrf_token_form', csrfToken);
        data.append('questionnaire_id', String(questionnaireRecordId));
        data.append('response_id', responseId);
        data.append('questionnaire_response', JSON.stringify(runtime.getQuestionnaireResponse()));

        try {
            top.restoreSession();
            const response = await fetch(saveUrl, {
                method: 'POST',
                body: data,
                credentials: 'same-origin',
            });
            const result = await response.json();
            if (!response.ok || result.success !== true) {
                throw new Error(result.message || <?php echo xlj('Assessment save failed.'); ?>);
            }

            parent.postMessage({
                assessmentSaved: true,
                responseId: result.response_id,
                recordId: result.id,
            }, window.location.origin);
        } catch (error) {
            const message = error instanceof Error ? error.message : <?php echo xlj('Assessment save failed.'); ?>;
            showAssessmentAlert(message);
            parent.postMessage({assessmentSaveFailed: true}, window.location.origin);
        }
    });
</script>
</body>
</html>
