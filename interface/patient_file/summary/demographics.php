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
 require_once("./patient_picture.php");
 require_once("$srcdir/options.inc.php");
 require_once("../history/history.inc.php");
 require_once("$srcdir/formatting.inc.php");
 require_once("$srcdir/user.inc");
  if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) {
  include_once("$srcdir/pid.inc");
  setpid($_GET['set_pid']);
 }

// COLLECT the user settings
//  currently collects flags to keep track of
//  which sections to persistently expand/collapse
$user_settings = getUserSettings($_SESSION['authUserID']);

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
<script language="JavaScript">
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

function toggle( target, div ) {

    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" ) {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'),ENT_QUOTES); ?>" );
        $(div).hide();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 0 });
    } else {
        $(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" );
        $(div).show();
	$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 1 });
    }

}

$(document).ready(function(){

    $("#dem_view").click( function() {
        toggle( $(this), "#DEM" );
    });

    $("#his_view").click( function() {
        toggle( $(this), "#HIS" );
    });

    $("#ins_view").click( function() {
        toggle( $(this), "#INSURANCE" );
    });

    $("#notes_view").click( function() {
        toggle( $(this), "#notes_div" );
    });

    $("#disc_view").click( function() {
        toggle( $(this), "#disc_div" );
    });
 
    // load divs
    $("#stats_div").load("stats.php", { 'embeddedScreen' : true }, function() {
	// special size for (note need to place here to get the dynamic link to work
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
    $("#notes_div").load("pnotes_fragment.php");
    $("#disc_div").load("disc_fragment.php");

    // fancy box
    enable_modals();

    tabbify();

    // special size for
	$(".large_modal").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 600,
		'frameWidth' : 1000,
        'centerOnScroll' : false
	});

    // special size for
	$(".medium_modal").fancybox( {
		'overlayOpacity' : 0.0,
		'showCloseButton' : true,
		'frameHeight' : 500,
		'frameWidth' : 800,
        'centerOnScroll' : false
	});

});
</script>

<style type="css/text">
    #notes_div {
        height:auto;
        width:100%;
    }
</style>

</head>

<body class="body_top">
<table cellspacing='0' cellpadding='0' border='0'>
<tr>
<?php
 $result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
 $result2 = getEmployerData($pid);

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
  foreach (pic_array() as $var) {print $var;}
  echo "<td><span class='title'>" .
   htmlspecialchars(getPatientName($pid),ENT_NOQUOTES) .
   "</span>&nbsp;&nbsp;</td>";

  if (acl_check('admin', 'super')) {
   echo "<td><a class='css_button iframe' href='../deleter.php?patient=" . 
    htmlspecialchars($pid,ENT_QUOTES) . "'>" .
    "<span>".htmlspecialchars(xl('Delete'),ENT_NOQUOTES).
    "</span></a></td>";
  }
	if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
	  // Show current balance and billing note, if any.
    echo "<td>&nbsp;&nbsp;&nbsp;<span class='bold'><font color='#ee6600'>" .
      htmlspecialchars(xl('Balance Due'),ENT_NOQUOTES) .
      ": " . htmlspecialchars(oeFormatMoney(get_patient_balance($pid)),ENT_NOQUOTES) .
      "</font><br />";
	  if ($result['genericname2'] == 'Billing') {
		htmlspecialchars(xl('Billing Note'),ENT_NOQUOTES) . ":";
		echo "<span class='bold'><font color='red'>" .
		  htmlspecialchars($result['genericval2'],ENT_NOQUOTES) .
		  "</font></span>";
	  }
	  echo "</span></td>";
	}

 }

// Get the document ID of the patient ID card if access to it is wanted here.
$document_id = 0;
if ($GLOBALS['patient_id_category_name']) {
  $tmp = sqlQuery("SELECT d.id, d.date, d.url FROM " .
    "documents AS d, categories_to_documents AS cd, categories AS c " .
    "WHERE d.foreign_id = ? " .
    "AND cd.document_id = d.id " .
    "AND c.id = cd.category_id " .
    "AND c.name LIKE ? " .
    "ORDER BY d.date DESC LIMIT 1", array($pid, $GLOBALS['patient_id_category_name']) );
  if ($tmp) $document_id = $tmp['id'];
}
?>
</tr>

