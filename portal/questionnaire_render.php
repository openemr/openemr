<?php

/**
 * Questionnaire Template
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../src/Common/Forms/CoreFormToPortalUtility.php");

use OpenEMR\Common\Forms\CoreFormToPortalUtility;
use OpenEMR\Core\Header;
use OpenEMR\Services\QuestionnaireService;

// block of code to securely support use by the patient portal
$patientPortalSession = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($patientPortalSession) {
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . "/../interface/globals.php");

$mode = $_REQUEST['mode'] ?? '';
$q = $_REQUEST['qId'] ?? null;
$q_file = $_REQUEST['importFile'] ?? null;
$q_name = $_REQUEST['name'] ?? null;

if (!empty($q)) {
    $templateService = new QuestionnaireService();
    $resource = $templateService->fetchQuestionnaireResource($q, $q);
    $q_json = $resource['questionnaire'];
    $lform = $resource['lform'];
}

?>
<html>
<head>
    <title><?php echo xlt('Portal'); ?> | <?php echo xlt('Questionnaire'); ?></title>
    <?php Header::setupHeader(['opener']); ?>
    <script>
        let lform, qform;
        let baseMsg = xl('Convert and verify import');

        function renderForm(type) {
            if (type === 'lForm') {
                LForms.Util.addFormToPage(lform, 'formContainer');
                baseMsg = xl('Rendering LHC Form');
            } else {
                LForms.Util.addFormToPage(qform, 'formContainer');
                baseMsg = xl('Rendering Questionnaire');
            }
            document.getElementById('subtitle').innerHTML = baseMsg;
        }

        function doCancel() {
            opener.callBackCmd = '';
            dlgclose();
        }

        function doImport() {
            opener.callBackCmd = 'submit';
            opener.document.getElementById('q_mode').value = formMode;
            dlgclose('doImportSubmit');
        }

        function doManualImport() {
            $('.isRender').toggleClass('d-none');
            let content = document.getElementById('q_import').value;
            if (content.length > 80) {
                typeAndConvert(content);
            } else {
                $('.isRender').toggleClass('d-none');
                alertMsg(xl("You must enter valid form json."), 5000, 'danger', false);
            }
        }

        function renderManualImport() {
            $('.isManual').toggleClass('d-none');
        }

        // convert import to all definitions available
        // store in parent upload form
        let gotQ = false;
        let gotL = false;
        function typeAndConvert(file, displayMsg = true) {
            let obj = JSON.parse(file);
            // reformat file to get rid of lf
            file = JSON.stringify(obj)
            if (obj && obj.resourceType === "Questionnaire") {
                gotQ = true;
            } else if (obj) {
                gotL = true;
            }
            if (gotL) {
                // convert to questionnaire
                qform = LForms.Util.getFormFHIRData("Questionnaire", 'R4', obj);
                lform = file;
                opener.document.getElementById('questionnaire').value = JSON.stringify(qform);
                opener.document.getElementById('lform').value = file;
                baseMsg += ' ' + xl('Rendering LHC-Form');
                LForms.Util.addFormToPage(opener.document.getElementById('lform').value, 'formContainer');
            } else if (gotQ) {
                // convert to lform
                lform = LForms.Util.convertFHIRQuestionnaireToLForms(obj, 'R4');
                qform = file;
                opener.document.getElementById('lform').value = JSON.stringify(lform);
                opener.document.getElementById('questionnaire').value = file;
                baseMsg += ' ' + xl('Rendering Questionnaire');
                LForms.Util.addFormToPage(opener.document.getElementById('questionnaire').value, 'formContainer');
            } else {
                alert(xl('Error! Import conversion failed.'));
                return false;
            }

            if (displayMsg) {
                document.getElementById('subtitle').innerHTML = baseMsg;
            }
        }

        function readQuestionnaireFile() {
            // grab first file from parent
            const file = opener.document.getElementById("fetch_files").files.item(0)
            if (file) {
                let reader = new FileReader();
                reader.readAsText(file);
                reader.addEventListener('load', (evt) => {
                    return typeAndConvert(evt.target.result);
                });
                reader.addEventListener('onerror', (evt) => {
                    alert("Error reading file");
                    return false;
                });
            }
        }

        function saveQR() {
            let qr = LForms.Util.getFormFHIRData('QuestionnaireResponse', 'R4');
            let formElement = opener.document.getElementById("formContainer");
            let data = LForms.Util.getUserData(formElement, true, true, true);
            window.alert(JSON.stringify(data, null, 2));
            window.alert(JSON.stringify(qr, null, 2));
            return false;
        }
    </script>
</head>
<body>
    <div class="container-xl mt-2">
        <div class="my-2">
            <h3><?php echo xlt("FHIR Questionnaire"); ?><small id="subtitle" class="ml-2"></small></h3>
        </div>
        <form id="qForm">
            <div class="isManual isRender d-none">
                <label for="q_import"><strong><?php echo xlt("To manually import paste json here"); ?></strong></label>
                <textarea id="q_import" cols="120" rows="20" class="form-control"></textarea>
            </div>
            <div class="my-2">
                <button type="button" class="isManual btn btn-sm btn-primary btn-save d-none" onclick="doManualImport()"><?php echo xlt("Render"); ?></button>
                <button type="button" class="isManual isRender btn btn-sm btn-primary btn-save" onclick="doImport()"><?php echo xlt("Import"); ?></button>
                <div class="btn-group">
                    <button type="button" class="isManual isRender btn btn-sm btn-success" onclick="renderForm('lForm')"><?php echo xlt("LHC Form Version"); ?></button>
                    <button type="button" class="isManual isRender btn btn-sm btn-success" onclick="renderForm('qForm')"><?php echo xlt("Questionnaire Version"); ?></button>
                </div>
                <button type="button" class="isManual isRender btn btn-sm btn-primary btn-save" onclick="saveQR()"><?php echo xlt("View Response"); ?></button>
                <button type="button" class="btn btn-sm btn-secondary btn-cancel" onclick="doCancel()"><?php echo xlt("Cancel"); ?></button>
            </div>
            <div id=formContainer></div>
            <div class="isManual isRender d-none">
                <label for="q_import"><strong><?php echo xlt("To manually import paste json here"); ?></strong></label>
                <textarea id="q_import" cols="120" rows="20" class="form-control"></textarea>
            </div>
            <div class="my-2">
                <button type="button" class="isManual btn btn-sm btn-primary btn-save d-none" onclick="doManualImport()"><?php echo xlt("Render"); ?></button>
                <button type="button" class="isManual isRender btn btn-sm btn-primary btn-save" onclick="doImport()"><?php echo xlt("Import"); ?></button>
                <div class="btn-group">
                    <button type="button" class="isManual isRender btn btn-sm btn-success" onclick="renderForm('lForm')"><?php echo xlt("LHC Form Version"); ?></button>
                    <button type="button" class="isManual isRender btn btn-sm btn-success" onclick="renderForm('qForm')"><?php echo xlt("Questionnaire Version"); ?></button>
                </div>
                <button type="button" class="isManual isRender btn btn-sm btn-primary btn-save" onclick="saveQR()"><?php echo xlt("View Response"); ?></button>
                <button type="button" class="btn btn-sm btn-secondary btn-cancel" onclick="doCancel()"><?php echo xlt("Cancel"); ?></button>
            </div>
        </form>
    </div>
    <?php require(__DIR__ . "/../interface/forms/questionnaire_assessments/lform_webcomponents.php") ?>
</body>
<script>
    let formMode = <?php echo js_escape($mode); ?>;
    <?php if ($mode == 'render_import') { ?>
    window.onload = readQuestionnaireFile();
    <?php } elseif ($mode == 'render_import_manual') { ?>
    window.onload = renderManualImport();
    <?php } ?>
</script>
</html>
