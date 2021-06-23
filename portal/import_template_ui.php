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

$patient = (int)($_POST['sel_pt'] ?? 0);
$patient_dir = $patient > 0 ? convert_safe_file_dir_name($patient . "_tpls") : "";
$cat_dir = convert_safe_file_dir_name($_POST['doc_category']) ?? "";
// default root
$tdir = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/';
if (!empty($patient_dir)) {
    $tdir = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/' . $patient_dir . '/';
} elseif (!empty($cat_dir)) {
    $tdir = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/' . $cat_dir . '/';
}

$rtn = sqlStatement("SELECT `option_id`, `title`, `seq` FROM `list_options` WHERE `list_id` = ? ORDER BY `seq`", array('Document_Template_Categories'));
$category_list = array();
while ($row = sqlFetchArray($rtn)) {
    $category_list[] = $row;
}

function getAuthUsers()
{
    $response = sqlStatement("SELECT patient_data.pid, Concat_Ws(' ', patient_data.fname, patient_data.lname) as ptname FROM patient_data WHERE allow_patient_portal = 'YES'");
    $resultpd = array();
    while ($row = sqlFetchArray($response)) {
        $resultpd[] = $row;
    }

    return $resultpd;
}

function getTemplateList($dir, $location = "")
{
    $retval = array();
    if (substr($dir, -1) !== "/") {
        $dir .= "/";
    }

    if (false === $d = @dir($dir)) {
        return false;
    }
    while (false !== ($entry = $d->read())) {
        if ($entry[0] === "." || substr($entry, -3) !== 'tpl') {
            continue;
        }
        if (is_dir("$dir$entry")) {
            continue;
        }

        if (is_readable("$dir$entry")) {
            $retval[] = array(
                'pathname' => "$dir$entry",
                'name' => "$entry",
                'size' => filesize("$dir$entry"),
                'lastmod' => filemtime("$dir$entry"),
                'location' => text("./documents/onsite_portal_documents/templates/" . $location)
            );
        }
    }

    $d->close();
    return $retval;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo xlt('Portal'); ?> | <?php echo xlt('Templates'); ?></title>
    <meta name="description" content="Developed By sjpadgett@gmail.com">
    <?php Header::setupHeader(['datetime-picker', 'summernote', 'summernote-ext-nugget']); ?>

</head>
<script>
    let currentEdit = "";
    let tedit = function (docname) {
        currentEdit = docname;
        getDocument(docname, 'get', '');
        return false;
    };

    let tsave = function () {
        let makrup = $('#templatecontent').summernote('code');
        getDocument(currentEdit, 'save', makrup)
    };
    let tdelete = function (docname) {
        let delok = confirm(<?php echo xlj('You are about to delete template'); ?> +": " + docname + "\n" + <?php echo xlj('Is this Okay?'); ?>);
        if (delok === true) {
            getDocument(docname, 'delete', '')
        }
        return false;
    };

    function getDocument(docname, mode, content) {
        let liburl = 'import_template.php';
        $.ajax({
            type: "POST",
            url: liburl,
            data: {docid: docname, mode: mode, content: content},
            beforeSend: function (xhr) {
                console.log("Please wait..." + content);
            },
            error: function (qXHR, textStatus, errorThrow) {
                console.log("There was an error");
                alert(<?php echo xlj("File Error") ?> +"\n" + docname)
            },
            success: function (templateHtml, textStatus, jqXHR) {
                if (mode == 'get') {
                    let editHtml = '<div class="edittpl" id="templatecontent"></div>';
                    dlgopen('', 'popeditor', 'modal-full', 850, '', '', {
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
        <h3><?php echo xlt('Patient Document Template Maintenance'); ?></h3>
        <hr />
        <div class="jumbotron jumbotron-fluid p-1 text-center">
            <p>
                <?php echo xlt('Select a text or html template and upload for selected patient or all portal patients.'); ?><br /><?php echo xlt('Files base name becomes a pending document selection in Portal Documents.'); ?><br />
                <em><?php echo xlt('For example: Privacy_Agreement.txt becomes Privacy Agreement button in Patient Documents.'); ?></em>
            </p>
        </div>
        <form id="form_upload" class="form-inline" action="import_template.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <div class="btn-group">
                    <input class="btn btn-outline-info" type="file" name="tplFile">
                    <button class="btn btn-outline-primary" type="submit" name="upload_submit" id="upload_submit"><?php echo xlt('Uploading For'); ?> <label id='ptstatus'></label></button>
                </div>
                <button class="btn btn-success ml-2" type="button" onclick="location.href='./patient/provider'"><?php echo xlt('Dashboard'); ?></button>
            </div>
            <input type='hidden' name="up_dir" value='<?php global $patient_dir;
            echo $patient_dir; ?>' />
            <input type='hidden' name="doc_category" value='<?php global $cat_dir;
            echo $cat_dir; ?>' />
        </form>
        <hr>
        <div class='row'>
            <h4><?php echo xlt('Active Templates'); ?></h4>
            <div class='col col-md col-lg'>
                <form id="edit_form" name="edit_form" class="form-inline mb-2" action="" method="post">
                    <div class="form-group">
                        <label class="label mx-1" for="doc_category"><?php echo xlt('Category'); ?></label>
                        <select class="form-control" id="doc_category" name="doc_category">
                            <option value=""><?php echo xlt('General'); ?></option>
                            <?php
                            foreach ($category_list as $dir) {
                                if ($cat_dir == $dir['option_id']) {
                                    echo "<option value='" . text($dir['option_id']) . "' selected>" . text($dir['title']) . "</option>\n";
                                } else {
                                    echo "<option value='" . text($dir['option_id']) . "'>" . text($dir['title']) . "</option>\n";
                                }
                            }
                            ?>
                        </select>
                        <label class="label mx-1" for="sel_pt"><?php echo xlt('Patient'); ?></label>
                        <select class="form-control" id="sel_pt" name="sel_pt">
                            <option value='0'><?php echo xlt("All Patients") ?></option>
                            <?PHP
                            $ppt = getAuthUsers();
                            global $patient_dir;
                            foreach ($ppt as $pt) {
                                if ($patient_dir != $pt['pid'] . "_tpls") {
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
            $dir_list = [];
            $show_cat_flag = false;
            if (!empty($cat_dir)) {
                $dir_list['general'] = getTemplateList($tdir);
            } else {
                $dir_list['general'] = getTemplateList($tdir);
                foreach ($category_list as $cat) {
                    if (!empty($cat_dir) && $cat_dir != $cat['option_id']) {
                        continue;
                    }
                    if ($cat_dir_iter = getTemplateList(($tdir . $cat['option_id']), $cat['option_id'])) {
                        $dir_list[$cat['title']] = $cat_dir_iter;
                    }
                }
            }
            echo "<table class='table table-sm table-striped table-bordered'>\n";
            echo "<thead>\n";
            echo "<tr>\n" .
                "<th>" . xlt("Template") . " - <i>" . xlt("Click to edit") . "</i></th>" .
                "<th>" . xlt("Category") . "</th><th>" . xlt("Location") . "</th><th>" . xlt("Size") . "</th><th>" . xlt("Last Modified") . "</th>" .
                "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";
            foreach ($dir_list as $cat => $files) {
                foreach ($files as $file) {
                    $t = $file['pathname'];
                    echo "<tr><td>";
                    echo '<button id="tedit' . attr($t) .
                        '" class="btn btn-sm btn-outline-primary" onclick="tedit(' . attr_js($t) . ')" type="button">' . text($file['name']) . '</button>' .
                        '<button id="tdelete' . attr($t) .
                        '" class="btn btn-sm btn-outline-danger" onclick="tdelete(' . attr_js($t) . ')" type="button">' . xlt("Delete") . '</button>';
                    echo "</td><td>" . text(ucwords($cat)) . "</td>";
                    echo "<td>" . text($file['location']) . "</td>";
                    echo "<td>" . text($file['size']) . "</td>";
                    echo "<td>" . text(date('r', $file['lastmod'])) . "</td>";
                    echo "</tr>";
                }
            }
            echo "</tbody>";
            echo "</table>";
            ?>
        </div>
    </div>
    <script>
        $(function () {
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
                if (patient !== "0" && cat !== "") {
                    alert(xl("Alert! Can only use the General category with patients."));
                    $("#doc_category").val("");
                    return false;
                }
                return true;
            }
        });
    </script>
</body>
</html>
