<?php

/**
 * Questionnaire Assessment Encounters Template
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2022 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../../../src/Common/Forms/CoreFormToPortalUtility.php");

use OpenEMR\Common\Forms\CoreFormToPortalUtility;

// block of code to securely support use by the patient portal
$isPortal = CoreFormToPortalUtility::isPatientPortalSession($_GET);
if ($isPortal) {
    $ignoreAuth_onsite_portal = true;
}
$patientPortalOther = CoreFormToPortalUtility::isPatientPortalOther($_GET);

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/user.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\QuestionnaireResponseService;
use OpenEMR\Services\QuestionnaireService;

$service = new QuestionnaireService();
$responseService = new QuestionnaireResponseService();
$questionnaire_form = $_GET['questionnaire_form'] ?? null;
$repository_item = $_POST['select_item'] ?? null;

if ($isPortal) {
    $questionnaire_form = $_GET['formname'] ?? null;
}
// for new questionnaires user must be admin. leave strict conditional.
$isAdmin = AclMain::aclCheckCore('admin', 'forms');
$is_authorized = $isAdmin || ($questionnaire_form !== 'New Questionnaire' && $_GET['formname'] ?? null === 'questionnaire_assessments');

// General error trap. Echo and die.
try {
    if (!empty($_GET['id'] ?? 0)) {
        $mode = 'update';
        $formid = $_GET['id'];
        $form = formFetch("form_questionnaire_assessments", $formid);
        CoreFormToPortalUtility::confirmFormBootstrapPatient($isPortal, $formid, 'questionnaire_assessments', $_SESSION['pid']);
        $qr = $responseService->fetchQuestionnaireResponse(null, $form["response_id"]);
        // if empty form will revert to the backup response stored with form.
        if (!empty($qr)) {
            // This is primary response.
            $form['questionnaire_response'] = $qr['questionnaire_response'];
            $form['response_id'] = $qr['response_id'];
        }
    }
    $q_json = '';
    $lform = '';
    $form_name = '';

    if (empty($formid) && !empty($questionnaire_form) && $questionnaire_form != 'New Questionnaire') {
        // since we are here then user is authorized for a pre-approved questionnaire form.
        $is_authorized = true;
        if ($isPortal) {
            if (is_numeric($questionnaire_form)) {
                $q = $service->fetchQuestionnaireById((int)$questionnaire_form);
            } else {
                $q = $service->fetchQuestionnaireResource($questionnaire_form);
            }
        } else {
            $q = $service->fetchEncounterQuestionnaireForm($questionnaire_form);
        }
        $q_json = $q['questionnaire'] ?: '';
        $lform = $q['lform'] ?: '';
        $mode = 'new_form';
        $form_name = $q['name'] ?: '';
    }
// This is for newly selected questionnaire from repository dropdown.
    if (!empty($repository_item) && $questionnaire_form == 'New Questionnaire') {
        $q = $service->fetchQuestionnaireById($repository_item);
        $q_json = $q['questionnaire'] ?: '';
        $lform = $q['lform'] ?: '';
        $form_name = $q['name'] ?: '';
        $mode = 'new_repository_form';
    }

    if (!$isPortal) {
        $do_warning = checkUserSetting('disable_form_disclaimer', '1') === true ? 0 : 1;
    }
    if ($questionnaire_form == 'New Questionnaire') {
        $q_list = $service->getQuestionnaireList(true);
    }
} catch (Exception $e) {
    die(xlt("Can not continue with reason.") . '<br />' . text($e->getMessage()));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title id="main_title"><?php echo xlt('Questionnaire'); ?></title>
    <?php Header::setupHeader(); ?>
    <!--<link href="<?php /*echo $GLOBALS['assets_static_relative']; */?>/lforms/webcomponent/styles.css" media="screen" rel="stylesheet" />-->
    <!-- TODO remove next release -->
    <script>
        let isPortal = <?php echo js_escape($isPortal); ?>;
        let portalOther = <?php echo js_escape($patientPortalOther); ?>;

        function initSelect() {
            let ourSelect = $('.select-dropdown');
            ourSelect.select2({
                multiple: false,
                placeholder: xl('Type to search.'),
                theme: 'bootstrap4',
                dropdownAutoWidth: true,
                width: 'resolve',
                closeOnSelect: true,
                <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
            });
            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });
            ourSelect.on("change", function (e) {
                top.restoreSession();
                let data = $('#select_item').select2('data');
                if (data) {
                    document.getElementById('form_name').value = data[0].text;
                }
                document.qa_form.action = "#";
                document.qa_form.submit();
            });
        }

        let formOptions = {
            questionLayout: "vertical",
            hideTreeLine: true
        };

        function saveQR() {
            if (!isPortal) {
                top.restoreSession();
            }
            if (formMode == 'register') {
                return true;
            }
            let formElement = document.getElementById("formContainer");
            let notValid = LForms.Util.checkValidity(formElement);
            if (notValid) {
                notValid = "<span class='font-weight-bold p-2'>" + jsText(notValid) + "</span>";
                let error = notValid.replace(/requires a value,/g, "requires a value,<br />");
                error = error.replace(/requires a value/g, "<span class='text-danger'>requires a value</span>")
                let formatText = "<span class='h5'>" + xl('Form failed validation!') + "</span><br />" + error;
                dialog.alert(formatText).then(returned => {
                    dialog.close();
                    return false;
                });
            } else {
                let qr = LForms.Util.getFormFHIRData('QuestionnaireResponse', 'R4');
                let data = LForms.Util.getUserData(formElement, false, true, true);
                document.getElementById('lform_response').value = JSON.stringify(data);
                document.getElementById('questionnaire_response').value = JSON.stringify(qr);
                if (!document.getElementById('questionnaire').value) {
                    let lForm = JSON.parse(document.getElementById('lform').value);
                    let qFhir = LForms.Util.getFormFHIRData("Questionnaire", 'R4', data);
                    document.getElementById('questionnaire').value = JSON.stringify(qFhir);
                }
                return true;
            }
            return false;
        }

        function initUpdate() {
            // Merge QuestionnaireResponse
            let lForm = null;
            let qFhir = null;
            if (document.getElementById('lform').value) {
                lForm = JSON.parse(document.getElementById('lform').value);
            }
            if (document.getElementById('questionnaire').value) {
                qFhir = document.getElementById('questionnaire').value;
            }
            let qr = document.getElementById('questionnaire_response').value;
            let qResponse;
            let responseData;
            if (!lForm && qFhir > '') {
                let qData = JSON.parse(qFhir);
                lForm = LForms.Util.convertFHIRQuestionnaireToLForms(qData, 'R4');
                document.getElementById('lform').value = JSON.stringify(lForm);
            }
            if (qr > '') {
                qResponse = JSON.parse(qr);
                responseData = LForms.Util.mergeFHIRDataIntoLForms("QuestionnaireResponse", qResponse, lForm, "R4");
            } else {
                alert(xl('Form response data missing or corrupt. Resetting form.'))
            }

            if (responseData > '') {
                LForms.Util.addFormToPage(responseData, 'formContainer', formOptions);
            } else {
                LForms.Util.addFormToPage(lForm, 'formContainer', formOptions);
            }
        }

        function initNewForm(flag = false) {
            let lform = <?php echo js_escape($lform); ?>;
            let qFhir = <?php echo js_escape($q_json); ?>;
            let formName = <?php echo js_escape($form_name); ?>;
            let data;
            if (lform) {
                data = JSON.parse(lform);
            } else if (qFhir) {
                let qData = JSON.parse(qFhir);
                data = LForms.Util.convertFHIRQuestionnaireToLForms(qData, 'R4');
            } else {
                alert(xl('Error Missing Form.'));
                parent.closeTab(window.name, false);
            }
            document.getElementById('questionnaire').value = qFhir;
            document.getElementById('lform').value = JSON.stringify(data);
            if (!flag) {
                document.getElementById('form_name').value = jsAttr(formName);
            }
            if (typeof data.copyrightNotice !== 'undefined' && data.copyrightNotice > '') {
                document.getElementById('copyright').value = jsAttr(data.copyrightNotice);
                document.getElementById('copyrightNotice').innerHTML = jsText(data.copyrightNotice);
            }
            LForms.Util.addFormToPage(data, 'formContainer', formOptions);
        }

        function initSearch() {
            initSelect();
            <?php if ($do_warning) { ?>
            let msg = xl("OpenEMR is not responsible for any copyright and or permissions pertaining to questionnaires or assessments imported from external sources and then implemented and used by this feature.");
            msg += "<br />" + xl("Some, if not many, LOINC forms will display a copyright notice for information regarding permissions.");
            msg += "<br /><br />" + xl("Click Got it icon to dismiss this alert forever.");
            // dialog alertMsg 5th parameter will set flag to disable this user seeing message
            alertMsg(msg, 20000, 'danger', '', 'disable_form_disclaimer');
            <?php } ?>
            $(".isNew").toggleClass('d-none');
            // setup LOINC search listener
            let ac;
            ac = new LForms.Def.Autocompleter.Search(
                'loinc_item',
                'https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/search?type=form&available=true&df=text,LOINC_NUM',
                {
                    tableFormat: true, valueCols: [0, 1],
                    colHeaders: ['Text', 'LOINC Code']
                }
            );
            LForms.Def.Autocompleter.Event.observeListSelections('loinc_item', function () {
                let formCode = ac.getSelectedCodes()[0];
                if (formCode) {
                    top.restoreSession();
                    let url = "https://clinicaltables.nlm.nih.gov/loinc_form_definitions?loinc_num=" + encodeURIComponent(formCode);
                    fetch(url).then((form) => {
                        return form.json();
                    }).then((data) => {
                        let saveButton = document.getElementById('save_response');
                        let registryButton = document.getElementById('save_registry');
                        saveButton.classList.remove("d-none");
                        registryButton.classList.remove("d-none");
                        document.getElementById('form_name').value = jsAttr(data.name);
                        document.getElementById('lform').value = JSON.stringify(data);
                        if (typeof data.copyrightNotice !== 'undefined' && data.copyrightNotice > '') {
                            document.getElementById('copyright').value = jsAttr(data.copyrightNotice);
                            document.getElementById('copyrightNotice').innerHTML = jsText(data.copyrightNotice);
                        }
                        LForms.Util.addFormToPage(data, 'formContainer', formOptions);
                        return data;
                    }).then((data) => {
                        let qFhir = LForms.Util.getFormFHIRData("Questionnaire", 'R4', data);
                        document.getElementById('questionnaire').value = JSON.stringify(qFhir);
                    });
                }
            });
        }

        function initSearchForm() {
            initSelect();
            $(".isNew").toggleClass('d-none');

            document.getElementById('select_item').addEventListener('change', function () {
                let el = document.getElementById('select_item');
                document.getElementById('form_name').value = el.options[el.selectedIndex].text;
                document.qa_form.action = "#";
                document.qa_form.submit();
            });

            let ac;
            ac = new LForms.Def.Autocompleter.Search(
                'loinc_item',
                'https://clinicaltables.nlm.nih.gov/api/loinc_items/v3/search?type=form&available=true&df=text,LOINC_NUM',
                {
                    tableFormat: true, valueCols: [0, 1],
                    colHeaders: ['Text', 'LOINC Code']
                }
            );
            LForms.Def.Autocompleter.Event.observeListSelections('loinc_item', function () {
                let formCode = ac.getSelectedCodes()[0];
                if (formCode) {
                    top.restoreSession();
                    let url = "https://clinicaltables.nlm.nih.gov/loinc_form_definitions?loinc_num=" + encodeURIComponent(formCode);
                    fetch(url).then((form) => {
                        return form.json();
                    }).then((data) => {
                        let saveButton = document.getElementById('save_response');
                        let registryButton = document.getElementById('save_registry');
                        saveButton.classList.remove("d-none");
                        registryButton.classList.remove("d-none");
                        document.getElementById('lform').value = JSON.stringify(data);
                        document.getElementById('form_name').value = jsAttr(data.name);
                        if (typeof data.copyrightNotice !== 'undefined' && data.copyrightNotice > '') {
                            document.getElementById('copyright').value = jsAttr(data.copyrightNotice);
                            document.getElementById('copyrightNotice').innerHTML = jsText(data.copyrightNotice);
                        }
                        LForms.Util.addFormToPage(data, 'formContainer', formOptions);
                        return data;
                    }).then((data) => {
                        let qFhir = LForms.Util.getFormFHIRData("Questionnaire", 'R4', data);
                        document.getElementById('questionnaire').value = JSON.stringify(qFhir);
                    });
                }
            });

            initNewForm(true);
            let saveButton = document.getElementById('save_response');
            let registryButton = document.getElementById('save_registry');
            saveButton.classList.remove("d-none");
            registryButton.classList.remove("d-none");
        }
    </script>
