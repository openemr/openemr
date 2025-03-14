<?php

/**
 * import_template_ui.php - Patient Portal Template Maintenance
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2022 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Events\Messaging\SendNotificationEvent;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use OpenEMR\Services\PatientPortalService;
use OpenEMR\Services\QuestionnaireService;

if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}

// Service
$eventDispatcher = $GLOBALS['kernel']->getEventDispatcher();
$portalService = new PatientPortalService();
// auto allow if a portal user else must be an admin
$authUploadTemplates = $portalService::authPortalUser('admin', 'forms');

$templateService = new DocumentTemplateService();
$from_demo_pid = $_GET['from_demo_pid'] ?? '0';
$patient = $_REQUEST['selected_patients'] ?? null;
$patient = $patient ?: ($_REQUEST['upload_pid'] ?? 0);
// our lists
$category = $_REQUEST['template_category'] ?? '';
$category_list = $templateService->fetchDefaultCategories();
$profile_list = $templateService->fetchDefaultProfiles();
$group_list = $templateService->fetchDefaultGroups();
// for empty lists
$none_message = xlt("Nothing to show for current actions.");
// init status array
$audit_status_blank = array(
    'audit_id' => null,
    'pid' => null,
    'create_date' => null,
    'doc_type' => null,
    'id' => null,
    'facility' => null,
    'provider' => null,
    'encounter' => null,
    'patient_signed_status' => null,
    'patient_signed_time' => null,
    'authorize_signed_time' => null,
    'accept_signed_status' => null,
    'authorizing_signator' => null,
    'review_date' => null,
    'denial_reason' => null,
    'authorized_signature' => null,
    'patient_signature' => null,
    'full_document' => null,
    'file_name' => null,
    'file_path' => null,
    'template_data' => null,
    'date' => null,
    'patient_id' => null,
    'activity' => null,
    'require_audit' => null,
    'pending_action' => null,
    'action_taken' => null,
    'status' => null,
    'narrative' => null,
    'table_action' => null,
    'table_args' => null,
    'action_user' => null,
    'action_taken_time' => null,
    'checksum' => null,
);

$searchTerm = '';
if (!empty($_GET['search_term']) || !empty($_GET['search'])) {
    $searchTerm = $_GET['search_term'] ?? $_GET['search'];
}
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title><?php echo xlt('Portal'); ?> | <?php echo xlt('Templates'); ?></title>
    <meta name="description" content="Developed By sjpadgett@gmail.com">
    <?php Header::setupHeader(['datetime-picker', 'select2', 'summernote']); ?>
    <script>
        const profiles = <?php echo js_escape($profile_list); ?>;
        let currentEdit = "";
        let editor;
        let callBackCmd = null;

        <?php
        $eventDispatcher->dispatch(new SendNotificationEvent($pid ?? 0, ['is_onetime' => 1]), SendNotificationEvent::JAVASCRIPT_READY_NOTIFICATION_POST);
        ?>
        $(document).ready(function () {
            let selectDropdown = $('.select-dropdown');
            selectDropdown.select2({
                multiple: true,
                placeholder: 'Type to search.',
                theme: 'bootstrap4',
                dropdownAutoWidth: true,
                width: '100%',
                closeOnSelect: false
            });

            let searchBox = document.querySelector('.select2-search__field');

            searchBox.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    $('#selectSearch').trigger('click');
                }
            });
            searchBox.addEventListener('input', function (event) {
                let currentValue = event.target.value;
                $('#search_term').val(currentValue);
            });

            // Set the search term in the search box after the page reloads
            let searchTerm = <?php echo js_escape($searchTerm); ?>;
            if (searchTerm) {
                let searchBox = document.querySelector('.select2-search__field');
                $(searchBox).val(searchTerm).trigger('input');
            }

            document.querySelector('.select2-search__field').focus();
            // When the search box is opened update the hidden input
            selectDropdown.on('select2:open', function () {
                let searchBox = document.querySelector('.select2-search__field');
                $(searchBox).val($('#search_term').val()).trigger('input');
                $(searchBox).on('input', function () {
                    let searchTerm = $(this).val();
                    $('#search_term').val(searchTerm);
                });
            });
            // Get selected templates before the form is submitted
            $('#edit_form').on('submit', function (e) {
                e.preventDefault();
                let checked = getSendChecks();
                if (checked.length > 0) {
                    return false;
                }
                this.submit();
            });

            $("#selectSearch").on('click', function () {
                $('#edit_form').submit();
            });

            document.getElementById('clearSelection').addEventListener('click', function () {
                let selectedPatients = $('#selected_patients');
                selectedPatients.val(null).trigger('change');
                let searchBox = document.querySelector('.select2-search__field');
                $(searchBox).val(null).trigger('input');
                $('#search_term').val("");
                $('#edit_form').submit();
            });

            $('#selected_patients').on('change', function () {
                let selectedValues = [];
                $(this).find('option:selected').each(function () {
                    selectedValues.push({
                        pid: $(this).val(),
                        ptname: $(this).text()
                    });
                });
                $('#persist_checks').val(JSON.stringify(selectedValues));
            });

            $('#selected_patients').trigger('change');
        });

        $(function () {
            let ourSelect = $('.select-questionnaire');
            /* Questionnaires */
            ourSelect.select2({
                multiple: false,
                placeholder: xl('Type to search Questionnaire Repository.'),
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
                let data = $('#select_item').select2('data');
                if (data) {
                    document.getElementById('upload_name').value = data[0].text;
                }
                $('#repository-submit').removeClass('d-none');
            });

            $("#repository-submit").on("click", function (e) {
                top.restoreSession();
                let data = $('#select_item').select2('data');
                if (data) {
                    document.getElementById('upload_name').value = data[0].text;
                } else {
                    alert(xl("Missing Template name."))
                    return false;
                }
                return true;
            });

            $('#fetch_files').on('click touchstart', function () {
                $(this).val('');
            });

            $('#fetch_files').change(function (e) {
                const file = document.getElementById("fetch_files").files.item(0);
                const fileName = file.name;
                let howManyFiles = document.getElementById("fetch_files").files.length;
                $('#upload_submit').removeClass('d-none');
                if (howManyFiles === 1 && document.getElementById("upload_scope").checked) {
                    if (fileName.toLowerCase().indexOf('.json') > 0 || file.type === 'application/json') {
                        $('#upload_submit_questionnaire').removeClass('d-none');
                        resolveImport();
                    }
                } else {
                    if (fileName.toLowerCase().indexOf('.json') > 0 || file.type === 'application/json') {
                        document.getElementById("upload_submit_questionnaire").type = 'submit';
                        document.getElementById("upload_submit_questionnaire").removeAttribute("onclick");
                        document.getElementById("upload_submit_questionnaire").innerText = xl("Questionnaires Repository All")
                        $('#upload_submit_questionnaire').removeClass('d-none');
                    }
                }
                return false;
            });

            $('input:checkbox[name=send]').change(function () {
                let checked = getSendChecks();
                if (checked.length > 0) {
                    $('#send-button').removeClass('d-none');
                    $('#category_group').addClass('d-none');
                    $('#send-profile-hide').addClass('d-none');
                } else {
                    $('#send-button').addClass('d-none');
                    $('#category_group').removeClass('d-none');
                }
            });

            $('input:checkbox[name=send_profile]').change(function () {
                $('#send-profile-hide').removeClass('d-none');
                $('#category_group').addClass('d-none');
                $('input:checkbox[name=send]').addClass('d-none');
            });

            $('#upload-nav').on('hidden.bs.collapse', function () {
                $('#upload-nav-value').val('collapse');
            });
            $('#upload-nav').on('show.bs.collapse', function () {
                $('#upload-nav-value').val('show');
                //$('#edit_form').submit();
            });

            $("#template_category").change(function () {
                $('#edit_form').submit();
            });

            $('#template-collapse').on('show.bs.collapse', function () {
                $('#edit_form #all_state').val('show');
            });
            $('#template-collapse').on('hidden.bs.collapse', function () {
                $('#edit_form #all_state').val('collapse');
            });

            $('#assigned_collapse').on('show.bs.collapse', function () {
                $('#repository-collapse').collapse('hide');
                $('#template-collapse').collapse('hide');
                $('#edit_form #assigned_state').val('show');
            });
            $('#assigned_collapse').on('hidden.bs.collapse', function () {
                $('#edit_form #assigned_state').val('collapse');
            });

            $('#repository-collapse').on('show.bs.collapse', function () {
                $('#edit_form #repository_send_state').val('show');
            });
            $('#repository-collapse').on('hidden.bs.collapse', function () {
                $('#edit_form #repository_send_state').val('collapse');
            });

            let selText = '';
            let selCat = $('#template_category').find(':selected').text();
            let ids = $('#selected_patients').find(':selected').each(function () {
                selText += $(this).text() + '; ';
            });
            $('#upload_scope_category').empty().append(' ' + xl('For Category') + ': ' + selCat);
        });

        // a callback from dlgclose(fn) in render form
        function doImportSubmit() {
            // todo add message to user
            top.restoreSession();
            document.getElementById('form_upload').submit();
            return false;
        }

        function resolveImport(mode = 'render_import') {
            if (mode === 'render_import') {
                const file = document.getElementById("fetch_files").files.item(0);
                if (file.name.toLowerCase().indexOf('.json') === -1 && file.type !== 'application/json') {
                    return false;
                }
            }
            top.restoreSession();
            let callBack = '';
            let url = './questionnaire_render.php?mode=' + encodeURIComponent(mode);
            dlgopen(url, 'pop-questionnaire', 'modal-lg', 850, '', '', {
                allowDrag: true,
                allowResize: true,
                sizeHeight: 'full',
                resolvePromiseOn: 'close',
            }).then(() => {
                // set callBackCmd from iframe then eval here
                // currently using callback from dlgclose();
                return false;
            });
        }

        let questionnaireViewCurrent = function (encounter, flag = '') {
            currentEdit = encounter;
            alertMsg(xl("New coming feature. View patient progress with the completion of the form with the ability to send a secure notification to patient."), 6000, 'success')
            return false;
        };

        let templateEdit = function (id, flag = '') {
            currentEdit = id;
            handleTemplate(id, 'get', '', flag);
            return false;
        };

        let templateDelete = function (id, template = '') {
            let delok = confirm(<?php echo xlj('You are about to delete a template'); ?> +
                ": " + "\n" + <?php echo xlj('Is this Okay?'); ?>);
            if (delok === true) {
                handleTemplate(id, 'delete', '', false, template, <?php echo js_escape(CsrfUtils::collectCsrfToken('import-template-delete')); ?>)
            }
            return false;
        };

        function getSendChecks() {
            let checked = [];
            $('input:checked[name=send]:checked').each(function () {
                let isProfile = this.dataset.send_profile;
                if (isProfile == 'yes') {
                    checked.push([$(this).val(), true]);
                } else {
                    checked.push($(this).val());
                }
            });
            console.log(checked)
            return checked;
        }

        function getSendCheckProfiles() {
            let checked = [];
            $('input:checked[name=send_profile]:checked').each(
                function () {
                    let isProfile = this.dataset.send_profile;
                    if (isProfile == 'yes') {
                        checked.push($(this).val());
                    }
                });
            console.log(checked)
            return checked;
        }

        function updateCategory(id) {
            top.restoreSession();
            let url = 'import_template.php';
            let category = event.currentTarget.value;
            const data = new FormData();
            data.append('docid', id);
            data.append('category', category);
            data.append('mode', 'update_category');
            fetch(url, {
                method: 'POST',
                body: data,
            }).then(rtn => rtn.text()).then((rtn) => {
                (async (time) => {
                    await asyncAlertMsg(rtn, time, 'success', 'lg');
                })(2000).then(rtn => {
                    //document.edit_form.submit();
                });
            }).catch((error) => {
                console.error('Error:', error);
            });
        }

        function sendTemplate(mode = 'send', content = '') {
            top.restoreSession();
            let url = 'import_template.php';
            let ids = $('#selected_patients').select2('val');
            let category = $('#template_category').val();
            let checked = getSendChecks();
            const data = new FormData();
            data.append('docid', JSON.stringify(ids));
            data.append('category', category);
            data.append('checked', JSON.stringify(checked));
            data.append('mode', mode);
            data.append('content', content);
            fetch(url, {
                method: 'POST',
                body: data,
            }).then(rtn => rtn.text()).then((rtn) => {
                (async (time) => {
                    await asyncAlertMsg(rtn, time, 'success', 'lg');
                })(1500).then(rtn => {
                    document.edit_form.submit();
                });
            }).catch((error) => {
                console.error('Error:', error);
            });
        }

        function sendProfiles() {
            top.restoreSession();
            let mode = 'send_profiles'
            let url = 'import_template.php';
            let checked = getSendCheckProfiles();
            const data = new FormData();
            data.append('checked', JSON.stringify(checked));
            data.append('mode', mode);
            fetch(url, {
                method: 'POST',
                body: data,
            }).then(rtn => rtn.text()).then((rtn) => {
                (async (time) => {
                    await asyncAlertMsg(rtn, time, 'success', 'lg');
                })(1500).then(rtn => {
                    document.edit_form.submit();
                });
            }).catch((error) => {
                console.error('Error:', error);
            });
        }

        function handleTemplate(id, mode, content = '', isDocument = '', template = '', csrf = '') {
            top.restoreSession();
            let libUrl = 'import_template.php';
            let renderUrl = 'import_template.php?mode=editor_render_html&docid=' + id;

            if (document.getElementById('is_modal').checked && mode === 'get') {
                dialog.popUp(renderUrl, null, 'edit' + id);
                return false;
            }
            if (isDocument == true) {
                dialog.popUp(renderUrl, null, ('edit' + id));
                return false;
            }
            if (mode == 'get') {
                renderUrl += '&dialog=true';
                dlgopen(renderUrl, 'pop-editor', 'modal-lg', 850, '', '', {
                    resolvePromiseOn: 'show',
                    allowDrag: true,
                    allowResize: true,
                    sizeHeight: 'full'
                });
            }
            $.ajax({
                type: "POST",
                url: libUrl,
                data: {docid: id, mode: mode, content: content, template: template, csrf_token_form: csrf},
                error: function (qXHR, textStatus, errorThrow) {
                    console.log("There was an error");
                    alert(<?php echo xlj("File Error") ?> +"\n" + id)
                },
                success: function (templateHtml, textStatus, jqXHR) {
                    document.edit_form.submit();
                }
            });
        }

        function popProfileDialog() {
            top.restoreSession();
            let url = './import_template.php?mode=render_profile';
            dlgopen(url, 'pop-profile', 'modal-lg', 850, '', '', {
                allowDrag: true,
                allowResize: true,
                sizeHeight: 'full',
            });
        }

        function popPatientDialog() {
            top.restoreSession();
            let url = './lib/patient_groups.php';
            dlgopen(url, 'pop-profile', 'modal-lg', 850, '', '', {
                allowDrag: true,
                allowResize: true,
                sizeHeight: 'full'
            });
        }

        function popGroupsDialog() {
            let url = './lib/patient_groups.php?render_group_assignments=true';
            dlgopen(url, 'pop-groups', 'modal-lg', 850, '', '', {
                allowDrag: true,
                allowResize: true,
                sizeHeight: 'full',
            });
        }

        function createBlankTemplate() {
            top.restoreSession();
            let name = prompt(xl('Enter a valid name for this new template.') + "\n" + xl("For example: Pain Assessment"));
            if (name === null) {
                return false;
            }
            if (name === "") {
                alert(xl('A name must be entered. Try again.'));
                createBlankTemplate();
            }
            $("#upload_name").val(name);
            return true;
        }
    </script>
    <style>
      .select2-container .select2-search--inline .select2-search__field {
        min-width: 5vw !important;
        padding: 2px 2px;
        color: var(--light);
        background-color: var(--dark);
      }

      .select2-container {
        max-width: 50% !important;
      }

      .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #dc3545;
        font-size: 1rem;
        line-height: revert !important;
      }

      caption {
        caption-side: top !important;
      }

      .note-editor.dragover .note-dropzone {
        display: none
      }
    </style>
