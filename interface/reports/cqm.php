<?php
 // Copyright (C) 2010 Brady Miller <brady@sparmy.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("$srcdir/formatting.inc.php");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/formdata.inc.php";
require_once "$srcdir/clinical_rules.php";

//Remove time limit, since script can take many minutes
set_time_limit(0);

// Collect report type parameter (standard, amc, or cqm)
$type_report = (isset($_GET['type'])) ? trim($_GET['type']) : "standard";

// Collect form parameters (set defaults if empty)
if ($type_report == "amc") {
  $begin_date = (isset($_POST['form_begin_date'])) ? trim($_POST['form_begin_date']) : "";
  $labs_manual = (isset($_POST['labs_manual_entry'])) ? trim($_POST['labs_manual_entry']) : "0";
}
$target_date = (isset($_POST['form_target_date'])) ? trim($_POST['form_target_date']) : date('Y-m-d H:i:s');
$rule_filter = (isset($_POST['form_rule_filter'])) ? trim($_POST['form_rule_filter']) : "";
$plan_filter = (isset($_POST['form_plan_filter'])) ? trim($_POST['form_plan_filter']) : "";
$organize_method = (empty($plan_filter)) ? "default" : "plans";
$provider  = trim($_POST['form_provider']);
$pat_prov_rel = (empty($_POST['form_pat_prov_rel'])) ? "primary" : trim($_POST['form_pat_prov_rel']);
?>

<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<?php if ($type_report == "standard") { ?>
  <title><?php echo htmlspecialchars( xl('Standard Measures'), ENT_NOQUOTES); ?></title>
<?php } ?>
<?php if ($type_report == "cqm") { ?>
  <title><?php echo htmlspecialchars( xl('Clinical Quality Measures (CQM)'), ENT_NOQUOTES); ?></title>
<?php } ?>
<?php if ($type_report == "amc") { ?>
  <title><?php echo htmlspecialchars( xl('Automated Measure Calculations (AMC)'), ENT_NOQUOTES); ?></title>
<?php } ?>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function refreshme() {
    // location.reload();
    top.restoreSession();
    document.forms[0].submit();
 }

 function GenXml(sNested) {
	  top.restoreSession();
	  var sLoc = '../../custom/export_registry_xml.php?&target_date=' + theform.form_target_date.value + '&nested=' + sNested;
	  dlgopen(sLoc, '_blank', 600, 500);
	  return false;
}

</script>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo htmlspecialchars( xl('Report'), ENT_NOQUOTES); ?> - 

<?php if ($type_report == "standard") { ?>
  <?php echo htmlspecialchars( xl('Standard Measures'), ENT_NOQUOTES); ?></span>
<?php } ?>
<?php if ($type_report == "cqm") { ?>
  <?php echo htmlspecialchars( xl('Clinical Quality Measures (CQM)'), ENT_NOQUOTES); ?></span>
<?php } ?>
<?php if ($type_report == "amc") { ?>
  <?php echo htmlspecialchars( xl('Automated Measure Calculations (AMC)'), ENT_NOQUOTES); ?></span>
<?php } ?>


