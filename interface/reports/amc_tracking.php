<?php
 // Copyright (C) 2011 Brady Miller <brady@sparmy.com>
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
require_once "$srcdir/amc.php";

// Collect form parameters (set defaults if empty)
$begin_date = (isset($_POST['form_begin_date'])) ? trim($_POST['form_begin_date']) : "";
$begin_date = (isset($_POST['form_end_date'])) ? trim($_POST['form_end_date']) : "";
$rule = (isset($_POST['form_rule'])) ? trim($_POST['form_rule']) : "";
$provider  = trim($_POST['form_provider']);

?>

<html>

<head>
<?php html_header_show();?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<title><?php echo htmlspecialchars( xl('Automated Measure Calculations (AMC) Tracking'), ENT_NOQUOTES); ?></title>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function send_sum(patient_id,transaction_id) {
   if ( $('#send_sum_flag').attr('checked') ) {
     var mode = "add";
   }
   else {
     var mode = "remove";
   }
   top.restoreSession();
   $.post( "../../library/ajax/amc_misc_data.php",
     { amc_id: "send_sum_amc",
       complete: true,
       mode: mode,
       patient_id: patient_id,
       object_category: "transactions",
       object_id: transaction_id
     }
   );
 }

 function provide_rec_pat(patient_id,date_created) {
   if ( $('#provide_rec_pat_flag').attr('checked') ) {
     var mode = "complete_safe";
   }
   else {
     var mode = "uncomplete_safe";
   }
   top.restoreSession();
   $.post( "../../library/ajax/amc_misc_data.php",
     { amc_id: "provide_rec_pat_amc",
       complete: true,
       mode: mode,
       date_created: date_created,
       patient_id: patient_id
     }
   );
 }

 function provide_sum_pat(patient_id,encounter_id) {
   if ( $('#provide_sum_pat_flag').attr('checked') ) {
     var mode = "add";
   }
   else {
     var mode = "remove";
   }
   top.restoreSession();
   $.post( "../../library/ajax/amc_misc_data.php",
     { amc_id: "provide_sum_pat_amc",
       complete: true,
       mode: mode,
       patient_id: patient_id,
       object_category: "form_encounter",
       object_id: encounter_id
     }
   );
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

<?php echo htmlspecialchars( xl('Automated Measure Calculations (AMC) Tracking'), ENT_NOQUOTES); ?></span>

<form method='post' name='theform' id='theform' action='amc_tracking.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<table>
 <tr>
  <td width='470px'>
	<div style='float:left'>

	<table class='text'>

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

                <tr>
                        <td class='label'>
                           <?php echo htmlspecialchars( xl('End Date'), ENT_NOQUOTES); ?>:
                        </td>
                        <td>
                           <input type='text' name='form_end_date' id="form_end_date" size='20' value='<?php echo htmlspecialchars( $end_date, ENT_QUOTES); ?>'
                                onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='<?php echo htmlspecialchars( xl('yyyy-mm-dd hh:mm:ss'), ENT_QUOTES); ?>'>
                           <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
                                id='img_end_date' border='0' alt='[?]' style='cursor:pointer'
                                title='<?php echo htmlspecialchars( xl('Click here to choose a date'), ENT_QUOTES); ?>'>
                        </td>
                </tr>

                <tr>
                        <td class='label'>
                            <?php echo htmlspecialchars( xl('Rule'), ENT_NOQUOTES); ?>:
                        </td>
                        <td>
                            <select name='form_rule'>
                            <option value='send_sum_amc' <?php if ($rule == "send_sum_amc") echo "selected"; ?>>
                            <?php echo htmlspecialchars( xl('Send Summaries with Referrals'), ENT_NOQUOTES); ?></option>
                            <option value='provide_rec_pat_amc' <?php if ($rule == "provide_rec_pat_amc") echo "selected"; ?>>
                            <?php echo htmlspecialchars( xl('Patient Requested Medical Records'), ENT_NOQUOTES); ?></option>
                            <option value='provide_sum_pat_amc' <?php if ($rule == "provide_sum_pat_amc") echo "selected"; ?>>
                            <?php echo htmlspecialchars( xl('Provide Records to Patient for Visit'), ENT_NOQUOTES); ?></option>
                            </select>
                        </td>
                </tr>

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
				 echo "    <option value=''>-- " . htmlspecialchars( xl('All'), ENT_NOQUOTES) . " --\n";

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
   <?php echo htmlspecialchars( xl('Patient Name'), ENT_NOQUOTES); ?>
  </th>

  <th>
   <?php echo htmlspecialchars( xl('Patient ID'), ENT_NOQUOTES); ?>
  </th>

  <th>
    <?php
      if ($rule == "send_sum_amc") {
        echo htmlspecialchars( xl('Referral Date'), ENT_NOQUOTES);
      }
      else if ($rule == "provide_rec_pat_amc") {
        echo htmlspecialchars( xl('Record Request Date'), ENT_NOQUOTES);
      }
      else { // $rule == "provide_sum_pat_amc"
        echo htmlspecialchars( xl('Encounter Date'), ENT_NOQUOTES);
      }
    ?>
  </th>

  <th>
   <?php
     if ($rule == "send_sum_amc") {
       echo htmlspecialchars( xl('Referral ID'), ENT_NOQUOTES);
     }
      else if ($rule == "provide_rec_pat_amc") {
        echo "&nbsp";
      }
     else { // $rule == "provide_sum_pat_amc"
       echo htmlspecialchars( xl('Encounter ID'), ENT_NOQUOTES);
     }
   ?>
  </th>

  <th>
   <?php
     if ($rule == "send_sum_amc" || $rule == "provide_rec_pat_amc") {
       echo htmlspecialchars( xl('Medical Records Sent'), ENT_NOQUOTES);
     }
     else { // $rule == "provide_sum_pat_amc"
       echo htmlspecialchars( xl('Medical Summary Given'), ENT_NOQUOTES);
     }
   ?>
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
<?php

  // Send the request for information
  $resultsArray = amcTrackingRequest($rule,$begin_date,$end_date,$provider);

?>

  <?php
   foreach ($resultsArray as $result) {
     echo "<tr bgcolor='" . $bgcolor ."'>";
     echo "<td>" . htmlspecialchars($result['lname'].",".$result['fname'], ENT_NOQUOTES) . "</td>";
     echo "<td>" . htmlspecialchars($result['pid'],ENT_NOQUOTES) . "</td>";
     echo "<td>" . htmlspecialchars($result['date'],ENT_NOQUOTES) . "</td>";
     if ($rule == "send_sum_amc" || $rule == "provide_sum_pat_amc") {
       echo "<td>" . htmlspecialchars($result['id'],ENT_NOQUOTES) . "</td>";
     }
     else { //$rule == "provide_rec_pat_amc"
       echo "&nbsp";
     }

     if ($rule == "send_sum_amc") {
       echo "<td><input type='checkbox' id='send_sum_flag' onclick='send_sum(\"".htmlspecialchars($result['pid'],ENT_QUOTES)."\",\"".htmlspecialchars($result['id'],ENT_QUOTES)."\")'>" . htmlspecialchars( xl('Yes'), ENT_NOQUOTES) . "</td>";
     }
     else if ($rule == "provide_rec_pat_amc") {
       echo "<td><input type='checkbox' id='provide_rec_pat_flag' onclick='provide_rec_pat(\"".htmlspecialchars($result['pid'],ENT_QUOTES)."\",\"".htmlspecialchars($result['date'],ENT_QUOTES)."\")'>" . htmlspecialchars( xl('Yes'), ENT_NOQUOTES) . "</td>";
     }
     else { //$rule == "provide_sum_pat_amc"
       echo "<td><input type='checkbox' id='provide_sum_pat_flag' onclick='provide_sum_pat(\"".htmlspecialchars($result['pid'],ENT_QUOTES)."\",\"".htmlspecialchars($result['id'],ENT_QUOTES)."\")'>" . htmlspecialchars( xl('Yes'), ENT_NOQUOTES) . "</td>";
     }
     echo "</tr>";
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
 Calendar.setup({inputField:"form_begin_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_begin_date", showsTime:'true'});
 Calendar.setup({inputField:"form_end_date", ifFormat:"%Y-%m-%d %H:%M:%S", button:"img_end_date", showsTime:'true'});

</script>

</html>

