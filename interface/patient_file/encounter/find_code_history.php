<?php
/**
 * Lookup past and current dx codes (favorites)
 * (Temporary rest test interface until add a model)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/options.inc.php");

use OpenEMR\Core\Header;

function unique_array_by_key($array, $key)
{
    $temp_array = array();
    $i = 0;
    $key_array = array();

    foreach ($array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $temp_array[$i] = $val;
        }
        $i++;
    }
    return $temp_array;
}

function get_history_codes($pid)
{
    $origin = xlt('Patient Problems');
    $probcodes = array();
    $bld = '';
    $dres = sqlStatementNoLog(
        "SELECT diagnosis as codes FROM lists " .
        "Where activity = 1 And type = ? And pid = ?",
        array('medical_problem', $pid)
    );
    while ($diag = sqlFetchArray($dres)) {
        if (strpos($diag['codes'], 'ICD') === false) {
            continue;
        }
        $bld .= $diag['codes'] . ';';
    }
    $diags = explode(';', $bld);
    $diags = array_unique($diags);
    foreach ($diags as $d) {
        if (!$d) {
            continue;
        }
        $r['origin'] = $origin;
        $r['code'] = $d;
        $r['desc'] = lookup_code_descriptions($d);
        $probcodes[] = $r;
    }
    // well that's problems history, now procedure history
    $dres = sqlStatementNoLog(
        "Select procedure_order_code.diagnoses as codes From procedure_order " .
        "Inner Join procedure_order_code On procedure_order_code.procedure_order_id = procedure_order.procedure_order_id " .
        "Where procedure_order_code.diagnoses > '' Group By procedure_order_code.diagnoses"
    );
    $origin = xlt('Procedures History');
    $dxcodes = array();
    $bld = '';
    while ($diag = sqlFetchArray($dres)) {
        $bld .= $diag['codes'] . ';';
    }
    $diags = explode(';', $bld);
    $diags = array_unique($diags);
    foreach ($diags as $d) {
        if (!$d) {
            continue;
        }
        $r['origin'] = $origin;
        $r['code'] = $d;
        $r['desc'] = lookup_code_descriptions($d);
        $dxcodes[] = $r;
    }
    return unique_array_by_key(array_merge($probcodes, $dxcodes), 'code');
}

$dxcodes = get_history_codes($pid);
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php Header::setupHeader(['opener']); ?>
    <style>
        .tips {
            display: none;
        }
    </style>
    <script>
        const errorMsg = '' + <?php echo xlj("Error finding diagnosis element. Try again."); ?>;

        function setFormDx() {
            if (opener) {
                let currentDx = document.getElementById('workingDx').value;
                currentDx = currentDx.replace(';;', ';');
                currentDx = currentDx.trim().replace(/^;+|;+$/gm, '');
                opener.targetElement.value = currentDx;
            } else {
                alert(errorMsg);
            }
            dlgclose();
        }

        function rtnCode(codeElement) {
            let target = opener.targetElement;
            let currentDx = document.getElementById('workingDx').value;
            if (currentDx.indexOf(codeElement.value) !== -1) {
                currentDx = currentDx.replace(codeElement.value, '').replace(';;', ';');
                currentDx = currentDx.trim().replace(/^;+|;+$/gm, '');
                document.getElementById('workingDx').value = currentDx;
                return;
            }
            if (currentDx.length > 1) {
                currentDx += ';' + codeElement.value;
            } else {
                currentDx = codeElement.value;
            }
            document.getElementById('workingDx').value = currentDx;
        }

        function clearCodes(codeElement) {
            if (opener) {
                opener.targetElement.value = '';
                document.getElementById('workingDx').value = "";
            } else {
                alert(errorMsg);
            }
        }

        function setCodes() {
            if (opener) {
                document.getElementById('workingDx').value = opener.targetElement.value;
            } else {
                alert(errorMsg);
            }
        }

        $(function () {
            setCodes();
            let targetDoneButton = top.document.getElementById('saveDx');
            let targetTipsButton = top.document.getElementById('showTips');
            targetDoneButton.onclick = function () {
                setFormDx();
            };
            targetTipsButton.onclick = function () {
                $("#tips").toggle();
            };
        });
    </script>
</head>
<body>
    <div class="container-fluid">
        <div id="tips" class="tips">
            <section class="panel panel-default">
                <header class="panel-heading panel-heading-sm">
                    <h4 class="panel-title"><?php echo xlt('Usage Tips') ?></h4>
                </header>
                <div class="panel-body panel-body-sm">
                    <ul>
                        <?php
                        echo "<li>" . xlt("This dialog is generated from patients problems diagnoses and the accumulated diagnoses of past procedures.") . "</li>";
                        echo "<li>" . xlt("Build diagnoses list by clicking appropriate code button.") . "</li>";
                        echo "<li>" . xlt("Duplicate codes are deleted from list, otherwise; appends to list.") . "</li>";
                        echo "<li>" . xlt("Once finished editing, click Save. Procedure forms procedure diagnoses will fill exactly as built in this dialog.") . "</li>";
                        echo "<li>" . xlt("The legacy code finder is still available for codes not found in this finder or codes list editing.") . "</li>";
                        ?>
                    </ul>
                    <button class='btn btn-xs btn-success pull-right' onclick='$("#tips").toggle();return false;'><?php echo xlt('Dismiss') ?></button>
                </div>
            </section>
        </div>
        <div class="input-group">
            <span class="input-group-addon" onclick='clearCodes(this)'><i class="fa fa-trash fa-1x"></i></span>
            <input class='form-control' type='text' id='workingDx'
                title='<?php echo xla('Current Working Procedure Diagnoses'); ?>' value='' />
        </div>
        <br>
        <div>
            <table class="table table-condensed table-striped">
                <thead>
                <tr>
                    <th><?php echo xlt('Origin'); ?></th>
                    <th><?php echo xlt('Code'); ?></th>
                    <th><?php echo xlt('Description'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($dxcodes as $pc) {
                    echo "<tr>\n" .
                        "<td>" . $pc['origin'] . "</td>\n" .
                        "<td><button class='btn btn-xs btn-default' onclick='rtnCode(this)' value='" . attr($pc['code']) . "'>" .
                        text(explode(':', $pc['code'])[1]) . "</button></td>\n" .
                        "<td>" . $pc['desc'] . "</td>\n" .
                        "</tr>\n";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
