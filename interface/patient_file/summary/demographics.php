<?php
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

 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/classes/Address.class.php");
 require_once("$srcdir/classes/InsuranceCompany.class.php");
 require_once("$srcdir/classes/Document.class.php");
 require_once("$srcdir/options.inc.php");
 require_once("../history/history.inc.php");
 require_once("$srcdir/formatting.inc.php");
 require_once("$srcdir/edi.inc");

  if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) {
  include_once("$srcdir/pid.inc");
  setpid($_GET['set_pid']);
 }

function print_as_money($money) {
	preg_match("/(\d*)\.?(\d*)/",$money,$moneymatches);
	$tmp = wordwrap(strrev($moneymatches[1]),3,",",1);
	$ccheck = strrev($tmp);
	if ($ccheck[0] == ",") {
		$tmp = substr($ccheck,1,strlen($ccheck)-1);
	}
	if ($moneymatches[2] != "") {
		return "$ " . strrev($tmp) . "." . $moneymatches[2];
	} else {
		return "$ " . strrev($tmp);
	}
}

// get an array from Photos category
function pic_array($pid,$picture_directory) {
    $pics = array();
    $sql_query = "select documents.id from documents join categories_to_documents " .
                 "on documents.id = categories_to_documents.document_id " .
                 "join categories on categories.id = categories_to_documents.category_id " .
                 "where categories.name like ? and documents.foreign_id = ?";
    if ($query = sqlStatement($sql_query, array($picture_directory,$pid))) {
      while( $results = sqlFetchArray($query) ) {
            array_push($pics,$results['id']);
        }
      }
    return ($pics);
}
// Get the document ID of the first document in a specific catg.
function get_document_by_catg($pid,$doc_catg) {

    $result = array();

	if ($pid and $doc_catg) {
	  $result = sqlQuery("SELECT d.id, d.date, d.url FROM " .
	    "documents AS d, categories_to_documents AS cd, categories AS c " .
	    "WHERE d.foreign_id = ? " .
	    "AND cd.document_id = d.id " .
	    "AND c.id = cd.category_id " .
	    "AND c.name LIKE ? " .
	    "ORDER BY d.date DESC LIMIT 1", array($pid, $doc_catg) );
	    }

	return($result['id']);
}

// Display image in 'widget style'
function image_widget($doc_id,$doc_catg)
{
        global $pid, $web_root;
        $docobj = new Document($doc_id);
        $image_file = $docobj->get_url_file();
        $extension = substr($image_file, strrpos($image_file,"."));
        $viewable_types = array('.png','.jpg','.jpeg','.png','.bmp'); // image ext supported by fancybox viewer
        if ( in_array($extension,$viewable_types) ) { // extention matches list
                $to_url = "<td> <a href = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id" .
				"/tmp$extension" .  // Force image type URL for fancybox
				" onclick=top.restoreSession(); class='image_modal'>" .
                " <img src = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id" .
				" width=100 alt='$doc_catg:$image_file'>  </a> </td> <td valign='center'>".
                htmlspecialchars($doc_catg) . '<br />&nbsp;' . htmlspecialchars($image_file) .
				"</td>";
        }
     	else {
				$to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
                    "&patient_id=$pid&document_id=$doc_id'" .
                    " onclick='top.restoreSession()' class='css_button_small'>" .
                    "<span>" .
                    htmlspecialchars( xl("View"), ENT_QUOTES )."</a> &nbsp;" . 
					htmlspecialchars( "$doc_catg - $image_file", ENT_QUOTES ) .
                    "</span> </td>";
		}
        echo "<table><tr>";
        echo $to_url;
        echo "</tr></table>";
}

// Determine if the Vitals form is in use for this site.
$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .
  "directory = 'vitals' AND state = 1");
