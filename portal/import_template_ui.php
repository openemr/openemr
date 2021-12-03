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

$patient = $_REQUEST['selected_patients'] ?? null;
$patient = $patient ?: ($_REQUEST['upload_pid'] ?? 0);

$category = $_REQUEST['template_category'] ?? '';
$category_list = $templateService->getDefaultCategories();
$profile_list = $templateService->getDefaultProfiles();

$none_message = xlt("Nothing to show for current actions.");

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
    <?php Header::setupHeader(['datetime-picker', 'select2', 'ckeditor']); ?>
    <script>
        const profiles = <?php echo js_escape($profile_list); ?>;
        let currentEdit = "";
        let editor;
        let templateEdit = function (id, flag = '') {
            currentEdit = id;
            handleTemplate(id, 'get', '', flag);
            return false;
        };

        let templateSave = function () {
            let markup = CKEDITOR.instances.templateContent.getData();
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
                let isProfile = this.dataset.send_profile;
                if (isProfile == 'yes') {
                    checked.push([$(this).val(),true]);
                } else {
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
                })(1000).then(rtn => {
                    document.edit_form.submit();
                });
            }).catch((error) => {
                console.error('Error:', error);
            });
        }

        function handleTemplate(id, mode, content = '', isDocument = '') {
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
                    /*buttons: [
                        {text: <?php echo xlj('Save'); ?>, close: false, style: 'success btn-sm', click: templateSave},
                        {text: <?php echo xlj('Dismiss'); ?>, style: 'danger btn-sm', close: true}
                    ],*/
                    resolvePromiseOn: 'show',
                    allowDrag: true,
                    allowResize: true,
                    sizeHeight: 'full',
                    //onClosed: 'reload'
                });
            }
            $.ajax({
                type: "POST",
                url: libUrl,
                data: {docid: id, mode: mode, content: content},
                error: function (qXHR, textStatus, errorThrow) {
                    console.log("There was an error");
                    alert(<?php echo xlj("File Error") ?> +"\n" + id)
                },
                success: function (templateHtml, textStatus, jqXHR) {
                    if (mode === 'save') {
                        location.reload();
                    } else if (mode === 'delete') {
                        location.reload();
                    }
                }
            });
        }

        $(function () {
            $('#fetch_files').on('click touchstart', function () {
                $(this).val('');
            });
            $('#fetch_files').change(function (e) {
                $('#upload_submit').removeClass('d-none');
            });

            $('.select-dropdown').select2({
                placeholder: xl("Type to search."),
                minimumResultsForSearch: 15,
                theme: 'bootstrap4',
                width: 'resolve',
                closeOnSelect: false,
                <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
            });

            $('input:checkbox[name=send]').change(function () {
                let checked = getSendChecks();
                if (checked.length > 0) {
                    $('#send-button').removeClass('d-none');
                    $('#category_group').addClass('d-none');
                } else {
                    $('#send-button').addClass('d-none');
                    $('#category_group').removeClass('d-none');
                }
            });

            $('#selected_patients').on('select2:close', function (e) {
                let checked = getSendChecks();
                if (checked.length > 0) {
                    return false;
                }
                $('#edit_form').submit();
            });

            $('#upload-nav').on('hidden.bs.collapse', function () {
                $('#upload-nav-value').val('collapse');
            });
            $('#upload-nav').on('show.bs.collapse', function () {
                $('#upload-nav-value').val('show');
                //$('#edit_form').submit();
            });

            $("#template_category").change(function () {
                if (checkCategory()) {
                    $("#edit_form").submit();
                }
            });

            $('#template-collapse').on('show.bs.collapse', function (e) {
                $('#repository-collapse').collapse('hide');
                $('#profile_send_collapse').collapse('hide');
                $('#edit_form #all_state').val('show');
            });
            $('#template-collapse').on('hidden.bs.collapse', function () {
                $('#edit_form #all_state').val('collapse');
            });

            $('#profile_send_collapse').on('show.bs.collapse', function (e) {
                $('#edit_form #profile_send_state').val('show');
            });
            $('#profile_send_collapse').on('hidden.bs.collapse', function () {
                $('#edit_form #profile_send_state').val('collapse');
            });

            $('#repository-collapse').on('show.bs.collapse', function (e) {
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
            $("#upload_scope").empty().append(xl('To Locations') + ': ' + selText);

            function checkCategory() {
                let cat = $("#template_category").val();
                let patient = $("#selected_patients").val();
                return true;
            }

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });
        });

        function popProfileDialog() {
            top.restoreSession();
            let url = './import_template.php?mode=renderProfile';
            dlgopen(url, 'pop-profile', 'modal-lg', 850, '', '', {
                allowDrag: true,
                allowResize: true,
                sizeHeight: 'full',
            });
        }
    </script>
    <style>
      .draggable {
        touch-action: none;
        user-select: none;
      }
      caption {
        caption-side: top !important;
      }
    </style>