</head>
<body>
    <div class="container-xl my-2">
        <div class="title"><h3><?php if ($mode != 'new_form' && $mode != 'update') {
            echo xlt("Create Encounter Questionnaires");
                               } else {
                                   echo xlt("Edit Questionnaire");
                               } ?></h3></div>
        <?php if (!$is_authorized) { ?>
        <div class="d-flex flex-column w-100 align-items-center">
            <?php
            echo "<h3>" . xlt("Not Authorized") . "</h3>";
            echo "<h4>" . xlt("You must have administrator privileges.") . "</h4>";
            echo "<h5>" . xlt("Contact an administrator to use this feature.") . "</h5>";
            ?>
            <button type='button' class="btn btn-secondary btn-cancel" onclick="parent.closeTab(window.name, false)"><?php echo xlt('Exit'); ?></button>
        </div>
        <?php die(); } ?>
        <form method="post" id="qa_form" name="qa_form" onsubmit="return saveQR()" action="<?php echo $rootdir; ?>/forms/questionnaire_assessments/save.php?form_id=<?php echo attr_url($formid); ?><?php echo ($isPortal) ? '&isPortal=1' : ''; ?><?php echo ($patientPortalOther) ? '&formOrigin=' . attr_url($_GET['formOrigin']) : '' ?><?php echo '&mode=' . attr_url($mode ?? ''); ?>">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" id="lform" name="lform" value="<?php echo attr($form['lform'] ?? ''); ?>" />
            <input type="hidden" id="lform_response" name="lform_response" value="<?php echo attr($form['lform_response'] ?? ''); ?>" />
            <input type="hidden" id="response_id" name="response_id" value="<?php echo attr($form["response_id"] ?? ''); ?>" />
            <input type="hidden" id="response_meta" name="response_meta" value="<?php echo attr($form['response_meta'] ?? ''); ?>" />
            <input type="hidden" id="copyright" name="copyright" value="<?php echo attr($form['copyright'] ?? ''); ?>" />
            <input type="hidden" id="questionnaire" name="questionnaire" value="<?php echo attr($form['questionnaire'] ?? ''); ?>" />
            <input type="hidden" id="questionnaire_response" name="questionnaire_response" value="<?php echo attr($form['questionnaire_response'] ?? ''); ?>" />
            <div>
                <p class="text-center"><?php echo "<span class='font-weight-bold'>" . xlt("Important to Note") . ": </span><i>" . xlt("LOINC form definitions are subject to the LOINC"); ?>
                    <a href="http://loinc.org/terms-of-use" target="_blank"><?php echo xlt("terms of use.") . "</i>"; ?></php></a></p>
                <p id="copyrightNotice">
                    <?php echo text($form['copyright'] ?? ''); ?>
                </p>
            </div>
            <div class="mb-3">
                <div class="input-group isNew d-none">
                    <label for="loinc_item" class="font-weight-bold mt-2 mr-1"><?php echo xlt("Search and Select a LOINC form") . ': '; ?></label>
                    <input class="form-control search_field" type="text" id="loinc_item" placeholder="<?php echo xla("Type to search"); ?>" autocomplete="off" role="combobox" aria-expanded="false">
                </div>
                <div class="input-group isNew d-none mt-2">
                    <label for="select_item" class="font-weight-bold my-2 mr-1"><?php echo xlt("Select new from Questionnaire Repository") . ': '; ?></label>
                    <select class="select-dropdown my-2" type="text" id="select_item" name="select_item" autocomplete="off" role="combobox" aria-expanded="false">
                        <option value=""></option>
                        <?php
                        foreach ($q_list as $item) {
                            $id = attr($item['id']);
                            if ($id == $repository_item) {
                                echo "<option selected value='$id'>" . text($item['name']) . "</option>";
                                continue;
                            }
                            echo "<option value='$id'>" . text($item['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="input-group isNew d-none">
                    <hr />
                    <label class="font-weight-bold my-2" for="form_name"><?php echo xlt("Form Name") . ':'; ?></label>
                    <input required type="text" class="form-control skip-template-editor ml-1" id="form_name" name="form_name" title="<?php echo xla('You may edit name to shorten to be more understandable.'); ?>" placeholder="<?php echo xla('Name of new Encounter form. You may change or leave as displayed.'); ?>" value="<?php echo attr($form['form_name'] ?: $form_name); ?>" />
                </div>
            </div>
            <hr />
            <div id="formContainer"></div>
            <?php if (!$isPortal && !$patientPortalOther) { ?>
                <div class="btn-group my-2">
                    <button type="submit" class="btn btn-primary btn-save isNew" id="save_response" title="<?php echo xla('Save current form or create a new one time questionnaire for this encounter if this is a New Questionnaire form.'); ?>"><?php echo xlt("Save Current"); ?></button>
                    <button type="submit" class="btn btn-primary d-none" id="save_registry" name="save_registry" title="<?php echo xla('Register as a new encounter form for reuse in any encounter.'); ?>" onclick="formMode = 'register'"><?php echo xlt("or Register New"); ?></button>
                    <button type='button' class="btn btn-secondary btn-cancel" onclick="parent.closeTab(window.name, false)"><?php echo xlt('Cancel'); ?></button>
                </div>
            <?php } ?>
        </form>
    </div>
    <!-- Below scripts must be in body. -->
    <!--<script src="<?php /*echo $GLOBALS['assets_static_relative']; */?>/lforms/webcomponent/assets/lib/zone.min.js"></script>
    <script src="<?php /*echo $GLOBALS['assets_static_relative']; */?>/lforms/webcomponent/scripts.js"></script>
    <script src="<?php /*echo $GLOBALS['assets_static_relative']; */?>/lforms/webcomponent/runtime-es2015.js"></script>
    <script src="<?php /*echo $GLOBALS['assets_static_relative']; */?>/lforms/webcomponent/polyfills-es2015.js"></script>
    <script src="<?php /*echo $GLOBALS['assets_static_relative']; */?>/lforms/webcomponent/main-es2015.js"></script>
    <script src="<?php /*echo $GLOBALS['assets_static_relative']; */?>/lforms/fhir/R4/lformsFHIR.min.js"></script>-->

    <!-- TODO Temporary dependencies location -->
    <?php require(__DIR__ . "/../../forms/questionnaire_assessments/lform_webcomponents.php") ?>
    <!-- Dependency scopes seem strange using the way we have to implement the necessary web components. -->
    <?php Header::setupAssets(['select2', 'bootstrap']); ?>
    <script>
        <?php if ($isPortal || $patientPortalOther) { ?>
        $(function () {
            window.addEventListener("message", (e) => {
                if (e.origin !== window.location.origin) {
                    syncAlertMsg(<?php echo xlj("Request is not same origin!"); ?>, 15000);
                    return false;
                }
                if (e.data.submitForm === true) {
                    let pass = saveQR();
                    if (pass) {
                        e.preventDefault();
                        document.forms[0].submit();
                    } else {
                        syncAlertMsg(<?php echo xlj("Form validation failed."); ?>);
                        return false;
                    }
                }
            });
        });
        <?php }
        if (($mode == "update") && $patientPortalOther && !empty($formid)) { ?>
        parent.postMessage({formid:<?php echo attr($formid) ?>}, window.location.origin);
        <?php } ?>
    </script>
</body>
<script>
    /*
    * mode update = existing form edit
    * mode new = new registry form being added
    * mode new_form = a new registered form for first edit
    * mode new_repository_form = a new registered form selected from Questionnaire Repository.
    * */
    let formMode = <?php echo js_escape($mode); ?>;
    <?php if ($mode == 'update') { ?>
    window.onload = initUpdate();
    <?php } elseif ($mode == 'new') { ?>
    window.onload = initSearch();
    <?php } elseif ($mode == 'new_form') { ?>
    window.onload = initNewForm();
    <?php } elseif ($mode == 'new_repository_form') { ?>
    window.onload = initSearchForm();
    <?php } ?>
</script>
</html>