<tr>
<td class="small" colspan='4'>
<a href="../history/history.php" onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('History'),ENT_NOQUOTES); ?></a>
|
<a href="../report/patient_report.php" class='iframe  medium_modal' onclick='top.restoreSession()'>
<?php echo htmlspecialchars(xl('Report'),ENT_NOQUOTES); ?></a>
|
<?php //note that we have temporarily removed document screen from the modul view ?>
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
				<div class="section-header">
					<table><tr>
					<?php if ($thisauth == 'write') {
						echo "<td><a class='css_button_small' href='demographics_full.php'";
						if (! $GLOBALS['concurrent_layout']) echo " target='Main'";
						echo " onclick='top.restoreSession()'><span>" .
						htmlspecialchars(xl("Edit" ),ENT_NOQUOTES). "</span></a></td>";
					} ?>
					<td><a href='javascript:;' class='small' id='dem_view'><span class='text'><b>
					<?php echo htmlspecialchars(xl("Demographics"),ENT_NOQUOTES); ?></b></span>
					<?php if ($user_settings['dem_expand']) {
						$label = xl('collapse');
					}
					else {
						$label = xl('expand');
					} ?>
					(<span class="indicator"><?php echo htmlspecialchars($label, ENT_QUOTES); ?></span>)</a></td>
					</tr></table>
				</div>

				<!-- Demographics -->
				<?php if ($user_settings['dem_expand']) {
					$styling = "";
				}
				else {
					$styling = "style='display:none'";
				} ?>
				<div id="DEM" <?php echo $styling; ?>>
					<ul class="tabNav">
					   <?php display_layout_tabs('DEM', $result, $result2); ?>
					</ul>
					<div class="tabContainer">
					   <?php display_layout_tabs_data('DEM', $result, $result2); ?>
					</div>
				</div>
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

		   ?>
			<div class="section-header">
				<table><tr>
				<?php if ($thisauth == 'write') {
					echo "<td><a class='css_button_small' href='demographics_full.php'";
					if (! $GLOBALS['concurrent_layout']) echo " target='Main'";
					echo " onclick='top.restoreSession()'><span>" .
					htmlspecialchars(xl("Edit" ),ENT_NOQUOTES). "</span></a></td>";
				} ?>
				<td><a href='javascript:;' class='small' id='ins_view'><span class='text'><b>
				<?php echo htmlspecialchars(xl("Insurance"),ENT_NOQUOTES); ?></b></span>
				<?php if ($user_settings['ins_expand']) {
					$label = xl('collapse');
				}
				else {
					$label = xl('expand');
				} ?>
                                (<span class="indicator"><?php echo htmlspecialchars($label, ENT_QUOTES); ?></span>)</a></td>
				</tr></table>
			</div>

			<?php if ($user_settings['ins_expand']) {
				$styling = "";
			}
			else {
				$styling = "style='display:none'";
			} ?>
			<div id="INSURANCE" <?php echo $styling; ?>>

			   <?php
			   if ( $insurance_count > 1 ) {

				   ?><ul class="tabNav"><?php

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

			///////////////////////////////// END INSURANCE SECTION
			?>
			</div>

			<?php } // ?>

			</td>
		</tr>

		<tr>
			<td width='650px'>
				<div class="section-header">
                    <table><tr>
                    <?php echo "<td><a class='css_button_small' href='pnotes_full.php'";
                    if (! $GLOBALS['concurrent_layout']) echo " target='Main'";
                    echo " onclick='top.restoreSession()'><span>" .
                    htmlspecialchars(xl("Edit" ),ENT_NOQUOTES). "</span></a></td>";
                    ?>
                    <td><a href='javascript:;' class='small' id='notes_view'><span class='text'><b><?php echo htmlspecialchars(xl("Notes"),ENT_NOQUOTES);?></b></span>
                    <?php if ($user_settings['not_expand']) {
                          $label = xl('collapse');
                    }
                    else {
                          $label = xl('expand');
                    } ?>
                    (<span class="indicator"><?php echo htmlspecialchars($label, ENT_QUOTES); ?></span>)</a></td>
                    </tr></table>
				</div>
                 <?php if ($user_settings['not_expand']) {
                         $styling = "style='height:auto; width:100%;'";
                 }
                 else {
                         $styling = "style='height:auto; width:100%; display:none;'";
                 } ?>
                 <div id='notes_div' class='tab current' <?php echo $styling; ?>>

                    <br/>
                    <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
			</td>
		</tr>
		 <tr>
                        <td width='650px'>
                                <div class="section-header">
                    <table><tr>
                    <?php echo "<td><a class='css_button_small' href='disclosure_full.php'";
                    if (! $GLOBALS['concurrent_layout']) echo " target='Main'";
                    echo " onclick='top.restoreSession()'><span>" .
                    htmlspecialchars(xl("Edit" ),ENT_NOQUOTES). "</span></a></td>";
                    ?>
                    <td><a href='javascript:;' class='small' id='disc_view'><span class='text'><b><?php echo htmlspecialchars(xl("Disclosures"),ENT_NOQUOTES);?></b></span>
                    <?php if ($user_settings['dis_expand']) {
                          $label = xl('collapse');
                    }
                    else {
                          $label = xl('expand');
                    } ?>
                    (<span class="indicator"><?php echo htmlspecialchars($label, ENT_QUOTES); ?></span>)</a></td>
                    </tr></table>
                                </div>
                 <?php if ($user_settings['dis_expand']) {
                         $styling = "style='height:auto; width:100%;'";
                 }
                 else {
                         $styling = "style='height:auto; width:100%; display:none;'";
                 } ?>
                 <div id='disc_div' class='tab current' <?php echo $styling; ?>>

                    <br/>
                    <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>
                </div>
                        </td>
              </tr>		

	   </table>

       </div>


	</div>
    <!-- end left column div -->

    <!-- start right column div -->
	<div class='text'>
    <table>
    <tr>
    <td>
    <?php
    if ($GLOBALS['advance_directives_warning']) { ?>
        <div>
            <span class="text"><b><?php echo htmlspecialchars(xl('Advance Directives'),ENT_NOQUOTES); ?></b></span>
            <a href="#" class="small" onclick="return advdirconfigure();">
                (<b><?php echo htmlspecialchars(xl('Manage'),ENT_NOQUOTES); ?></b>)
            </a>
        </div>
		<div class='small'>
		<?php
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
              echo htmlspecialchars(xl('None'),ENT_NOQUOTES);
          } ?>
      </div>
      <? } ?>
	<?php
	// This is a feature for a specific client.  -- Rod
	if ($GLOBALS['cene_specific']) {
	  echo "   <br />\n";

	  $imagedir  = "$webserver_root/documents/$pid/demographics";
	  $imagepath = "$web_root/documents/$pid/demographics";

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

	// If there is a patient ID card, then show a link to it.
	if ($document_id) {
	  echo "<a href='" . $web_root . "/controller.php?document&retrieve" .
		"&patient_id=$pid&document_id=$document_id' style='color:#00cc00' " .
		"onclick='top.restoreSession()'>Click for ID card</a><br />";
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

	 if (isset($res) && $res != null) { ?>
        <div>
            <span class="text"><b><?php echo htmlspecialchars(xl('Appointments'),ENT_NOQUOTES); ?></b></span>
            <a href="#" class="small" onclick="return newEvt();" >
                (<b><?php echo htmlspecialchars(xl('Add'),ENT_NOQUOTES); ?></b>)
            </a>
        </div>
     <?php } ?>
		<div class='small'>
			<?php
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
				if ( $count < 1 ) { echo htmlspecialchars(xl('None'),ENT_NOQUOTES); }
				echo "</div>";
			 }
			}
			?>
		</div>

		<div id='stats_div' style='float:left'>
            <br/>
            <div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/></div><br/>
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