</head>
<body class="body-top">
    <div class='container'>
        <nav class='nav navbar bg-light text-dark sticky-top'>
            <span class='title'><?php echo xlt('Template Maintenance'); ?></span>
            <span>
                <div class='btn-group ml-1'>
                    <button type='button' class='btn btn-secondary' data-toggle='collapse' data-target='#help-panel'>
                        <?php echo xlt('Help') ?>
                    </button>
                    <button class='btn btn-success' type='button' onclick="location.href='./patient/provider'">
                        <?php echo xlt('Dashboard'); ?>
                    </button>
                </div>
            </span>
        </nav>
        <div class='col col-12'>
            <hr />
            <?php include_once('./../Documentation/help_files/template_maintenance_help.php'); ?>
            <!-- Actions Scope to act on -->
            <nav class='navbar navbar-dark bg-dark text-light sticky-top'>
                <form id="edit_form" name="edit_form" class="row form-inline w-100" action="" method="get">
                    <a class='navbar-brand ml-1'><?php echo xlt('Scope'); ?></a>
                    <div class="form-group">
                        <label class='font-weight-bold mx-1' for='selected_patients'><?php echo xlt('Location'); ?></label>
                        <select class="form-control select-dropdown" id="selected_patients" name="selected_patients[]" multiple>
                            <?PHP
                            $ppt = getAuthUsers();
                            foreach ($ppt as $pt) {
                                if ((is_array($patient) && !in_array($pt['pid'], $patient)) || empty($patient)) {
                                    echo '<option value=' . attr($pt['pid']) . '>' . text($pt['ptname']) . '</option>';
                                } else {
                                    echo "<option value='" . attr($pt['pid']) . "' selected='selected'>" . text($pt['ptname'] . ' ') . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <a class='btn-refresh ml-1' onclick="$('#selected_patients').val(null).trigger('change');" role="button"></a>
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
                        <div class="form-group" id="category_group">
                            <label class="font-weight-bold mx-1" for="template_category"><?php echo xlt('Category'); ?></label>
                            <select class="form-control" id="template_category" name="template_category">
                                <?php echo $select_cat_options ?>
                            </select>
                        </div>
                    </div>
                    <div class='btn-group ml-1'>
                        <button type='submit' class='btn btn-search btn-light'><?php /*echo xlt('Refresh'); */ ?></button>
                        <button type='button' id="send-button" class='btn btn-transmit btn-success d-none' onclick="return sendTemplate()">
                            <?php echo xlt('Send'); ?>
                        </button>
                        <button type='button' id="upload-nav-button" name='upload-nav-button' class='btn btn-primary' data-toggle='collapse' data-target='#upload-nav'>
                            <?php echo xlt('Files') ?>
                        </button>
                    </div>
                    <div class="ml-auto">
                        <label class="form-check"><?php echo xlt('Use Popout Editor'); ?>
                            <input type='checkbox' class='form-check-inline mx-1' id='is_modal' name='is_modal' checked='checked' />
                        </label>
                    </div>
                    <input type='hidden' id='upload-nav-value' name='upload-nav-value' value='<?php echo attr($_REQUEST['upload-nav-value'] ?? 'collapse') ?>' />
                    <input type='hidden' id='persist_checks' name='persist_checks' value='' />
                    <input type='hidden' id='all_state' name='all_state' value='<?php echo attr($_REQUEST['all_state'] ?? 'collapse') ?>' />
                    <input type='hidden' id='profile_send_state' name='profile_send_state' value='<?php echo attr($_REQUEST['profile_send_state'] ?? 'collapse') ?>' />
                    <input type='hidden' id='repository_send_state' name='repository_send_state' value='<?php echo attr($_REQUEST['repository_send_state'] ?? 'collapse') ?>' />
                </form>
            </nav>
            <!-- Upload -->
            <nav class="collapse my-2 <?php echo attr($_REQUEST['upload-nav-value']) ?>" id="upload-nav">
                <div class='col col-12'>
                    <form id='form_upload' class='form-inline row' action='import_template.php' method='post' enctype='multipart/form-data'>
                        <hr />
                        <div class='col'>
                            <div id='upload_scope_category'></div>
                            <div class='mb-2' id='upload_scope'></div>
                        </div>
                        <div class='form-group col'>
                            <div class='form-group'>
                                <input type='file' class='btn btn-outline-info' id="fetch_files" name='template_files[]' multiple />
                                <button class='btn btn-outline-success d-none' type='submit' name='upload_submit' id='upload_submit'><i class='fa fa-upload' aria-hidden='true'></i></button>
                            </div>
                        </div>
                        <input type='hidden' name='upload_pid' value='<?php echo attr(json_encode($patient)); ?>' />
                        <input type='hidden' name="template_category" value='<?php echo attr($category); ?>' />
                    </form>
                </div>
            </nav>
            <hr />
            <!-- Repository -->
            <div class='row'>
                <div class='col col-12'>
                    <h5><i class='fa fa-eye mr-1' data-toggle='collapse' data-target='#repository-collapse' role='button' title="<?php echo xlt('Click to expand or collapse Repository templates panel.'); ?>"></i><?php echo xlt('Template Repository') ?></h5>
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
                            $cat = xlt('Default');
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
                                '</button>' .
                                '<button id="templateDelete' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-danger float-right" onclick="templateDelete(' . attr_js($template_id) . ')" type="button">' . xlt("Delete") .
                                '</button></td>';
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
                </div>
            </div>
            <hr />
            <!-- Send Profiles -->
            <div class='row'>
                <div class='col col-12'>
                    <div class="h5">
                        <i class='fa fa-eye mr-1' data-toggle='collapse' data-target='#profile_send_collapse' role='button' title="<?php echo xla('Click to expand or collapse Send Profile panel.'); ?>"></i><?php echo xlt('Send Profiles') ?>
                        <span class="mx-auto"><button class="btn btn-sm btn-primary mb-1" onclick="return popProfileDialog()"><?php echo xlt('Manage Profiles') ?></button></span>
                    </div>
                </div>
                <!-- Send Profiles table -->
                <div class='col col-12 table-responsive <?php echo attr($_REQUEST['profile_send_state'] ?: 'collapse') ?>' id="profile_send_collapse">
                    <?php
                    echo "<table class='table table-sm table-striped table-bordered'>\n";
                    echo "<thead>\n";
                    echo "<tr>\n" .
                        "<th style='width: 5%'>" . xlt('Send') . '</th>' .
                        '<th style="min-width: 25%">' . xlt('Profile') . '</th>' .
                        '<th style="max-width: 65%">' . xlt('Assigned Templates') . '</th>' .
                        '<th>' . xlt('Total') . '</th>' .
                        "</tr>\n";
                    echo "</thead>\n";
                    foreach ($profile_list as $profile => $profiles) {
                        $template_list = '';
                        $profile_items_list = $templateService->getProfileListByProfile($profile);
                        if (empty($profile_items_list)) {
                            continue;
                        }
                        $total = 0;
                        foreach ($profile_items_list as $key => $files) {
                            $total += count($files ?? []);
                            foreach ($files as $file) {
                                $template_list .= $file['template_name'] . '; ';
                            }
                        }
                        $profile_esc = attr($profile);
                        echo '<tr>';
                        echo "<td><input type='checkbox' class='form-check-inline' name='send' data-send_profile='yes' value='" . $profile_esc . "' /></td>";
                        echo '<td>' . text($profiles['title']) . '</td>';
                        echo '<td>' . text($template_list) . '</td>';
                        echo '<td>' . text($total) . '</td>';
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
            <hr />
            <div class='row'>
                <div class='col col-12' data-toggle='collapse' data-target='#template-collapse'>
                    <h5><i class='fa fa-eye mr-1' role='button' title="<?php echo xlt('Click to expand or collapse All active patient templates panel.'); ?>"></i><?php echo '' . xlt('All Patient Templates') . '' ?></h5>
                </div>
                <div class='col col-12 table-responsive <?php echo attr($_REQUEST['all_state'] ?: 'collapse') ?>' id='template-collapse'>
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
                        if (empty($cat)) {
                            $cat = xlt('Default');
                        }
                        foreach ($files as $file) {
                            $template_id = $file['id'];
                            echo '<tr>';
                            /*echo "<td><input type='checkbox' class='form-check-inline' id='send' name='send' value='" . attr($template_id) . "' /></td>";*/
                            echo '<td>' . text(ucwords($cat)) . '</td><td>';
                            echo '<button id="templateEdit' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ')" type="button">' . text($file['template_name']) . '</button>' .
                                '<button id="templateDelete' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-danger" onclick="templateDelete(' . attr_js($template_id) . ')" type="button">' . xlt('Delete') . '</button>';
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
                <div class='col col-12 table-responsive'>
                    <?php
                    // by categories and patient pid.
                    $templates = [];
                    $show_cat_flag = false;

                    // Category selected so get all of them for pid's
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
                        '<th>' . xlt('Patient') . '</th>' .
                        '<th>' . xlt('Category') . '</th>' .
                        '<th>' . xlt('Template Actions') .
                        '</th><th>' . xlt('Status') .
                        '</th><th>' . xlt('Size') .
                        '</th><th>' . xlt('Last Modified') . '</th>' .
                        "</tr>\n";
                    echo "</thead>\n";
                    echo "<tbody>\n";
                    foreach ($templates as $cat => $files) {
                        if (empty($cat)) {
                            $cat = xlt('Default');
                        }
                        foreach ($files as $file) {
                            $template_id = $file['id'];
                            echo '<tr><td>' . text($file['location']) . '</td>';
                            echo '<td>' . text(ucwords($cat)) . '</td>';
                            echo '<td>' .
                                '<button type="button" id="patientEdit' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-primary" onclick="templateEdit(' . attr_js($template_id) . ')">' .
                                text($file['template_name']) . "</button>\n" .
                                '<button type="button" id="patientDelete' . attr($template_id) .
                                '" class="btn btn-sm btn-outline-danger" onclick="templateDelete(' . attr_js($template_id) . ')">' . xlt('Delete') . "</button></td>\n";
                            echo '<td>' . text($file['status']) . '</td>';
                            echo '<td>' . text($file['size']) . '</td>';
                            echo '<td>' . text(date('m/d/Y H:i:s', strtotime($file['modified_date']))) . '</td>';
                            echo "</tr>\n";
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
        </div>
    </div>
</body>
</html>
