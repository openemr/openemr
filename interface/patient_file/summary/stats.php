<?php
include_once("../../globals.php");
include_once("$srcdir/lists.inc");
include_once("$srcdir/acl.inc");
include_once("$srcdir/options.inc.php");

?>

<div id="patient_stats_summary">

<?php
$thisauth = acl_check('patients', 'med');
if ($thisauth) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
        $thisauth = 0;
}
if (!$thisauth) {
    echo "<p>(".xl('Issues not authorized').")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}
?>

<script type='text/javascript'>
    function load_location( location ) {
        top.restoreSession();
		if ( !top.frames["RTop"] ) {
			document.location=location;
		} else {
        	top.frames["RTop"].location=location;
		}
    }
</script>

<table id="patient_stats_issues">

<?php
$numcols = '1';
$ix = 0;
foreach ($ISSUE_TYPES as $key => $arr) {
    // $result = getListByType($pid, $key, "id,title,begdate,enddate,returndate,extrainfo", "all", "all", 0);

    $query = "SELECT * FROM lists WHERE pid = $pid AND type = '$key' AND ";
    $query .= "enddate IS NULL ";
    $query .= "ORDER BY begdate";
    $pres = sqlStatement($query);

    if (mysql_num_rows($pres) > 0 || $ix == 0) {

        // output a header for the $ISSUE_TYPE (matches to themes/__.css entry for issuetitle)
        echo " <tr class='$arr[0]'>\n";
        echo "  <td colspan='$numcols'>\n";

        ?>
        <span class="text"><b><?php echo $arr[0] ?></b></span>
        <a href="javascript:;" class="small" onclick="load_location('stats_full.php?active=all')">
            (<b><?php echo xl('Manage') ?></b>)
        </a>
        <?php

        echo "  </td>\n";
        echo " </tr>\n";
       
	    // set display tupe for details (matches to themes/__.css entry for "issuetitle"-detail)
        $disptype = $arr[0] . '-detail';

        while ($row = sqlFetchArray($pres)) {
            // output each issue for the $ISSUE_TYPE
            if (!$row['enddate'] && !$row['returndate'])
                $rowclass="noend_noreturn";
            else if (!$row['enddate'] && $row['returndate'])
                $rowclass="noend";
            else if ($row['enddate'] && !$row['returndate'])
                $rowclass = "noreturn";

            echo " <tr class='text $rowclass;'>\n";

            echo "  <td class='$disptype', colspan='$numcols'>&nbsp;&nbsp;" . $row['title'] . "</td>\n";

            echo " </tr>\n";
        }
    }

    ++$ix;
}
?>
</table> <!-- end patient_stats_issues -->

<table id="patient_stats_spreadsheets">
<?php

// Show spreadsheet forms if any are present.
//
$need_head = true;
foreach (array('treatment_protocols','injury_log') as $formname) {
    if (mysql_num_rows(sqlStatement("SHOW TABLES LIKE 'form_$formname'")) > 0) {
        $dres = sqlStatement("SELECT tp.id, tp.value FROM forms, " .
                            "form_$formname AS tp WHERE forms.pid = $pid AND " .
                            "forms.formdir = '$formname' AND tp.id = forms.form_id AND " .
                            "tp.rownbr = -1 AND tp.colnbr = -1 AND tp.value LIKE '0%' " .
                            "ORDER BY tp.value DESC");
        if (mysql_num_rows($dres) > 0 && $need_head) {
            $need_head = false;
            echo " <tr>\n";
            echo "  <td colspan='$numcols' valign='top'>\n";
            echo "   <span class='title'>Injury Log</span>\n";
            echo "  </td>\n";
            echo " </tr>\n";
        }
        while ($row = sqlFetchArray($dres)) {
            list($completed, $start_date, $template_name) = explode('|', $row['value'], 3);
            echo " <tr>\n";
            echo "  <td colspan='$numcols'>&nbsp;&nbsp;";
            echo "<a class='link' href='javascript:;' ";
            echo "onclick='load_location(\"../../forms/$formname/new.php?popup=1&id=";
            echo $row['id'] . "\")'>$start_date $template_name</a></td>\n";
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
<td colspan='<?php echo $numcols ?>' valign='top'>
<span class="text"><b><?php echo xl('Immunizations', 'e') ?></b></span>
<a href="javascript:;" class="small" onclick="javascript:load_location('immunizations.php')">
    (<b><?php echo xl('Manage') ?></b>)
</a>
</td></tr>
<tr><td>

<?php
  $sql = "select i1.id as id, i1.immunization_id as immunization_id,".
         " if (i1.administered_date, concat(i1.administered_date,' - '), substring(i1.note,1,20)) as immunization_data ".
         " from immunizations i1 ".
         " where i1.patient_id = $pid ".
         " order by i1.immunization_id, i1.administered_date desc";

  $result = sqlStatement($sql);

  while ($row=sqlFetchArray($result)){
    echo "&nbsp;&nbsp;";
    echo "<a class='link'";
    echo "' href='javascript:;' onclick='javascript:load_location(\"immunizations.php?mode=edit&id=".$row['id'] . "\")'>" .
    $row{'immunization_data'} .
    generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']) .
    "</a><br>\n";
  }
?>
</td>
</tr>
</table> <!-- end patient_stats_imm-->
</div>
<?php } ?>

<?php if (!$GLOBALS['disable_prescriptions']) { ?>
<div>
<table id="patient_stats_prescriptions">
<tr><td colspan='<?php echo $numcols ?>' class='issuetitle'>
<span class='text'><b><?php echo xl('Prescriptions'); ?></b></span>
</td></tr>
</tr><td>
<?php
$cwd= getcwd();
chdir("../../../");
require_once("library/classes/Controller.class.php");
$c = new Controller();
echo $c->act(array("prescription" => "", "fragment" => "", "patient_id" => $pid));
?>
</td></tr>
</table> <!-- end patient_stats_prescriptions -->
</div>
<?php } ?>

</div> <!-- end patient_stats_summary -->
