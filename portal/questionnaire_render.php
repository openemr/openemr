<?php

/**
 * FHIR Questionnaire import and preview dialog.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022-2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Core\Header;
use OpenEMR\Services\QuestionnaireService;

// block of code to securely support use by the patient portal
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../vendor/autoload.php");
$patientPortalSession = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($patientPortalSession) {
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . "/../interface/globals.php");

// Keep the legacy request access count stable until the portal file baseline is retired separately.
$mode = $_REQUEST['mode'] ?? '';
$q = $_REQUEST['qId'] ?? null;
$q_file = $_REQUEST['importFile'] ?? null;
$q_name = $_REQUEST['name'] ?? null;
$questionnaireJson = '';

if (!empty($q)) {
    $templateService = new QuestionnaireService();
    $resource = $templateService->fetchQuestionnaireResource($q, $q);
    // Only surface active repository questionnaires through the qId path. fetchQuestionnaireResource()
    // is a shared lookup with no active filter, so without this gate a portal (or any) caller could
    // enumerate the repository, including inactive/draft records, by iterating qId values.
    $activeFlag = $resource['active'] ?? null;
    $resourceIsActive = is_numeric($activeFlag) && (int) $activeFlag === 1;
    $questionnaireJson = ($resourceIsActive && is_string($resource['questionnaire'] ?? null))
        ? $resource['questionnaire']
        : '';
}

$initialQuestionnaireJson = json_encode(
    $questionnaireJson,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR
);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Portal'); ?> | <?php echo xlt('Questionnaire'); ?></title>
    <?php Header::setupHeader(['opener']); ?>
    <?php require __DIR__ . '/../interface/forms/questionnaire_assessments/openemr_questionnaire_components.php'; ?>
    <script>
        const initialQuestionnaireJson = <?php echo $initialQuestionnaireJson; ?>;
        let formMode = <?php echo js_escape($mode); ?>;
        let questionnaireRuntime = null;
        let questionnaireResource = null;

        function setSubtitle(message) {
            document.getElementById('subtitle').textContent = message;
        }

        function showImportAlert(message, type = 'danger') {
            const alertElement = document.getElementById('questionnaire-alert');
            alertElement.className = 'alert alert-' + type;
            alertElement.textContent = message;
        }

        function clearImportAlert() {
            const alertElement = document.getElementById('questionnaire-alert');
            alertElement.className = 'd-none';
            alertElement.textContent = '';
        }

        function setQuestionnaireReady(isReady) {
            document.querySelectorAll('.questionnaire-ready').forEach((element) => {
                element.classList.toggle('d-none', !isReady);
            });
        }

        function getOpenerField(id) {
            const field = opener?.document?.getElementById(id);
            if (!field) {
                throw new Error(xl('Questionnaire import form is no longer available.'));
            }
            return field;
        }

        function normalizeQuestionnaireJson(content) {
            let resource;
            try {
                resource = JSON.parse(content);
            } catch (error) {
                throw new Error(xl('Questionnaire JSON is invalid.'));
            }

            if (!resource || resource.resourceType !== 'Questionnaire') {
                throw new Error(
                    xl('Only FHIR Questionnaire JSON can be imported by the OpenEMR Questionnaire Runtime.')
                );
            }

            return resource;
        }

        function renderQuestionnaireJson(content, displayMessage = true) {
            clearImportAlert();
            document.getElementById('response-panel').classList.add('d-none');

            try {
                const resource = normalizeQuestionnaireJson(content);
                const normalizedJson = JSON.stringify(resource);
                const questionnaireField = getOpenerField('questionnaire');

                questionnaireField.value = normalizedJson;

                questionnaireResource = resource;
                questionnaireRuntime = OpenEMRQuestionnaire.mount({
                    questionnaire: resource,
                    container: document.getElementById('formContainer'),
                    options: {
                        questionLayout: 'vertical',
                    },
                });

                setQuestionnaireReady(true);
                if (displayMessage) {
                    setSubtitle(xl('Rendering FHIR Questionnaire'));
                }
                return true;
            } catch (error) {
                questionnaireRuntime = null;
                questionnaireResource = null;
                setQuestionnaireReady(false);
                document.getElementById('formContainer').replaceChildren();
                const message = error instanceof Error
                    ? error.message
                    : xl('Questionnaire import failed.');
                showImportAlert(message);
                setSubtitle(xl('Unable to render Questionnaire'));
                return false;
            }
        }

        function doCancel() {
            opener.callBackCmd = '';
            dlgclose();
        }

        function doImport() {
            if (!questionnaireRuntime || !questionnaireResource) {
                showImportAlert(xl('Render a valid FHIR Questionnaire before importing.'));
                return false;
            }

            opener.callBackCmd = 'submit';
            getOpenerField('q_mode').value = formMode || 'render_import';
            dlgclose('doImportSubmit');
            return true;
        }

        function doManualImport() {
            const content = document.getElementById('q_import').value.trim();
            if (content === '') {
                showImportAlert(xl('You must enter valid FHIR Questionnaire JSON.'));
                return false;
            }

            return renderQuestionnaireJson(content);
        }

        function renderManualImport() {
            document.getElementById('manual-import').classList.remove('d-none');
            document.querySelectorAll('.manual-render').forEach((element) => {
                element.classList.remove('d-none');
            });
            setSubtitle(xl('Paste FHIR Questionnaire JSON to preview'));
        }

        function readQuestionnaireFile() {
            clearImportAlert();
            const file = opener?.document?.getElementById('fetch_files')?.files?.item(0);
            if (!file) {
                showImportAlert(xl('No Questionnaire file was selected.'));
                return false;
            }

            const reader = new FileReader();
            reader.addEventListener('load', (event) => {
                const content = event.target?.result;
                if (typeof content !== 'string') {
                    showImportAlert(xl('Unable to read Questionnaire file.'));
                    return;
                }
                renderQuestionnaireJson(content);
            });
            reader.addEventListener('error', () => {
                showImportAlert(xl('Unable to read Questionnaire file.'));
            });
            reader.readAsText(file);
            return true;
        }

        function viewResponse() {
            if (!questionnaireRuntime) {
                showImportAlert(xl('FHIR Questionnaire runtime is not initialized.'));
                return false;
            }

            const validation = questionnaireRuntime.validate();
            const response = questionnaireRuntime.getQuestionnaireResponse();
            document.getElementById('questionnaire-response').textContent = JSON.stringify(response, null, 2);
            document.getElementById('response-panel').classList.remove('d-none');

            if (!validation.valid) {
                const message = validation.issues.map((issue) => issue.message).join(' ');
                showImportAlert(message || xl('Questionnaire response contains validation errors.'), 'warning');
            } else {
                clearImportAlert();
            }

            return true;
        }

        function initializeQuestionnaireImport() {
            setQuestionnaireReady(false);

            if (initialQuestionnaireJson !== '') {
                renderQuestionnaireJson(initialQuestionnaireJson, false);
                setSubtitle(xl('Rendering FHIR Questionnaire'));
            }

            if (formMode === 'render_import') {
                readQuestionnaireFile();
            } else if (formMode === 'render_import_manual') {
                renderManualImport();
            }
        }
    </script>
</head>
<body>
<div class="container-xl mt-2">
    <div class="my-2">
        <h3><?php echo xlt('FHIR Questionnaire'); ?><small id="subtitle" class="ml-2"></small></h3>
    </div>

    <div id="questionnaire-alert" class="d-none" role="alert"></div>

    <form id="qForm" onsubmit="return false;">
        <div id="manual-import" class="d-none mb-3">
            <label for="q_import">
                <strong><?php echo xlt('Paste FHIR Questionnaire JSON here'); ?></strong>
            </label>
            <textarea id="q_import" cols="120" rows="18" class="form-control"></textarea>
        </div>

        <div class="my-2">
            <button
                type="button"
                class="manual-render btn btn-sm btn-primary btn-save d-none"
                onclick="doManualImport()"
            ><?php echo xlt('Render'); ?></button>
            <button
                type="button"
                class="questionnaire-ready btn btn-sm btn-primary btn-save d-none"
                onclick="doImport()"
            ><?php echo xlt('Import'); ?></button>
            <button
                type="button"
                class="questionnaire-ready btn btn-sm btn-outline-primary d-none"
                onclick="viewResponse()"
            ><?php echo xlt('View Response'); ?></button>
            <button
                type="button"
                class="btn btn-sm btn-secondary btn-cancel"
                onclick="doCancel()"
            ><?php echo xlt('Cancel'); ?></button>
        </div>

        <div id="formContainer"></div>

        <div id="response-panel" class="card my-3 d-none">
            <div class="card-header font-weight-bold"><?php echo xlt('QuestionnaireResponse Preview'); ?></div>
            <div class="card-body">
                <pre id="questionnaire-response" class="mb-0"></pre>
            </div>
        </div>

        <div class="my-2">
            <button
                type="button"
                class="manual-render btn btn-sm btn-primary btn-save d-none"
                onclick="doManualImport()"
            ><?php echo xlt('Render'); ?></button>
            <button
                type="button"
                class="questionnaire-ready btn btn-sm btn-primary btn-save d-none"
                onclick="doImport()"
            ><?php echo xlt('Import'); ?></button>
            <button
                type="button"
                class="questionnaire-ready btn btn-sm btn-outline-primary d-none"
                onclick="viewResponse()"
            ><?php echo xlt('View Response'); ?></button>
            <button
                type="button"
                class="btn btn-sm btn-secondary btn-cancel"
                onclick="doCancel()"
            ><?php echo xlt('Cancel'); ?></button>
        </div>
    </form>
</div>
<script>
    window.addEventListener('DOMContentLoaded', initializeQuestionnaireImport);
</script>
</body>
</html>