<form method='post' name='theform' id='theform' action='cqm.php?type=<?php echo htmlspecialchars($type_report,ENT_QUOTES) ;?>' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
 <tr>
  <td width='470px'>
	<div style='float:left'>

	<table class='text'>

		<?php if ($type_report == "amc") { ?>
                   <tr>
                      <td class='label'>
                         <?php echo htmlspecialchars( xl('Begin Date'), ENT_NOQUOTES); ?>:
                      </td>
                      <td>
                         <input type='text' name='form_begin_date' id="form_begin_date" size='20' value='<?php echo htmlspecialchars( $begin_date, ENT_QUOTES); ?>'
                            onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo htmlspecialchars( xl('yyyy-mm-dd hh:mm:ss'), ENT_QUOTES); ?>'>
                         <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
                            id='img_begin_date' border='0' alt='[?]' style='cursor:pointer'
                            title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>'>
                      </td>
                   </tr>
		<?php } ?>

                <tr>
                        <td class='label'>
                           <?php if ($type_report == "amc") { ?>
                              <?php echo htmlspecialchars( xl('End Date'), ENT_NOQUOTES); ?>:
                           <?php } else { ?>
                              <?php echo htmlspecialchars( xl('Target Date'), ENT_NOQUOTES); ?>:
                           <?php } ?>
                        </td>
                        <td>
                           <input type='text' name='form_target_date' id="form_target_date" size='20' value='<?php echo htmlspecialchars( $target_date, ENT_QUOTES); ?>'
                                onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo htmlspecialchars( xl('yyyy-mm-dd hh:mm:ss'), ENT_QUOTES); ?>'>
                           <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
                                id='img_target_date' border='0' alt='[?]' style='cursor:pointer'
                                title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>'>
                        </td>
                </tr>

                <?php if ($type_report == "cqm") { ?>
                    <input type='hidden' name='form_rule_filter' value='cqm'>
                <?php } ?>
                <?php if ($type_report == "amc") { ?>
                    <input type='hidden' name='form_rule_filter' value='amc'>
                <?php } ?>
                <?php if ($type_report == "standard") { ?>
                    <tr>
                        <td class='label'>
                            <?php echo htmlspecialchars( xl('Rule Set'), ENT_NOQUOTES); ?>:
                        </td>
                        <td>
                            <select name='form_rule_filter'>
                            <option value='passive_alert' <?php if ($rule_filter == "passive_alert") echo "selected"; ?>>
                            <?php echo htmlspecialchars( xl('Passive Alert Rules'), ENT_NOQUOTES); ?></option>
                            <option value='active_alert' <?php if ($rule_filter == "active_alert") echo "selected"; ?>>
                            <?php echo htmlspecialchars( xl('Active Alert Rules'), ENT_NOQUOTES); ?></option>
                            <option value='patient_reminder' <?php if ($rule_filter == "patient_reminder") echo "selected"; ?>>
                            <?php echo htmlspecialchars( xl('Patient Reminder Rules'), ENT_NOQUOTES); ?></option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($type_report == "amc") { ?>
                    <input type='hidden' name='form_plan_filter' value=''>
                <?php } else { ?>
                    <tr>
                        <td class='label'>
                           <?php echo htmlspecialchars( xl('Plan Set'), ENT_NOQUOTES); ?>:
                        </td>
                        <td>
                                 <select name='form_plan_filter'>
                                 <option value=''>-- <?php echo htmlspecialchars( xl('Ignore'), ENT_NOQUOTES); ?> --</option>
                                 <?php if ($type_report == "cqm") { ?>
                                   <option value='cqm' <?php if ($plan_filter == "cqm") echo "selected"; ?>>
                                   <?php echo htmlspecialchars( xl('Official Clinical Quality Measures (CQM) Measure Groups'), ENT_NOQUOTES); ?></option>
                                 <?php } ?>
                                 <?php if ($type_report == "standard") { ?>
                                   <option value='normal' <?php if ($plan_filter == "normal") echo "selected"; ?>>
                                   <?php echo htmlspecialchars( xl('Active Plans'), ENT_NOQUOTES); ?></option>
                                 <?php } ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>      
			<td class='label'>
			   <?php echo htmlspecialchars( xl('Provider'), ENT_NOQUOTES); ?>:
			</td>
			<td>
				<?php

				 // Build a drop-down list of providers.
				 //

				 $query = "SELECT id, lname, fname FROM users WHERE ".
				  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter

				 $ures = sqlStatement($query);

				 echo "   <select name='form_provider'>\n";
				 echo "    <option value=''>-- " . htmlspecialchars( xl('All (Cumulative)'), ENT_NOQUOTES) . " --\n";

                                 echo "    <option value='collate_outer'";
                                 if ($_POST['form_provider'] == 'collate_outer') echo " selected";
                                 echo ">-- " . htmlspecialchars( xl('All (Collated Format A)'), ENT_NOQUOTES) . " --\n";

                                 echo "    <option value='collate_inner'";
                                 if ($_POST['form_provider'] == 'collate_inner') echo " selected";
                                 echo ">-- " . htmlspecialchars( xl('All (Collated Format B)'), ENT_NOQUOTES) . " --\n";

				 while ($urow = sqlFetchArray($ures)) {
				  $provid = $urow['id'];
				  echo "    <option value='".htmlspecialchars( $provid, ENT_QUOTES)."'";
				  if ($provid == $_POST['form_provider']) echo " selected";
				  echo ">" . htmlspecialchars( $urow['lname'] . ", " . $urow['fname'], ENT_NOQUOTES) . "\n";
				 }

				 echo "   </select>\n";

				?>
                        </td>
		</tr>

                <tr>
                        <td class='label'>
                           <?php echo htmlspecialchars( xl('Provider Relationship'), ENT_NOQUOTES); ?>:
                        </td>
                        <td>
                                <?php

                                 // Build a drop-down list of of patient provider relationships.
                                 //
                                 echo "   <select name='form_pat_prov_rel' title='" . xlt('Only applicable if a provider or collated list was chosen above. PRIMARY only selects patients that the provider is the primary provider. ENCOUNTER selects all patients that the provider has seen.') . "'>\n";
                                 echo "    <option value='primary'";
                                 if ($pat_prov_rel == 'primary') echo " selected";
                                 echo ">" . xlt('Primary') . "\n";
                                 echo "    <option value='encounter'";
                                 if ($pat_prov_rel == 'encounter') echo " selected";
                                 echo ">" . xlt('Encounter') . "\n";
                                 echo "   </select>\n";
                                ?>
                        </td>
                </tr>

                <?php if ($type_report == "amc") { ?>
                  <tr>
                        <td>
                               <?php echo htmlspecialchars( xl('Number labs'), ENT_NOQUOTES); ?>:<br>
                               (<?php echo htmlspecialchars( xl('Non-electronic'), ENT_NOQUOTES); ?>)
                        </td>
                        <td>
                               <input type="text" name="labs_manual_entry" value="<?php echo htmlspecialchars($labs_manual,ENT_QUOTES); ?>">
                        </td>
                  </tr>
                <?php } ?>

	</table>

	</div>

  </td>
  <td align='left' valign='middle' height="100%">
	<table style='border-left:1px solid; width:100%; height:100%' >
		<tr>
			<td>
				<div style='margin-left:15px'>
					<a href='#' class='css_button' onclick='$("#form_refresh").attr("value","true"); top.restoreSession(); $("#theform").submit();'>
					<span>
						<?php echo htmlspecialchars( xl('Submit'), ENT_NOQUOTES); ?>
					</span>
					</a>
					<?php if ($type_report == "cqm") { ?>
						<a href='#' class='css_button' onclick='return GenXml("false")'>
							<span>
								<?php echo htmlspecialchars( xl('Generate PQRI report (Method A)'), ENT_NOQUOTES); ?>
							</span>
						</a>
                                        	<a href='#' class='css_button' onclick='return GenXml("true")'>
                                                	<span>
                                                        	<?php echo htmlspecialchars( xl('Generate PQRI report (Method E)'), ENT_NOQUOTES); ?>
                                                	</span>
                                        	</a>
					<?php } ?>
                                        <?php if ($_POST['form_refresh']) { ?>
					<a href='#' class='css_button' onclick='window.print()'>
						<span>
							<?php echo htmlspecialchars( xl('Print'), ENT_NOQUOTES); ?>
						</span>
					</a>
					<?php } ?>
				</div>
			</td>
		</tr>
	</table>
  </td>
 </tr>
</table>

</div>  <!-- end of search parameters -->

<br>

<?php
 if ($_POST['form_refresh']) {
?>


<div id="report_results">
<table>

 <thead>
  <th>
   <?php echo htmlspecialchars( xl('Title'), ENT_NOQUOTES); ?>
  </th>

  <th>
   <?php echo htmlspecialchars( xl('Total Patients'), ENT_NOQUOTES); ?>
  </th>

  <th>
   <?php if ($type_report == "amc") { ?>
    <?php echo htmlspecialchars( xl('Denominator'), ENT_NOQUOTES); ?></a>
   <?php } else { ?>
    <?php echo htmlspecialchars( xl('Applicable Patients') .' (' . xl('Denominator') . ')', ENT_NOQUOTES); ?></a>
   <?php } ?>
  </th>

  <?php if ($type_report != "amc") { ?>
   <th>
    <?php echo htmlspecialchars( xl('Excluded Patients'), ENT_NOQUOTES); ?></a>
   </th>
  <?php } ?>

  <th>
   <?php if ($type_report == "amc") { ?>
    <?php echo htmlspecialchars( xl('Numerator'), ENT_NOQUOTES); ?></a>
   <?php } else { ?>
    <?php echo htmlspecialchars( xl('Passed Patients') . ' (' . xl('Numerator') . ')', ENT_NOQUOTES); ?></a>
   <?php } ?>
  </th>

  <th>
   <?php echo htmlspecialchars( xl('Performance Percentage'), ENT_NOQUOTES); ?></a>
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
<?php

  // Process report and collect results
  $options = array();
  $array_date = array();
  if ($type_report == "amc") {
    // For AMC:
    //   need to make $target_date an array with two elements ('dateBegin' and 'dateTarget')
    //   need to send a manual data entry option (number of labs)
    $array_date['dateBegin'] = $begin_date;
    $array_date['dateTarget'] = $target_date;
    $options = array('labs_manual'=>$labs_manual);
  }
  else {
    // For others, use the unmodified target date array and send an empty options array
    $array_date = $target_date;
  }
  $dataSheet = test_rules_clinic($provider,$rule_filter,$array_date,"report",'',$plan_filter,$organize_method,$options,$pat_prov_rel);

  $firstProviderFlag = TRUE;
  $firstPlanFlag = TRUE;
  $existProvider = FALSE;
  foreach ($dataSheet as $row) {

?>

 <tr bgcolor='<?php echo $bgcolor ?>'>

  <?php
   if (isset($row['is_main']) || isset($row['is_sub'])) {
     echo "<td class='detail'>";
     if (isset($row['is_main'])) {
       echo "<b>".generate_display_field(array('data_type'=>'1','list_id'=>'clinical_rules'),$row['id'])."</b>";
       if (!empty($row['cqm_pqri_code']) || !empty($row['cqm_nqf_code']) || !empty($row['amc_code'])) {
         echo " (";
         if (!empty($row['cqm_pqri_code'])) {
         echo " " . htmlspecialchars( xl('PQRI') . ":" . $row['cqm_pqri_code'], ENT_NOQUOTES) . " ";
         }
         if (!empty($row['cqm_nqf_code'])) {
         echo " " . htmlspecialchars( xl('NQF') . ":" . $row['cqm_nqf_code'], ENT_NOQUOTES) . " ";
         }
         if (!empty($row['amc_code'])) {
         echo " " . htmlspecialchars( xl('AMC') . ":" . $row['amc_code'], ENT_NOQUOTES) . " ";
         }
         echo ")";
       }

       if ( !(empty($row['concatenated_label'])) ) {
           echo ", " . htmlspecialchars( xl( $row['concatenated_label'] ), ENT_NOQUOTES) . " ";
       }
       
     }
     else { // isset($row['is_sub'])
       echo generate_display_field(array('data_type'=>'1','list_id'=>'rule_action_category'),$row['action_category']);
       echo ": " . generate_display_field(array('data_type'=>'1','list_id'=>'rule_action'),$row['action_item']);
     }
     echo "</td>";
     echo "<td align='center'>" . $row['total_patients'] . "</td>";
     echo "<td align='center'>" . $row['pass_filter'] . "</td>";
     if ($type_report != "amc") {
       echo "<td align='center'>" . $row['excluded'] . "</td>";
     }
     echo "<td align='center'>" . $row['pass_target'] . "</td>";
     echo "<td align='center'>" . $row['percentage'] . "</td>";
   }
   else if (isset($row['is_provider'])) {
     // Display the provider information
     if (!$firstProviderFlag && $_POST['form_provider'] == 'collate_outer') {
       echo "<tr><td>&nbsp</td></tr>";
     }
     echo "<td class='detail' align='center'><b>";
     echo htmlspecialchars( xl("Provider").": " . $row['prov_lname'] . "," . $row['prov_fname'], ENT_NOQUOTES);
     if (!empty($row['npi']) || !empty($row['federaltaxid'])) {
       echo " (";
       if (!empty($row['npi'])) {
        echo " " . htmlspecialchars( xl('NPI') . ":" . $row['npi'], ENT_NOQUOTES) . " ";
       }
       if (!empty($row['federaltaxid'])) {
        echo " " . htmlspecialchars( xl('TID') . ":" . $row['federaltaxid'], ENT_NOQUOTES) . " ";
       }
       echo ")";
     }
     echo "</b></td>";
     $firstProviderFlag = FALSE;
     $existProvider = TRUE;
   }
   else { // isset($row['is_plan'])
     if (!$firstPlanFlag && $_POST['form_provider'] != 'collate_outer') {
       echo "<tr><td>&nbsp</td></tr>";
     }
     echo "<td class='detail' align='center'><b>";
     echo htmlspecialchars( xl("Plan"), ENT_NOQUOTES) . ": ";
     echo generate_display_field(array('data_type'=>'1','list_id'=>'clinical_plans'),$row['id']);
     if (!empty($row['cqm_measure_group'])) {
       echo " (". htmlspecialchars( xl('Measure Group Code') . ": " . $row['cqm_measure_group'], ENT_NOQUOTES) . ")";
     }
     echo "</b></td>";
     $firstPlanFlag = FALSE;
   }
  ?>
 </tr>

<?php
  }
?>
</tbody>
</table>
</div>  <!-- end of search results -->
<?php } else { ?>
<div class='text'>
 	<?php echo htmlspecialchars( xl('Please input search criteria above, and click Submit to view results.'), ENT_NOQUOTES); ?>
</div>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>

</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 <?php if ($type_report == "amc") { ?>
  Calendar.setup({inputField:"form_begin_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_begin_date", showsTime:'true'});
 <?php } ?>
 Calendar.setup({inputField:"form_target_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_target_date", showsTime:'true'});
</script>

</html>