$vitals_is_registered = $tmp['count'];
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/js/jquery.1.3.2.js"></script>
<script type="text/javascript" src="../../../library/js/common.js"></script>
<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
<script type="text/javascript" language="JavaScript">
//Visolve - sync the radio buttons - Start
if((top.window.parent) && (parent.window)){
        var wname = top.window.parent.left_nav;
        wname.syncRadios();
        wname.setRadio(parent.window.name, "dem");
}
//Visolve - sync the radio buttons - End

 var mypcc = '<?php echo htmlspecialchars($GLOBALS['phone_country_code'],ENT_QUOTES); ?>';

 function oldEvt(eventid) {
  dlgopen('../../main/calendar/add_edit_event.php?eid=' + eventid, '_blank', 550, 270);
 }

 function advdirconfigure() {
   dlgopen('advancedirectives.php', '_blank', 500, 450);
  }

 function refreshme() {
  top.restoreSession();
  location.reload();
 }

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?patient=<?php echo htmlspecialchars($pid,ENT_QUOTES); ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.left_nav.clearPatient();
<?php } else { ?>
  top.restoreSession();
  top.location.href = '../main/main_screen.php';
<?php } ?>
 }

 function validate() {
  var f = document.forms[0];
<?php
if ($GLOBALS['athletic_team']) {
  echo "  if (f.form_userdate1.value != f.form_original_userdate1.value) {\n";
  $irow = sqlQuery("SELECT id, title FROM lists WHERE " .
    "pid = ? AND enddate IS NULL ORDER BY begdate DESC LIMIT 1", array($pid));
  if (!empty($irow)) {
?>
   if (confirm('Do you wish to also set this new return date in the issue titled "<?php echo htmlspecialchars($irow['title'],ENT_QUOTES); ?>"?')) {
    f.form_issue_id.value = '<?php echo htmlspecialchars($irow['id'],ENT_QUOTES); ?>';
   } else {
    alert('OK, you will need to manually update the return date in any affected issue(s).');
   }
<?php } else { ?>
   alert('You have changed the return date but there are no open issues. You probably need to create or modify one.');
<?php
  } // end empty $irow
  echo "  }\n";
} // end athletic team
?>
  return true;
 }

 function newEvt() {
  dlgopen('../../main/calendar/add_edit_event.php?patientid=<?php echo htmlspecialchars($pid,ENT_QUOTES); ?>', '_blank', 550, 270);
  return false;
 }

function sendimage(pid, what) {
 // alert('Not yet implemented.'); return false;
 dlgopen('../upload_dialog.php?patientid=' + pid + '&file=' + what,
  '_blank', 500, 400);
 return false;
}

</script>

<script type="text/javascript">

function toggleIndicator(target,div) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" ) {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'),ENT_QUOTES); ?>" );
        $("#"+div).hide();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 0 });
    } else {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" );
        $("#"+div).show();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 1 });
    }
}

$(document).ready(function(){

    // load divs
    $("#stats_div").load("stats.php", { 'embeddedScreen' : true }, function() {
	// (note need to place javascript code here also to get the dynamic link to work)
        $(".rx_modal").fancybox( {
                'overlayOpacity' : 0.0,
                'showCloseButton' : true,
                'frameHeight' : 500,
                'frameWidth' : 800,
        	'centerOnScroll' : false,
        	'callbackOnClose' : function()  {
                refreshme();
        	}
        });
    });
    $("#pnotes_ps_expand").load("pnotes_fragment.php");
    $("#disclosures_ps_expand").load("disc_fragment.php");

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) { ?>
      $("#clinical_reminders_ps_expand").load("clinical_reminders_fragment.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
    <?php } // end crw?>

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) { ?>
      $("#patient_reminders_ps_expand").load("patient_reminders_fragment.php");
    <?php } // end prw?>

<?php if ($vitals_is_registered) { ?>
    // Initialize the Vitals form if it is registered.
    $("#vitals_ps_expand").load("vitals_fragment.php");
<?php } ?>

<?php
  // Initialize for each applicable LBF form.
  $gfres = sqlStatement("SELECT option_id FROM list_options WHERE " .
    "list_id = 'lbfnames' AND option_value > 0 ORDER BY seq, title");
  while($gfrow = sqlFetchArray($gfres)) {
?>
    $("#<?php echo $gfrow['option_id']; ?>_ps_expand").load("lbf_fragment.php?formname=<?php echo $gfrow['option_id']; ?>");
<?php
  }
?>

    // fancy box
    enable_modals();

    tabbify();

// modal for dialog boxes
  $(".large_modal").fancybox( {
    'overlayOpacity' : 0.0,
    'showCloseButton' : true,
    'frameHeight' : 600,
    'frameWidth' : 1000,
    'centerOnScroll' : false
  });

// modal for image viewer
  $(".image_modal").fancybox( {
    'overlayOpacity' : 0.0,
    'showCloseButton' : true,
    'centerOnScroll' : false,
    'autoscale' : true
  });

});

</script>

<style type="css/text">
#pnotes_ps_expand {
  height:auto;
  width:100%;
}
</style>

</head>

<body class="body_top">

