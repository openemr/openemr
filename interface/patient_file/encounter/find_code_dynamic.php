<?php

/**
 * Interactive code finder.
 * For DataTables documentation see: http://legacy.datatables.net/
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2015-2017 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: Make more intuitive

require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc.php');
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$info_msg = "";

// What we are picking from: codes, fields, lists or groups
$what = empty($_GET['what']) ? 'codes' : $_GET['what'];

// For what == codes
$codetype = empty($_GET['codetype']) ? '' : $_GET['codetype'];
if (!empty($codetype)) {
    $allowed_codes = split_csv_line($codetype);
}
// This is the html element of the target script where the selected code will be stored.
$target_element = empty($_GET['target_element']) ? '' : $_GET['target_element'];

// For what == fields
$source = empty($_GET['source']) ? 'D' : $_GET['source'];

// For what == groups
$layout_id = empty($_GET['layout_id']) ? '' : $_GET['layout_id'];

// For setting limit on selection
$limit = empty($_GET['limit']) ? 0 : (int)$_GET['limit'];

$singleCodeSelection = $_GET['singleCodeSelection'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo xlt('Code Finder'); ?></title>

<?php Header::setupHeader(['opener', 'datatables', 'datatables-bs', 'datatables-colreorder']); ?>

<script>
    var singleCodeSelection = <?php echo (isset($_GET['singleCodeSelection'])) ? js_escape($_GET['singleCodeSelection']) : 0 ?>;
    var oTable;

    // Keeps track of which items have been selected during this session.
    var oChosenIDs = {};

    $(function () {
        // Initializing the DataTable.
        oTable = $('#my_data_table').dataTable({
            "bProcessing": true,
            // Next 2 lines invoke server side processing
            "bServerSide": true,
            "sAjaxSource": "find_code_dynamic_ajax.php?csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>,
            // Vertical length options and their default
            "aLengthMenu": [15, 25, 50, 100],
            "iDisplayLength": 50,
            // Specify a width for the first column.
            "aoColumns": [{"sWidth": "20%"}, {"sWidth": "60%"}, {"sWidth": "10%"}],
            // This callback function passes some form data on each call to the ajax handler.
            "fnServerParams": function (aoData) {
                aoData.push({"name": "what", "value": <?php echo js_escape($what); ?>});
                <?php if ($what == 'codes') { ?>
                aoData.push({"name": "codetype", "value": document.forms[0].form_code_type.value});
                aoData.push({"name": "inactive", "value": (document.forms[0].form_include_inactive.checked ? 1 : 0)});
                <?php } elseif ($what == 'fields') { ?>
                aoData.push({"name": "source", "value": <?php echo js_escape($source); ?>});
                <?php } elseif ($what == 'groups') { ?>
                aoData.push({"name": "layout_id", "value": <?php echo js_escape($layout_id); ?>});
                <?php } ?>
            },
            // Drawing a row, apply styling if it is previously selected.
            "fnCreatedRow": function (nRow, aData, iDataIndex) {
                if (oChosenIDs[nRow.id]) {
                    nRow.style.fontWeight = 'bold';
                }
            },
            // Language strings are included so we can translate them
            "oLanguage": {
                "sSearch": <?php echo xlj('Search for'); ?> +":",
                "sLengthMenu": <?php echo xlj('Show'); ?> +" _MENU_ " + <?php echo xlj('entries'); ?>,
                "sZeroRecords": <?php echo xlj('No matching records found'); ?>,
                "sInfo": <?php echo xlj('Showing'); ?> +" _START_ " + <?php echo xlj('to{{range}}'); ?> +" _END_ " + <?php echo xlj('of'); ?> +" _TOTAL_ " + <?php echo xlj('entries'); ?>,
                "sInfoEmpty": <?php echo xlj('Nothing to show'); ?>,
                "sInfoFiltered": "(" + <?php echo xlj('filtered from'); ?> +" _MAX_ " + <?php echo xlj('total entries'); ?> +")",
                "oPaginate": {
                    "sFirst": <?php echo xlj('First'); ?>,
                    "sPrevious": <?php echo xlj('Previous'); ?>,
                    "sNext": <?php echo xlj('Next'); ?>,
                    "sLast": <?php echo xlj('Last'); ?>
                }
            }
        });

        // OnClick handler for the rows
        $('#my_data_table').on('click', 'tbody tr', function () {
            var limit = <?php echo js_escape($limit); ?>;
            var target_element = <?php echo js_escape($target_element); ?>;

            if (Object.values(oChosenIDs).length < limit || limit <= 0) {
                var jobj = JSON.parse(this.id.substring(4));

                this.style.fontWeight = 'bold';
                oChosenIDs[this.id] = 1;

                <?php if ($what == 'codes') { ?>
                // this.id is of the form "CID|jsonstring".
                var codesel = jobj['code'].split('|');
                selcode(jobj['codetype'], codesel[0], codesel[1], jobj['description'], target_element, limit, jobj['modifier']);
                <?php } elseif ($what == 'fields') { ?>
                selectField(jobj);
                <?php } elseif ($what == 'lists') { ?>
                SelectList(jobj);
                <?php } elseif ($what == 'groups') { ?>
                SelectItem(jobj);
                <?php } ?>
            }

        });

        <?php if ($what == 'codes') { ?>
        // Initialize the selector of codes that can be deleted.
        if (opener.get_related) {
            var acodes = opener.get_related();
            var sel = document.forms[0].form_delcodes;
            if (acodes.length > 1) {
                for (var i = 0; i < acodes.length; ++i) {
                    sel.options[sel.options.length] = new Option(acodes[i], acodes[i]);
                }
            } else {
                sel.style.display = 'none';
            }
        }
        <?php } ?>

        // Set focus to the Search field.
        $('#my_data_table_filter input').focus();

    });

    <?php if ($what == 'codes') { ?>
    // Pass info back to the opener and close this window. Specific to billing/product codes.
    function selcode(codetype, code, selector, codedesc, target_element, limit = 0, modifier = '') {
        if (opener.closed || (!opener.set_related && !opener.set_related_target)) {
            alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
        } else {
            if (target_element != '') {
                var msg = opener.set_related_target(codetype, code, selector, codedesc, target_element, limit);
            } else if (singleCodeSelection == '1') {
                opener.promiseData = JSON.stringify({codetype, code, selector, codedesc});
                dlgclose();
            } else {
                var msg = opener.set_related(codetype, code, selector, codedesc, modifier);
            }
            if (msg) alert(msg);
            // window.close();
            return false;
        }
    }

    // Function to call the opener to delete all or one related code. Specific to billing/product codes.
    function delcode() {
        if (opener.closed || !opener.del_related) {
            alert(<?php echo xlj('The destination form was closed; I cannot act on your selection.'); ?>);
        } else {
            var sel = document.forms[0].form_delcodes;
            opener.del_related(sel.value);
            oChosenIDs = {};
            oTable.fnDraw();
            // window.close();
            return false;
        }
    }

    <?php } elseif ($what == 'fields') { ?>
    function selectField(jobj) {
        if (opener.closed || !opener.SetField) {
            alert('The destination form was closed; I cannot act on your selection.');
        } else {
            opener.SetField(jobj['field_id'], jobj['title'], jobj['data_type'], jobj['uor'], jobj['fld_length'],
                jobj['max_length'], jobj['list_id'], jobj['titlecols'], jobj['datacols'], jobj['edit_options'],
                jobj['description'], jobj['fld_rows']);
        }
        dlgclose();
        return false;
    }

    function newField() {
        return selectField({
            "field_id": document.forms[0].new_field_id.value,
            "title": '',
            "data_type": 2,
            "uor": 1,
            "fld_length": 10,
            "max_length": 255,
            "list_id": '',
            "titlecols": 1,
            "datacols": 3,
            "edit_options": '',
            "description": '',
            "fld_rows": 0
        });
    }

    <?php } elseif ($what == 'lists') { ?>
    function SelectList(jobj) {
        if (opener.closed || !opener.SetList)
            alert('The destination form was closed; I cannot act on your selection.');
        else
            opener.SetList(jobj['code']);
        dlgclose();
        return false;
    }

    <?php } elseif ($what == 'groups') { ?>
    var SelectItem = function (jobj) {
        if (opener.closed)
            alert('The destination form was closed; I cannot act on your selection.');
        else
            opener.MoveFields(jobj['code']);
        dlgclose();
        return false;
    };

    <?php } ?>

</script>

</head>

<body id="codes_search">
<div class="container-fluid">
    <?php
    $string_target_element = empty($target_element) ? '?' : "?target_element=" . attr_url($target_element) . "&";
    ?>

    <form method='post' name='theform'>
        <?php
        echo "<div class='form-group row mb-3'>\n";
        if ($what == 'codes') {
            if (isset($allowed_codes)) {
                if (count($allowed_codes) == 1) {
                    echo "<div class='col'><input type='text' class='form-control' name='form_code_type' value='" . attr($codetype) . "' size='5' readonly /></div>\n";
                } else {
                    echo "<div class='col'><select name='form_code_type' onchange='oTable.fnDraw()'>\n";
                    foreach ($allowed_codes as $code) {
                        if (empty($code_types[$code]['label'])) {
                            continue;
                        }
                        echo " <option value='" . attr($code) . "'>" . xlt($code_types[$code]['label']) . "</option>\n";
                    }
                    echo "</select></div>\n";
                }
            } else {
                echo "<div class='col'><select class='form-control' name='form_code_type' onchange='oTable.fnDraw()'>\n";
                foreach ($code_types as $key => $value) {
                    if (empty($value['label'])) {
                        continue;
                    }
                    echo " <option value='" . attr($key) . "'";
                    echo ">" . xlt($value['label']) . "</option>\n";
                }
                echo " <option value='PROD'";
                echo ">" . xlt("Product") . "</option>\n";
                echo "   </select></div>\n";
            }
            echo "\n";
            echo "<div class='col'>";
            echo "<input type='checkbox' name='form_include_inactive' value='1' onclick='oTable.fnDraw()' />" .
                xlt('Include Inactive') . "\n";
            echo "\n";
            echo "<button class='btn btn-danger btn-sm btn-delete' value='" . xla('Delete') . "' onclick='delcode()'>" . xla('Delete') . "</button>\n";
            echo "<select name='form_delcodes'>\n";
            echo " <option value=''>" . xlt('All') . "</option>\n";
            echo "</select>\n";
            echo "\n";
            echo "<button class='btn btn-secondary btn-sm btn-cancel' value='" . xla('Close') . "' onclick='dlgclose()'>" . xla('Close') . "</button>\n";
            echo "</div>";
        }
        if ($what == 'lists') {
            echo "<button class='btn btn-secondary btn-sm btn-delete' value='" . xla('Delete') . "' onclick='SelectList({\"code\":\"\"})'>" . xla('Delete') . "</button>\n";
        }
        echo "</div>\n";
        ?>

        <!-- Exception here: Do not use table-responsive as it breaks datatables
        note by sjp: table-responsive does not go in table but the container div!
        !-->
        <table id="my_data_table" class="table table-striped table-hover table-sm w-100">
            <thead>
            <tr>
                <th><?php echo xlt('Code'); ?></th>
                <th><?php echo xlt('Description'); ?></th>
                <th><?php echo xlt('Modifier'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <!-- Class "dataTables_empty" is defined in jquery.dataTables.css -->
                <td colspan="2" class="dataTables_empty">...</td>
            </tr>
            </tbody>
        </table>

        <?php if ($what == 'fields' && $source == 'E') { ?>
            <div class="row">
                <div class="col">
                    <input type='text' class="form-control" name='new_field_id' size='20' />
                </div>
                <div class="col">
                    <button class="btn btn-secondary" value='<?php echo xla('Or create this new field ID') ?>' onclick='newField()'>
                        <?php echo xla('Or create this new field ID') ?>
                    </button>
                </div>
            </div>
        <?php } ?>

    </form>
</div>
</body>
</html>
