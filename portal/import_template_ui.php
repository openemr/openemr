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

$patient = $_POST['selected_patients'] ?? null;
$patient = $patient ?: ($_POST['upload_pid'] ?? 0);

$category = $_POST['template_category'] ?? '';

$category_list = $templateService->getDefaultCategories();

function getAuthUsers()
{
    $response = sqlStatement("SELECT `pid`, Concat_Ws(', ', `lname`, `fname`) as ptname FROM `patient_data` WHERE `allow_patient_portal` = 'YES' ORDER BY `lname`");

    $result_data = array(
        ['pid' => '0', 'ptname' => 'All Patients'],
        ['pid' => '-1', 'ptname' => 'Repository'],
    );
    while ($row = sqlFetchArray($response)) {
        $result_data[] = $row;
    }

    return $result_data;
}

?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title><?php echo xlt('Portal'); ?> | <?php echo xlt('Templates'); ?></title>
    <meta name="description" content="Developed By sjpadgett@gmail.com">
    <?php Header::setupHeader(['datetime-picker', 'summernote', 'summernote-ext-nugget', 'select2']); ?>
    <script src='./../../node_modules/@ckeditor/ckeditor5-build-classic/build/ckeditor.js'></script>
    <script>
        let currentEdit = "";
        let editor;
        let templateEdit = function (id) {
            currentEdit = id;
            handleTemplate(id, 'get', '');
            return false;
        };

        let templateSave = function () {
            let markup = editor.getData();
            handleTemplate(currentEdit, 'save', markup);
        };

        let templateDelete = function (id) {
            let delok = confirm(<?php echo xlj('You are about to delete a template'); ?> +
                ": " + "\n" + <?php echo xlj('Is this Okay?'); ?>);
            if (delok === true) {
                handleTemplate(id, 'delete', '')
            }
            return false;
        };

        function getSendChecks() {
            let checked = [];
            $('input:checked[name=send]:checked').each(function () {
                checked.push($(this).val());
            });
            return checked;
        }

        function sendTemplate(mode = 'send', content = '') {
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
                dialog.alert(xl('Result ' + rtn)).then((rtn) => {
                    alert('submit')
                    document.edit_form.submit();
                });
            }).catch((error) => {
                console.error('Error:', error);
            });
        }

        function handleTemplate(id, mode, content) {
            let liburl = 'import_template.php';
            $.ajax({
                type: "POST",
                url: liburl,
                data: {docid: id, mode: mode, content: content},
                error: function (qXHR, textStatus, errorThrow) {
                    console.log("There was an error");
                    alert(<?php echo xlj("File Error") ?> +"\n" + id)
                },
                success: function (templateHtml, textStatus, jqXHR) {
                    if (mode == 'get') {
                        let editHtml = '<div class="edittpl" id="templatecontent"></div>';
                        dlgopen('', 'popeditor', 'modal-lg', 850, '', '', {
                            buttons: [
                                {text: <?php echo xlj('Save'); ?>, close: false, style: 'success btn-sm', click: templateSave},
                                {text: <?php echo xlj('Dismiss'); ?>, style: 'danger btn-sm', close: true}
                            ],
                            allowDrag: false,
                            allowResize: true,
                            sizeHeight: 'full',
                            onClosed: 'reload',
                            html: editHtml,
                            type: 'alert'
                        });
                        //$('#templatecontent').summernote('destroy');
                        $('#templatecontent').empty().append(templateHtml);
                        ClassicEditor.create(document.querySelector('#templatecontent')).then(newEditor => {
                            editor = newEditor;
                        }).catch(error => {
                            console.error(error);
                        });
                    } else if (mode === 'save') {
                        location.reload();
                    } else if (mode === 'delete') {
                        location.reload();
                    }
                }
            });
        }

        $(function () {
            $('.select-dropdown').select2({
                placeholder: xl("Type to search."),
                minimumResultsForSearch: 15,
                theme: 'bootstrap4',
                <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
            });

            $('input:checkbox[name=send]').change(function () {
                let checked = getSendChecks();
                if (checked.length > 0) {
                    $('#send-button').removeClass('d-none');
                } else {
                    $('#send-button').addClass('d-none');
                }
            });

            /*$('#selected_patients').change(function () {
                if (checkCategory()) {
                    $('#edit_form').submit();
                }
            });*/

            $('#upload-nav').on('hidden.bs.collapse', function () {
                $('#upload-nav-value').val('hidden');
                $('#edit_form').submit();
            });
            $('#upload-nav').on('show.bs.collapse', function () {
                $('#upload-nav-value').val('show');
                $('#edit_form').submit();
            });

            $("#template_category").change(function () {
                if (checkCategory()) {
                    $("#edit_form").submit();
                }
            });

            $("#ptstatus").text($("#selected_patients").find(":selected").text());
            $("#ptstatus").append(' ' + xl("to Category") + ' ');
            $("#ptstatus").append($("#template_category").find(":selected").text());

            function checkCategory() {
                let cat = $("#template_category").val();
                let patient = $("#selected_patients").val();
                return true;
            }
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });
    </script>
    <style>
      .modal.modal-wide .modal-dialog {
        width: 55%;
      }

      .modal-wide .modal-body {
        overflow-y: auto;
      }

      caption {
        caption-side: top !important;
      }
    </style>
