<?php

/**
 * import_template.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;
use OpenEMR\Services\QuestionnaireService;

if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}

$authUploadTemplates = AclMain::aclCheckCore('admin', 'forms');
$templateService = new DocumentTemplateService();
$patient = json_decode($_POST['upload_pid'] ?? '');
$template_content = null;

if (($_POST['mode'] ?? null) === 'save_profiles') {
    $profiles = json_decode($_POST['profiles'], true);
    $rtn = $templateService->saveAllProfileTemplates($profiles);
    if ($rtn) {
        echo xlt("Profiles successfully saved.");
    } else {
        echo xlt('Error! Profiles save failed. Check your Profile lists.');
    }
    exit;
}

if (($_REQUEST['mode'] ?? null) === 'render_profile') {
    echo renderProfileHtml();
    exit;
}

if (($_REQUEST['mode'] ?? null) === 'getPdf') {
    if ($_REQUEST['docid']) {
        $template = $templateService->fetchTemplate($_REQUEST['docid']);
        echo "data:application/pdf;base64," . base64_encode($template['template_content']);
        exit();
    }
    die(xlt('Invalid File'));
}

if (($_POST['mode'] ?? null) === 'get') {
    if ($_REQUEST['docid']) {
        $template = $templateService->fetchTemplate($_POST['docid']);
        echo $template['template_content'];
        exit();
    }
    die(xlt('Invalid File'));
}

if (($_POST['mode'] ?? null) === 'send_profiles') {
    if (!empty($_POST['checked'])) {
        $profiles = json_decode($_POST['checked']) ?: [];
        $last_id = $templateService->setProfileActiveStatus($profiles);
        if ($last_id) {
            echo xlt('Profile Templates Successfully set to Active in portal.');
        } else {
            echo xlt('Error. Problem setting one or more profiles.');
        }
        exit;
    }
    die(xlt('Invalid Request'));
}

if (($_POST['mode'] ?? null) === 'send') {
    if (!empty($_POST['docid'])) {
        $pids_array = json_decode($_POST['docid']) ?: ['0'];
        // profiles are in an array with flag to indicate a group of template id's
        $ids = json_decode($_POST['checked']) ?: [];
        $master_ids = [];
        foreach ($ids as $id) {
            if (is_array($id)) {
                if ($id[1] !== true) {
                    continue;
                }
                $profile = $id[0];
                // get all template ids for this profile
                $rtn_ids = sqlStatement('SELECT `template_id` as id FROM `document_template_profiles` WHERE `profile` = ? AND `template_id` > "0"', array($profile));
                while ($rtn_id = sqlFetchArray($rtn_ids)) {
                    $master_ids[$rtn_id['id']] = $profile;
                }
                continue;
            }
            $master_ids[$id] = '';
        }
        $last_id = $templateService->sendTemplate($pids_array, $master_ids, $_POST['category']);
        if ($last_id) {
            echo xlt('Templates Successfully sent to Locations.');
        } else {
            echo xlt('Error. Problem sending one or more templates.');
        }
        exit;
    }
    die(xlt('Invalid Request'));
}

if (($_POST['mode'] ?? null) === 'save') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'import-template-save')) {
        CsrfUtils::csrfNotVerified();
    }
    if (!$authUploadTemplates) {
        die(xlt('Not authorized to edit template'));
    }
    if ($_POST['docid']) {
        if (stripos($_POST['content'], "<?php") === false) {
            $template = $templateService->updateTemplateContent($_POST['docid'], $_POST['content']);
            if ($_POST['service'] === 'window') {
                echo "<script>if (typeof parent.dlgopen === 'undefined') window.close(); else parent.dlgclose();</script>";
            }
        } else {
            die(xlt('Invalid Content'));
        }
    } else {
        die(xlt('Invalid File'));
    }
} elseif (($_POST['mode'] ?? null) === 'delete') {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'import-template-delete')) {
        CsrfUtils::csrfNotVerified();
    }
    if (!$authUploadTemplates) {
        die(xlt('Not authorized to delete template'));
    }
    if ($_POST['docid']) {
        $template = $templateService->deleteTemplate($_POST['docid'], ($_POST['template'] ?? null));
        exit($template);
    }
    die(xlt('Invalid File'));
} elseif (($_POST['mode'] ?? null) === 'update_category') {
    if ($_POST['docid']) {
        $template = $templateService->updateTemplateCategory($_POST['docid'], $_POST['category']);
        echo xlt('Template Category successfully changed to new Category') . ' ' . text($_POST['category']);
        exit;
    }
    die(xlt('Invalid Request Parameters'));
}

if (isset($_POST['blank-nav-button'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'import-template-upload')) {
        CsrfUtils::csrfNotVerified();
    }
    if (!$authUploadTemplates) {
        xlt("Not Authorized to Upload Templates");
        exit;
    }
    $is_blank = isset($_POST['blank-nav-button']);
    $upload_name = $_POST['upload_name'] ?? '';
    $category = $_POST['template_category'] ?? '';
    $patient = '-1';
    if (!empty($upload_name)) {
        $name = preg_replace("/[^A-Z0-9.]/i", " ", $upload_name);
        try {
            $content = "{ParseAsHTML}";
            $success = $templateService->insertTemplate($patient, $category, $upload_name, $content, 'application/text');
            if (!$success) {
                header('refresh:3;url= import_template_ui.php');
                echo "<h4 style='color:red;'>" . xlt("New template save failed. Try again.") . "</h4>";
                exit;
            }
        } catch (Exception $e) {
            header('refresh:3;url= import_template_ui.php');
            echo '<h3>' . xlt('Error') . "</h3><h4 style='color:red;'>" .
                text($e->getMessage()) . '</h4>';
            exit;
        }
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    die();
}

if (isset($_REQUEST['q_mode']) && !empty($_REQUEST['q_mode'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'import-template-upload')) {
        CsrfUtils::csrfNotVerified();
    }
    if (!$authUploadTemplates) {
        xlt("Not Authorized to Upload Templates");
        exit;
    }
    $id = 0;
    $q = $_POST['questionnaire'] ?? '';
    $l = $_POST['lform'] ?? '';
    if (!empty($q)) {
        $service = new QuestionnaireService();
        try {
            $id = $service->saveQuestionnaireResource($q, null, null, null, $l);
        } catch (Exception $e) {
            header('refresh:3;url= import_template_ui.php');
            echo '<h3>' . xlt('Error') . "</h3><h4 style='color:red;'>" .
                text($e->getMessage()) . '</h4>';
            exit;
        }
        if (empty($id)) {
            header('refresh:3;url= import_template_ui.php');
            echo '<h3>' . xlt('Error') . "</h3><h4 style='color:red;'>" .
                xlt("Import failed to save.") . '</h4>';
            exit;
        }
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    die();
}

// templates file import
if ((count($_FILES['template_files']['name'] ?? []) > 0) && !empty($_FILES['template_files']['name'][0] ?? '')) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'import-template-upload')) {
        CsrfUtils::csrfNotVerified();
    }
    if (!$authUploadTemplates) {
        xlt("Not Authorized to Upload Templates");
        exit;
    }
    // so it is a template file import. create record(s).
    $import_files = $_FILES["template_files"];
    $total = count($_FILES['template_files']['name']);
    for ($i = 0; $i < $total; $i++) {
        if ($_FILES['template_files']['error'][$i] !== UPLOAD_ERR_OK) {
            header('refresh:3;url= import_template_ui.php');
            echo '<h3>' . xlt('Error') . "</h3><h4 style='color:red;'>" .
                xlt('An error occurred: Missing file to upload. Returning to form.') . '</h4>';
            exit;
        }
        // parse out what we need
        $name = preg_replace("/[^A-Z0-9.]/i", " ", $_FILES['template_files']['name'][$i]);
        if (preg_match("/(.*)\.(php|php7|php8|doc|docx)$/i", $name) !== 0) {
            die(xlt('Invalid file type.'));
        }
        $parts = pathinfo($name);
        $name = ucwords(strtolower($parts["filename"]));
        if (empty($patient)) {
            $patient = ['-1'];
        }
        // get em and dispose
        try {
            $success = $templateService->uploadTemplate($name, $_POST['template_category'], $_FILES['template_files']['tmp_name'][$i], $patient, isset($_POST['upload_submit_questionnaire']));
            if (!$success) {
                echo "<p>" . xlt("Unable to save files. Use back button!") . "</p>";
                exit;
            }
        } catch (Exception $e) {
            header('refresh:3;url= import_template_ui.php');
            echo '<h3>' . xlt('Error') . "</h3><h4 style='color:red;'>" .
                text($e->getMessage()) . '</h4>';
            exit;
        }
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    die();
}

if (isset($_POST['repository-submit']) && !empty($_POST['upload_name'] ?? '')) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], 'import-template-upload')) {
        CsrfUtils::csrfNotVerified();
    }
    if (!$authUploadTemplates) {
        xlt("Not Authorized to Upload Templates");
        exit;
    }
    $selected_q = (int)($_POST['select_item'] ?? 0);
    $upload_name = $_POST['upload_name'] ?? '';
    $category = $_POST['template_category'] ?? '';
    if (empty($category)) {
        $category = 'questionnaire';
    }
    if (empty($patient) || $patient === [-1]) {
        $patient = '-1';
    }
    if (!empty($upload_name)) {
        // will use same name as questionnaire from repository
        try {
            $content = "{ParseAsHTML}{Questionnaire:$selected_q}" . "\n";
            $mimetype = 'application/text';
            $success = $templateService->insertTemplate($patient, $category, $upload_name, $content, 'application/text');
            if (!$success) {
                header('refresh:3;url= import_template_ui.php');
                echo "<h4 style='color:red;'>" . xlt("New template save failed. Try again.") . "</h4>";
                exit;
            }
        } catch (Exception $e) {
            header('refresh:3;url= import_template_ui.php');
            echo '<h3>' . xlt('Error') . "</h3><h4 style='color:red;'>" .
                text($e->getMessage()) . '</h4>';
            exit;
        }
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    die();
}

if (($_REQUEST['mode'] ?? '') === 'editor_render_html') {
    if ($_REQUEST['docid']) {
        $content = $templateService->fetchTemplate($_REQUEST['docid']);
        $template_content = $content['template_content'];
        if ($content['mime'] === 'application/pdf') {
            $content = "<iframe width='100%' height='100%' src='data:application/pdf;base64, " .
                attr(base64_encode($template_content)) . "'></iframe>";
            echo $content;
            exit;
        }
        renderEditorHtml($_REQUEST['docid'], $template_content);
    } else {
        die(xlt('Invalid File'));
    }
} elseif (!empty($_GET['templateHtml'] ?? null)) {
    renderEditorHtml($_REQUEST['docid'], $_GET['templateHtml']);
}

/**
 * @param $template_id
 * @param $content
 */
