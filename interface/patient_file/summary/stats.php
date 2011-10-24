<?php

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

include_once("../../globals.php");
include_once("$srcdir/lists.inc");
include_once("$srcdir/acl.inc");
include_once("$srcdir/options.inc.php");
include_once("$srcdir/formdata.inc.php");
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
    echo "<p>(".htmlspecialchars(xl('Issues not authorized'),ENT_NOQUOTES).")</p>\n";
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
$erx_upload_complete = 0;
$old_key="";$display_current_medications_below=1;
foreach ($ISSUE_TYPES as $key => $arr) {
    // $result = getListByType($pid, $key, "id,title,begdate,enddate,returndate,extrainfo", "all", "all", 0);

    $query = "SELECT * FROM lists WHERE pid = ? AND type = ? AND ";
    $query .= "(enddate is null or enddate = '' or enddate = '0000-00-00') ";
    if($GLOBALS['erx_enable'] && $GLOBALS['erx_medication_display'] && $key=='medication')
		$query .= "and erx_uploaded != '1' ";
	if($GLOBALS['erx_enable'] && $GLOBALS['erx_allergy_display'] && $key=='allergy')
		$query .= "and erx_uploaded != '1' ";
    $query .= "ORDER BY begdate";
    $pres = sqlStatement($query, array($pid, $key) );
    if($old_key=="medication" && $GLOBALS['erx_enable'] && $erx_upload_complete == 1)
    {
	$display_current_medications_below=0;
	?>
	<div>
	    <table id="patient_stats_prescriptions">
	    <?php if($GLOBALS['erx_enable']){ ?>
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
		expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
	    }
	    ?>
	    
	    <?php
	    $res=sqlStatement("select * from prescriptions where patient_id=? and active='1'",array($pid));
	    ?>
	    <table>
	    <?php
	    if(sqlNumRows($res)==0)
	    {
		?>
		<tr class=text>
			<td><?php echo htmlspecialchars(xl('None'), ENT_NOQUOTES);?></td>
		</tr>
		<?php
	    }
	    while($row_currentMed=sqlFetchArray($res))
	    {
		$runit=generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'),htmlspecialchars($row_currentMed['unit'],ENT_NOQUOTES));
		$rin=generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'),htmlspecialchars($row_currentMed['form'],ENT_NOQUOTES));
		$rroute=generate_display_field(array('data_type'=>'1','list_id'=>'drug_route'),htmlspecialchars($row_currentMed['route'],ENT_NOQUOTES));
		$rint=generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'),htmlspecialchars($row_currentMed['interval'],ENT_NOQUOTES));
		?>
		<tr class=text >
			<td><?php echo htmlspecialchars($row_currentMed['drug'],ENT_NOQUOTES);?></td>
			<td><?php
			    $unit='';
			    if($row_currentMed['size']>0)
				$unit=$row_currentMed['size']." ".$runit." ";
			    echo htmlspecialchars($unit." ".$row_currentMed['dosage']." ".$rin." ".$rroute." ".$rint,ENT_NOQUOTES);
			?></td>
		</tr>
	    <?php
	    }
	    ?>
	    </table>
	    </td></tr>
	    <?php }
	    $old_key='';
    }
    if (sqlNumRows($pres) > 0 || $ix == 0 || $key == "allergy" || $key == "medication") {
	$old_key=$key;
	if ($_POST['embeddedScreen']) {
	    
	    if($GLOBALS['erx_enable'] && $key == "medication"){
		$query_uploaded = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' AND ";
		$query_uploaded .= "(enddate is null or enddate = '' or enddate = '0000-00-00') ";
		$query_uploaded .= "and erx_uploaded != '1' ";
		$query_uploaded .= "ORDER BY begdate";
		$res_uploaded = sqlStatement($query_uploaded, array($pid) );
		if(sqlNumRows($res_uploaded) == 0){
		    $erx_upload_complete = 1;
		    continue;
		}
	    }
	    
	    echo "<tr><td>";
            // Issues expand collapse widget
            $widgetTitle = $arr[0];
            $widgetLabel = $key;	    
	    if(($key == "allergy" || $key == "medication") && $GLOBALS['erx_enable'])
	    {
		$widgetButtonLabel = xl("Add");
		$widgetButtonLink = "load_location(\"${GLOBALS['webroot']}/interface/eRx.php?page=medentry\")";
	    }
	    else
	    {
		$widgetButtonLabel = xl("Edit");
		$widgetButtonLink = "load_location(\"${GLOBALS['webroot']}/interface/patient_file/summary/stats_full.php?active=all&category=" . $key . "\")";
	    }
	    $widgetButtonClass = "";
            $linkMethod = "javascript";
            $bodyClass = "summary_item small";
            $widgetAuth = true;
            $fixedWidth = false;
            expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
	}
	else { ?>
            <tr class='issuetitle'>
            <td colspan='$numcols'>
            <span class="text"><b><?php echo htmlspecialchars($arr[0],ENT_NOQUOTES); ?></b></span>
            <a href="javascript:;" class="small" onclick="load_location('stats_full.php?active=all&category=" . $key . "')">
            (<b><?php echo htmlspecialchars(xl('Manage'),ENT_NOQUOTES); ?></b>)
            </a>
            </td>
            </tr>
        <?php }
        echo "<table>";    
	if (sqlNumRows($pres) == 0) {
          if ( getListTouch($pid,$key) ) {
            // Data entry has happened to this type, so can display an explicit None.
            echo "  <tr><td colspan='$numcols' class='text'>&nbsp;&nbsp;" . htmlspecialchars( xl('None'), ENT_NOQUOTES) . "</td></tr>\n";
          }
          else {
            // Data entry has not happened to this type, so show 'Nothing Recorded"
            echo "  <tr><td colspan='$numcols' class='text'>&nbsp;&nbsp;" . htmlspecialchars( xl('Nothing Recorded'), ENT_NOQUOTES) . "</td></tr>\n";
          }
	}
        	    
        while ($row = sqlFetchArray($pres)) {
            // output each issue for the $ISSUE_TYPE
            if (!$row['enddate'] && !$row['returndate'])
                $rowclass="noend_noreturn";
            else if (!$row['enddate'] && $row['returndate'])
                $rowclass="noend";
            else if ($row['enddate'] && !$row['returndate'])
                $rowclass = "noreturn";

            echo " <tr class='text $rowclass;'>\n";

	    //turn allergies red and bold and show the reaction (if exist)
	    if ($key == "allergy") {
                $reaction = "";
                if (!empty($row['reaction'])) {
                    $reaction = " (" . $row['reaction'] . ")";
                }
                echo "  <td colspan='$numcols' style='color:red;font-weight:bold;'>&nbsp;&nbsp;" . htmlspecialchars( $row['title'] . $reaction, ENT_NOQUOTES) . "</td>\n";
	    }
	    else {
	        echo "  <td colspan='$numcols'>&nbsp;&nbsp;" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</td>\n";
	    }

            echo " </tr>\n";
        }
	echo "</table>";
	if ($_POST['embeddedScreen']) {
	    echo "</div></td></tr>";
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
    if (sqlNumRows(sqlStatement("SHOW TABLES LIKE ?", array("form_".$formname) )) > 0) {
        $dres = sqlStatement("SELECT tp.id, tp.value FROM forms, " .
                            "form_" . add_escape_custom($formname) .
			    " AS tp WHERE forms.pid = ? AND " .
                            "forms.formdir = ? AND tp.id = forms.form_id AND " .
                            "tp.rownbr = -1 AND tp.colnbr = -1 AND tp.value LIKE '0%' " .
                            "ORDER BY tp.value DESC", array($pid, $formname) );
        if (sqlNumRows($dres) > 0 && $need_head) {
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
            echo htmlspecialchars($row['id'],ENT_QUOTES) . "\")'>" .
	        htmlspecialchars($start_date,ENT_NOQUOTES) . " " .
		htmlspecialchars($template_name,ENT_NOQUOTES) . "</a></td>\n";
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
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
}
else { ?>
<td colspan='<?php echo $numcols ?>' valign='top'>
<span class="text"><b><?php echo htmlspecialchars(xl('Immunizations', 'e'),ENT_NOQUOTES); ?></b></span>
<a href="javascript:;" class="small" onclick="javascript:load_location('immunizations.php')">
    (<b><?php echo htmlspecialchars(xl('Manage'),ENT_NOQUOTES) ?></b>)
</a>
</td></tr>
<tr><td>
<?php } ?>

<?php
  $sql = "select i1.id as id, i1.immunization_id as immunization_id, i1.cvx_code as cvx_code, c.code_text_short as cvx_text, ".
         " if (i1.administered_date, concat(i1.administered_date,' - '), substring(i1.note,1,20)) as immunization_data ".
         " from immunizations i1 ".
         " left join codes c on CAST(IFNULL(i1.cvx_code,0) AS CHAR) = c.code ".
         " left join code_types ct on c.code_type = ct.ct_id ".
         " where i1.patient_id = ? ".
         " AND (( i1.cvx_code = '0' OR i1.cvx_code IS NULL ) OR ".
         " ( ( i1.cvx_code != '0' AND i1.cvx_code IS NOT NULL ) AND ct.ct_key = 'CVX') ) ".
         " order by i1.administered_date desc";

  $result = sqlStatement($sql, array($pid) );

  if (sqlNumRows($result) == 0) {
    echo " <table><tr>\n";
    echo "  <td colspan='$numcols' class='text'>&nbsp;&nbsp;" . htmlspecialchars( xl('None'), ENT_NOQUOTES) . "</td>\n";
    echo " </tr></table>\n";
  }   
    
  while ($row=sqlFetchArray($result)){
    echo "&nbsp;&nbsp;";
    echo "<a class='link'";
    echo "' href='javascript:;' onclick='javascript:load_location(\"immunizations.php?mode=edit&id=".htmlspecialchars($row['id'],ENT_QUOTES) . "\")'>" .
    htmlspecialchars($row{'immunization_data'},ENT_NOQUOTES);

    // Figure out which name to use (ie. from cvx list or from the custom list)
    if ($GLOBALS['use_custom_immun_list']) {
      echo generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
    }
    else {
      if (!(empty($row['cvx_text']))) {
        echo htmlspecialchars( xl($row['cvx_text']), ENT_NOQUOTES );
      }
      else {
        echo generate_display_field(array('data_type'=>'1','list_id'=>'immunizations'), $row['immunization_id']);
      }
    }
    echo "</a><br>\n";
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

<?php if (!$GLOBALS['disable_prescriptions']) { ?>
<div>
<table id="patient_stats_prescriptions">
<?php if($GLOBALS['erx_enable'] && $display_current_medications_below==1){ ?>
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
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
}
?>

<?php
$res=sqlStatement("select * from prescriptions where patient_id=? and active='1'",array($pid));
?>
<table>
<?php
if(sqlNumRows($res)==0)
{
    ?>
    <tr class=text>
	    <td><?php echo htmlspecialchars(xl('None'), ENT_NOQUOTES);?></td>
    </tr>
    <?php
}
while($row_currentMed=sqlFetchArray($res))
{
    $runit=generate_display_field(array('data_type'=>'1','list_id'=>'drug_units'),$row_currentMed['unit']);
    $rin=generate_display_field(array('data_type'=>'1','list_id'=>'drug_form'),$row_currentMed['form']);
    $rroute=generate_display_field(array('data_type'=>'1','list_id'=>'drug_route'),$row_currentMed['route']);
    $rint=generate_display_field(array('data_type'=>'1','list_id'=>'drug_interval'),$row_currentMed['interval']);
    ?>
    <tr class=text >
	    <td><?php echo $row_currentMed['drug'];?></td>
	    <td><?php $unit=''; if($row_currentMed['size']>0) $unit=$row_currentMed['size']." ".$runit." "; echo htmlspecialchars($unit." ".$row_currentMed['dosage']." ".$rin." ".$rroute." ".$rint,ENT_NOQUOTES);?></td>
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
    if($GLOBALS['erx_enable'])    
    {
	$widgetTitle = xl('Prescription History');
	$widgetButtonLabel = xl("Add/Edit eRx");
	$widgetButtonLink = $GLOBALS['webroot'] . "/interface/eRx.php?page=compose";
	$widgetButtonClass = "";
    }
    else
    {
	$widgetTitle = xl('Prescription');
	$widgetButtonLabel = xl("Edit");
	$widgetButtonLink = $GLOBALS['webroot'] . "/interface/patient_file/summary/rx_frameset.php";
	$widgetButtonClass = "iframe rx_modal";
    }
    $bodyClass = "summary_item small";
    $widgetAuth = true;
    $fixedWidth = false;	
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
}
else { ?>
    <span class='text'><b><?php echo htmlspecialchars(xl('Prescriptions'),ENT_NOQUOTES); ?></b></span>
    </td></tr>
    </tr><td>
<?php } ?>	

<?php
$cwd= getcwd();
chdir("../../../");
require_once("library/classes/Controller.class.php");
$c = new Controller();
echo $c->act(array("prescription" => "", "fragment" => "", "patient_id" => $pid));
?>
	
<?php if ($_POST['embeddedScreen']) {
    echo "</div>";
} ?>
	
</td></tr>

<?php }

if($erx_upload_complete == 1){
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
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
    $query_uploaded_old = "SELECT * FROM lists WHERE pid = ? AND type = 'medication' AND ";
    $query_uploaded_old .= "(enddate is null or enddate = '' or enddate = '0000-00-00') ";
    $query_uploaded_old .= "ORDER BY begdate";
    $res_uploaded_old = sqlStatement($query_uploaded_old, array($pid) );
    echo "<table>";
    while ($row = sqlFetchArray($res_uploaded_old)) {
	// output each issue for the $ISSUE_TYPE
	if (!$row['enddate'] && !$row['returndate'])
	    $rowclass="noend_noreturn";
	else if (!$row['enddate'] && $row['returndate'])
	    $rowclass="noend";
	else if ($row['enddate'] && !$row['returndate'])
	    $rowclass = "noreturn";
	echo " <tr class='text $rowclass;'>\n";
	echo "  <td colspan='$numcols'>&nbsp;&nbsp;" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</td>\n";
	echo " </tr>\n";
    }
    echo "</table>";
    echo "</div></td></tr>";
}

?>
</table> <!-- end patient_stats_prescriptions -->
</div>
</div> <!-- end patient_stats_summary -->
