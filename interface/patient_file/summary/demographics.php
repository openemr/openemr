<?php
 require_once("../../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/classes/Address.class.php");
 require_once("$srcdir/classes/InsuranceCompany.class.php");
 require_once("./patient_picture.php");
 require_once("$srcdir/options.inc.php");
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
?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../../library/textformat.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language="JavaScript">
//Visolve - sync the radio buttons - Start
if((top.window.parent) && (parent.window)){
        var wname = top.window.parent.left_nav;
        wname.syncRadios();
        wname.setRadio(parent.window.name, "dem");
}
//Visolve - sync the radio buttons - End

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function oldEvt(eventid) {
  dlgopen('../../main/calendar/add_edit_event.php?eid=' + eventid, '_blank', 550, 270);
 }

 function refreshme() {
  top.restoreSession();
  location.reload();
 }

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?patient=<?php echo $pid ?>', '_blank', 500, 450);
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
    "pid = '$pid' AND enddate IS NULL ORDER BY begdate DESC LIMIT 1");
  if (!empty($irow)) {
?>
   if (confirm('Do you wish to also set this new return date in the issue titled "<?php echo addslashes($irow['title']) ?>"?')) {
    f.form_issue_id.value = '<?php echo $irow['id'] ?>';
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
  dlgopen('../../main/calendar/add_edit_event.php?patientid=<?php echo $pid ?>', '_blank', 550, 270);
  return false;
 }

function sendimage(pid, what) {
 // alert('Not yet implemented.'); return false;
 dlgopen('../upload_dialog.php?patientid=' + pid + '&file=' + what,
  '_blank', 500, 400);
 return false;
}

</script>
</head>

<body class="body_top">
<?php
 $result = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD"); 
 $result2 = getEmployerData($pid);

 $thisauth = acl_check('patients', 'demo');
 if ($thisauth) {
  if ($result['squad'] && ! acl_check('squads', $result['squad']))
   $thisauth = 0;
 }

 if (!$thisauth) {
  echo "<p>(" . xl('Demographics not authorized') . ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 if ($thisauth == 'write') {
  foreach (pic_array() as $var) {print $var;}
  echo "<p><a href='demographics_full.php'";
  if (! $GLOBALS['concurrent_layout']) echo " target='Main'";
  echo " onclick='top.restoreSession()'><span class='title'>" .
   xl('Demographics') . "</span>" .
   " <span class='more'>$tmore</span></a>";
  if (acl_check('admin', 'super')) {
   echo "&nbsp;&nbsp;<a href='' onclick='return deleteme()'>" .
    "<span class='more' style='color:red'>(".xl('Delete').")</span></a>";
  }
  echo "</p>\n";
 }

// Get the document ID of the patient ID card if access to it is wanted here.
$document_id = 0;
if ($GLOBALS['patient_id_category_name']) {
  $tmp = sqlQuery("SELECT d.id, d.date, d.url FROM " .
    "documents AS d, categories_to_documents AS cd, categories AS c " .
    "WHERE d.foreign_id = $pid " .
    "AND cd.document_id = d.id " .
    "AND c.id = cd.category_id " .
    "AND c.name LIKE '" . $GLOBALS['patient_id_category_name'] . "' " .
    "ORDER BY d.date DESC LIMIT 1");
  if ($tmp) $document_id = $tmp['id'];
}
?>

<table border="0" width="100%">
 <tr>
  <!-- Left column of main table; contains another table -->
  <td align="left" valign="top">
   <div id="DEM">
   <table border='0' cellpadding='0'>
<?php display_layout_rows('DEM', $result, $result2); ?>
   </table>
   </div>

   <table border='0' cellpadding='0' width='100%'>
<?php
///////////////////////////////// INSURANCE SECTION

foreach (array('primary','secondary','tertiary') as $instype) {
  $enddate = 'Present';

  $query = "SELECT * FROM insurance_data WHERE " .
    "pid = '$pid' AND type = '$instype' " .
    "ORDER BY date DESC";
  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
    if ($row['provider']) {
      $icobj = new InsuranceCompany($row['provider']);
      $adobj = $icobj->get_address();
      $insco_name = trim($icobj->get_name());
?>
    <tr>
     <td valign='top' colspan='3'>
      <br><span class='bold'>
      <?php if (strcmp($enddate, 'Present') != 0) echo "Old "; ?>
      <?php xl(ucfirst($instype) . ' Insurance','e'); ?>
<?php if (strcmp($row['date'], '0000-00-00') != 0) { ?>
      <?php xl(' from','e'); echo ' ' . $row['date']; ?>
<?php } ?>
      <?php xl(' until ','e'); echo $enddate; ?>
      :</span>
     </td>
    </tr>
    <tr>
     <td valign='top'>
      <span class='text'>
<?php
      if ($insco_name) {
        echo $insco_name . '<br>';
        if (trim($adobj->get_line1())) {
          echo $adobj->get_line1() . '<br>';
          echo $adobj->get_city() . ', ' . $adobj->get_state() . ' ' . $adobj->get_zip();
        }
      } else {
        echo "<font color='red'><b>Unassigned</b></font>";
      }
?>
      <br>
      <?php xl('Policy Number','e'); ?>: <?php echo $row['policy_number'] ?><br>
      Plan Name: <?php echo $row['plan_name']; ?><br>
      Group Number: <?php echo $row['group_number']; ?></span>
     </td>
     <td valign='top'>
      <span class='bold'><?php xl('Subscriber','e'); ?>: </span><br>
      <span class='text'><?php echo $row['subscriber_fname'] . ' ' . $row['subscriber_mname'] . ' ' . $row['subscriber_lname'] ?>
<?php
      if ($row['subscriber_relationship'] != "") {
        echo "(" . $row['subscriber_relationship'] . ")";
      }
?>
      <br>
      S.S.: <?php echo $row['subscriber_ss']; ?><br>
      <?php xl('D.O.B.','e'); ?>:
      <?php if ($row['subscriber_DOB'] != "0000-00-00 00:00:00") echo $row['subscriber_DOB']; ?><br>
      Phone: <?php echo $row['subscriber_phone'] ?>
      </span>
     </td>
     <td valign='top'>
      <span class='bold'><?php xl('Subscriber Address','e'); ?>: </span><br>
      <span class='text'><?php echo $row['subscriber_street']; ?><br>
      <?php echo $row['subscriber_city']; ?>
      <?php if($row['subscriber_state'] != "") echo ", "; echo $row['subscriber_state']; ?>
      <?php if($row['subscriber_country'] != "") echo ", "; echo $row['subscriber_country']; ?>
      <?php echo " " . $row['subscriber_postal_code']; ?></span>

<?php if (trim($row['subscriber_employer'])) { ?>
      <br><span class='bold'><?php xl('Subscriber Employer','e'); ?>: </span><br>
      <span class='text'><?php echo $row['subscriber_employer']; ?><br>
      <?php echo $row['subscriber_employer_street']; ?><br>
      <?php echo $row['subscriber_employer_city']; ?>
      <?php if($row['subscriber_employer_city'] != "") echo ", "; echo $row['subscriber_employer_state']; ?>
      <?php if($row['subscriber_employer_country'] != "") echo ", "; echo $row['subscriber_employer_country']; ?>
      <?php echo " " . $row['subscriber_employer_postal_code']; ?>
      </span>
<?php } ?>

     </td>
    </tr>
    <tr>
     <td>
<?php if ($row['copay'] != "") { ?>
      <span class='bold'><?php xl('CoPay','e'); ?>: </span>
      <span class='text'><?php echo $row['copay']; ?></span>
<?php } ?>
<br>
      <span class='bold'><?php xl('Accept Assignment','e'); ?>:</span>
      <span class='text'><?php if($row['accept_assignment'] == "TRUE") echo "YES"; ?>
      <?php if($row['accept_assignment'] == "FALSE") echo "NO"; ?></span>
     </td>
     <td valign='top'></td>
     <td valign='top'></td>
   </tr>
<?php
    } // end if ($row['provider'])
    $enddate = $row['date'];
  } // end while
} // end foreach

///////////////////////////////// END INSURANCE SECTION

?>
   </table>
  </td>

  <!-- Right column of main table -->

  <td valign="top" class="text">
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
if ($GLOBALS['athletic_team']) {
  //                  blue      green     yellow    red       orange
  $fitcolors = array('#6677ff','#00cc00','#ffff00','#ff3333','#ff8800','#ffeecc','#ffccaa');
  if (!empty($GLOBALS['fitness_colors'])) $fitcolors = $GLOBALS['fitness_colors'];
  $fitcolor = $fitcolors[0];
  $form_fitness   = $_POST['form_fitness'];
  $form_userdate1 = fixDate($_POST['form_userdate1'], '');
  $form_issue_id  = $_POST['form_issue_id'];
  if ($form_submit) {
    $returndate = $form_userdate1 ? "'$form_userdate1'" : "NULL";
    sqlStatement("UPDATE patient_data SET fitness = '$form_fitness', " .
      "userdate1 = $returndate WHERE pid = '$pid'");
    // Update return date in the designated issue, if requested.
    if ($form_issue_id) {
      sqlStatement("UPDATE lists SET returndate = $returndate WHERE " .
        "id = '$form_issue_id'");
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
    echo "    <option value='$key'";
    if ($key == $form_fitness) echo " selected";
    echo ">" . $row['title'] . "</option>\n";
  }
  echo "   </select>\n";
  echo "   <br /><span class='bold'>Return to Play:</span><br>\n";
  echo "   <input type='text' size='10' name='form_userdate1' id='form_userdate1' " .
    "value='$form_userdate1' " .
    "title='" . xl('yyyy-mm-dd Date of return to play') . "' " .
    "onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />\n" .
    "   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' " .
    "id='img_userdate1' border='0' alt='[?]' style='cursor:pointer' " .
    "title='" . xl('Click here to choose a date') . "'>\n";
  echo "   <input type='hidden' name='form_original_userdate1' value='$form_userdate1' />\n";
  echo "   <input type='hidden' name='form_issue_id' value='' />\n";
  echo "<p><input type='submit' name='form_submit' value='Change' /></p>\n";
  echo "   </form>\n";
}

if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
  // Show current balance and billing note, if any.
  echo "<span class='bold'><font color='#ee6600'>" . xl('Balance Due') . ": " . xl('$') .
    get_patient_balance($pid) . "</font><br />";
  if ($result['genericname2'] == 'Billing') {
    xl('Billing Note') . ":";
    echo "<span class='bold'><font color='red'>" .
      $result['genericval2'] . "</font></span>";
  }
  echo "</span><br />";
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
  "e.pc_pid = '$pid' AND e.pc_eventDate >= CURRENT_DATE AND " .
  "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
  "ORDER BY e.pc_eventDate, e.pc_startTime";
 $res = sqlStatement($query);

 if (isset($res) && $res != null) {
    echo "<div id='appts'><span id='apptstitle'>" . xl('Upcoming Appointments') . "</span><br>";
    // link to create a new appointment
    echo "<span class='link' style='margin: 2px 0px 5px 0px; width:100%; text-align: center;'>";
    echo "<a href='#' onclick='return newEvt()'>".xl('New Appointment')."</a>";
    echo "</span><br>";
 }
 while($row = sqlFetchArray($res)) {
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
    $etitle = xl('Comments').": ".addslashes($row['pc_hometext'])."\r\n".$etitle;
  }
  echo "<a href='javascript:oldEvt(" . $row['pc_eid'] .  ")' title='$etitle'>";
  echo "<b>" . xl($dayname) . ", " . $row['pc_eventDate'] . "</b><br>";
  echo "$disphour:$dispmin " . xl($dispampm) . " " . xl_appt_category($row['pc_catname']) . "<br>\n";
  echo $row['fname'] . " " . $row['lname'] . "</a><br>&nbsp;<br>\n";
 }
 if (isset($res) && $res != null) { echo "</div>"; }
}
?>
  </td>

 </tr>
</table>

<?php if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) { ?>
<script language='JavaScript'>
 top.window.parent.left_nav.setPatient(<?php echo "'" . htmlspecialchars(addslashes($result['fname']) . " " . addslashes($result['lname'])) . "',$pid,'" . addslashes($result['pubpid']) . "','', ' ".xl('DOB').": ".$result['DOB_YMD']." ".xl('Age').": ".getPatientAge($result['DOB_YMD'])."'"; ?>);
 parent.left_nav.setRadio(window.name, 'dem');
<?php if (!$_GET['is_new']) { // if new pt, do not load other frame ?>
 var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
 parent.left_nav.forceDual();
 parent.left_nav.setRadio(othername, 'sum');
 parent.left_nav.loadFrame('sum1', othername, 'patient_file/summary/summary_bottom.php');
<?php } ?>
</script>
<?php } ?>

<?php if ($GLOBALS['athletic_team']) { ?>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_userdate1", ifFormat:"%Y-%m-%d", button:"img_userdate1"});
</script>
<?php } ?>

</body>
</html>
