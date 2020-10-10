<?php

/**
 * import_template_ui.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");

use OpenEMR\Core\Header;

$getdir = isset($_POST['sel_pt']) ? $_POST['sel_pt'] : 0;
if ($getdir > 0) {
    $tdir = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/' . convert_safe_file_dir_name($getdir) . '/';
    if (!is_dir($tdir)) {
        if (!mkdir($tdir, 0755, true) && !is_dir($tdir)) {
            die(xl('Failed to create folder'));
        }
    }
} else {
    $tdir = $GLOBALS['OE_SITE_DIR'] . '/documents/onsite_portal_documents/templates/';
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

function getTemplateList($dir)
{
    $retval = array();
    if (substr($dir, -1) !== "/") {
        $dir .= "/";
    }

    $d = @dir($dir) or die("File List: Failed opening directory " . text($dir) . " for reading");
    while (false !== ($entry = $d->read())) {
        if ($entry[0] === "." || substr($entry, -3) !== 'tpl') {
            continue;
        }

        if (is_dir("$dir$entry")) {
            $retval[] = array(
                'pathname' => "$dir$entry",
                'name' => "$entry",
                'size' => 0,
                'lastmod' => filemtime("$dir$entry")
            );
        } elseif (is_readable("$dir$entry")) {
            $retval[] = array(
                'pathname' => "$dir$entry",
                'name' => "$entry",
                'size' => filesize("$dir$entry"),
                'lastmod' => filemtime("$dir$entry")
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
    <?php Header::setupHeader(['no_main-theme', 'datetime-picker', 'summernote', 'summernote-ext-nugget', 'patientportal-style']); ?>

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
                    dlgopen('','popeditor','modal-full', 850,'', '', {
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
                                '{ParseAsHTML}','{TextInput}','{sizedTextInput:120px}','{smTextInput}','{TextBox:03x080}','{DatePicker}','{CheckMark}','{ynRadioGroup}','{TrueFalseRadioGroup}','{DateTimePicker}','{StandardDatePicker}','{DOS}','{ReferringDOC}','{PatientID}','{PatientName}','{PatientSex}','{PatientDOB}','{PatientPhone}','{Address}','{City}','{State}','{Zip}','{PatientSignature}','{AdminSignature}','{Medications}','{ProblemList}','{Allergies}','{ChiefComplaint}','{EncounterForm:LBF}','{DEM: }','{HIS: }','{LBF: }','{GRP}{/GRP}'
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
        <form id="form_upload" class="form" action="import_template.php" method="post" enctype="multipart/form-data">
            <div class="btn-group my-2">
            <input class="btn btn-outline-info" type="file" name="tplFile">
            <button class="btn btn-outline-primary" type="submit" name="upload_submit" id="upload_submit"><?php echo xlt('Upload For'); ?> <span class="badge badge-warning" id='ptstatus'></span></button>
            <button class="btn btn-success" type="button" onclick="location.href='./patient/provider'"><?php echo xlt('Dashboard'); ?></button>
            </div>
            <input type='hidden' name="up_dir" value='<?php global $getdir;
            echo $getdir; ?>' />
        </form>
        <hr>
        <div class='row'>
            <h4><?php echo xlt('Active Templates'); ?></h4>
            <div class='col col-md col-lg'>
                <form id="edit_form" name="edit_form" class="form-inline" action="" method="post">
                    <div class="form-group">
                        <label for="sel_pt"><?php echo xlt('Patient'); ?></label>
                        <select class="form-control" id="sel_pt" name="sel_pt">
                            <option value='0'><?php echo xlt("Global All Patients") ?></option>
                            <?PHP
                            $ppt = getAuthUsers();
                            global $getdir;
                            foreach ($ppt as $pt) {
                                if ($getdir != $pt['pid']) {
                                    echo "<option value=" . attr($pt['pid']) . ">" . text($pt['ptname']) . "</option>";
                                } else {
                                    echo "<option value='" . attr($pt['pid']) . "' selected='selected'>" . text($pt['ptname']) . "</option>";
                                }
                            }
                            ?>
                        </select></div>
                    <button type="submit" class="btn btn-secondary"><?php echo xlt('Refresh'); ?></button>
                </form>
            </div>
            <?php
            $dirlist = getTemplateList($tdir);
            echo "<table  class='table table-responsive-sm table-striped table-bordered'>";
            echo "<thead>";
            echo "<tr><th>" . xlt("Template") . " - <i>" . xlt("Click to edit") . "</i></th><th>" . xlt("Size") . "</th><th>" . xlt("Last Modified") . "</th></tr>";
            echo "</thead>";
            echo "<tbody>";
            foreach ($dirlist as $file) {
                $t = $file['pathname'];
                echo "<tr><td>";
                echo '<button id="tedit' . attr($t) .
                    '" class="btn btn-sm btn-outline-primary" onclick="tedit(' . attr_js($t) . ')" type="button">' . text($file['name']) . '</button>' .
                    '<button id="tdelete' . attr($t) .
                    '" class="btn btn-sm btn-outline-danger" onclick="tdelete(' . attr_js($t) . ')" type="button">' . xlt("Delete") . '</button>';
                echo "</td><td>" . text($file['size']) . "</td>";
                echo "<td>" . text(date('r', $file['lastmod'])) . "</td>";
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            ?>
            <script>
                $(function () {
                    $("#sel_pt").change(function () {
                        $("#edit_form").submit();
                    });
                    $("#ptstatus").text($("#sel_pt").find(":selected").text())
                });
            </script>
        </div>
    </div>
</body>
</html>
