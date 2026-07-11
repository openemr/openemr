<?php

/**
 * Save or register a FHIR Questionnaire encounter assessment.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Common\Session\EncounterSessionUtil;
use OpenEMR\Common\Session\PatientSessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

require_once(__DIR__ . '/../../../vendor/autoload.php');
$isPortal = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($isPortal) {
    $ignoreAuth_onsite_portal = true;
}
$patientPortalOther = CoreFormToPortalUtility::isPatientPortalOther($_GET);

require_once(__DIR__ . '/../../globals.php');

$srcdir = OEGlobalsBag::getInstance()->getSrcDir();
$pid = PatientSessionUtil::getPid();
$encounter = EncounterSessionUtil::getEncounter();
$userauthorized = PatientSessionUtil::getUserAuthorized();

require_once($srcdir . '/api.inc.php');
require_once($srcdir . '/forms.inc.php');

$session = SessionWrapperFactory::getInstance()->getActiveSession();
CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);

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
$formIdInput = filter_input(INPUT_GET, 'form_id', FILTER_VALIDATE_INT);
$formid = is_int($formIdInput) && $formIdInput >= 0 ? $formIdInput : 0;
$modeInput = filter_input(INPUT_GET, 'mode', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
$mode = is_string($modeInput) ? $modeInput : '';
$formNameInput = filter_input(INPUT_POST, 'form_name', FILTER_UNSAFE_RAW, FILTER_REQUIRE_SCALAR);
$formName = is_string($formNameInput) ? $formNameInput : '';
$questionnaireJson = $_POST['questionnaire'] ?? '';
$questionnaireResponseJson = $_POST['questionnaire_response'] ?? '';
$responseMetaInput = $_POST['response_meta'] ?? null;
$responseMeta = is_string($responseMetaInput) ? $responseMetaInput : '';
$category = $_POST['category'] ?? null;
$qid = null;
$qrid = null;
$isRegistering = isset($_POST['save_registry']);

unset($_POST['select_item']);

if ($isPortal && $mode === 'update' && $formid > 0) {
    CoreFormToPortalUtility::confirmFormBootstrapPatient(
        $isPortal,
        $formid,
        'questionnaire_assessments',
        $nonNegativeInt($session->get('pid', 0)) ?? 0
    );
}
if (($_REQUEST['formOrigin'] ?? null) == 2) {
    $encounter = 0;
}

if ($mode !== 'new' && !$isRegistering) {
    $responseService = new QuestionnaireResponseService();
    try {
        if ($responseMeta !== '') {
            $questionnaireResponseJson = $responseService->insertResponseMetaData(
                $questionnaireResponseJson,
                $responseMeta
            );
        }

        $savedResponse = $responseService->saveQuestionnaireResponse(
            $questionnaireResponseJson,
            $pid,
            $encounter,
            null,
            null,
            $questionnaireJson,
            null,
            null,
            true
        );
        if (!is_array($savedResponse)) {
            throw new RuntimeException(xlt('QuestionnaireResponse save failed.'));
        }
        $_POST['response_id'] = $savedResponse['response_id'] ?? null;
        $qrid = $_POST['response_id'];

        if ($responseMeta === '' || ($savedResponse['new'] ?? false)) {
            $saved = $responseService->fetchQuestionnaireResponseById(
                $savedResponse['id'],
                $savedResponse['response_id'] ?? null
            );
            $responseMeta = $responseService->extractResponseMetaData(
                $saved['questionnaire_response'] ?? '',
                true
            );
            $_POST['response_meta'] = $responseMeta;
            $_POST['response_id'] = $saved['response_id'] ?? $_POST['response_id'];
            $qrid = $_POST['response_id'];
        }
    } catch (\Throwable $e) {
        ServiceContainer::getLogger()->error(
            'QuestionnaireResponse save failed; using backed up form answers.',
            ['exception' => $e]
        );
        echo '<p>' . xlt('Questionnaire Response save failed.') . '<br /><h3>'
            . xlt('Will attempt to save using backed up answers.') . '</h3></p>';
    }
}

if ($isRegistering) {
    unset($_POST['save_registry']);
    $check = sqlQuery(
        'SELECT `id` FROM `registry` WHERE `directory` = ? AND `name` = ? AND `form_foreign_id` > 0',
        ['questionnaire_assessments', $formName]
    );

    if (empty($check['id'])) {
        $questionnaireService = new QuestionnaireService();
        try {
            $formForeignId = $questionnaireService->saveQuestionnaireResource(
                $questionnaireJson,
                $formName,
                null,
                null,
                null,
                'encounter',
                is_string($category) ? $category : null
            );
            $qid = $formForeignId;
        } catch (\Throwable $e) {
            ServiceContainer::getLogger()->error(
                'Questionnaire registration failed.',
                ['exception' => $e]
            );
            die(xlt('New Questionnaire insert failed.'));
        }

        $registrySql = 'INSERT INTO `registry` SET '
            . '`name` = ?, `state` = ?, `directory` = ?, `sql_run` = ?, '
            . '`unpackaged` = ?, `category` = ?, `date` = NOW(), `form_foreign_id` = ?';
        sqlInsert(
            $registrySql,
            [$formName, 1, 'questionnaire_assessments', 1, 1, 'Questionnaires', $formForeignId]
        );
    } else {
        $msg = "<br /><br /><div><h3 style='color: red;font-weight: normal;'>"
            . xlt('Error. Form already registered! Redirecting back to form.')
            . "</h3></div><script>setTimeout(() => {history.back();}, 4000)</script>";
        die($msg);
    }

    formHeader('Redirecting....');
    formJump();
    formFooter();
}

if ($formid === 0) {
    $newid = formSubmit('form_questionnaire_assessments', $_POST, '', $userauthorized);
    addForm($encounter, $formName, $newid, 'questionnaire_assessments', $pid, $userauthorized);
    $formid = $nonNegativeInt($newid) ?? 0;
} else {
    CoreFormToPortalUtility::confirmFormBootstrapPatient(
        $isPortal,
        $formid,
        'questionnaire_assessments',
        $nonNegativeInt($session->get('pid', 0)) ?? 0
    );
    $formUpdateData = filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW);
    unset($formUpdateData['select_item'], $formUpdateData['save_registry']);
    $formUpdateData['response_id'] = $qrid;
    $formUpdateData['response_meta'] = $responseMeta;
    formUpdate('form_questionnaire_assessments', $formUpdateData, $formid, $userauthorized);
}

if ($isPortal || $patientPortalOther) {
    echo CoreFormToPortalUtility::formQuestionnairePortalPostSave($formid, $qid, $qrid, $encounter);
} else {
    formHeader('Redirecting....');
    formJump();
    formFooter();
}
