<?php

/**
 * stats.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}
?>

<script>
    if(typeof load_location === 'undefined') {
        function load_location(location) {
            top.restoreSession();
            document.location = location;
        }
    }
</script>

<div id="patient_stats_summary">

<table id="patient_stats_issues">

<?php
$numcols = '1';
$erx_upload_complete = 0;
$old_key = "";
$display_current_medications_below = 1;

foreach ($ISSUE_TYPES as $key => $arr) {
  // Skip if user has no access to this issue type.
    if (!AclMain::aclCheckIssue($key)) {
        continue;
    }


    $query = "SELECT * FROM lists WHERE pid = ? AND type = ? AND ";
    $query .= dateEmptySql('enddate');
    if ($GLOBALS['erx_enable'] && $GLOBALS['erx_medication_display'] && $key == 'medication') {
        $query .= "and erx_uploaded != '1' ";
    }

    if ($GLOBALS['erx_enable'] && $GLOBALS['erx_allergy_display'] && $key == 'allergy') {
        $query .= "and erx_uploaded != '1' ";
    }

    $query .= "ORDER BY begdate";
    $pres = sqlStatement($query, array($pid, $key));
    if ($old_key == "medication" && $GLOBALS['erx_enable'] && $erx_upload_complete == 1) {
        $display_current_medications_below = 0;
        ?>
    <div>
        <table id="patient_stats_prescriptions">
        <?php
        if ($GLOBALS['erx_enable']) {
            ?>
        <tr><td>
            <?php
            if ($_POST['embeddedScreen']) {
                $widgetTitle = xl('Current Medications');
                $widgetLabel = "current_prescriptions";
                $widgetButtonLabel = '';
                $widgetButtonLink = '';
                $widgetAuth = false;
                $widgetButtonClass = '';
                $bodyClass = "summary_item small";
                $fixedWidth = false;
                expand_collapse_widget(
                    $widgetTitle,
                    $widgetLabel,
                    $widgetButtonLabel,
                    $widgetButtonLink,
                    $widgetButtonClass,
                    $linkMethod,
                    $bodyClass,
                    $widgetAuth,
                    $fixedWidth
                );
            }

            $res = sqlStatement("select * from prescriptions where patient_id=? and active='1'", array($pid));
            ?>
        <table>
            <?php
            if (sqlNumRows($res) == 0) {
                ?>
  <tr class="text">
<td><?php echo xlt('None{{Prescriptions}}'); ?></td>
  </tr>
                <?php
            }

            while ($row_currentMed = sqlFetchArray($res)) {
                $runit = generate_display_field(array('data_type' => '1','list_id' => 'drug_units'), $row_currentMed['unit']);
                $rin = generate_display_field(array('data_type' => '1','list_id' => 'drug_form'), $row_currentMed['form']);
                $rroute = generate_display_field(array('data_type' => '1','list_id' => 'drug_route'), $row_currentMed['route']);
                $rint = generate_display_field(array('data_type' => '1','list_id' => 'drug_interval'), $row_currentMed['interval']);
                ?>
  <tr class=text >
<td><?php echo text($row_currentMed['drug']);?></td>
<td><?php
        $unit = '';
if ($row_currentMed['size'] > 0) {
    $unit = text($row_currentMed['size']) . " " . $runit . " ";
}

        echo $unit . " " . text($row_currentMed['dosage']) . " " . $rin . " " . $rroute . " " . $rint;
?></td>
  </tr>
                <?php
            } // end while
            ?>
        </table>
        </td></tr>
            <?php
        } // end erx_enable
        $old_key = '';
    }

    if (sqlNumRows($pres) > 0 || $arr[4] == 1) {
        $old_key = $key;
        if ($_POST['embeddedScreen']) {
            if ($GLOBALS['erx_enable'] && $key == "medication") {
                $query_uploaded = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' AND ";
                $query_uploaded .= dateEmptySql('enddate');
                $query_uploaded .= "and erx_uploaded != '1' ";
                $query_uploaded .= "ORDER BY begdate";
                $res_uploaded = sqlStatement($query_uploaded, array($pid));
                if (sqlNumRows($res_uploaded) == 0) {
                    $erx_upload_complete = 1;
                    continue;
                }
            }

            echo "<tr><td>";
            // Issues expand collapse widget
            $widgetTitle = $arr[0];
            $widgetLabel = $key;
            if (($key == "allergy" || $key == "medication") && $GLOBALS['erx_enable']) {
                $widgetButtonLabel = xl("Add");
                $widgetButtonLink = "load_location(\"${GLOBALS['webroot']}/interface/eRx.php?page=medentry\")";
            } else {
                $widgetButtonLabel = xl("Edit");
                $widgetButtonLink = "load_location(\"${GLOBALS['webroot']}/interface/patient_file/summary/stats_full.php?active=all&category=" . attr_url($key) . "\")";
            }

            $widgetButtonClass = "";
            $linkMethod = "javascript";
            $bodyClass = "summary_item small";
            $widgetAuth = AclMain::aclCheckIssue($key, '', array('write', 'addonly'));
            $fixedWidth = false;
            expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        } else { // end embeddedScreen
            ?>
            <tr class='issuetitle'>
            <td colspan='$numcols'>
            <span class="text font-weight-bold"><?php echo text($arr[0]); ?></span>
            <a href="javascript:;" class="small font-weight-bold" onclick="load_location(<?php echo attr_js("stats_full.php?active=all&category=" . urlencode($key)); ?>)">(<?php echo xlt('Manage'); ?>)</a>
            </td>
            </tr>
            <?php
        }

        echo "<table>";
        if (sqlNumRows($pres) == 0) {
            if (getListTouch($pid, $key)) {
                // Data entry has happened to this type, so can display an explicit None.
                echo "  <tr><td colspan='$numcols' class='text'>&nbsp;&nbsp;" . xlt('None{{Issues}}') . "</td></tr>\n";
            } else {
                // Data entry has not happened to this type, so show 'Nothing Recorded"
                echo "  <tr><td colspan='$numcols' class='text'>&nbsp;&nbsp;" . xlt('Nothing Recorded') . "</td></tr>\n";
            }
        }

        while ($row = sqlFetchArray($pres)) {
            // output each issue for the $ISSUE_TYPE
            if (!$row['enddate'] && !$row['returndate']) {
                $rowclass = "noend_noreturn";
            } elseif (!$row['enddate'] && $row['returndate']) {
                $rowclass = "noend";
            } elseif ($row['enddate'] && !$row['returndate']) {
                $rowclass = "noreturn";
            }

            echo " <tr class='text $rowclass;'>\n";

            //turn allergies red and bold and show the reaction (if exist)
            if ($key == "allergy") {
                $reaction = "";
                if (!empty($row['reaction'])) {
                    $reaction = " (" . getListItemTitle("reaction", $row['reaction']) . ")";
                }

                echo "  <td colspan='" . attr($numcols) . "' style='color:red;font-weight:bold;'>&nbsp;&nbsp;" . text($row['title'] . $reaction) . "</td>\n";
            } else {
                echo "  <td colspan='" . attr($numcols) . "'>&nbsp;&nbsp;" . text($row['title']) . "</td>\n";
            }

            echo " </tr>\n";
        }

        echo "</table>";
        if ($_POST['embeddedScreen']) {
            echo "</div></td></tr>";
        }
    }
}
?>
</table> <!-- end patient_stats_issues -->

<table id="patient_stats_spreadsheets">
<?php

// Show spreadsheet forms if any are present.
//
$need_head = true;
foreach (array('treatment_protocols','injury_log') as $formname) {
    if (sqlNumRows(sqlStatement("SHOW TABLES LIKE ?", array("form_" . $formname))) > 0) {
        $dres = sqlStatement("SELECT tp.id, tp.value FROM forms, " .
                            "form_" . add_escape_custom($formname) .
                " AS tp WHERE forms.pid = ? AND " .
                            "forms.formdir = ? AND tp.id = forms.form_id AND " .
                            "tp.rownbr = -1 AND tp.colnbr = -1 AND tp.value LIKE '0%' " .
                            "ORDER BY tp.value DESC", array($pid, $formname));
        if (sqlNumRows($dres) > 0 && $need_head) {
            $need_head = false;
            echo " <tr>\n";
            echo "  <td colspan='" . attr($numcols) . "' valign='top'>\n";
            echo "   <span class='title'>Injury Log</span>\n";
            echo "  </td>\n";
            echo " </tr>\n";
        }

        while ($row = sqlFetchArray($dres)) {
            list($completed, $start_date, $template_name) = explode('|', $row['value'], 3);
            echo " <tr>\n";
            echo "  <td colspan='$numcols'>&nbsp;&nbsp;";
            echo "<a class='link' href='javascript:;' ";
            echo "onclick='load_location(\"../../forms/" . attr($formname) . "/new.php?popup=1&id=";
            echo attr_url($row['id']) . "\")'>" .
            text($start_date) . " " .
            text($template_name) . "</a></td>\n";
            echo " </tr>\n";
        }
    }
}
?>
</table> <!-- end patient_stats_spreadsheets -->

<?php if (!$GLOBALS['disable_immunizations'] && !$GLOBALS['weight_loss_clinic']) { ?>
<div>
<table id="patient_stats_imm">
<tr>
    <?php if ($_POST['embeddedScreen']) {
        echo "<td>";
        // Issues expand collapse widget
        $widgetTitle = xl('Immunizations');
        $widgetLabel = "immunizations";
        $widgetButtonLabel = xl("Edit");
        $widgetButtonLink = "javascript:load_location(\"${GLOBALS['webroot']}/interface/patient_file/summary/immunizations.php\")";
        $widgetButtonClass = "";
        $linkMethod = "javascript";
        $bodyClass = "summary_item small";
        $widgetAuth = true;
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
    } else { ?>
<td colspan='<?php echo $numcols ?>' valign='top'>
<span class="text font-weight-bold"><?php echo xlt('Immunizations'); ?></span>
<a href="javascript:;" class="small" onclick="javascript:load_location('immunizations.php')">
    (<b><?php echo xlt('Manage'); ?></b>)
</a>
</td></tr>
<tr><td>
    <?php } ?>

    <?php
    $sql = "select i1.id as id, i1.immunization_id as immunization_id, i1.cvx_code as cvx_code, c.code_text_short as cvx_text, " .
         " if (i1.administered_date, concat(i1.administered_date,' - ',c.code_text_short), IF(i1.note,substring(i1.note,1,20),c.code_text_short)) as immunization_data " .
         " from immunizations i1 " .
         " left join code_types ct on ct.ct_key = 'CVX' " .
         " left join codes c on c.code_type = ct.ct_id AND i1.cvx_code = c.code " .
         " where i1.patient_id = ? " .
         " and i1.added_erroneously = 0" .
         " order by i1.administered_date desc";

    $result = sqlStatement($sql, array($pid));

    if (sqlNumRows($result) == 0) {
        echo " <table><tr>\n";
        echo "  <td colspan='$numcols' class='text'>&nbsp;&nbsp;" . xlt('None{{Immunizations}}') . "</td>\n";
        echo " </tr></table>\n";
    }

    while ($row = sqlFetchArray($result)) {
        echo "&nbsp;&nbsp;";
        echo "<a class='link'";
        echo "' href='javascript:;' onclick='javascript:load_location(" . attr_js("immunizations.php?mode=edit&id=" . urlencode($row['id']) . "&csrf_token_form=" . urlencode(CsrfUtils::collectCsrfToken())) . ")'>" .
        text($row['immunization_data']);

        // Figure out which name to use (ie. from cvx list or from the custom list)
        if ($GLOBALS['use_custom_immun_list']) {
            echo generate_display_field(array('data_type' => '1','list_id' => 'immunizations'), $row['immunization_id']);
        } else {
            if (!(empty($row['cvx_text']))) {
                echo htmlspecialchars(xl($row['cvx_text']), ENT_NOQUOTES);
            } else {
                echo generate_display_field(array('data_type' => '1','list_id' => 'immunizations'), $row['immunization_id']);
            }
        }

        echo "</a><br />\n";
    }
    ?>

    <?php if ($_POST['embeddedScreen']) {
        echo "</td></tr></div>";
    } ?>

</td>
</tr>
</table> <!-- end patient_stats_imm-->
</div>
<?php } ?>

<?php if (!$GLOBALS['disable_prescriptions'] && AclMain::aclCheckCore('patients', 'rx')) { ?>
<div>
<table id="patient_stats_prescriptions">
    <?php if ($GLOBALS['erx_enable'] && $display_current_medications_below == 1) { ?>
<tr><td>
        <?php if ($_POST['embeddedScreen']) {
            $widgetTitle = '';
            $widgetTitle = xl('Current Medications');
            $widgetLabel = "current_prescriptions";
            $widgetButtonLabel = '';
            $widgetButtonLink = '';
            $widgetAuth = false;
            $widgetButtonClass = '';
            $bodyClass = "summary_item small";
            $fixedWidth = false;
            expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        }
        ?>

        <?php
        $res = sqlStatement("select * from prescriptions where patient_id=? and active='1'", array($pid));
        ?>
<table>
        <?php
        if (sqlNumRows($res) == 0) {
            ?>
    <tr class="text">
        <td><?php echo xlt('None{{Prescriptions}}');?></td>
    </tr>
            <?php
        }

        while ($row_currentMed = sqlFetchArray($res)) {
            $runit = generate_display_field(array('data_type' => '1','list_id' => 'drug_units'), $row_currentMed['unit']);
            $rin = generate_display_field(array('data_type' => '1','list_id' => 'drug_form'), $row_currentMed['form']);
            $rroute = generate_display_field(array('data_type' => '1','list_id' => 'drug_route'), $row_currentMed['route']);
            $rint = generate_display_field(array('data_type' => '1','list_id' => 'drug_interval'), $row_currentMed['interval']);
            ?>
    <tr class="text">
        <td><?php echo text($row_currentMed['drug']); ?></td>
        <td><?php $unit = '';
        if ($row_currentMed['size'] > 0) {
            $unit = text($row_currentMed['size']) . " " . $runit . " ";
        }

        echo $unit . " " . text($row_currentMed['dosage']) . " " . $rin . " " . $rroute . " " . $rint; ?></td>
    </tr>
            <?php
        }
        ?>
</table>
</td></tr>
    <?php } ?>
<tr><td colspan='<?php echo $numcols ?>' class='issuetitle'>

    <?php if ($_POST['embeddedScreen']) {
        // Issues expand collapse widget
        $widgetLabel = "prescriptions";
        $linkMethod = "html";
        if ($GLOBALS['erx_enable']) {
            $widgetTitle = xl('Prescription History');
            $widgetButtonLabel = xl("Add/Edit eRx");
            $widgetButtonLink = $GLOBALS['webroot'] . "/interface/eRx.php?page=compose";
            $widgetButtonClass = "";
        } else {
            $linkMethod = "javascript";
            $widgetTitle = xl('Prescription');
            $widgetButtonLabel = xl("Edit");
            $oeLink = $GLOBALS['webroot'] . "/controller.php?prescription&list&id=" . attr_url($pid);
            $widgetButtonLink = 'editScripts("' . $oeLink . '")';

            $widgetButtonClass = "iframe rx_modal";
        }

        $bodyClass = "summary_item small";
        $widgetAuth = AclMain::aclCheckCore('patients', 'rx', '', array('write','addonly'));
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
        if ($GLOBALS['weno_rx_enable']) {
            echo "<button onclick='getWeno()'>" . xlt('eRx') . "</button>";
        }
    } else { ?>
    <span class='text font-weight-bold'><?php echo xlt('Prescriptions'); ?></span>
    </td></tr>
    </tr><td>
    <?php } ?>

    <?php
    $cwd = getcwd();
    chdir("../../../");
    $c = new Controller();
    echo $c->act(array("prescription" => "", "fragment" => "", "patient_id" => $pid));
    ?>

    <?php if ($_POST['embeddedScreen']) {
        echo "</div>";
    } ?>

</td></tr>

<?php }

if ($erx_upload_complete == 1) {
    echo "<tr><td>";
    // Old Medication Widget
    $widgetTitle = "Old Medication";
    $widgetLabel = "old_medication";
    $widgetButtonLabel = xl("Edit");
    $widgetButtonLink = "load_location(\"${GLOBALS['webroot']}/interface/patient_file/summary/stats_full.php?active=all&category=medication\")";
    $widgetButtonClass = "";
    $linkMethod = "javascript";
    $bodyClass = "summary_item small";
    $widgetAuth = true;
    $fixedWidth = false;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
    $query_uploaded_old = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' AND ";
    $query_uploaded_old .= dateEmptySql('enddate');
    $query_uploaded_old .= "ORDER BY begdate";
    $res_uploaded_old = sqlStatement($query_uploaded_old, array($pid));
    echo "<table>";
    while ($row = sqlFetchArray($res_uploaded_old)) {
    // output each issue for the $ISSUE_TYPE
        if (!$row['enddate'] && !$row['returndate']) {
            $rowclass = "noend_noreturn";
        } elseif (!$row['enddate'] && $row['returndate']) {
            $rowclass = "noend";
        } elseif ($row['enddate'] && !$row['returndate']) {
            $rowclass = "noreturn";
        }

        echo " <tr class='text $rowclass;'>\n";
        echo "  <td colspan='$numcols'>&nbsp;&nbsp;" . text($row['title']) . "</td>\n";
        echo " </tr>\n";
    }

    echo "</table>";
    echo "</div></td></tr>";
}

?>
</table> <!-- end patient_stats_prescriptions -->
</div>
</div> <!-- end patient_stats_summary -->