<?php
 $result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
 $result2 = getEmployerData($pid);
 $result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
 $insco_name = "";

 if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
     $insco_name = getInsuranceProvider($result3['provider']);
 }

 $thisauth = acl_check('patients', 'demo');
 if ($thisauth) {
  if ($result['squad'] && ! acl_check('squads', $result['squad']))
   $thisauth = 0;
 }

 if (!$thisauth) {
  echo "<p>(" . htmlspecialchars(xl('Demographics not authorized'),ENT_NOQUOTES) . ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 if ($thisauth == 'write') {
  echo "<table><tr><td><span class='title'>" .
   htmlspecialchars(getPatientName($pid),ENT_NOQUOTES) .
   "</span></td>";

  if (acl_check('admin', 'super')) {
   echo "<td style='padding-left:1em;'><a class='css_button iframe' href='../deleter.php?patient=" . 
    htmlspecialchars($pid,ENT_QUOTES) . "'>" .
    "<span>".htmlspecialchars(xl('Delete'),ENT_NOQUOTES).
    "</span></a></td>";
  }
  echo "</tr></table>";
 }

// Get the document ID of the patient ID card if access to it is wanted here.
$idcard_doc_id = false;
if ($GLOBALS['patient_id_category_name']) {
  $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name']);
}

?>
<table cellspacing='0' cellpadding='0' border='0'>
 <tr>
  <td class="small" colspan='4'>
<a href="../history/history.php" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('History'),ENT_NOQUOTES); ?></a>
|
<?php //note that we have temporarily removed report screen from the modal view ?>
<a href="../report/patient_report.php" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Report'),ENT_NOQUOTES); ?></a>
|
<?php //note that we have temporarily removed document screen from the modal view ?>
<a href="../../../controller.php?document&list&patient_id=<?php echo $pid;?>" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Documents'),ENT_NOQUOTES); ?></a>
|
<a href="../transaction/transactions.php" class='iframe large_modal' onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Transactions'),ENT_NOQUOTES); ?></a>
  </td>
 </tr>
</table> <!-- end header -->

<div style='margin-top:10px'> <!-- start main content div -->
 <table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr>
   <td align="left" valign="top">
    <!-- start left column div -->
    <div style='float:left; margin-right:20px'>
     <table cellspacing=0 cellpadding=0>
      <tr>
       <td>
<?php
// Billing expand collapse widget
$widgetTitle = xl("Billing");
$widgetLabel = "billing";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "return newEvt();";
$widgetButtonClass = "";
$linkMethod = "javascript";
$bodyClass = "notab";
$widgetAuth = false;
$fixedWidth = true;
if ($GLOBALS['force_billing_widget_open']) {
  $forceExpandAlways = true;
}
else {
  $forceExpandAlways = false;
}
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth, $forceExpandAlways);
?>
        <br>
<?php
 if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
 // Show current balance and billing note, if any.
  echo "        <div style='margin-left: 10px; margin-right: 10px'>" .
   "<span class='bold'><font color='#ee6600'>" .
   htmlspecialchars(xl('Balance Due'),ENT_NOQUOTES) .
   ": " . htmlspecialchars(oeFormatMoney(get_patient_balance($pid)),ENT_NOQUOTES) .
   "</font></span><br>";
  if ($result['genericname2'] == 'Billing') {
   echo "<span class='bold'><font color='red'>" .
    htmlspecialchars(xl('Billing Note'),ENT_NOQUOTES) . ":" .
    htmlspecialchars($result['genericval2'],ENT_NOQUOTES) .
    "</font></span><br>";
  } 
  if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
   echo "<span class='bold'>" .
    htmlspecialchars(xl('Primary Insurance'),ENT_NOQUOTES) . ': ' . htmlspecialchars($insco_name,ENT_NOQUOTES) .
    "</span>&nbsp;&nbsp;&nbsp;";
   if ($result3['copay'] > 0) {
    echo "<span class='bold'>" .
     htmlspecialchars(xl('Copay'),ENT_NOQUOTES) . ': ' .  htmlspecialchars($result3['copay'],ENT_NOQUOTES) .
     "</span>&nbsp;&nbsp;&nbsp;";
   }
   echo "<span class='bold'>" .
    htmlspecialchars(xl('Effective Date'),ENT_NOQUOTES) . ': ' .  htmlspecialchars(oeFormatShortDate($result3['effdate'],ENT_NOQUOTES)) .
    "</span>";
  }
  echo "</div><br>";
 }
?>
        </div> <!-- required for expand_collapse_widget -->
       </td>
      </tr>
      <tr>
       <td>
<?php
// Demographics expand collapse widget
$widgetTitle = xl("Demographics");
$widgetLabel = "demographics";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "demographics_full.php";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "";
$widgetAuth = ($thisauth == "write");
$fixedWidth = true;
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
?>
         <div id="DEM" >
          <ul class="tabNav">
           <?php display_layout_tabs('DEM', $result, $result2); ?>
          </ul>
          <div class="tabContainer">
           <?php display_layout_tabs_data('DEM', $result, $result2); ?>
          </div>
         </div>
        </div> <!-- required for expand_collapse_widget -->
       </td>
      </tr>

      <tr>
       <td>
<?php
$insurance_count = 0;
foreach (array('primary','secondary','tertiary') as $instype) {
  $enddate = 'Present';
  $query = "SELECT * FROM insurance_data WHERE " .
    "pid = ? AND type = ? " .
    "ORDER BY date DESC";
  $res = sqlStatement($query, array($pid, $instype) );
  while( $row = sqlFetchArray($res) ) {
    if ($row['provider'] ) $insurance_count++;
  }
}

