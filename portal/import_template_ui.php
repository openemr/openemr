<?php

/**
 * import_template_ui.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\DocumentTemplates\DocumentTemplateService;

$templateService = new DocumentTemplateService();

$patient = (int)($_POST['sel_pt'] ?? 0);
$patient = $patient ?: ((int)$_POST['upload_pid']);

$category = $_POST['doc_category'] ?? null;

$category_list = $templateService->getDefaultCategories();

function getAuthUsers()
{
    $response = sqlStatement("SELECT patient_data.pid, Concat_Ws(', ', patient_data.lname, patient_data.fname) as ptname FROM patient_data WHERE allow_patient_portal = 'YES'");
    $resultpd = array();
    while ($row = sqlFetchArray($response)) {
        $resultpd[] = $row;
    }

    return $resultpd;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo xlt('Portal'); ?> | <?php echo xlt('Templates'); ?></title>
    <meta name="description" content="Developed By sjpadgett@gmail.com">
    <?php Header::setupHeader(['datetime-picker', 'summernote', 'summernote-ext-nugget', 'select2']); ?>

</head>
<script>
    let currentEdit = "";
    let tedit = function (id) {
        currentEdit = id;
        getDocument(id, 'get', '');
        return false;
    };

    let tsave = function () {
        let makrup = $('#templatecontent').summernote('code');
        getDocument(currentEdit, 'save', makrup)
    };

    let tdelete = function (id) {
        let delok = confirm(<?php echo xlj('You are about to delete a template'); ?> + ": " + "\n" + <?php echo xlj('Is this Okay?'); ?>);
        if (delok === true) {
            getDocument(id, 'delete', '')
        }
        return false;
    };

    function getDocument(id, mode, content) {
        let liburl = 'import_template.php';
        $.ajax({
            type: "POST",
            url: liburl,
            data: {docid: id, mode: mode, content: content},
            beforeSend: function (xhr) {
                console.log("Please wait..." + content);
            },
            error: function (qXHR, textStatus, errorThrow) {
                console.log("There was an error");
                alert(<?php echo xlj("File Error") ?> +"\n" + id)
            },
            success: function (templateHtml, textStatus, jqXHR) {
                if (mode == 'get') {
                    let editHtml = '<div class="edittpl" id="templatecontent"></div>';
                    dlgopen('', 'popeditor', 'modal-lg', 850, '', '', {
                        buttons: [
                            {text: <?php echo xlj('Save'); ?>, close: false, style: 'success btn-sm', click: tsave},
                            {text: <?php echo xlj('Dismiss'); ?>, style: 'danger btn-sm', close: true}
                        ],
                        allowDrag: false,
                        allowResize: true,
                        sizeHeight: 'full',
                        onClosed: 'reload',
                        html: editHtml,
                        type: 'alert'
                    });
                    $('#templatecontent').summernote('destroy');
                    $('#templatecontent').empty().append(templateHtml);
                    $('#templatecontent').summernote({
                        focus: true,
                        dialogsInBody: true,
                        placeholder: '',
                        toolbar: [
                            ['style', ['bold', 'italic', 'underline', 'clear']],
                            ['fontsize', ['fontsize']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['insert', ['link', 'picture', 'video', 'hr']],
                            ['view', ['fullscreen', 'codeview']],
                            ['insert', ['nugget']],
                            ['edit', ['undo', 'redo']]
                        ],
                        nugget: {
                            list: [
                                '{ParseAsHTML}', '{TextInput}', '{sizedTextInput:120px}', '{smTextInput}', '{TextBox:03x080}', '{DatePicker}', '{CheckMark}', '{ynRadioGroup}', '{TrueFalseRadioGroup}', '{DateTimePicker}', '{StandardDatePicker}', '{DOS}', '{ReferringDOC}', '{PatientID}', '{PatientName}', '{PatientSex}', '{PatientDOB}', '{PatientPhone}', '{Address}', '{City}', '{State}', '{Zip}', '{PatientSignature}', '{AdminSignature}', '{Medications}', '{ProblemList}', '{Allergies}', '{ChiefComplaint}', '{EncounterForm:LBF}', '{DEM: }', '{HIS: }', '{LBF: }', '{GRP}{/GRP}'
                            ],
                            label: 'Directives',
                            tooltip: 'Select Directive to insert at current cursor position.'
                        },
                        options: {}
                    });
                } else if (mode === 'save') {
                    $('#templatecontent').summernote('destroy');
                    location.reload();
                } else if (mode === 'delete') {
                    location.reload();
                }
            }
        });
    }
</script>
<style>
  .modal.modal-wide .modal-dialog {
    width: 55%;
  }

  .modal-wide .modal-body {
    overflow-y: auto;
  }
</style>
<body class="body-top">
    <div class='container'>
        <h3 class='ml-0 mt-2'><?php echo xlt('Patient Document Template Maintenance'); ?></h3>
        <div class='col col-md col-lg'>
        <div class="card col-8 offset-2 border-0 mb-2">
            <div id="help-panel" class="card-block border-2 bg-dark text-light collapse">
                <div class="card-title bg-light text-dark text-center"><?php echo xlt('Template Help'); ?></div>
                <div class='card-text p-2'>
                <?php echo xlt('Select a text or html template and upload for selected patient or all portal patients.'); ?><br /><?php echo xlt('Files base name becomes a pending document selection in Portal Documents.'); ?><br />
                <em><?php echo xlt('For example: Privacy_Agreement.txt becomes Privacy Agreement button in Patient Documents.'); ?></em>
                </div>
            </div>
        </div>
        <form id="form_upload" class="form-inline row" action="import_template.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <div class="btn-group">
                    <input class="btn btn-outline-info" type="file" multiple name="template_files[]">
                    <button class="btn btn-outline-primary" type="submit" name="upload_submit" id="upload_submit"><label id='ptstatus'></label></button>
                </div>
                <div class='btn-group ml-2'>
                <button type="button" class='btn btn-primary' data-toggle='collapse' data-target='#help-panel'>
                    <i class="fas fa-toggle-on mr-1"></i><?php echo xlt('Help') ?>
                </button>
                <button class="btn btn-success" type="button" onclick="location.href='./patient/provider'"><?php echo xlt('Dashboard'); ?></button>
                </div>
            </div>
            <?php //global $patient, $category; ?>
            <input type='hidden' name='upload_pid' value='<?php echo $patient; ?>' />
            <input type='hidden' name="doc_category" value='<?php echo $category; ?>' />
        </form>
        <hr>
        <div class='row'>
            <div class='col col-md col-lg'>
                <form id="edit_form" name="edit_form" class="row form-inline mb-2" action="" method="post">
                    <h4 class="mb-1 mr-2"><?php echo xlt('Active Templates'); ?></h4>
                    <div class="form-group">
                        <label class="label mx-1" for="doc_category"><?php echo xlt('Category'); ?></label>
                        <select class="form-control" id="doc_category" name="doc_category">
                            <option value=""><?php echo xlt('Default'); ?></option>
                            <?php
                            foreach ($category_list as $option_category) {
                                if ($category == $option_category['option_id']) {
                                    echo "<option value='" . text($option_category['option_id']) . "' selected>" . text($option_category['title']) . "</option>\n";
                                } else {
                                    echo "<option value='" . text($option_category['option_id']) . "'>" . text($option_category['title']) . "</option>\n";
                                }
                            }
                            ?>
                        </select>
                        <label class="label mx-1" for="sel_pt"><?php echo xlt('Patient'); ?></label>
                        <select class="form-control select-dropdown" id="sel_pt" name="sel_pt">
                            <option value='0'><?php echo xlt("All Patients") ?></option>
                            <?PHP
                            $ppt = getAuthUsers();
                            foreach ($ppt as $pt) {
                                if ($patient != $pt['pid']) {
                                    echo "<option value=" . attr($pt['pid']) . ">" . text($pt['ptname']) . "</option>";
                                } else {
                                    echo "<option value='" . attr($pt['pid']) . "' selected='selected'>" . text($pt['ptname']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary"><?php echo xlt('Refresh'); ?></button>
                </form>
            </div>
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
                "<th>" . xlt("Template") . " - <i>" . xlt("Click to edit") . "</i></th>" .
                "<th>" . xlt("Category") . "</th><th>" . xlt("Location") . "</th><th>" . xlt("Size") . "</th><th>" . xlt("Last Modified") . "</th>" .
                "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";
            foreach ($templates as $cat => $files) {
                foreach ($files as $file) {
                    $template_id = $file['id'];
                    echo "<tr><td>";
                    echo '<button id="tedit' . attr($template_id) .
                        '" class="btn btn-sm btn-outline-primary" onclick="tedit(' . attr_js($template_id) . ')" type="button">' . text($file['name']) . '</button>' .
                        '<button id="tdelete' . attr($template_id) .
                        '" class="btn btn-sm btn-outline-danger" onclick="tdelete(' . attr_js($template_id) . ')" type="button">' . xlt("Delete") . '</button>';
                    echo "</td><td>" . text(ucwords($cat)) . "</td>";
                    echo "<td>" . text($file['location']) . "</td>";
                    echo "<td>" . text($file['size']) . "</td>";
                    echo "<td>" . text(date('r', strtotime($file['modified_date']))) . "</td>";
                    echo "</tr>";
                }
            }
            echo "</tbody>";
            echo "</table>";
            ?>
        </div>
        <div class='row'>
            <h4><?php echo xlt('Patient Templates'); ?></h4>
            <div class='col col-md col-lg'>
                <!--<form id="edit_form" name="edit_form" class="form-inline mb-2" action="" method="post">
                    <div class="form-group">
                        <label class="label mx-1" for="doc_category"><?php /*echo xlt('Category'); */ ?></label>
                        <select class="form-control" id="doc_category" name="doc_category">
                            <option value=""><?php /*echo xlt('General'); */ ?></option>
                            <?php
                /*                            foreach ($category_list as $option_category) {
                                                if ($category == $option_category['option_id']) {
                                                    echo "<option value='" . text($option_category['option_id']) . "' selected>" . text($option_category['title']) . "</option>\n";
                                                } else {
                                                    echo "<option value='" . text($option_category['option_id']) . "'>" . text($option_category['title']) . "</option>\n";
                                                }
                                            }
                                            */ ?>
                        </select>
                        <label class="label mx-1" for="sel_pt"><?php /*echo xlt('Patient'); */ ?></label>
                        <select class="form-control" id="sel_pt" name="sel_pt">
                            <option value='0'><?php /*echo xlt('All Patients') */ ?></option>
                            <?PHP
                /*                            $ppt = getAuthUsers();
                                            foreach ($ppt as $pt) {
                                                if ($patient != $pt['pid']) {
                                                    echo '<option value=' . attr($pt['pid']) . '>' . text($pt['ptname']) . '</option>';
                                                } else {
                                                    echo "<option value='" . attr($pt['pid']) . "' selected='selected'>" . text($pt['ptname']) . '</option>';
                                                }
                                            }
                                            */ ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary"><?php /*echo xlt('Refresh'); */ ?></button>
                </form>-->
            </div>
            <?php
            $templates = [];
            $show_cat_flag = false;
            if (!empty($category)) {
                $templates = $templateService->getTemplateCategoriesByPatient($patient, $category);
            } else {
                $templates = $templateService->getTemplateCategoriesByPatient($patient);
            }
            echo "<table class='table table-sm table-striped table-bordered'>\n";
            echo "<thead>\n";
            echo "<tr>\n" .
                '<th>' . xlt('Template') . ' - <i>' . xlt('Click to edit') . '</i></th>' .
                '<th>' . xlt('Category') . '</th><th>' . xlt('Location') . '</th><th>' . xlt('Size') . '</th><th>' . xlt('Last Modified') . '</th>' .
                "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";
            foreach ($templates as $cat => $files) {
                foreach ($files as $file) {
                    $template_id = $file['id'];
                    echo '<tr><td>';
                    echo '<button id="tedit' . attr($template_id) .
                        '" class="btn btn-sm btn-outline-primary" onclick="tedit(' . attr_js($template_id) . ')" type="button">' . text($file['name']) . '</button>' .
                        '<button id="tdelete' . attr($template_id) .
                        '" class="btn btn-sm btn-outline-danger" onclick="tdelete(' . attr_js($template_id) . ')" type="button">' . xlt('Delete') . '</button>';
                    echo '</td><td>' . text(ucwords($cat)) . '</td>';
                    echo '<td>' . text($file['location']) . '</td>';
                    echo '<td>' . text($file['size']) . '</td>';
                    echo '<td>' . text(date('r', strtotime($file['modified_date']))) . '</td>';
                    echo '</tr>';
                }
            }
            echo '</tbody>';
            echo '</table>';
            ?>
        </div>
        </div></div>
    <script>
        $(function () {
            $('.select-dropdown').select2({
                theme: 'bootstrap4',
                <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
            });

            $("#sel_pt").change(function () {
                if (checkCategory()) {
                    $("#edit_form").submit();
                }
            });

            $("#doc_category").change(function () {
                if (checkCategory()) {
                    $("#edit_form").submit();
                }
            });

            $("#ptstatus").text($("#sel_pt").find(":selected").text());
            $("#ptstatus").append(' ' + xl("to Category") + ' ');
            $("#ptstatus").append($("#doc_category").find(":selected").text());

            function checkCategory() {
                let cat = $("#doc_category").val();
                let patient = $("#sel_pt").val();
                return true;
            }
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
    </script>
</body>
</html>
