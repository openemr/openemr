<?php

/**
 * Code selector.
 * For DataTables documentation see: http://legacy.datatables.net/
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Thomas Pantelis <tompantelis@gmail.com>
 * @copyright Copyright (c) 2020 Thomas Pantelis <tompantelis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/patient.inc');
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$codetype = empty($_GET['codetype']) ? '' : $_GET['codetype'];
if (! empty($codetype)) {
    $allowed_codes = split_csv_line($codetype);
}

?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo xlt('Select Codes'); ?></title>

<?php Header::setupHeader(['opener', 'datatables', 'datatables-bs', 'datatables-colreorder']); ?>

<script>

var oTable;

// Keeps track of which items have been selected during this session.
var oSelectedIDs = {};

$(function () {
    // Initializing the DataTable.
    oTable = $('#my_data_table').dataTable({
        "bProcessing": true,

        // Next 2 lines invoke server side processing
        "bServerSide": true,
        "sAjaxSource": "find_code_dynamic_ajax.php?csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>,

        // Vertical length options and their default
        "aLengthMenu": [ 15, 25, 50, 100 ],
        "iDisplayLength": 15,

        // Specify a width for the first column.
        "aoColumns": [{"sWidth":"10%"}, null],

        // This callback function passes some form data on each call to the ajax handler.
        "fnServerParams": function (aoData) {
            aoData.push({"name": "what", "value": <?php echo js_escape('codes'); ?>});
            aoData.push({"name": "codetype", "value": document.forms[0].form_code_type.value});
            aoData.push({"name": "inactive", "value": (document.forms[0].form_include_inactive.checked ? 1 : 0)});
        },

        // Drawing a row, apply styling if it is previously selected.
        "fnCreatedRow": function (nRow, aData, iDataIndex) {
            showRowSelection(nRow)
        },

        // Language strings are included so we can translate them
        "oLanguage": {
            "sSearch"       : <?php echo xlj('Search for'); ?> + ":",
            "sLengthMenu"   : <?php echo xlj('Show'); ?> + " _MENU_ " + <?php echo xlj('entries'); ?>,
            "sZeroRecords"  : <?php echo xlj('No matching records found'); ?>,
            "sInfo"         : <?php echo xlj('Showing'); ?> + " _START_ " + <?php echo xlj('to{{range}}'); ?> + " _END_ " + <?php echo xlj('of'); ?> + " _TOTAL_ " + <?php echo xlj('entries'); ?>,
            "sInfoEmpty"    : <?php echo xlj('Nothing to show'); ?>,
            "sInfoFiltered" : "(" + <?php echo xlj('filtered from'); ?> + " _MAX_ " + <?php echo xlj('total entries'); ?> + ")",
            "oPaginate"     : {
                "sFirst"        : <?php echo xlj('First'); ?>,
                    "sPrevious" : <?php echo xlj('Previous'); ?>,
                    "sNext"    : <?php echo xlj('Next'); ?>,
                    "sLast"    : <?php echo xlj('Last'); ?>
            }
        }
    });

    // OnClick handler for the rows
    oTable.on('click', 'tbody tr', function () {
        oSelectedIDs[this.id] = oSelectedIDs[this.id] ? 0 : 1;
        showRowSelection(this)
     });
    
    // onmouseover handler for rows
    oTable.on('mouseover', 'tr', function() {
        showCursor(this);
    });
});

function showRowSelection(row) {
    row.style.fontWeight = oSelectedIDs[row.id] ? 'bold' : 'normal';
}

function showCursor(row) {
    row.style.cursor = "pointer";
}

function onOK() {
    if (opener.closed || ! opener.OnCodeSelected) {
        alert(<?php echo xlj('The destination form was closed.'); ?>);
    }
    else {
        var ids = Object.keys(oSelectedIDs)
        for (i = 0; i < ids.length; i++) {
            if (!oSelectedIDs[ids[i]]) {
                continue
            }

            // Row ids are of the form "CID|jsonstring".
            var jobj = JSON.parse(ids[i].substring(4));
            var code = jobj['code'].split('|');
            var msg = opener.OnCodeSelected(jobj['codetype'], code[0], code[1], jobj['description']);
            if (msg) {
                alert(msg);
            }
        }

        dlgclose()
        return false;
    }
}

</script>

</head>

<body id="codes_search">
    <div class="container-fluid">
        <form method='post' name='theform'>
        <?php
        echo "<div class='form-group row mb-3'>\n";
        if (isset($allowed_codes)) {
            if (count($allowed_codes) == 1) {
                echo "<div class='col'><input type='text' name='form_code_type' value='" . attr($codetype) . "' size='5' readonly /></div>\n";
            } else {
                echo "<div class='col'><select name='form_code_type' onchange='oTable.fnDraw()'>\n";
                foreach ($allowed_codes as $code) {
                    echo " <option value='" . attr($code) . "'>" . xlt($code_types[$code]['label']) . "</option>\n";
                }
                echo "</select></div>\n";
            }
        } else {
            echo "<div class='col'><select class='form-control' name='form_code_type' onchange='oTable.fnDraw()'>\n";
            foreach ($code_types as $key => $value) {
                echo " <option value='" . attr($key) . "'";
                echo ">" . xlt($value['label']) . "</option>\n";
            }
            echo " <option value='PROD'";
            echo ">" . xlt("Product") . "</option>\n";
            echo "   </select></div>\n";
        }
        echo "\n";
        echo "<div class='col'>";
        echo "<input type='checkbox' name='form_include_inactive' value='1' onclick='oTable.fnDraw()' />" . xlt('Include Inactive') . "\n";
        echo "\n";
        echo "<button class='btn btn-secondary btn-sm btn-save' value='" . xla('OK') . "' onclick='onOK()'>" . xla('Ok') . "</button>\n";
        echo "\n";
        echo "<button class='btn btn-secondary btn-sm btn-cancel' value='" . xla('Cancel') . "' onclick='dlgclose()'>" . xla('Cancel') . "</button>\n";
        echo "</div>";

        echo "</div>\n";
        ?>

          <!-- Exception here: Do not use table-responsive as it breaks datatables !-->
          <table id="my_data_table" class="table table-sm">
              <thead>
                  <tr>
                      <th><?php echo xlt('Code'); ?></th>
                      <th><?php echo xlt('Description'); ?></th>
                  </tr>
              </thead>
              <tbody>
                  <tr>
                      <!-- Class "dataTables_empty" is defined in jquery.dataTables.css -->
                      <td colspan="2" class="dataTables_empty">...</td>
                  </tr>
              </tbody>
          </table>
        </form>
    </div>
</body>
</html>
