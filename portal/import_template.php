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

use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

$templateService = new DocumentTemplateService();
$patient = json_decode($_POST['upload_pid']);

$template_content = null;

if ($_REQUEST['mode'] === 'getPdf') {
    if ($_REQUEST['docid']) {
        $template = $templateService->fetchTemplate($_REQUEST['docid']);
        echo "data:application/pdf;base64," . base64_encode($template['template_content']);
        exit();
    } else {
        die(xlt('Invalid File'));
    }
}

if ($_POST['mode'] === 'get') {
    if ($_REQUEST['docid']) {
        $template = $templateService->fetchTemplate($_POST['docid']);
        echo $template['template_content'];
        exit();
    } else {
        die(xlt('Invalid File'));
    }
} elseif ($_POST['mode'] === 'save') {
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
} elseif ($_POST['mode'] === 'delete') {
    if ($_POST['docid']) {
        $template = $templateService->deleteTemplate($_POST['docid']);
        exit(true);
    }
    die(xlt('Invalid File'));
} elseif ($_POST['mode'] === 'send') {
    if (!empty($_POST['docid'])) {
        $pids_array = json_decode($_POST['docid']) ?: ['0'];
        $ids = json_decode($_POST['checked']) ?: [];

        $last_id = $templateService->sendTemplate($pids_array, $ids, $_POST['category']);
        if ($last_id) {
            echo xlt("Templates Successfully sent to patients.");
        } else {
            echo xlt('Error. Problem sending one or more templates. Some templates may not have been sent.');
        }
        exit;
    }
    die(xlt('Invalid Request'));
} elseif ($_POST['mode'] === 'update_category') {
    if ($_POST['docid']) {
        $template = $templateService->updateTemplateCategory($_POST['docid'], $_POST['category']);
        echo xlt('Template Category successfully changed to new Category') . ' ' . text($_POST['category']);
        exit;
    }
    die(xlt('Invalid Request Parameters'));
} elseif (!empty($_FILES["template_files"])) {
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
        if ($content['mime'] == 'application/pdf') {
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

function renderEditorHtml($template_id, $content)
{
    $lists = [
        '{ParseAsHTML}', '{TextInput}', '{sizedTextInput:120px}', '{smTextInput}', '{TextBox:03x080}', '{CheckMark}', '{ynRadioGroup}', '{TrueFalseRadioGroup}', '{DatePicker}', '{DateTimePicker}', '{StandardDatePicker}', '{CurrentDate:"global"}', '{CurrentTime}', '{DOS}', '{ReferringDOC}', '{PatientID}', '{PatientName}', '{PatientSex}', '{PatientDOB}', '{PatientPhone}', '{Address}', '{City}', '{State}', '{Zip}', '{PatientSignature}', '{AdminSignature}', '{AcknowledgePdf: : }', '{EncounterForm:LBF}', '{Medications}', '{ProblemList}', '{Allergies}', '{ChiefComplaint}', '{DEM: }', '{HIS: }', '{LBF: }', '{GRP}{/GRP}'
    ];
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?php Header::setupHeader(['ckeditor']); ?>
    </head>
    <style>
      input:focus,
      input:active
      {
        outline:0px !important;
        -webkit-appearance:none;
        box-shadow: none !important;
      }
      .list-group-item {
        font-size: .9rem;
      }
    </style>
    <body>
        <div class="container-fluid">
            <div class="row">
                <div class="col-10 px-1">
                    <form class="sticky-top" action='./import_template.php' method='post'>
                        <input type="hidden" name="docid" value="<?php echo attr($template_id) ?>">
                        <input type='hidden' name='mode' value="save">
                        <input type='hidden' name='service' value='window'>
                        <textarea class='inline-editor' contenteditable='true' cols='80' rows='10' id='templateContent' name='content'><?php echo text($content) ?></textarea>
                        <div class="row btn-group mt-1 float-right">
                            <div class='col btn-group mt-1 float-right'>
                                <button type="submit" class="btn btn-sm btn-primary"><?php echo xlt("Save"); ?></button>
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
            /*
            CKEDITOR.config.autoGrow_onStartup = true;
            CKEDITOR.config.autoGrow_maxWidth = '100%';
            CKEDITOR.config.autoGrow_minHeight = 200;
            CKEDITOR.config.autoGrow_maxHeight = 580;
            CKEDITOR.config.autoGrow_bottomSpace = 10;
            CKEDITOR.config.autoParagraph = false;
            CKEDITOR.config.forceEnterMode = true;
            CKEDITOR.config.extraPlugins = 'sourcearea';
            CKEDITOR.config. removePlugins = 'sourcedialog';
            CKEDITOR.config.removeButtons = 'PasteFromWord';
            CKEDITOR.config. = ;
            */
            CKEDITOR.disableAutoInline = true;
            CKEDITOR.config.extraPlugins = "preview,save,docprops,justify";
            CKEDITOR.config.allowedContent = true;
            CKEDITOR.config.fullPage = true;
            CKEDITOR.config.height = height;
            CKEDITOR.config.width = '100%';
            CKEDITOR.config.resize_dir = 'both';
            CKEDITOR.config.resize_minHeight = max/2;
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