if ( $insurance_count > 0 ) {
  // Insurance expand collapse widget
  $widgetTitle = xl("Insurance");
  $widgetLabel = "insurance";
  $widgetButtonLabel = xl("Edit");
  $widgetButtonLink = "demographics_full.php";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "";
  $widgetAuth = ($thisauth == "write");
  $fixedWidth = true;
  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
    $widgetAuth, $fixedWidth);

  if ( $insurance_count > 0 ) {
?>

        <ul class="tabNav"><?php
					///////////////////////////////// INSURANCE SECTION
					$first = true;
					foreach (array('primary','secondary','tertiary') as $instype) {

						$query = "SELECT * FROM insurance_data WHERE " .
						"pid = ? AND type = ? " .
						"ORDER BY date DESC";
						$res = sqlStatement($query, array($pid, $instype) );

						$enddate = 'Present';

						  while( $row = sqlFetchArray($res) ) {
							if ($row['provider'] ) {

								$ins_description  = ucfirst($instype);
	                                                        $ins_description = xl($ins_description);
								$ins_description  .= strcmp($enddate, 'Present') != 0 ? " (".xl('Old').")" : "";
								?>
								<li <?php echo $first ? 'class="current"' : '' ?>><a href="/play/javascript-tabbed-navigation/">
								<?php echo htmlspecialchars($ins_description,ENT_NOQUOTES); ?></a></li>
								<?php
								$first = false;
							}
							$enddate = $row['date'];
						}
					}
					// Display the eligibility tab
					echo "<li><a href='/play/javascript-tabbed-navigation/'>" .
						htmlspecialchars( xl('Eligibility'), ENT_NOQUOTES) . "</a></li>";

					?></ul><?php

				} ?>

				<div class="tabContainer">
					<?php
					$first = true;
					foreach (array('primary','secondary','tertiary') as $instype) {
					  $enddate = 'Present';

						$query = "SELECT * FROM insurance_data WHERE " .
						"pid = ? AND type = ? " .
						"ORDER BY date DESC";
						$res = sqlStatement($query, array($pid, $instype) );
					  while( $row = sqlFetchArray($res) ) {
						if ($row['provider'] ) {
							?>
								<div class="tab <?php echo $first ? 'current' : '' ?>">
								<table border='0' cellpadding='0' width='100%'>
								<?php
								$icobj = new InsuranceCompany($row['provider']);
								$adobj = $icobj->get_address();
								$insco_name = trim($icobj->get_name());
								?>
								<tr>
								 <td valign='top' colspan='3'>
								  <span class='text'>
								  <?php if (strcmp($enddate, 'Present') != 0) echo htmlspecialchars(xl("Old"),ENT_NOQUOTES)." "; ?>
								  <?php $tempinstype=ucfirst($instype); echo htmlspecialchars(xl($tempinstype.' Insurance'),ENT_NOQUOTES); ?>
								  <?php if (strcmp($row['date'], '0000-00-00') != 0) { ?>
								  <?php echo htmlspecialchars(xl('from','',' ',' ').$row['date'],ENT_NOQUOTES); ?>
								  <?php } ?>
						                  <?php echo htmlspecialchars(xl('until','',' ',' '),ENT_NOQUOTES);
								    echo (strcmp($enddate, 'Present') != 0) ? $enddate : htmlspecialchars(xl('Present'),ENT_NOQUOTES); ?>:</span>
								 </td>
								</tr>
								<tr>
								 <td valign='top'>
								  <span class='text'>
								  <?php
								  if ($insco_name) {
									echo htmlspecialchars($insco_name,ENT_NOQUOTES) . '<br>';
									if (trim($adobj->get_line1())) {
									  echo htmlspecialchars($adobj->get_line1(),ENT_NOQUOTES) . '<br>';
									  echo htmlspecialchars($adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip(),ENT_NOQUOTES);
									}
								  } else {
									echo "<font color='red'><b>".htmlspecialchars(xl('Unassigned'),ENT_NOQUOTES)."</b></font>";
								  }
								  ?>
								  <br>
								  <?php echo htmlspecialchars(xl('Policy Number'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['policy_number'],ENT_NOQUOTES) ?><br>
								  <?php echo htmlspecialchars(xl('Plan Name'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['plan_name'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars(xl('Group Number'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['group_number'],ENT_NOQUOTES); ?></span>
								 </td>
								 <td valign='top'>
								  <span class='bold'><?php echo htmlspecialchars(xl('Subscriber'),ENT_NOQUOTES); ?>: </span><br>
								  <span class='text'><?php echo htmlspecialchars($row['subscriber_fname'] . ' ' . $row['subscriber_mname'] . ' ' . $row['subscriber_lname'],ENT_NOQUOTES); ?>
							<?php
								  if ($row['subscriber_relationship'] != "") {
									echo "(" . htmlspecialchars($row['subscriber_relationship'],ENT_NOQUOTES) . ")";
								  }
							?>
								  <br>
								  <?php echo htmlspecialchars(xl('S.S.'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['subscriber_ss'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars(xl('D.O.B.'),ENT_NOQUOTES); ?>:
								  <?php if ($row['subscriber_DOB'] != "0000-00-00 00:00:00") echo htmlspecialchars($row['subscriber_DOB'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars(xl('Phone'),ENT_NOQUOTES); ?>: 
								  <?php echo htmlspecialchars($row['subscriber_phone'],ENT_NOQUOTES); ?>
								  </span>
								 </td>
								 <td valign='top'>
								  <span class='bold'><?php echo htmlspecialchars(xl('Subscriber Address'),ENT_NOQUOTES); ?>: </span><br>
								  <span class='text'><?php echo htmlspecialchars($row['subscriber_street'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars($row['subscriber_city'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_state'] != "") echo ", "; echo htmlspecialchars($row['subscriber_state'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_country'] != "") echo ", "; echo htmlspecialchars($row['subscriber_country'],ENT_NOQUOTES); ?>
								  <?php echo " " . htmlspecialchars($row['subscriber_postal_code'],ENT_NOQUOTES); ?></span>

							<?php if (trim($row['subscriber_employer'])) { ?>
								  <br><span class='bold'><?php echo htmlspecialchars(xl('Subscriber Employer'),ENT_NOQUOTES); ?>: </span><br>
								  <span class='text'><?php echo htmlspecialchars($row['subscriber_employer'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars($row['subscriber_employer_street'],ENT_NOQUOTES); ?><br>
								  <?php echo htmlspecialchars($row['subscriber_employer_city'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_employer_city'] != "") echo ", "; echo htmlspecialchars($row['subscriber_employer_state'],ENT_NOQUOTES); ?>
								  <?php if($row['subscriber_employer_country'] != "") echo ", "; echo htmlspecialchars($row['subscriber_employer_country'],ENT_NOQUOTES); ?>
								  <?php echo " " . htmlspecialchars($row['subscriber_employer_postal_code'],ENT_NOQUOTES); ?>
								  </span>
							<?php } ?>

								 </td>
								</tr>
								<tr>
								 <td>
							<?php if ($row['copay'] != "") { ?>
								  <span class='bold'><?php echo htmlspecialchars(xl('CoPay'),ENT_NOQUOTES); ?>: </span>
								  <span class='text'><?php echo htmlspecialchars($row['copay'],ENT_NOQUOTES); ?></span>
							<?php } ?>
							<br>
								  <span class='bold'><?php echo htmlspecialchars(xl('Accept Assignment'),ENT_NOQUOTES); ?>:</span>
								  <span class='text'><?php if($row['accept_assignment'] == "TRUE") echo xl("YES"); ?>
								  <?php if($row['accept_assignment'] == "FALSE") echo xl("NO"); ?></span>
								 </td>
								 <td valign='top'></td>
								 <td valign='top'></td>
							   </tr>

							</table>
							</div>
							<?php

						} // end if ($row['provider'])
						$enddate = $row['date'];
						$first = false;
					  } // end while
					} // end foreach

					// Display the eligibility information
					echo "<div class='tab'>";
					show_eligibility_information($pid,true);
					echo "</div>";

			///////////////////////////////// END INSURANCE SECTION
			?>
			</div>

			<?php } // ?>

			</td>
		</tr>

		<tr>
			<td width='650px'>

<?php
// Notes expand collapse widget
$widgetTitle = xl("Notes");
$widgetLabel = "pnotes";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "pnotes_full.php";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = true;
$fixedWidth = true;
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
?>

                    <br/>
                    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
			</td>
		</tr>
                <?php if ( (acl_check('patients', 'med')) && ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) ) {
                echo "<tr><td width='650px'>";
                // patient reminders collapse widget
                $widgetTitle = xl("Patient Reminders");
                $widgetLabel = "patient_reminders";
                $widgetButtonLabel = xl("Edit");
                $widgetButtonLink = "../reminder/patient_reminders.php?mode=simple&patient_id=".$pid;
                $widgetButtonClass = "";
                $linkMethod = "html";
                $bodyClass = "notab";
                $widgetAuth = true;
                $fixedWidth = true;
                expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth); ?>
                    <br/>
                    <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
                        </td>
                </tr>
                <?php } //end if prw is activated  ?>
              
       <tr>
       <td width='650px'>
<?php
// disclosures expand collapse widget
$widgetTitle = xl("Disclosures");
$widgetLabel = "disclosures";
$widgetButtonLabel = xl("Edit");
$widgetButtonLink = "disclosure_full.php";
$widgetButtonClass = "";
$linkMethod = "html";
$bodyClass = "notab";
$widgetAuth = true;
$fixedWidth = true;
expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
  $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
  $widgetAuth, $fixedWidth);
?>
                    <br/>
                    <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
     </td>
    </tr>		

<?php if ($vitals_is_registered) { ?>
    <tr>
     <td width='650px'>
<?php // vitals expand collapse widget
  $widgetTitle = xl("Vitals");
  $widgetLabel = "vitals";
  $widgetButtonLabel = xl("Trend");
  $widgetButtonLink = "../encounter/trend_form.php?formname=vitals";
  $widgetButtonClass = "";
  $linkMethod = "html";
  $bodyClass = "notab";
  // check to see if any vitals exist
  $existVitals = sqlQuery("SELECT * FROM form_vitals WHERE pid=?", array($pid) );
  if ($existVitals) {
    $widgetAuth = true;
  }
  else {
    $widgetAuth = false;
  }
  $fixedWidth = true;
  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
    $widgetAuth, $fixedWidth);
?>
      <br/>
      <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
      </div>
     </td>
    </tr>
<?php } // end if ($vitals_is_registered) ?>

<?php
  // This generates a section similar to Vitals for each LBF form that
  // supports charting.  The form ID is used as the "widget label".
  //
  $gfres = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
    "list_id = 'lbfnames' AND option_value > 0 ORDER BY seq, title");
  while($gfrow = sqlFetchArray($gfres)) {
?>
    <tr>
     <td width='650px'>
<?php // vitals expand collapse widget
    $vitals_form_id = $gfrow['option_id'];
    $widgetTitle = $gfrow['title'];
    $widgetLabel = $vitals_form_id;
    $widgetButtonLabel = xl("Trend");
    $widgetButtonLink = "../encounter/trend_form.php?formname=$vitals_form_id";
    $widgetButtonClass = "";
    $linkMethod = "html";
    $bodyClass = "notab";
    // check to see if any instances exist for this patient
    $existVitals = sqlQuery(
      "SELECT * FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0",
      array($pid, $vitals_form_id));
    $widgetAuth = $existVitals ? true : false;
    $fixedWidth = true;
    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
      $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
      $widgetAuth, $fixedWidth);
?>
       <br/>
       <div style='margin-left:10px' class='text'>
        <image src='../../pic/ajax-loader.gif'/>
       </div>
       <br/>
      </div> <!-- This is required by expand_collapse_widget(). -->
     </td>
    </tr>
<?php
  } // end while
?>

   </table>

  </div>
    <!-- end left column div -->

    <!-- start right column div -->
	<div>
    <table>
    <tr>
    <td>

<div>
    <?php

    // If there is an ID Card or any Photos show the widget
    $photos = pic_array($pid, $GLOBALS['patient_photo_category_name']);
    if ($photos or $idcard_doc_id )
    {
        $widgetTitle = xl("ID Card") . '/' . xl("Photos");
        $widgetLabel = "photos";
        $linkMethod = "javascript";
        $bodyClass = "notab-right";
        $widgetAuth = false;
        $fixedWidth = false;
        expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel ,
                $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
                $widgetAuth, $fixedWidth);
?>
<br />
<?php
    	if ($idcard_doc_id) {
        	image_widget($idcard_doc_id, $GLOBALS['patient_id_category_name']);
		}

        foreach ($photos as $photo_doc_id) {
            image_widget($photo_doc_id, $GLOBALS['patient_photo_category_name']);
        }
    }
?>

<br />
</div>
<div>
 <?php
    // Advance Directives
    if ($GLOBALS['advance_directives_warning']) {
	// advance directives expand collapse widget
	$widgetTitle = xl("Advance Directives");
	$widgetLabel = "directives";
	$widgetButtonLabel = xl("Edit");
	$widgetButtonLink = "return advdirconfigure();";
	$widgetButtonClass = "";
	$linkMethod = "javascript";
	$bodyClass = "summary_item small";
	$widgetAuth = true;
	$fixedWidth = false;
	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
          $counterFlag = false; //flag to record whether any categories contain ad records
          $query = "SELECT id FROM categories WHERE name='Advance Directive'";
          $myrow2 = sqlQuery($query);
          if ($myrow2) {
          $parentId = $myrow2['id'];
          $query = "SELECT id, name FROM categories WHERE parent=?";
          $resNew1 = sqlStatement($query, array($parentId) );
          while ($myrows3 = sqlFetchArray($resNew1)) {
              $categoryId = $myrows3['id'];
              $nameDoc = $myrows3['name'];
              $query = "SELECT documents.date, documents.id " .
                   "FROM documents " .
                   "INNER JOIN categories_to_documents " .
                   "ON categories_to_documents.document_id=documents.id " .
                   "WHERE categories_to_documents.category_id=? " .
                   "AND documents.foreign_id=? " .
                   "ORDER BY documents.date DESC";
              $resNew2 = sqlStatement($query, array($categoryId, $pid) );
              $limitCounter = 0; // limit to one entry per category
              while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
                  $dateTimeDoc = $myrows4['date'];
              // remove time from datetime stamp
              $tempParse = explode(" ",$dateTimeDoc);
              $dateDoc = $tempParse[0];
              $idDoc = $myrows4['id'];
              echo "<a href='$web_root/controller.php?document&retrieve&patient_id=" .
                    htmlspecialchars($pid,ENT_QUOTES) . "&document_id=" .
                    htmlspecialchars($idDoc,ENT_QUOTES) . "&as_file=true'>" .
                    htmlspecialchars(xl_document_category($nameDoc),ENT_NOQUOTES) . "</a> " .
                    htmlspecialchars($dateDoc,ENT_NOQUOTES);
              echo "<br>";
              $limitCounter = $limitCounter + 1;
              $counterFlag = true;
              }
          }
          }
          if (!$counterFlag) {
              echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES);
          } ?>
      </div>
 <?php  }  // close advanced dir block
 
	// This is a feature for a specific client.  -- Rod
	if ($GLOBALS['cene_specific']) {
	  echo "   <br />\n";

          $imagedir  = $GLOBALS['OE_SITE_DIR'] . "/documents/$pid/demographics";
          $imagepath = "$web_root/sites/" . $_SESSION['site_id'] . "/documents/$pid/demographics";

	  echo "   <a href='' onclick=\"return sendimage($pid, 'photo');\" " .
		"title='Click to attach patient image'>\n";
	  if (is_file("$imagedir/photo.jpg")) {
		echo "   <img src='$imagepath/photo.jpg' /></a>\n";
	  } else {
		echo "   Attach Patient Image</a><br />\n";
	  }
	  echo "   <br />&nbsp;<br />\n";

	  echo "   <a href='' onclick=\"return sendimage($pid, 'fingerprint');\" " .
		"title='Click to attach fingerprint'>\n";
	  if (is_file("$imagedir/fingerprint.jpg")) {
		echo "   <img src='$imagepath/fingerprint.jpg' /></a>\n";
	  } else {
		echo "   Attach Biometric Fingerprint</a><br />\n";
	  }
	  echo "   <br />&nbsp;<br />\n";
	}

	// This stuff only applies to athletic team use of OpenEMR.  The client
	// insisted on being able to quickly change fitness and return date here:
	//
	if (false && $GLOBALS['athletic_team']) {
	  //                  blue      green     yellow    red       orange
	  $fitcolors = array('#6677ff','#00cc00','#ffff00','#ff3333','#ff8800','#ffeecc','#ffccaa');
	  if (!empty($GLOBALS['fitness_colors'])) $fitcolors = $GLOBALS['fitness_colors'];
	  $fitcolor = $fitcolors[0];
	  $form_fitness   = $_POST['form_fitness'];
	  $form_userdate1 = fixDate($_POST['form_userdate1'], '');
	  $form_issue_id  = $_POST['form_issue_id'];
	  if ($form_submit) {
		$returndate = $form_userdate1 ? "'$form_userdate1'" : "NULL";
		sqlStatement("UPDATE patient_data SET fitness = ?, " .
		  "userdate1 = ? WHERE pid = ?", array($form_fitness, $returndate, $pid) );
		// Update return date in the designated issue, if requested.
		if ($form_issue_id) {
		  sqlStatement("UPDATE lists SET returndate = ? WHERE " .
		    "id = ?", array($returndate, $form_issue_id) );
		}
	  } else {
		$form_fitness = $result['fitness'];
		if (! $form_fitness) $form_fitness = 1;
		$form_userdate1 = $result['userdate1'];
	  }
	  $fitcolor = $fitcolors[$form_fitness - 1];
	  echo "   <form method='post' action='demographics.php' onsubmit='return validate()'>\n";
	  echo "   <span class='bold'>Fitness to Play:</span><br />\n";
	  echo "   <select name='form_fitness' style='background-color:$fitcolor'>\n";
	  $res = sqlStatement("SELECT * FROM list_options WHERE " .
		"list_id = 'fitness' ORDER BY seq");
	  while ($row = sqlFetchArray($res)) {
		$key = $row['option_id'];
		echo "    <option value='" . htmlspecialchars($key,ENT_QUOTES) . "'";
		if ($key == $form_fitness) echo " selected";
		echo ">" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</option>\n";
	  }
	  echo "   </select>\n";
	  echo "   <br /><span class='bold'>Return to Play:</span><br>\n";
	  echo "   <input type='text' size='10' name='form_userdate1' id='form_userdate1' " .
		"value='$form_userdate1' " .
		"title='" . htmlspecialchars(xl('yyyy-mm-dd Date of return to play'),ENT_QUOTES) . "' " .
		"onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />\n" .
		"   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' " .
		"id='img_userdate1' border='0' alt='[?]' style='cursor:pointer' " .
		"title='" . htmlspecialchars(xl('Click here to choose a date'),ENT_QUOTES) . "'>\n";
	  echo "   <input type='hidden' name='form_original_userdate1' value='" . htmlspecialchars($form_userdate1,ENT_QUOTES) . "' />\n";
	  echo "   <input type='hidden' name='form_issue_id' value='' />\n";
	  echo "<p><input type='submit' name='form_submit' value='Change' /></p>\n";
	  echo "   </form>\n";
	}

	// Show current and upcoming appointments.
	if (isset($pid) && !$GLOBALS['disable_calendar']) {
	 $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
	  "e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
	  "c.pc_catname " .
	  "FROM openemr_postcalendar_events AS e, users AS u, " .
	  "openemr_postcalendar_categories AS c WHERE " .
	  "e.pc_pid = ? AND e.pc_eventDate >= CURRENT_DATE AND " .
	  "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
	  "ORDER BY e.pc_eventDate, e.pc_startTime";
	 $res = sqlStatement($query, array($pid) );

        if ( (acl_check('patients', 'med')) && ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) ) {
          // clinical summary expand collapse widget
	  $widgetTitle = xl("Clinical Reminders");
	  $widgetLabel = "clinical_reminders";
	  $widgetButtonLabel = xl("Edit");
	  $widgetButtonLink = "../reminder/clinical_reminders.php?patient_id=".$pid;;
	  $widgetButtonClass = "";
	  $linkMethod = "html";
	  $bodyClass = "summary_item small";
	  $widgetAuth = true;
	  $fixedWidth = false;
	  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
          echo "<br/>";
          echo "<div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>";
	  echo "</div>";
        } // end if crw

	// appointments expand collapse widget
	$widgetTitle = xl("Appointments");
	$widgetLabel = "appointments";
	$widgetButtonLabel = xl("Add");
	$widgetButtonLink = "return newEvt();";
	$widgetButtonClass = "";
	$linkMethod = "javascript";
	$bodyClass = "summary_item small";
	$widgetAuth = (isset($res) && $res != null);
	$fixedWidth = false;
	expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);

			 $count = 0;
			 while($row = sqlFetchArray($res)) {
			  $count++;
			  $dayname = date("l", strtotime($row['pc_eventDate']));
			  $dispampm = "am";
			  $disphour = substr($row['pc_startTime'], 0, 2) + 0;
			  $dispmin  = substr($row['pc_startTime'], 3, 2);
			  if ($disphour >= 12) {
			   $dispampm = "pm";
			   if ($disphour > 12) $disphour -= 12;
			  }
			  $etitle = xl('(Click to edit)');
			  if ($row['pc_hometext'] != "") {
				$etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
			  }
              echo "<a href='javascript:oldEvt(" . htmlspecialchars($row['pc_eid'],ENT_QUOTES) .
		")' title='" . htmlspecialchars($etitle,ENT_QUOTES) . "'>";
			  echo "<b>" . htmlspecialchars(xl($dayname) . ", " . $row['pc_eventDate'],ENT_NOQUOTES) . "</b><br>";
			  echo htmlspecialchars("$disphour:$dispmin " . xl($dispampm) . " " . xl_appt_category($row['pc_catname']),ENT_NOQUOTES) . "<br>\n";
			  echo htmlspecialchars($row['fname'] . " " . $row['lname'],ENT_NOQUOTES) . "</a><br>\n";
			 }
			 if (isset($res) && $res != null) {
				if ( $count < 1 ) { echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES); }
				echo "</div>";
			 }
			}
			?>
		</div>

		<div id='stats_div'>
            <br/>
            <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
        </div>
    </td>
    </tr>
    </table>

	</div> <!-- end right column div -->

  </td>

 </tr>
</table>

</div> <!-- end main content div -->

<?php if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) { ?>
<script language='JavaScript'>
 top.window.parent.left_nav.setPatient(<?php echo "'" . htmlspecialchars(($result['fname']) . " " . ($result['lname']),ENT_QUOTES) .
   "'," . htmlspecialchars($pid,ENT_QUOTES) . ",'" . htmlspecialchars(($result['pubpid']),ENT_QUOTES) .
   "','', ' " . htmlspecialchars(xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAge($result['DOB_YMD']), ENT_QUOTES) . "'"; ?>);
EncounterDateArray=new Array;
CalendarCategoryArray=new Array;
EncounterIdArray=new Array;
Count=0;
 <?php
 //Encounter details are stored to javacript as array.
$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
	" left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
   if(sqlNumRows($result4)>0)
	while($rowresult4 = sqlFetchArray($result4))
	 {
?>
		EncounterIdArray[Count]='<?php echo htmlspecialchars($rowresult4['encounter'], ENT_QUOTES); ?>';
		EncounterDateArray[Count]='<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date']))), ENT_QUOTES); ?>';
		CalendarCategoryArray[Count]='<?php echo htmlspecialchars( xl_appt_category($rowresult4['pc_catname']), ENT_QUOTES); ?>';
		Count++;
 <?php
	 }
 ?>
 top.window.parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
 parent.left_nav.setRadio(window.name, 'dem');
</script>
<?php } ?>

<?php if (false && $GLOBALS['athletic_team']) { ?>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_userdate1", ifFormat:"%Y-%m-%d", button:"img_userdate1"});
</script>
<?php } ?>

</body>
</html>
