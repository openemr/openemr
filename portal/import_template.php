<?php

/**
 * import_template.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

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

if ($_POST['mode'] === 'get') {
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
} elseif (!empty($_FILES["template_files"])) {
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
            echo '<title>' . xlt('Error') . " ...</title><h4 style='color:red;'>" .
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
        $success = $templateService->uploadTemplate($name, $_POST['template_category'], $_FILES['template_files']['tmp_name'][$i], $patient);
        if (!$success) {
            echo "<p>" . xlt("Unable to save files. Use back button!") . "</p>";
            exit;
        }
    }
    header("location: " . $_SERVER['HTTP_REFERER']);
    die();
}

if ($_REQUEST['mode'] === 'editor_render_html') {
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
        '{ParseAsHTML}', '{SignaturesRequired}', '{TextInput}', '{sizedTextInput:120px}', '{smTextInput}', '{TextBox:03x080}', '{CheckMark}', '{ynRadioGroup}', '{TrueFalseRadioGroup}', '{DatePicker}', '{DateTimePicker}', '{StandardDatePicker}', '{CurrentDate:"global"}', '{CurrentTime}', '{DOS}', '{ReferringDOC}', '{PatientID}', '{PatientName}', '{PatientSex}', '{PatientDOB}', '{PatientPhone}', '{Address}', '{City}', '{State}', '{Zip}', '{PatientSignature}', '{AdminSignature}', '{WitnessSignature}', '{AcknowledgePdf:pdf name or id:title}', '{EncounterForm:LBF}', '{Medications}', '{ProblemList}', '{Allergies}', '{ChiefComplaint}', '{DEM: }', '{HIS: }', '{LBF: }', '{GRP}{/GRP}'
    ];
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?php Header::setupHeader(['ckeditor']); ?>
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

      .cke_contents {
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
            editor = CKEDITOR.instances['templateContent'];
            if (editor) {
                editor.destroy(true);
            }
            CKEDITOR.disableAutoInline = true;
            CKEDITOR.config.extraPlugins = "preview,save,docprops,justify";
            CKEDITOR.config.allowedContent = true;
            //CKEDITOR.config.fullPage = true;
            CKEDITOR.config.height = height;
            CKEDITOR.config.width = '100%';
            CKEDITOR.config.resize_dir = 'both';
            CKEDITOR.config.resize_minHeight = max / 2;
            CKEDITOR.config.resize_maxHeight = max;
            CKEDITOR.config.resize_minWidth = '50%';
            CKEDITOR.config.resize_maxWidth = '100%';

            editor = CKEDITOR.replace('templateContent', {
                removeButtons: 'PasteFromWord'
            });
        });
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
                    animation: 150
                });
            });
        });

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
                    let pform = document.getElementById(ulItem.dataset.profile + '-form');
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
                        <div class='bg-dark text-light py-1 text-center'><?php echo xlt('Available Templates'); ?></div>
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
                                    echo "<li class='list-group-item px-1 py-1 mb-2' data-id='$template_id' data-name='$this_name' data-category='$title_esc'>" .
                                        "<strong>" . text($file['template_name']) .
                                        '</strong>' . ' ' . xlt('in category') . ' ' .
                                        '<strong>' . text($title) . '</strong>' . '</li>' . "\n";
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
                            $events = $templateService->fetchAllProfileEvents();
                            $recurring = attr($events[$profile]['recurring'] ?? '');
                            $trigger = attr($events[$profile]['event_trigger'] ?? '');
                            $days = attr($events[$profile]['period'] ?? '');
                            ?>
                            <form id="<?php echo $profile_esc ?>-form" name="<?php echo $profile_esc; ?>" class='form form-inline bg-dark text-light py-1 pl-1'>
                                <label class='mr-1'><?php echo xlt($profiles['title']) ?></label>
                                <div class='input-group-prepend ml-auto'>
                                    <label for="<?php echo $profile_esc ?>-recurring" class="form-check-inline"><?php echo xlt('Recurring') ?>
                                        <input <?php echo $recurring ? 'checked' : '' ?> name="recurring" type='checkbox' class="input-control ml-1 mt-1" id="<?php echo $profile_esc ?>-recurring" />
                                    </label>
                                </div>
                                <!-- @TODO Hide for now until sensible events can be determined. -->
                                <div class='input-group-prepend d-none'>
                                    <label for="<?php echo $profile_esc ?>-when"><?php echo xlt('On') ?></label>
                                    <select name="when" class='input-control-sm mx-1' id="<?php echo $profile_esc ?>-when">
                                        <!--<option value=""><?php /*echo xlt('Unassigned') */?></option>-->
                                        <option <?php echo $trigger === 'completed' ? 'selected' : ''; ?> value="completed"><?php echo xlt('Completed') ?></option>
                                        <option <?php echo $trigger === 'always' ? 'selected' : ''; ?> value='always'><?php echo xlt('Always') ?></option>
                                        <option <?php echo $trigger === 'once' ? 'selected' : ''; ?> value='once'><?php echo xlt('One time') ?></option>
                                    </select>
                                </div>
                                <div class='input-group-prepend'>
                                    <label for="<?php echo $profile_esc ?>-days"><?php echo xlt('Every') ?></label>
                                    <input name="days" type="text" style="width: 50px" class='input-control-sm ml-1' id="<?php echo $profile_esc ?>-days" placeholder="<?php echo xla('days') ?>" value="<?php echo $days ?>" />
                                    <label class="mx-1" for="<?php echo $profile_esc ?>-days"><?php echo xlt('Days') ?></label>
                                </div>
                            </form>
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
                                    if ($file['mime'] === 'application/pdf') {
                                        continue;
                                    }
                                    echo "<li class='list-group-item px-1 py-1 mb-2' data-id='$template_id' data-name='$this_name' data-category='$this_cat'>" .
                                        text($file['template_name']) . ' ' . xlt('in category') . ' ' . text($title) . "</li>\n";
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
