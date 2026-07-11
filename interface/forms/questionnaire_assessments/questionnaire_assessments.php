<?php

/**
 * FHIR Questionnaire encounter assessment form.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Acl\AccessDeniedHelper;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

// Need access to classes before globals.php so portal session context can be resolved.
require_once(__DIR__ . '/../../../vendor/autoload.php');
$isPortal = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($isPortal) {
    $ignoreAuth_onsite_portal = true;
}
$patientPortalOther = CoreFormToPortalUtility::isPatientPortalOther($_GET);

require_once(__DIR__ . '/../../globals.php');

$srcdir = OEGlobalsBag::getInstance()->getSrcDir();
$rootdir = OEGlobalsBag::getInstance()->getString('rootdir');

require_once($srcdir . '/api.inc.php');
require_once($srcdir . '/user.inc.php');
require_once($srcdir . '/options.inc.php');

$questionnaireService = new QuestionnaireService();
$responseService = new QuestionnaireResponseService();
$session = SessionWrapperFactory::getInstance()->getActiveSession();
$scalarString = static function (mixed $value, string $default = ''): string {
    if (is_string($value)) {
        return $value;
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    return $default;
};
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
$questionnaireForm = $_GET['questionnaire_form'] ?? null;
$repositoryItem = $_POST['select_item'] ?? null;
$isModule = ($_REQUEST['formOrigin'] ?? null) == 2;
$isDashboard = ($_REQUEST['formOrigin'] ?? null) == 1;
$mode = $scalarString($mode ?? 'new', 'new');
$formid = $nonNegativeInt($form_id ?? 0) ?? 0;
$form = [];
$q = [];
$qList = [];
$questionnaireJson = '';
$questionnaireResponseJson = '';
$formName = '';
$category = 'survey';
$copyright = '';

if ($isModule || $isPortal) {
    $questionnaireForm = $_GET['formname'] ?? $questionnaireForm;
}

$formDirectory = $scalarString($_GET['formname'] ?? null, 'questionnaire_assessments');
if (!AclMain::aclCheckForm($formDirectory)) {
    $formLabel = xl_form_title(getRegistryEntryByDirectory($formDirectory, 'name')['name'] ?? '');
    $formLabel = $formLabel !== '' ? $scalarString($formLabel) : $formDirectory;
    AccessDeniedHelper::denyWithTemplate('ACL check failed for form: ' . $formLabel, $formLabel);
}

try {
    if (!empty($_GET['id'])) {
        $mode = 'update';
        $formid = $nonNegativeInt($_GET['id']) ?? 0;

        if (($_REQUEST['formOrigin'] ?? null) == 1 && !$isPortal) {
            $fetchedForm = sqlQuery(
                'SELECT * FROM `form_questionnaire_assessments` WHERE `id` = ? AND `activity` = 1 LIMIT 1',
                [$formid]
            );
            $form = $fetchedForm;
        } else {
            $fetchedForm = formFetch('form_questionnaire_assessments', $formid);
            $form = $fetchedForm;
        }

        if ($form === []) {
            throw new RuntimeException(xlt('Can not find encounter form.'));
        }

        CoreFormToPortalUtility::confirmFormBootstrapPatient(
            $isPortal,
            $formid,
            'questionnaire_assessments',
            $nonNegativeInt($session->get('pid', 0)) ?? 0
        );

        $questionnaireJson = is_string($form['questionnaire'] ?? null)
            ? $form['questionnaire']
            : '';
        $questionnaireResponseJson = is_string($form['questionnaire_response'] ?? null)
            ? $form['questionnaire_response']
            : '';

        $responseId = is_string($form['response_id'] ?? null) ? $form['response_id'] : '';
        if ($responseId !== '') {
            $response = $responseService->fetchQuestionnaireResponseByResponseId($responseId);
            if ($response !== []) {
                $questionnaireResponseJson = is_string($response['questionnaire_response'] ?? null)
                    ? $response['questionnaire_response']
                    : $questionnaireResponseJson;
                $form['response_id'] = $response['response_id'] ?? $responseId;
            }
        }

        $formName = $scalarString($form['form_name'] ?? null);
        $category = $scalarString($form['category'] ?? null, 'survey');
        $copyright = $scalarString($form['copyright'] ?? null);
    }

    if (empty($formid) && !empty($questionnaireForm) && $questionnaireForm !== 'New Questionnaire') {
        if ($isPortal || $isModule) {
            $questionnaireId = $nonNegativeInt($questionnaireForm);
            $fetchedQuestionnaire = $questionnaireId !== null
                ? $questionnaireService->fetchQuestionnaireById($questionnaireId)
                : $questionnaireService->fetchQuestionnaireResource($scalarString($questionnaireForm));
        } else {
            $fetchedQuestionnaire = $questionnaireService->fetchEncounterQuestionnaireForm($questionnaireForm);
        }
        $q = $fetchedQuestionnaire;

        $questionnaireJson = is_string($q['questionnaire'] ?? null) ? $q['questionnaire'] : '';
        $mode = 'new_form';
        $formName = $scalarString($q['name'] ?? null);
        $category = $scalarString($q['category'] ?? null, 'survey');
    }

    $repositoryItemId = $nonNegativeInt($repositoryItem);
    if ($repositoryItemId !== null && $repositoryItemId > 0 && $questionnaireForm === 'New Questionnaire') {
        $fetchedQuestionnaire = $questionnaireService->fetchQuestionnaireById($repositoryItemId);
        $q = $fetchedQuestionnaire;
        $questionnaireJson = is_string($q['questionnaire'] ?? null) ? $q['questionnaire'] : '';
        $formName = $scalarString($q['name'] ?? null);
        $category = $scalarString($q['category'] ?? null, 'survey');
        $mode = 'new_repository_form';
    }

    if ($questionnaireForm === 'New Questionnaire' && $mode === 'new') {
        $qList = $questionnaireService->getQuestionnaireList(true);
    }

    $questionnaire = null;
    if ($questionnaireJson !== '') {
        $questionnaire = json_decode($questionnaireJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($questionnaire) || ($questionnaire['resourceType'] ?? null) !== 'Questionnaire') {
            throw new RuntimeException(xlt('The selected repository record does not contain a FHIR Questionnaire.'));
        }
        $copyright = $copyright !== '' ? $copyright : $scalarString($questionnaire['copyright'] ?? null);
    }

    $questionnaireResponse = null;
    if ($questionnaireResponseJson !== '') {
        $questionnaireResponse = json_decode($questionnaireResponseJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($questionnaireResponse) || ($questionnaireResponse['resourceType'] ?? null) !== 'QuestionnaireResponse') {
            throw new RuntimeException(xlt('The saved response is not a FHIR QuestionnaireResponse.'));
        }
    }

    if ($mode !== 'new' && $questionnaire === null) {
        throw new RuntimeException(xlt('Unable to find FHIR Questionnaire data for this form.'));
    }
} catch (\Throwable $e) {
    ServiceContainer::getLogger()->error(
        'Questionnaire assessment load failed.',
        ['exception' => $e]
    );
    die("<p class='text-danger h5 m-3'>" . xlt('Can not continue') . '</p>');
}

$topNote = true;
$bottomNote = false;
$loincText = "<span class='font-weight-bold bg-light text-dark'>" . xlt('Important to Note') . ': </span><i>'
    . xlt('LOINC form definitions are subject to the LOINC')
    . " <a href='http://loinc.org/terms-of-use' target='_blank'> "
    . xlt('terms of use.') . '</a></i>';

switch ($scalarString(OEGlobalsBag::getInstance()->get('questionnaire_display_LOINCnote'), '0')) {
    case '1':
        $topNote = false;
        $bottomNote = true;
        break;
    case '2':
        $topNote = true;
        $bottomNote = true;
        break;
    case '3':
        $topNote = false;
        $bottomNote = false;
        break;
}

if ($isPortal) {
    $theme = stripos($scalarString(OEGlobalsBag::getInstance()->get('portal_css_header')), 'dark') !== false ? 'dark' : 'light';
} else {
    $theme = stripos(OEGlobalsBag::getInstance()->getString('css_header'), 'dark') !== false ? 'dark' : 'light';
}
if ((OEGlobalsBag::getInstance()->get('questionnaire_display_style') ?? 0) == 3) {
    $theme = 'light';
} elseif ((OEGlobalsBag::getInstance()->get('questionnaire_display_style') ?? 0) == 4) {
    $theme = 'dark';
}

if ($isModule || $isDashboard || $isPortal) {
    $container = 'container-fluid';
} elseif (OEGlobalsBag::getInstance()->getBoolean('questionnaire_display_fullscreen')) {
    $container = 'container';
} else {
    $container = 'container-fluid';
}

$questionnaireForJs = json_encode(
    $questionnaire,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
$responseForJs = json_encode(
    $questionnaireResponse,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
);
$formAction = $rootdir . '/forms/questionnaire_assessments/save.php?form_id=' . urlencode((string) $formid);
if ($isPortal) {
    $formAction .= '&isPortal=1';
}
if ($patientPortalOther) {
    $formAction .= '&formOrigin=' . urlencode($scalarString($_GET['formOrigin'] ?? null));
}
$formAction .= '&mode=' . urlencode($mode);
?>
<!DOCTYPE html>
<html>
<head>
    <title id="main_title"><?php echo xlt('Questionnaire'); ?></title>
    <?php Header::setupHeader(['select2', 'bootstrap']); ?>
    <?php require __DIR__ . '/openemr_questionnaire_components.php'; ?>
    <script>
        const questionnaire = <?php echo $questionnaireForJs ?: 'null'; ?>;
        const questionnaireResponse = <?php echo $responseForJs ?: 'null'; ?>;
        const isPortal = <?php echo js_escape((int)$isPortal); ?>;
        const portalOther = <?php echo js_escape((int)$patientPortalOther); ?>;
        let formMode = <?php echo js_escape($mode); ?>;
        let questionnaireRuntime = null;

        function initSelect() {
            const select = $('.select-dropdown');
            select.select2({
                multiple: false,
                placeholder: xl('Type to search.'),
                theme: 'bootstrap4',
                dropdownAutoWidth: true,
                width: 'resolve',
                closeOnSelect: true,
                <?php require($srcdir . '/js/xl/select2.js.php'); ?>
            });
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field')?.focus();
            });
            select.on('change', function () {
                top.restoreSession();
                const data = $('#select_item').select2('data');
                if (data?.[0]) {
                    document.getElementById('form_name').value = data[0].text;
                }
                document.qa_form.action = '#';
                document.qa_form.submit();
            });
        }

        function initQuestionnaire() {
            if (!questionnaire) {
                return;
            }
            questionnaireRuntime = OpenEMRQuestionnaire.mount({
                questionnaire,
                questionnaireResponse,
                container: document.getElementById('formContainer'),
                options: {
                    questionLayout: 'vertical',
                },
            });
        }

        function saveQR() {
            if (!isPortal) {
                top.restoreSession();
            }
            if (formMode === 'register' || formMode === 'new') {
                return true;
            }
            if (!questionnaireRuntime) {
                asyncAlertMsg(<?php echo xlj('FHIR Questionnaire runtime is not initialized.'); ?>);
                return false;
            }

            const validation = questionnaireRuntime.validate();
            if (!validation.valid) {
                const message = validation.issues.map((issue) => issue.message).join('<br />');
                dialog.alert(
                    "<span class='h5'>" + xl('Form failed validation!') + '</span><br />' + jsText(message)
                ).then(() => dialog.close());
                return false;
            }

            document.getElementById('questionnaire_response').value = JSON.stringify(
                questionnaireRuntime.getQuestionnaireResponse()
            );
            return true;
        }

        function initPage() {
            if (formMode === 'new') {
                initSelect();
                document.querySelectorAll('.isNew').forEach((element) => element.classList.remove('d-none'));
                return;
            }

            initQuestionnaire();
            document.querySelectorAll('.doCancel').forEach((element) => element.classList.remove('d-none'));
            if (formMode === 'new_repository_form') {
                document.querySelectorAll('.repositoryAction').forEach((element) => element.classList.remove('d-none'));
            }
        }
    </script>
</head>
<body class="bg-light" data-theme="<?php echo attr($theme); ?>">
<div class="<?php echo attr($container); ?>">
    <?php if (!$isPortal && !$isModule && !$isDashboard) { ?>
        <div class="title bg-light text-dark">
            <h4>
                <?php echo $mode === 'new' || $mode === 'new_repository_form'
                    ? xlt('Create Encounter Questionnaires')
                    : xlt('Edit Questionnaire'); ?>
            </h4>
        </div>
    <?php } ?>

    <form
        class="form"
        method="post"
        id="qa_form"
        name="qa_form"
        onsubmit="return saveQR()"
        action="<?php echo attr($formAction); ?>"
    >
        <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
        <input type="hidden" id="lform" name="lform" value="" />
        <input type="hidden" id="lform_response" name="lform_response" value="" />
        <input type="hidden" id="response_id" name="response_id" value="<?php echo attr($form['response_id'] ?? ''); ?>" />
        <input type="hidden" id="response_meta" name="response_meta" value="<?php echo attr($form['response_meta'] ?? ''); ?>" />
        <input type="hidden" id="copyright" name="copyright" value="<?php echo attr($copyright); ?>" />
        <input type="hidden" id="questionnaire" name="questionnaire" value="<?php echo attr($questionnaireJson); ?>" />
        <input
            type="hidden"
            id="questionnaire_response"
            name="questionnaire_response"
            value="<?php echo attr($questionnaireResponseJson); ?>"
        />

        <?php if ($topNote && !$isPortal) { ?>
            <div>
                <p class="text-center bg-light text-dark"><?php echo $loincText; ?></p>
                <?php if ($copyright !== '') { ?>
                    <p id="copyrightNotice"><?php echo text($copyright); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="mb-3">
            <div class="input-group isNew d-none">
                <label for="category" class="font-weight-bold mt-2 mr-1"><?php echo xlt('Category'); ?>:</label>
                <?php echo generate_select_list('category', 'Observation_Types', $category, '', 'Unassigned', 'form-control-sm'); ?>
            </div>
            <div class="input-group isNew d-none mt-2">
                <label for="select_item" class="font-weight-bold my-2 mr-1">
                    <?php echo xlt('Select new from Questionnaire Repository'); ?>:
                </label>
                <select
                    class="select-dropdown my-2"
                    id="select_item"
                    name="select_item"
                    autocomplete="off"
                    role="combobox"
                    aria-expanded="false"
                >
                    <option value=""></option>
                    <?php foreach ($qList as $item) {
                        if (!is_array($item)) {
                            continue;
                        }
                        $id = $nonNegativeInt($item['id']) ?? 0;
                        if ($id < 1) {
                            continue;
                        }
                        $selected = ((string) $id === $scalarString($repositoryItem)) ? ' selected' : '';
                        echo '<option value="' . attr((string) $id) . '"' . $selected . '>'
                            . text($scalarString($item['name'] ?? null)) . '</option>';
                    } ?>
                </select>
            </div>
            <div class="input-group <?php echo $mode === 'new' ? 'isNew d-none' : ''; ?>">
                <label class="font-weight-bold my-2" for="form_name"><?php echo xlt('Form Name'); ?>:</label>
                <input
                    required
                    type="text"
                    class="form-control skip-template-editor ml-1"
                    id="form_name"
                    name="form_name"
                    title="<?php echo xla('You may edit name to shorten to be more understandable.'); ?>"
                    value="<?php echo attr($formName); ?>"
                />
            </div>
        </div>

        <?php if ($mode !== 'new') { ?>
            <hr />
            <?php if (!$isPortal && !$patientPortalOther) { ?>
                <div class="btn-group my-2">
                    <button type="submit" class="btn btn-primary btn-save"><?php echo xlt('Save Current'); ?></button>
                    <button
                        type="submit"
                        class="btn btn-primary repositoryAction d-none"
                        id="save_registry_top"
                        name="save_registry"
                        onclick="formMode = 'register'"
                    ><?php echo xlt('or Register New'); ?></button>
                    <button
                        type="button"
                        class="btn btn-secondary btn-cancel doCancel d-none"
                        onclick="parent.closeTab(window.name, false)"
                    ><?php echo xlt('Cancel'); ?></button>
                </div>
            <?php } ?>
            <div class="bg-light text-dark" id="formContainer"></div>
            <?php if ($bottomNote && !$isPortal) { ?>
                <div><p class="bg-light text-dark text-center"><?php echo $loincText; ?></p></div>
            <?php } ?>
            <?php if (!$isPortal && !$patientPortalOther) { ?>
                <div class="btn-group my-2">
                    <button type="submit" class="btn btn-primary btn-save"><?php echo xlt('Save Current'); ?></button>
                    <button
                        type="submit"
                        class="btn btn-primary repositoryAction d-none"
                        id="save_registry"
                        name="save_registry"
                        onclick="formMode = 'register'"
                    ><?php echo xlt('or Register New'); ?></button>
                    <button
                        type="button"
                        class="btn btn-secondary btn-cancel doCancel d-none"
                        onclick="parent.closeTab(window.name, false)"
                    ><?php echo xlt('Cancel'); ?></button>
                </div>
            <?php } ?>
        <?php } ?>
    </form>
</div>

<script>
    <?php if ($isPortal || $patientPortalOther) { ?>
    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin) {
            asyncAlertMsg(<?php echo xlj('Request is not same origin!'); ?>, 15000);
            return;
        }
        if (event.data?.submitForm === true) {
            if (saveQR()) {
                event.preventDefault();
                document.forms[0].submit();
            } else {
                asyncAlertMsg(<?php echo xlj('Form validation failed.'); ?>);
            }
        }
    });
    <?php }
    if ($mode === 'update' && $patientPortalOther && !empty($formid)) { ?>
    parent.postMessage({formid: <?php echo js_escape((string) $formid); ?>}, window.location.origin);
    <?php } ?>

    window.addEventListener('DOMContentLoaded', initPage);
</script>
</body>
</html>
