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

require_once(dirname(_DIR_) . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/options.inc.php");

use OpenEMR\Core\Header;

function get_history_codes($pid)
{
    $origin = xlt('Problems');
    $probcodes = array();
    $dres = sqlStatementNoLog(
        "SELECT diagnosis as codes, title FROM lists " .
        "Where activity = 1 And type = ? And pid = ? Group By lists.diagnosis",
        array('medical_problem', $pid)
    );
    while ($diag = sqlFetchArray($dres)) {
        $diag['codes'] = preg_replace('/^;+|;+$/', '', $diag['codes']);
        $bld = explode(';', $diag['codes']);
        foreach ($bld as $cde) {
            $probcodes[] = array(
                'origin' => $origin,
                'code' => $cde,
                'desc' => lookup_code_descriptions($cde),
                'procedure' => $diag['title']
            );
        }
    }
    // well that's problems history, now procedure history
    $dres = sqlStatementNoLog(
        "Select procedure_order_code.diagnoses as codes, procedure_order_code.procedure_name as proc From procedure_order " .
        "Inner Join procedure_order_code On procedure_order_code.procedure_order_id = procedure_order.procedure_order_id " .
        "Where procedure_order_code.diagnoses > '' Group By procedure_order_code.diagnoses"
    );
    $origin = xlt('Procedures');
    $dxcodes = array();
    while ($diag = sqlFetchArray($dres)) {
        $diag['codes'] = preg_replace('/^;+|;+$/', '', $diag['codes']);
        $bld = explode(';', $diag['codes']);
        foreach ($bld as $cde) {
            $dxcodes[] = array(
                'origin' => $origin,
                'code' => $cde,
                'desc' => lookup_code_descriptions($cde),
                'procedure' => $diag['proc']
            );
        }
    }
    // make unique
    $dxcodes = array_intersect_key($dxcodes, array_unique(array_map('serialize', $dxcodes)));
    // the king of sort
    array_multisort(
        array_column($dxcodes, 'procedure'),
        SORT_ASC,
        array_column($dxcodes, 'code'),
        SORT_ASC,
        $dxcodes
    );

    // problems on top then our sorted dx/procedure array
    return array_merge($probcodes, $dxcodes);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php Header::setupHeader(['opener']); ?>
    <style>
        body {
            height: 100%;
            width: 100%;
        }
        .tips {
            display: none;
        }
        .loading {
             position: relative;
             top: 40vh;
             background: #fff;
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
            // search table to find a match for procedure we are seeking dx for.
            let src = opener.targetProcedure.children[1].value;
            let rows = $("#historyTable tr td").filter(":contains(" + src + ")");
            let i = 0;
            while (i < rows.length) {
                rows[i].innerHTML = '<mark>' + rows[i].innerHTML + '</mark>';
                $(rows[i]).closest('tr').addClass('text-danger');
                i++;
            }
            $('.loading').fadeOut();
            if (rows.length) {
                // scroll to first match and make active
                $(rows[0]).closest('tr').addClass('active');
                $(window).scrollTop($(rows[0]).offset().top - ($(window).height() / 2));
            }
        });
    </script>
</head>

<body>
    <div class="container-fluid" style="position:fixed;width:100%;margin-right:10px;">
        <div class="input-group" style="background:white;">
            <span class="input-group-addon" onclick='clearCodes(this)'><i class="fa fa-trash fa-1x"></i></span>
            <input class='form-control text-danger' type='text' id='workingDx' style="color:#a94442;"
                title='<?php echo xla('Current Working Procedure Diagnoses'); ?>' value='' />
        </div>
        <div id="tips" class="tips">
            <section class="panel panel-default">
                <header class="panel-heading panel-heading-sm">
                    <h4 class="panel-title"><?php echo xlt('Usage Tips') ?></h4>
                </header>
                <div class="panel-body bg-warning">
                    <ul>
                        <?php
                        echo "<li>" . xlt("This dialog is generated from patient problem diagnoses and the accumulated diagnoses of all past procedures.") . "</li>";
                        echo "<li>" . xlt("The finder table is grouped by past procedures then diagnosis code. Although there may be duplicate dx codes, they will be grouped with the appropriate procedure making building diagnoses list easier.") . "</li>";
                        echo "<li>" . xlt("On opening, all dx code rows that match the new procedure from procedure order form will be marked and then will scroll to the first match.") . "</li>";
                        echo "<li>" . xlt("Build diagnoses list by clicking appropriate code button.") . "</li>";
                        echo "<li>" . xlt("Duplicate codes are deleted from editor list otherwise, code will append to list.") . "</li>";
                        echo "<li>" . xlt("Once finished editing, click Save. The procedure forms current procedure diagnoses will fill exactly as built in this dialog.") . "</li>";
                        echo "<li>" . xlt("The legacy code finder is still available for codes not found in this finder or code list editing.") . "</li>";
                        ?>
                    </ul>
                    <button class='btn btn-xs btn-success pull-right' onclick='$("#tips").toggle();return false;'><?php echo xlt('Dismiss') ?></button>
                </div>
            </section>
        </div>
        <div class="loading text-center"><i class="fa fa-refresh fa-3x fa-spin"></i></div>
    </div>
    <div class="container-fluid">
        <div style="margin-top:45px;">
            <table class="table table-condensed table-hover" id="historyTable">
                <thead>
                <tr>
                    <th><?php echo xlt('Origin'); ?></th>
                    <th><?php echo xlt('Code'); ?></th>
                    <th><?php echo xlt('Code Description'); ?></th>
                    <th><?php echo xlt('Origin Description'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $dxcodes = get_history_codes($pid);
                foreach ($dxcodes as $pc) {
                    $code = explode(':', $pc['code']);
                    $code[0] = text($code[0]);
                    $code[1] = text($code[1]);
                    echo "<tr>\n" .
                        "<td>" . $pc['origin'] . "</td>\n" .
                        "<td><button class='btn btn-xs btn-default' onclick='rtnCode(this)' " .
                        " value='" . attr($pc['code']) . "'>$code[0]:&nbsp;<u style='color:red;'>" . $code[1] . "</u></button></td>\n" .
                        "<td>" . text($pc['desc']) . "</td>\n" .
                        "<td>" . text($pc['procedure']) . "</td>\n" .
                        "</tr>\n";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
<script>

</script>
</body>
</html>