function renderEditorHtml($template_id, $content)
{
    global $authUploadTemplates;

    $lists = [
        '{ParseAsHTML}', '{ParseAsText}', '{styleBlockStart}', '{styleBlockEnd}', '{SignaturesRequired}', '{TextInput}', '{sizedTextInput:120px}', '{smTextInput}', '{TextBox:03x080}', '{CheckMark}', '{RadioGroup:option1_many...}', '{RadioGroupInline:option1_many...}', '{ynRadioGroup}', '{TrueFalseRadioGroup}', '{DatePicker}', '{DateTimePicker}', '{StandardDatePicker}', '{CurrentDate:"global"}', '{CurrentTime}', '{DOS}', '{ReferringDOC}', '{PatientID}', '{PatientName}', '{PatientSex}', '{PatientDOB}', '{PatientPhone}', '{Address}', '{City}', '{State}', '{Zip}', '{PatientSignature}', '{AdminSignature}', '{WitnessSignature}', '{AcknowledgePdf:pdf name or id:title}', '{EncounterForm:LBF}', '{Questionnaire:name or id}', '{Medications}', '{ProblemList}', '{Allergies}', '{ChiefComplaint}', '{DEM: }', '{HIS: }', '{LBF: }', '{GRP}{/GRP}'
    ];
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?php Header::setupHeader(['summernote']); ?>
    </head>
    <style>
        input:focus,
        input:active {
            outline: 0 !important;
            -webkit-appearance: none;
            box-shadow: none !important;
        }

        .list-group-item {
            font-size: .9rem;
        }

        .note-editable {
            height: 78vh !important;
        }
    </style>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-10 px-1 sticky-top">
                    <form class="sticky-top" action='./import_template.php' method='post'>
                        <input type="hidden" name="csrf_token_form" id="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken('import-template-save')); ?>" />
                        <input type="hidden" name="docid" value="<?php echo attr($template_id) ?>">
                        <input type='hidden' name='mode' value="save">
                        <input type='hidden' name='service' value='window'>
                        <textarea cols='80' rows='10' id='templateContent' name='content'><?php echo text($content) ?></textarea>
                        <div class="row btn-group mt-1 float-right">
                            <div class='col btn-group mt-1 float-right'>
                                <?php if ($authUploadTemplates) { ?>
                                    <button type="submit" class="btn btn-sm btn-primary"><?php echo xlt("Save"); ?></button>
                                <?php } else { ?>
                                    <button disabled title="<?php echo xla("Not Authorized to Edit Templates") ?>" type="submit" class="btn btn-sm btn-primary"><?php echo xlt("Save"); ?></button>
                                <?php } ?>
                                <button type='button' class='btn btn-sm btn-secondary' onclick='parent.window.close() || parent.dlgclose()'><?php echo xlt('Cancel'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-sm-2 px-0">
                    <div class='h4'><?php echo xlt("Directives") ?></div>
                    <ul class='list-group list-group-flush pl-1 mb-5'>
                        <?php
                        foreach ($lists as $list) {
                            echo '<input class="list-group-item p-1" value="' . attr($list) . '">';
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </body>
    <script>
        let isDialog = false;
        let height = 550;
        let max = 680;
        <?php if (!empty($_REQUEST['dialog'] ?? '')) { ?>
        isDialog = true;
        height = 425;
        max = 600;
        <?php } ?>
        let editor = '';
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.list-group-item').forEach(item => {
                item.addEventListener('mouseup', event => {
                    let input = event.currentTarget;
                    input.focus();
                    input.select();
                })
            })
        });
        $(function () {
            $('#templateContent').summernote({
                placeholder: 'Start typing here...',
                height: 550,
                minHeight: 300,
                maxHeight: 800,
                width: '100%',
                tabsize: 4,
                focus: true,
                disableDragAndDrop: true,
                dialogsInBody: true,
                dialogsFade: true
            });
        });
    </script>
    <script>
    </script>
    </html>