</head>
<body class="body-top">
    <div class='container'>
        <div class='col col-12'>
            <h3 class='ml-0 mt-2'><?php echo xlt('Template Maintenance'); ?></h3>
            <div class='card col-8 offset-2 border-0 mb-2'>
                <div id='help-panel' class='card-block border-2 bg-dark text-light collapse'>
                    <div class='card-title bg-light text-dark text-center'><?php echo xlt('Template Help'); ?></div>
                    <div class='card-text p-2'>
                        <?php echo xlt('Select a text or html template and upload for selected patient or all portal patients.'); ?><br /><?php echo xlt('Files base name becomes a pending document selection in Portal Documents.'); ?><br />
                        <em><?php echo xlt('For example: Privacy_Agreement.txt becomes Privacy Agreement button in Patient Documents.'); ?></em>
                    </div>
                </div>
            </div><hr />
            <nav class='navbar navbar-light bg-light sticky-top'>
                <form id="edit_form" name="edit_form" class="row form-inline" action="" method="post">
                    <a class='navbar-brand ml-1'><?php echo xlt('Scope'); ?></a>
                    <div class="form-group">
                        <label class="font-weight-bold mx-1" for="template_category"><?php echo xlt('Category'); ?></label>
                        <select class="form-control" id="template_category" name="template_category">
                            <option value=""><?php echo xlt('Standard'); ?></option>
                            <?php
                            foreach ($category_list as $option_category) {
                                if (stripos($option_category['option_id'], 'repository') !== false) {
                                    continue;
                                }
                                if ($category == $option_category['option_id']) {
                                    echo "<option value='" . attr($option_category['option_id']) . "' selected>" . text($option_category['title']) . "</option>\n";
                                } else {
                                    echo "<option value='" . text($option_category['option_id']) . "'>" . text($option_category['title']) . "</option>\n";
                                }
                            }
                            ?>
                        </select>
                        <label class="font-weight-bold mx-1" for="selected_patients"><?php echo xlt('Location'); ?></label>
                        <select class="form-control select-dropdown" id="selected_patients" name="selected_patients[]" multiple>
                            <?PHP
                            $ppt = getAuthUsers();
                            foreach ($ppt as $pt) {
                                if ((is_array($patient) && !in_array($pt['pid'], $patient)) || empty($patient)) {
                                    echo "<option value=" . attr($pt['pid']) . ">" . text($pt['ptname']) . "</option>";
                                } else {
                                    echo "<option value='" . attr($pt['pid']) . "' selected='selected'>" . text($pt['ptname'] . ' ') . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <button type='submit' class='btn btn-search btn-secondary'><?php /*echo xlt('Refresh'); */ ?></button>
                    </div>
                    <div class='btn-group ml-1'>
                        <button type='button' id="send-button" class='btn btn-transmit btn-outline-primary d-none' onclick="top.restoreSession(); return sendTemplate()">
                            <?php echo xlt('Send'); ?>
                        </button>
                        <button id="upload-nav-button" name='upload-nav-button' type='button' class='btn btn-primary' data-toggle='collapse' data-target='#upload-nav'>
                            <?php echo xlt('Files') ?>
                        </button>
                    </div>
                    <div class='btn-group ml-1'>
                        <button type='button' class='btn btn-secondary' data-toggle='collapse' data-target='#help-panel'>
                            <?php echo xlt('Help') ?>
                        </button>
                        <button class='btn btn-success' type='button' onclick="location.href='./patient/provider'">
                            <?php echo xlt('Dashboard'); ?>
                        </button>
                    </div>
                    <input type='hidden' id='upload-nav-value' name='upload-nav-value' value='<?php echo $_POST['upload-nav-value'] ?? 'hidden' ?>' />
                </form>
            </nav>
            <!-- Upload -->
            <nav class="collapse my-2 <?php echo $_POST['upload-nav-value'] ?>" id="upload-nav">
                <div class='col col-12'>
                    <form id='form_upload' class='form-inline row' action='import_template.php' method='post' enctype='multipart/form-data'>
                        <div class='form-group'>
                            <div class='form-group'>
                                <input class='btn btn-outline-info' type='file' multiple name='template_files[]' />
                                <button class='btn btn-outline-primary' type='submit' name='upload_submit' id='upload_submit'><label id='ptstatus'></label></button>
                            </div>
                        </div>
                        <input type='hidden' name='upload_pid' value='<?php echo json_encode($patient); ?>' />
                        <input type='hidden' name="template_category" value='<?php echo attr($category); ?>' />
                    </form>
                </div>
            </nav>
            <hr />
            <div class='row'>
                <div class='col col-12' data-toggle='collapse' data-target='#repository-collapse'>
                    <h5><i class='fa fa-eye-slash mr-1' role='button' title="<?php echo xlt('Click to expand or collapse Repository templates panel.'); ?>"></i><?php echo xlt('Template Repository') ?></h5>
                </div>
                <div class='col col-12 table-responsive collapse show' id="repository-collapse">
                    <?php
                    $templates = [];
                    $show_cat_flag = false;
                    if (!empty($category)) {
                        $templates = $templateService->getTemplateListByCategory($category, -1);
                    } else {
                        $templates = $templateService->getTemplateListAllCategories(-1);
                    }
                    echo "<table class='table table-sm table-striped table-bordered'>\n";
                   /* echo '<caption role="button" data-toggle="collapse" data-target="#repository-collapse" title="' .
                        xlt('Click to expand or collapse Repository templates panel.') .
                        '"><h5>' . xlt('Repository Available Templates') . '</h5></caption>';*/
                    echo "<thead>\n";
                    echo "<tr>\n" .
                        "<th>" . xlt('Send') . "</th>" .
                        '<th>' . xlt('Category') . '</th>' .
                        "<th>" . xlt("Template Actions") . "</th>" .
                        "<th>" . xlt("Size") . "</th>" .
                        "<th>" . xlt("Last Modified") . "</th>" .
                        "</tr>\n";
                    echo "</thead>\n";
                    echo "<tbody>\n";
                    foreach ($templates as $cat => $files) {
                        foreach ($files as $file) {
                            $template_id = $file['id'];
                            echo "<tr>";
                            echo "<td><input type='checkbox' class='form-check-inline' id='send' name='send' value='" . attr($template_id) . "' /></td>";
                            echo '<td>' . text(ucwords($cat)) . '</td><td>';
                            echo '<button id="templateEdit' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ')" type="button">' . text($file['template_name']) . '</button>' .
                                /*'<button id="sendTemplate' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-success" onclick="templateSend(' . attr_js($template_id) . ')" type="button">' . xlt('Send') . '</button>' .*/
                                '<button id="templateDelete' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-danger float-right" onclick="templateDelete(' . attr_js($template_id) . ')" type="button">' . xlt("Delete") . '</button>';
                            echo "<td>" . text($file['size']) . "</td>";
                            echo "<td>" . text(date('m/d/Y H:i:s', strtotime($file['modified_date']))) . "</td>";
                            echo "</tr>";
                            ?>
                            <?php
                        }
                    }
                    echo "</tbody>";
                    echo "</table>";
                    ?>
                </div>
            </div>
            <div class='row'>
                <div class='col col-12' data-toggle='collapse' data-target='#template-collapse'>
                    <h5><i class='fa fa-eye-slash mr-1' role='button' title="<?php echo xlt('Click to expand or collapse All active patient templates panel.'); ?>"></i><?php echo '' . xlt('All Patient Templates') . '' ?></h5>
                </div>
                <div class='col col-12 table-responsive collapse show' id='template-collapse'>
                    <?php
                    $templates = [];
                    $show_cat_flag = false;
                    if (!empty($category)) {
                        $templates = $templateService->getTemplateListByCategory($category);
                    } else {
                        $templates = $templateService->getTemplateListAllCategories();
                    }
                    echo "<table class='table table-sm table-striped table-bordered'>\n";
                    /*echo '<caption role="button" data-toggle="collapse" data-target="#template-collapse"><h6>' .
                        xlt('All Patients Templates') .
                        '</h6></caption>';*/
                    echo "<thead>\n";
                    echo "<tr>\n" .
                        /*'<th>' . xlt('Send') . '</th>' .*/
                        '<th>' . xlt('Category') . '</th>' .
                        '<th>' . xlt('Template Actions') . '</th>' .
                        '<th>' . xlt('Size') . '</th>' .
                        '<th>' . xlt('Last Modified') . '</th>' .
                        "</tr>\n";
                    echo "</thead>\n";
                    echo "<tbody>\n";
                    foreach ($templates as $cat => $files) {
                        foreach ($files as $file) {
                            $template_id = $file['id'];
                            echo '<tr>';
                            /*echo "<td><input type='checkbox' class='form-check-inline' id='send' name='send' value='" . attr($template_id) . "' /></td>";*/
                            echo '<td>' . text(ucwords($cat)) . '</td><td>';
                            echo '<button id="templateEdit' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ')" type="button">' . text($file['template_name']) . '</button>' .
                                /*'<button id="sendTemplate' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-success" onclick="templateSend(' . attr_js($template_id) . ')" type="button">' . xlt('Send') . '</button>' .*/
                                '<button id="templateDelete' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-danger" onclick="templateDelete(' . attr_js($template_id) . ')" type="button">' . xlt('Delete') . '</button>';
                            echo '<td>' . text($file['size']) . '</td>';
                            echo '<td>' . text(date('m/d/Y H:i:s', strtotime($file['modified_date']))) . '</td>';
                            echo '</tr>';
                            ?>
                            <?php
                        }
                    }
                    echo '</tbody>';
                    echo '</table>';
                    ?>
                </div>
            </div>
            <div class='row'>
                <div class='col col-12 table-responsive'>
                    <?php
                    // by categories and patient pid.
                    $templates = [];
                    $show_cat_flag = false;

                    // Category selected so get all of them for pids
                    if (!empty($category) && !empty($patient)) {
                        $templates = $templateService->getTemplateCategoriesByPids($patient, $category);
                    } elseif (empty($patient)) {// All templates for all patients
                        $templates = $templateService->getTemplateCategoriesByPatient(0, $category);
                    } elseif (empty($category) && !empty($patient)) {
                        $templates = $templateService->getTemplateCategoriesByPids($patient);
                    }
                    echo "<table class='table table-sm table-striped table-bordered'>\n";
                    echo '<caption><h5>' . xlt("Patient Assigned Templates") . '</h5></caption>';
                    echo "<thead>\n";
                    echo "<tr>\n" .
                        '<th>' . xlt('Category') . '</th>' .
                        '<th>' . xlt('Template Actions') .
                        '</th><th>' . xlt('Patient') .
                        '</th><th>' . xlt('Status') .
                        '</th><th>' . xlt('Size') .
                        '</th><th>' . xlt('Last Modified') . '</th>' .
                        "</tr>\n";
                    echo "</thead>\n";
                    echo "<tbody>\n";
                    foreach ($templates as $cat => $files) {
                        foreach ($files as $file) {
                            $template_id = $file['id'];
                            echo '<tr><td>' . text(ucwords($cat)) . '</td>';
                            echo '<td>' .
                                '<button type="button" id="patientEdit' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ')">' .
                                text($file['template_name']) . "</button>\n" .
                                '<button type="button" id="patientDelete' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-danger" onclick="templateDelete(' . attr_js($template_id) . ')">' . xlt('Delete') . "</button></td>\n";
                            echo '<td>' . text($file['location']) . '</td>';
                            echo '<td>' . text($file['status']) . '</td>';
                            echo '<td>' . text($file['size']) . '</td>';
                            echo '<td>' . text(date('m/d/Y H:i:s', strtotime($file['modified_date']))) . '</td>';
                            echo "</tr>\n";
                        }
                    }
                    echo '</tbody>';
                    echo '</table>';
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