</head>
<body class="body-top">
    <div class='container-xl'>
        <nav class='nav navbar bg-light text-dark sticky-top'>
            <span class='title'><?php echo xlt('Template Maintenance'); ?></span>
            <div class="ml-auto">
                <label class="form-check"><?php echo xlt('Full Editor'); ?>
                    <input type='checkbox' class='form-check-inline mx-1' id='is_modal' name='is_modal' checked='checked' />
                </label>
            </div>
            <div class='btn-group ml-1'>
                <button type='button' class='btn btn-secondary' data-toggle='collapse' data-target='#help-panel'>
                    <?php echo xlt('Help') ?>
                </button>
                <button class='btn btn-success' type='button' onclick="location.href='./patient/provider'">
                    <?php echo xlt('Dashboard'); ?>
                </button>
            </div>
        </nav>
        <div class='col col-12'>
            <hr />
            <?php include_once('./../Documentation/help_files/template_maintenance_help.php'); ?>
            <!-- Actions Scope to act on -->
            <nav class='navbar navbar-dark bg-dark text-light sticky-top'>
                <form id="edit_form" name="edit_form" class="row form-inline w-100" action="" method="get">
                    <a class='navbar-brand ml-1'><?php echo xlt('Scope'); ?></a>
                    <?php
                    $select_cat_options = '<option value="">' . xlt('General') . "</option>\n";
                    foreach ($category_list as $option_category) {
                        if (stripos($option_category['option_id'], 'repository') !== false) {
                            continue;
                        }
                        if ($category === $option_category['option_id']) {
                            $select_cat_options .= "<option value='" . attr($option_category['option_id']) . "' selected>" . text($option_category['title']) . "</option>\n";
                        } else {
                            $select_cat_options .= "<option value='" . attr($option_category['option_id']) . "'>" . text($option_category['title']) . "</option>\n";
                        }
                    }
                    ?>
                    <div class="input-group" id="category_group">
                        <label class="font-weight-bold mx-1" for="template_category"><?php echo xlt('Category'); ?></label>
                        <select class="form-control" id="template_category" name="template_category">
                            <?php echo $select_cat_options ?>
                        </select>
                    </div>
                    <div class="form-group mx-2">
                        <div class='btn-group ml-1'>
                            <button type='submit' class='btn btn-save btn-primary'><?php echo xlt("Submit"); ?></i></button>
                            <button type='button' id="send-button" class='btn btn-transmit btn-success d-none' onclick="return sendTemplate()">
                                <?php echo xlt('Send'); ?>
                            </button>
                            <button type='button' class='btn btn-primary' onclick='return popProfileDialog()'><?php echo xlt('Profiles') ?></button>
                            <button type='button' class='btn btn-primary' onclick='return popPatientDialog()'><?php echo xlt('Groups') ?></button>
                            <button type='button' class='btn btn-primary' onclick='return popGroupsDialog()'><?php echo xlt('Assign') ?></button>
                        </div>
                    </div>
                    <div class="form-row form-inline mx-1 w-100">
                        <!--<label class='font-weight-bold mx-1' for='selected_patients'><?php /*echo xlt('Location'); */ ?></label>-->
                        <?PHP
                        $searchTerm = '';
                        $ppt = array(
                            ['pid' => '0', 'ptname' => 'All Patients'],
                            ['pid' => '-1', 'ptname' => 'Repository'],
                        );
                        if (!empty($_GET['search_term']) || !empty($_GET['search'])) {
                            $searchTerm = $_GET['search_term'] ?? $_GET['search'];
                        }
                        if (!empty($searchTerm)) {
                            $ppt = $templateService->searchPatients($searchTerm);
                        }
                        $auth = '';
                        if (!empty($_REQUEST['persist_checks'])) {
                            $persist_checks = json_decode($_REQUEST['persist_checks'], true);
                            if (is_array($persist_checks)) {
                                foreach ($persist_checks as $pt) {
                                    foreach ($ppt as $k => $pc) {
                                        if ($pc['pid'] == $pt['pid']) {
                                            unset($ppt[$k]);
                                            break;
                                        }
                                    }
                                    if (isset($pt['pid']) && isset($pt['ptname'])) {
                                        $auth .= "<option value='" . attr($pt['pid']) . "' selected='selected'>" . text($pt['ptname']) . '</option>';
                                    }
                                }
                            }
                        }
                        foreach ($ppt as $pt) {
                            if (!empty($from_demo_pid)) {
                                $patient = [$from_demo_pid];
                            }
                            if ((is_array($patient) && !in_array($pt['pid'], $patient)) || empty($patient)) {
                                $auth .= '<option value=' . attr($pt['pid']) . '>' . text($pt['ptname']) . '</option>';
                            } else {
                                $auth .= "<option value='" . attr($pt['pid']) . "' selected='selected'>" . text($pt['ptname'] . ' ') . '</option>';
                            }
                        }
                        ?>
                        <select class="form-control select-dropdown d-none" id="selected_patients" name="selected_patients[]" multiple="multiple" value="<?php echo attr($searchTerm); ?>">
                            <?php echo $auth ?>
                        </select>
                        <button id="selectSearch" class='btn btn-search btn-primary' role="button"><?php echo xlt("Search"); ?></button>
                        <button id="clearSelection" class='btn btn-secondary btn-cancel' type="button"><?php echo xlt("Clear"); ?></button>
                    </div>
                    <input type='hidden' id='upload-nav-value' name='upload-nav-value' value='<?php echo attr($_REQUEST['upload-nav-value'] ?? 'collapse') ?>' />
                    <input type='hidden' id='persist_checks' name='persist_checks' value='' />
                    <input type='hidden' id='all_state' name='all_state' value='<?php echo attr($_REQUEST['all_state'] ?? 'collapse') ?>' />
                    <input type='hidden' id='assigned_state' name='assigned_state' value='<?php echo attr($_REQUEST['assigned_state'] ?? 'collapse') ?>' />
                    <input type='hidden' id='repository_send_state' name='repository_send_state' value='<?php echo attr($_REQUEST['repository_send_state'] ?? 'collapse') ?>' />
                    <input type='hidden' id='search_term' name='search_term' value="<?php echo attr($searchTerm); ?>" />
                </form>
            </nav>
            <!-- Upload -->
            <nav class="collapse my-2 <?php echo attr($_REQUEST['upload-nav-value'] ?? '') ?>" id="upload-nav">
                <div class='col col-12'>
                    <?php if ($authUploadTemplates) { ?>
                        <form id='form_upload' class='form-inline row' action='import_template.php' method='post' enctype='multipart/form-data'>
                            <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('import-template-upload')); ?>" />
                            <hr />
                            <div class='col'>
                                <div id='upload_scope_category'></div>
                                <div class="input-group">
                                    <label class="form-check"><?php echo xlt('If questionnaire import, use Questionnaire tool'); ?>
                                        <input type="checkbox" class='form-check-inline ml-1' id='upload_scope' checked>
                                </div>
                            </div>
                            <div class='form-group col'>
                                <input type='file' class='btn btn-outline-info mr-1 mt-1' id="fetch_files" name='template_files[]' multiple />
                                <div class="mt-1">
                                    <button class='btn btn-outline-success d-none' type='submit' name='upload_submit' id='upload_submit' title="<?php echo xla("Import a template file or if a Questionnaire then auto create a questionnaire template."); ?>">
                                        <i class='fa fa-upload mr-1' aria-hidden='true'></i><?php echo xlt("Templates"); ?></button>
                                    <button class='btn btn-outline-success d-none' type='button' name='upload_submit_questionnaire' id='upload_submit_questionnaire' title="<?php echo xla("Import to the questionnaire repository for later use in encounters or FHIR API"); ?>" onclick="return resolveImport();">
                                        <i class='fa fa-upload mr-1' aria-hidden='true'></i><?php echo xlt("Questionnaires Repository"); ?></button>
                                    <button type='button' id='render-nav-button' name='render-nav-button' class='btn btn-save btn-outline-primary' onclick="return resolveImport('render_import_manual');" title="<?php echo xla('Used to cut and paste Questionnaire or LHC Form json. Will then convert and import to questionnaire repository.') ?>"><?php echo xlt('Manual Questionnaire') ?></button>
                                    <button type='submit' id='blank-nav-button' name='blank-nav-button' class='btn btn-save btn-outline-primary' onclick="return createBlankTemplate();" title="<?php echo xla('Use this to create a new empty template for use with built in editor.') ?>"><?php echo xlt('New Empty Template') ?></button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="text-center m-0 p-0"><small class="my-1 font-weight-bolder font-italic"><?php echo xlt("Shows all existing Questionnaires available from repository. Select to automatically create template."); ?></small></div>
                                <div class="input-group input-group-append">
                                    <select class="select-questionnaire" type="text" id="select_item" name="select_item" autocomplete="off" role="combobox" aria-expanded="false" title="<?php echo xla('Items that are already an existing template will be overwritten if selected.') ?>">
                                        <option value=""></option>
                                        <?php
                                        $qService = new QuestionnaireService();
                                        $q_list = $qService->getQuestionnaireList(false);
                                        $repository_item = $_POST['select_item'] ?? null;
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
                                    <button type='submit' id='repository-submit' name='repository-submit' class='btn btn-save btn-success d-none' value="true"><?php echo xlt('Create') ?></button>
                                </div>
                            </div>
                            <input type='hidden' name='upload_pid' value='<?php echo attr(json_encode([-1])); ?>' />
                            <input type='hidden' name="template_category" value='<?php echo attr($category); ?>' />
                            <input type='hidden' name='upload_name' id='upload_name' value='<?php echo attr(json_encode([-1])); ?>' />
                            <input type="hidden" id="q_mode" name="q_mode" value="" />
                            <input type="hidden" id="lform" name="lform" value="" />
                            <input type="hidden" id="questionnaire" name="questionnaire" value="" />
                        </form>
                    <?php } else { ?>
                        <div class="alert alert-danger"><?php echo xlt("Not Authorized to Upload Templates") ?></div>
                    <?php } ?>
                </div>
            </nav>
            <hr />
            <!-- Repository -->
            <div class='row'>
                <div class='col col-12'>
                    <div class="h5"><i class='fa fa-eye mr-1' data-toggle='collapse' data-target='#repository-collapse' role='button' title="<?php echo xla('Click to expand or collapse Repository templates panel.'); ?>"></i><?php echo xlt('Template Repository') ?>
                        <span>
                        <button type='button' id='upload-nav-button' name='upload-nav-button' class='btn btn-sm btn-primary' data-toggle='collapse' data-target='#upload-nav'>
                            <i class='fa fa-upload mr-1' aria-hidden='true'></i><?php echo xlt('Upload') ?></button>
                        </span>
                    </div>
                </div>
                <!-- Repository table -->
                <div class='col col-12 table-responsive <?php echo attr($_REQUEST['repository_send_state'] ?? 'collapse') ?>' id="repository-collapse">
                    <?php
                    $templates = [];
                    $show_cat_flag = false;
                    if (!empty($category)) {
                        $templates = $templateService->getTemplateListByCategory($category, -1);
                    } else {
                        $templates = $templateService->getTemplateListAllCategories(-1);
                    }
                    echo "<table class='table table-sm table-striped table-bordered'>\n";
                    echo "<thead>\n";
                    echo "<tr>\n" .
                        "<th style='width:5%'>" . xlt('Send') . "</th>" .
                        '<th>' . xlt('Category') . '</th>' .
                        "<th>" . xlt("Template Actions") . "</th>" .
                        "<th>" . xlt("Size") . "</th>" .
                        "<th>" . xlt("Last Modified") . "</th>" .
                        "</tr>\n";
                    echo "</thead>\n";
                    echo "<tbody>\n";
                    foreach ($templates as $cat => $files) {
                        if (empty($cat)) {
                            $cat = xlt('General');
                        }
                        foreach ($files as $file) {
                            $template_id = $file['id'];
                            $this_cat = $file['category'];
                            $notify_flag = false;
                            $select_cat_options = '<option value="">' . xlt('General') . "</option>\n";
                            foreach ($category_list as $option_category) {
                                if (stripos($option_category['option_id'], 'repository') !== false) {
                                    continue;
                                }
                                if ($this_cat === $option_category['option_id']) {
                                    $select_cat_options .= "<option value='" . attr($option_category['option_id']) . "' selected>" . text($option_category['title']) . "</option>\n";
                                } else {
                                    $select_cat_options .= "<option value='" . attr($option_category['option_id']) . "'>" . text($option_category['title']) . "</option>\n";
                                }
                            }
                            echo "<tr>";
                            if ($file['mime'] == 'application/pdf') {
                                $this_cat = xlt('PDF Document');
                                $notify_flag = true;
                                echo '<td>' . '*' . '</td>';
                                echo "<td>" . $this_cat . " Id: " . attr($template_id) . "</td>";
                            } else {
                                echo "<td><input type='checkbox' class='form-check-inline' name='send' value='" . attr($template_id) . "' /></td>";
                                echo '<td><select class="form-control form-control-sm" id="category_table' . attr($template_id) .
                                    '" onchange="updateCategory(' . attr_js($template_id) . ')" value="' . attr($this_cat) . '">' .
                                    $select_cat_options . '</select></td>';
                            }
                            echo '<td>' .
                                '<button id="templateEdit' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ',' . attr_js($notify_flag) . ')" type="button">' . text($file['template_name']) .
                                '</button>';
                            if ($authUploadTemplates) {
                                echo '<button id="templateDelete' . attr($template_id) .
                                    '" class="btn btn-sm btn-outline-danger float-right" onclick="templateDelete(' . attr_js($template_id) . ',' . attr_js($file['template_name']) . ')" type="button">' . xlt("Delete") .
                                    '</button>';
                            }
                            echo "</td>";
                            echo "<td>" . text($file['size']) . "</td>";
                            echo "<td>" . text(date('m/d/Y H:i:s', strtotime($file['modified_date']))) . "</td>";
                            echo "</tr>";
                            ?>
                            <?php
                        }
                    }
                    if (empty($template_id)) {
                        echo '<tr><td></td><td>' . $none_message . "</td></tr>\n";
                    }
                    echo "</tbody>\n";
                    echo "</table>\n";
                    ?>
                    <div>
                        <?php
                        echo "<table class='table table-sm table-striped table-bordered'>\n";
                        echo '<caption>' . xlt('Profiles in Portal') . "</caption>";
                        echo "<thead>\n";
                        echo "<tr>\n" .
                            "<th>" . xlt('Active') . "<button type='button' id='send-profile-hide' class='btn btn-sm ml-1 py-0 btn-transmit btn-success d-none' onclick='return sendProfiles()'>" . xlt('Update') . "</button></th>" .
                            '<th style="min-width: 25%">' . xlt('Profile') . '</th>' .
                            '<th>' . xlt('Assigned Templates') . '</th>' .
                            '<th>' . xlt('Assigned Groups') . '</th>' .
                            "</tr>\n";
                        echo "</thead>\n";
                        foreach ($profile_list as $profile => $profiles) {
                            $template_list = '';
                            $group_list_text = '';
                            $group_items_list = $templateService->getPatientGroupsByProfile($profile);
                            $profile_items_list = $templateService->getTemplateListByProfile($profile);
                            if (empty($profile_items_list)) {
                                continue;
                            }
                            $total = 0;
                            foreach ($profile_items_list as $key => $files) {
                                $total += count($files ?? []);
                                foreach ($files as $file) {
                                    if (is_array($file)) {
                                        $template_list .= $file['template_name'] . ', ';
                                    }
                                }
                            }
                            $template_list = substr($template_list, 0, -2);
                            $profile_esc = attr($profile);
                            foreach ($group_items_list as $key => $groups) {
                                foreach ($groups as $group) {
                                    if (is_array($group)) {
                                        $group_list_text .= $group_list[$group['member_of']]['title'] . ', ';
                                    }
                                }
                            }
                            $group_list_text = substr($group_list_text, 0, -2);
                            $send = 'send';
                            if (!empty($group_list_text)) {
                                $send = 'send_profile';
                            }
                            echo '<tr>';
                            $is_checked = '';
                            if ((int)$templateService->fetchProfileStatus($profiles['option_id']) === 1) {
                                $is_checked = 'checked';
                            }
                            echo "<td><input type='checkbox' class='form-check-inline' $is_checked name='" . attr($send) . "' data-send_profile='yes' value='" . $profile_esc . "' /></td>";
                            echo '<td>' . text($profiles['title']) . '</td>';
                            echo '<td><em>' . text($template_list) . '</em></td>';
                            echo '<td><em>' . text($group_list_text) . '</em></td>';
                            echo '</tr>';
                        }
                        if (empty($profile_list)) {
                            echo '<tr><td></td><td>' . $none_message . "</td></tr>\n";
                        }
                        echo "</tbody>\n";
                        echo "</table>\n";
                        ?>
                    </div>
                </div>
            </div>
            <!-- All Patients -->
            <hr />
            <div class='row'>
                <div class='col col-12' data-toggle='collapse' data-target='#template-collapse'>
                    <h5><i class='fa fa-eye mr-1' role='button' title="<?php echo xla('Click to expand or collapse All active patient templates panel.'); ?>"></i><?php echo '' . xlt('Default Patient Templates') . '' ?></h5>
                </div>
                <div class='col col-12 table-responsive <?php echo attr(($_REQUEST['all_state'] ?? '') ?: 'collapse') ?>' id='template-collapse'>
                    <?php
                    $templates = [];
                    $show_cat_flag = false;
                    if (!empty($category)) {
                        $templates = $templateService->getTemplateListByCategory($category);
                    } else {
                        $templates = $templateService->getTemplateListAllCategories();
                    }
                    echo "<table class='table table-sm table-striped table-bordered'>\n";
                    echo "<thead>\n";
                    echo "<tr>\n" .
                        '<th>' . xlt('Category') . '</th>' .
                        '<th>' . xlt('Template Actions') . '</th>' .
                        '<th>' . xlt('Size') . '</th>' .
                        '<th>' . xlt('Created') . '</th>' .
                        '<th>' . xlt('Last Modified') . '</th>' .
                        "</tr>\n";
                    echo "</thead>\n";
                    echo "<tbody>\n";
                    foreach ($templates as $cat => $files) {
                        if (empty($cat)) {
                            $cat = xlt('General');
                        }
                        foreach ($files as $file) {
                            $template_id = $file['id'];
                            echo '<tr>';
                            /*echo "<td><input type='checkbox' class='form-check-inline' id='send' name='send' value='" . attr($template_id) . "' /></td>";*/
                            echo '<td>' . text(ucwords($cat)) . '</td><td>';
                            echo '<button id="templateEdit' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ')" type="button">' . text($file['template_name']) . '</button>';
                            if ($authUploadTemplates) {
                                echo '<button id="templateDelete' . attr($template_id) .
                                    '" class="btn btn-sm btn-outline-danger" onclick="templateDelete(' . attr_js($template_id) . ')" type="button">' . xlt('Delete') . '</button>';
                            }
                            echo '<td>' . text($file['size']) . '</td>';
                            echo '<td>' . text(date('m/d/Y H:i:s', strtotime($file['modified_date']))) . '</td>';
                            echo '</tr>';
                            ?>
                            <?php
                        }
                    }
                    if (empty($files)) {
                        echo '<tr><td>' . $none_message . "</td></tr>\n";
                    }
                    echo "</tbody>\n";
                    echo "</table>\n";
                    ?>
                </div>
            </div>
            <hr />
            <div class='row'>
                <div class='col col-12'>
                    <div class='h5'>
                        <i class='fa fa-eye mr-1' data-toggle='collapse' data-target='#assigned_collapse' role='button' title="<?php echo xla('Click to expand or collapse Assigned Patients panel.'); ?>"></i><?php echo xlt('Patient Assigned Templates') ?>
                    </div>
                </div>
                <!-- Assigned table -->
                <div class='col col-12 table-responsive <?php echo attr(($_REQUEST['assigned_state'] ?? '') ?: 'collapse') ?>' id="assigned_collapse">
                    <?php
                    // by categories and patient pid.
                    $templates = [];
                    $show_cat_flag = false;
                    if (is_array($patient) && $patient[0] === '0') {// All templates for all patients
                        $patient_templates = $templateService->getPortalAssignedTemplates(0, $category, false);
                    } else {// Category selected so get all of them for pid's
                        $patient_templates = $templateService->getTemplateCategoriesByPids($patient, $category);
                    }
                    echo "<table class='table table-sm table-bordered'>\n";
                    echo "<tbody>";
                    $idcnt = 0;
                    foreach ($patient_templates as $name => $templates) {
                        $count = 0;
                        $fetched_groups = $fetch_pid = null;
                        foreach ($templates as $c => $t) {
                            if (is_array($t)) {
                                $fetch_pid = $t[0]['pid'];
                                if (empty($fetched_groups)) {
                                    $fetched_groups = str_replace('|', ', ', $t[0]['patient_groups'] ?? '');
                                }
                                $count += count($t);
                            }
                        }

                        echo "<tr><td class='h6 font-weight-bolder bg-light text-dark' data-toggle='collapse' data-target='" .
                            attr('#id' . ++$idcnt) . "' role='button'>" . text($name) .
                            " (" . text($count . ' ' . xl('Templates')) . ") in " . text($fetched_groups) . "</td></tr>";
                        echo "<td class='collapse' id='" . attr('id' . $idcnt) . "'><table class='table table-sm table-striped table-bordered'>\n";
                        //echo '<caption><h5>' . text($name) . '</h5></caption>';
                        echo "<thead>\n";
                        echo "<tr>\n" .
                            '<th>' . xlt('Category') . '</th>' .
                            '<th>' . xlt('Profile') . '</th>' .
                            '<th>' . xlt('Template Actions') . '</th>' .
                            '<th>' . xlt('Status') . '</th>' .
                            '<th>' . xlt('Last Action') . '</th>' .
                            '<th>' . xlt('Next Due') . '</th>' .
                            "</tr>\n";
                        echo "</thead>\n";
                        echo "<tbody>\n";
                        foreach ($templates as $cat => $files) {
                            if (empty($cat)) {
                                $cat = xlt('General');
                            }
                            foreach ($files as $file) {
                                $template_id = $file['id'];

                                $audit_status = $audit_status_blank;
                                $audit_status_fetch = $templateService->fetchPatientDocumentStatus($file['pid'], $file['id']);
                                if (is_array($audit_status_fetch)) {
                                    $audit_status = array_merge($audit_status_blank, $file, $audit_status_fetch);
                                } else {
                                    $audit_status = array_merge($audit_status_blank, $file);
                                }
                                $last_date = $audit_status['create_date'] ?? '' ?: $file['modified_date'] ?? '';
                                $next_due = $templateService->showTemplateFromEvent($file, true);
                                $action_status = '';
                                if ($next_due > 1) {
                                    if ($audit_status['denial_reason'] === 'In Review') {
                                        $action_status = xl('Scheduled but Needs Review');
                                    } else {
                                        $action_status = xl('Scheduled');
                                    }
                                    $next_due = date('m/d/Y', $next_due);
                                } elseif ($next_due === 1 || ($next_due === true && ($file['recurring'] ?? 0))) {
                                    if ($audit_status['denial_reason'] === 'In Review') {
                                        $action_status = xl('In audit. Needs Review');
                                    } else {
                                        $action_status = xl('Recurring');
                                    }
                                    $next_due = xl('Active');
                                } elseif ($next_due === 0) {
                                    $action_status = xl('Completed');
                                    $next_due = xl('Inactive');
                                } elseif ($next_due === true && empty($file['recurring'] ?? 0)) {
                                    $next_due = xl('Active');
                                }
                                echo '<tr><td>' . text(ucwords($cat)) . '</td>';
                                echo '<td>' . text($profile_list[$file['profile']]['title'] ?? '') . '</td>';
                                echo '<td>' .
                                    '<button type="button" id="patientEdit' . attr($template_id) .
                                    '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ')" title="' . xla("Click to edit in editor.") . '">' .
                                    text($file['template_name']) . "</button>\n";
                                if ($authUploadTemplates && $cat == 'questionnaire' && !empty($audit_status['encounter'])) {
                                    echo '<button type="button" id="patientView' . attr($template_id) .
                                        '" class="btn btn-sm btn-outline-success" onclick="questionnaireViewCurrent(' . attr_js($audit_status['encounter']) . ')">' .
                                        xlt("View") . "</button>\n";
                                }
                                if ($authUploadTemplates && empty($file['member_of']) && !empty($file['status'])) {
                                    echo '<button type="button" id="patientDelete' . attr($template_id) .
                                        '" class="btn btn-sm btn-outline-danger" onclick="templateDelete(' . attr_js($template_id) . ')">' . xlt('Delete') . "</button>\n";
                                }
                                // onetime button for template.
                                $file['onetime_period'] = "P2D";
                                $file['is_onetime'] = 1;
                                $file['audit_id'] = 0;
                                if ($audit_status['denial_reason'] == 'In Review' || $audit_status['denial_reason'] == 'Editing') {
                                    $file['audit_id'] = $audit_status['audit_id'] ?? 0;
                                }
                                $e_pid = $fetch_pid ?: $file['pid'];
                                if (!empty($e_pid)) {
                                    $eventDispatcher->dispatch(new SendNotificationEvent($e_pid, $file), SendNotificationEvent::ACTIONS_RENDER_NOTIFICATION_POST);
                                }

                                echo '</td><td>' . text($action_status) . '</td>';
                                echo '<td>' . text(date('m/d/Y H:i:s', strtotime($last_date))) . '</td>';
                                echo '<td>' . text($next_due) . '</td>';
                                echo "</tr>\n";
                            }
                        }
                        echo "</tbody>\n";
                        echo "</table></td>\n";
                    }
                    if (empty($templates)) {
                        echo '<tr><td>' . xlt('Multi Select Patients or All Patients using toolbar Location') . "</td></tr>\n";
                    }
                    echo "</tbody>\n";
                    echo "</table>\n";
                    ?>
                </div>
            </div>
            <hr />
        </div>
    </div>
</body>
</html>