<?php }

/**
 *
 */
function renderProfileHtml()
{
    global $templateService;

    $category_list = $templateService->fetchDefaultCategories();
    $profile_list = $templateService->fetchDefaultProfiles();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?php
        if (empty($GLOBALS['openemr_version'] ?? null)) {
            Header::setupHeader(['opener', 'sortablejs']);
        } else {
            Header::setupHeader(['opener']); ?>
            <script src="<?php echo $GLOBALS['web_root']; ?>/portal/public/assets/sortablejs/Sortable.min.js?v=<?php echo $GLOBALS['v_js_includes']; ?>"></script>
        <?php } ?>
    </head>
    <style>
        body {
            overflow: hidden;
        }

        .list-group-item {
            cursor: move;
        }

        strong {
            font-weight: 600;
        }

        .col-height {
            max-height: 95vh;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .note-editor.dragover .note-dropzone {
            display: none
        }
    </style>
    <script>
        const profiles = <?php echo js_escape($profile_list); ?>;
        document.addEventListener('DOMContentLoaded', function () {
            // init drag and drop
            let repository = document.getElementById('drag_repository');
            Sortable.create(repository, {
                group: {
                    name: 'repo',
                    handle: '.move-handle',
                    pull: 'clone'
                },
                sort: true,
                animation: 150,
                onAdd: function (evt) {
                    let el = evt.item;
                    el.parentNode.removeChild(el);
                }
            });

            Object.keys(profiles).forEach(key => {
                let profileEl = profiles[key]['option_id']
                let id = document.getElementById(profileEl);
                Sortable.create(id, {
                    group: {
                        name: 'repo',
                        delay: 1000,
                        handle: '.move-handle',
                        put: (to, from, dragEl, event) => {
                            for (let i = 0; i < to.el.children.length; i++) {
                                if (to.el.children[i].getAttribute('data-id') === dragEl.getAttribute('data-id')) {
                                    return false
                                }
                            }
                            return true
                        },
                    },
                    onAdd: function (evt) {
                        let el = evt.item;
                        el.getElementsByTagName('form')[0].classList.remove("d-none");
                    },
                    animation: 150
                });
            });
        });
        top.restoreSession();

        function submitProfiles() {
            top.restoreSession();
            let target = document.getElementById('edit-profiles');
            let profileTarget = target.querySelectorAll('ul');
            let formTarget = target.querySelectorAll('form');
            let profileArray = [];
            let listData = {};
            profileTarget.forEach((ulItem, index) => {
                let lists = ulItem.querySelectorAll('li');
                lists.forEach((item, index) => {
                    //console.log({index, item})
                    let pform = item.getElementsByTagName('form')[0];
                    let formData = $(pform).serializeArray();
                    listData = {
                        'form': formData,
                        'profile': ulItem.dataset.profile,
                        'id': item.dataset.id,
                        'category': item.dataset.category,
                        'name': item.dataset.name
                    }
                    profileArray.push(listData);
                });
            });
            const data = new FormData();
            data.append('profiles', JSON.stringify(profileArray));
            data.append('mode', 'save_profiles');
            fetch('./import_template.php', {
                method: 'POST',
                body: data,
            }).then(rtn => rtn.text()).then((rtn) => {
                (async (time) => {
                    await asyncAlertMsg(rtn, time, 'success', 'lg');
                })(1000).then(rtn => {
                    opener.document.edit_form.submit();
                    dlgclose();
                });
            }).catch((error) => {
                console.error('Error:', error);
            });
        }
    </script>
    <body>
        <div class='container-fluid'>
            <?php
            // exclude templates sent to all patients(defaults)
            $templates = $templateService->getTemplateListAllCategories(-1, true);
            //$templates = $templateService->getTemplateListUnique(); // Reserved TBD future use
            ?>
            <div class='row'>
                <div class='col-6 col-height'>
                    <nav id='disposeProfile' class='navbar navbar-light bg-light sticky-top'>
                        <div class='btn-group'>
                            <button class='btn btn-primary btn-save btn-sm' onclick='return submitProfiles();'><?php echo xlt('Save Profiles'); ?></button>
                            <button class='btn btn-secondary btn-cancel btn-sm' onclick='dlgclose();'><?php echo xlt('Quit'); ?></button>
                        </div>
                    </nav>
                    <div class="border-left border-right">
                        <div class='bg-dark text-light py-1 mb-2 text-center'><?php echo xlt('Available Templates'); ?></div>
                        <ul id='drag_repository' class='list-group mx-2 mb-2'>
                            <?php
                            foreach ($templates as $cat => $files) {
                                if (empty($cat)) {
                                    $cat = xlt('General');
                                }
                                foreach ($files as $file) {
                                    $template_id = attr($file['id']);
                                    $title = $category_list[$cat]['title'] ?: $cat;
                                    $title_esc = attr($title);
                                    $this_name = attr($file['template_name']);
                                    if ($file['mime'] === 'application/pdf') {
                                        continue;
                                    }
                                    /* The drag container */
                                    echo "<li class='list-group-item px-1 py-1 mb-1 bg-primary' data-id='$template_id' data-name='$this_name' data-category='$title_esc'>" .
                                        "<strong>" . text($file['template_name']) .
                                        '</strong>' . ' ' . xlt('in category') . ' ' .
                                        '<strong>' . text($title) . '</strong>';
                                    ?>
                                    <form class='form form-inline bg-light text-dark py-1 pl-1 d-none'>
                                        <div class='input-group-sm input-group-prepend'>
                                            <label class="form-check-inline d-none"><?php echo xlt('OneTime') ?>
                                                <input name="onetimeIsOkay" type='checkbox' class="input-control-sm ml-1 mt-1" title="<?php echo xla('Enable Auto Portal log in for presenting document to patient.') ?>" />
                                            </label>
                                        </div>
                                        <label class='font-weight-bold mr-1 d-none'><?php echo xlt('Notify') ?></label>
                                        <div class='input-group-sm input-group-prepend d-none'>
                                            <input name="notify_days" type="text" style="width: 50px;" class='input-control-sm ml-1' placeholder="<?php echo xla('days') ?>" value="" />
                                            <label class="mx-1"><?php echo xlt('Days') ?></label>
                                        </div>
                                        <div class='input-group-sm input-group-prepend'>
                                            <select name="notify_when" class='input-control-sm mx-1 d-none'>
                                                <option value=""><?php echo xlt('Unassigned'); ?></option>
                                                <option value="new"><?php echo xlt('New'); ?></option>
                                                <option value='before_appointment'><?php echo xlt('Before Appointment'); ?></option>
                                                <option value='after_appointment'><?php echo xlt('After Appointment'); ?></option>
                                                <option value="before_expires"><?php echo xlt('Before Expires'); ?></option>
                                                <option value="in_edit"><?php echo xlt('In Edit'); ?></option>
                                            </select>
                                        </div>
                                        <div class='input-group-sm input-group-prepend'>
                                            <label class="form-check-inline"><?php echo xlt('Recurring') ?>
                                                <input name="recurring" type='checkbox' class="input-control-sm ml-1 mt-1" />
                                            </label>
                                        </div>
                                        <div class='input-group-sm input-group-prepend'>
                                            <label><?php echo xlt('On') ?></label>
                                            <select name="when" class='input-control-sm mx-1'>
                                                <!--<option value=""><?php /*echo xlt('Unassigned') */ ?></option>-->
                                                <option value="completed"><?php echo xlt('Completed') ?></option>
                                                <option value='always'><?php echo xlt('Always') ?></option>
                                                <option value='once'><?php echo xlt('One time') ?></option>
                                            </select>
                                        </div>
                                        <div class='input-group-sm input-group-prepend'>
                                            <label><?php echo xlt('Every') ?></label>
                                            <input name="days" type="text" style="width: 50px;" class='input-control-sm ml-1' placeholder="<?php echo xla('days') ?>" value="" />
                                            <label class="mx-1"><?php echo xlt('Days') ?></label>
                                        </div>
                                    </form>
                                    <?php
                                    echo '</li>' . "\n";
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class='col-6 col-height'>
                    <div id="edit-profiles" class='control-group mx-1 border-left border-right'>
                        <?php
                        foreach ($profile_list as $profile => $profiles) {
                            $profile_items_list = $templateService->getTemplateListByProfile($profile);
                            $profile_esc = attr($profile);
                            ?>
                            <div class='bg-dark text-light mb-1 py-1 pl-1'><?php echo xlt($profiles['title']) ?></div>
                            <?php
                            echo "<ul id='$profile_esc' class='list-group mx-2 mb-2' data-profile='$profile_esc'>\n";
                            foreach ($profile_items_list as $cat => $files) {
                                if (empty($cat)) {
                                    $cat = xlt('General');
                                }
                                foreach ($files as $file) {
                                    $template_id = attr($file['id']);
                                    $this_cat = attr($file['category']);
                                    $title = $category_list[$file['category']]['title'] ?: $cat;
                                    $this_name = attr($file['template_name']);
                                    $events = $templateService->fetchTemplateEvent($profile, $template_id);
                                    $recurring = attr($events['recurring'] ?? '');
                                    $trigger = attr($events['event_trigger'] ?? ''); // max 32 char
                                    $notify_trigger = attr($events['notify_trigger'] ?? ''); // max 32 char
                                    $days = attr($events['period'] ?? '');
                                    $notify_days = attr($events['notify_period'] ?? '');
                                    if ($file['mime'] === 'application/pdf') {
                                        continue;
                                    }
                                    ?>
                                    <li class='list-group-item bg-warning text-light px-1 py-1 mb-1' data-id="<?php echo $template_id; ?>" data-name="<?php echo $this_name; ?>" data-category="<?php echo $this_cat; ?>">
                                        <span class="p-1 font-weight-bold"><?php echo text($file['template_name']) . ' ' . xlt('in category') . ' ' . text($title); ?></span>
                                        <!-- Notice! The notify event input is patched out until I get around to it. -->
                                        <form class='form form-inline bg-light text-dark py-1 pl-1'>
                                            <div class='input-group-sm input-group-prepend d-none'>
                                                <label class="form-check-inline"><?php echo xlt('OneTime') ?>
                                                    <input name="onetimeIsOkay" type='checkbox' class="input-control-sm ml-1 mt-1" title="<?php echo xla('Enable Auto Portal log in for presenting document to patient.') ?>" />
                                                </label>
                                            </div>
                                            <label class='font-weight-bold mr-1 d-none'><?php echo xlt('Notify') ?></label>
                                            <div class='input-group-sm input-group-prepend d-none'>
                                                <input name="notify_days" type="text" style="width: 50px;" class='input-control-sm ml-1' placeholder="<?php echo xla('days') ?>" value="<?php echo $notify_days ?>" />
                                                <label class="mx-1"><?php echo xlt('Days') ?></label>
                                            </div>
                                            <div class='input-group-sm input-group-prepend d-none'>
                                                <select name="notify_when" class='input-control-sm mx-1'>
                                                    <option value=""><?php echo xlt('Unassigned'); ?></option>
                                                    <option <?php echo $notify_trigger === 'new' ? 'selected' : ''; ?> value="new"><?php echo xlt('New'); ?></option>
                                                    <option <?php echo $notify_trigger === 'before_appointment' ? 'selected' : ''; ?> value='before_appointment'><?php echo xlt('Before Appointment'); ?></option>
                                                    <option <?php echo $notify_trigger === 'after_appointment' ? 'selected' : ''; ?> value='after_appointment'><?php echo xlt('After Appointment'); ?></option>
                                                    <option <?php echo $notify_trigger === 'before_expires' ? 'selected' : ''; ?> value="before_expires"><?php echo xlt('Before Expires'); ?></option>
                                                    <option <?php echo $notify_trigger === 'in_edit' ? 'selected' : ''; ?> value="in_edit"><?php echo xlt('In Edit'); ?></option>
                                                </select>
                                            </div>
                                            <div class='input-group-sm input-group-prepend'>
                                                <label class="form-check-inline"><?php echo xlt('Recurring') ?>
                                                    <input <?php echo $recurring ? 'checked' : '' ?> name="recurring" type='checkbox' class="input-control-sm ml-1 mt-1" />
                                                </label>
                                            </div>
                                            <div class='input-group-sm input-group-prepend'>
                                                <label><?php echo xlt('On') ?></label>
                                                <select name="when" class='input-control-sm mx-1'>
                                                    <!--<option value=""><?php /*echo xlt('Unassigned') */ ?></option>-->
                                                    <option <?php echo $trigger === 'completed' ? 'selected' : ''; ?> value="completed"><?php echo xlt('Completed') ?></option>
                                                    <option <?php echo $trigger === 'always' ? 'selected' : ''; ?> value='always'><?php echo xlt('Always') ?></option>
                                                    <option <?php echo $trigger === 'once' ? 'selected' : ''; ?> value='once'><?php echo xlt('One time') ?></option>
                                                    <option <?php echo $trigger === '30:90:365' ? 'selected' : ''; ?> value='30:90:365'><?php echo xlt('30-90-365') ?></option>
                                                </select>
                                            </div>
                                            <div class='input-group-sm input-group-prepend'>
                                                <label><?php echo xlt('Every') ?></label>
                                                <input name="days" type="text" style="width: 50px;" class='input-control-sm ml-1' placeholder="<?php echo xla('days') ?>" value="<?php echo $days ?>" />
                                                <label class="mx-1" for="<?php echo $profile_esc ?>-days"><?php echo xlt('Days') ?></label>
                                            </div>
                                        </form>
                                    </li>
                                    <?php
                                }
                            }
                            echo "</ul>\n";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <hr />
    </body>
    </html>
<?php }
