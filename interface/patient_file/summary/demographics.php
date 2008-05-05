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

function get_patient_balance($pid) {
	require_once($GLOBALS['fileroot'] . "/library/classes/WSWrapper.class.php");
	$conn = $GLOBALS['adodb']['db'];
	$customer_info['id'] = 0;
	$sql = "SELECT foreign_id FROM integration_mapping AS im " .
		"LEFT JOIN patient_data AS pd ON im.local_id = pd.id WHERE " .
		"pd.pid = '" . $pid . "' AND im.local_table = 'patient_data' AND " .
		"im.foreign_table = 'customer'";
	$result = $conn->Execute($sql);
	if($result && !$result->EOF) {
		$customer_info['id'] = $result->fields['foreign_id'];
	}
	$function['ezybiz.customer_balance'] = array(new xmlrpcval($customer_info,"struct"));
	$ws = new WSWrapper($function);
	if(is_numeric($ws->value)) {
		return sprintf('%01.2f', $ws->value);
	}
	return '';
}

?>
<html>

<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language="JavaScript">

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

</script>
</head>

<body class="body_top">

<?php
 $result = getPatientData($pid);
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
  echo "<p><a href='demographics_full.php'";
  if (! $GLOBALS['concurrent_layout']) echo " target='Main'";
  echo " onclick='top.restoreSession()'><font class='title'>" .
   xl('Demographics') . "</font>" .
   "<font class='more'>$tmore</font></a>";
  if (acl_check('admin', 'super')) {
   echo "&nbsp;&nbsp;<a href='' onclick='return deleteme()'>" .
    "<font class='more' style='color:red'>(".xl('Delete').")</font></a>";
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
   <table border='0' cellpadding='0'>

<?php

$CPR = 4; // cells per row of generic data

function end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo "</td>";
    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $CPR;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $CPR; ++$cell_count) echo "<td></td>";
    echo "</tr>\n";
    $cell_count = 0;
  }
}

function end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    end_row();
  }
}

$fres = sqlStatement("SELECT * FROM layout_options " .
  "WHERE form_id = 'DEM' AND uor > 0 " .
  "ORDER BY group_name, seq");
$last_group = '';
$cell_count = 0;
$item_count = 0;

while ($frow = sqlFetchArray($fres)) {
  $this_group = $frow['group_name'];
  $titlecols  = $frow['titlecols'];
  $datacols   = $frow['datacols'];
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $currvalue  = '';
  if (strpos($field_id, 'em_') === 0) {
    $tmp = substr($field_id, 3);
    if (isset($result2[$tmp])) $currvalue = $result2[$tmp];
  }
  else {
    if (isset($result[$field_id])) $currvalue = $result[$field_id];
  }

  // Handle a data category (group) change.
  if (strcmp($this_group, $last_group) != 0) {
    end_group();
    $group_name = substr($this_group, 1);
    $last_group = $this_group;
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $CPR) || $cell_count == 0) {
    end_row();
    echo "  <tr><td class='bold' style='padding-right:5pt'>";
    if ($group_name) {
      echo "<font color='#008800'>$group_name</font>";
      $group_name = '';
    } else {
      echo '&nbsp;';
    }
    echo "</td>";
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td class='bold' colspan='$titlecols'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo ">";
    $cell_count += $titlecols;
  }
  ++$item_count;

  if ($frow['title']) echo $frow['title'] . ":"; else echo "&nbsp;";

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td colspan='$datacols' class='text'";
    if ($cell_count > 0) echo " style='padding-left:5pt'";
    echo ">";
    $cell_count += $datacols;
  }

  ++$item_count;
  echo generate_display_field($frow, $currvalue);
}

end_group();

echo "   </table>\n";
echo "   <table border='0' cellpadding='0' width='100%'>\n";

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
if ($GLOBALS['oer_config']['ws_accounting']['enabled']) {
  // Show current balance and billing note, if any.
  echo "<span class='bold'><font color='#ee6600'>Balance Due: $" .
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
  echo "<a href='/".$web_root."/controller.php?document&retrieve" .
    "&patient_id=$pid&document_id=$document_id' style='color:#00cc00' " .
    "onclick='top.restoreSession()'>Click for ID card</a><br />";
}

// Show current and upcoming appointments.
if (isset($pid)) {
 $query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
  "e.pc_startTime, u.fname, u.lname, u.mname " .
  "FROM openemr_postcalendar_events AS e, users AS u WHERE " .
  "e.pc_pid = '$pid' AND e.pc_eventDate >= CURRENT_DATE AND " .
  "u.id = e.pc_aid " .
  "ORDER BY e.pc_eventDate, e.pc_startTime";
 $res = sqlStatement($query);
 while($row = sqlFetchArray($res)) {
  $dayname = date("l", strtotime($row['pc_eventDate']));
  $dispampm = "am";
  $disphour = substr($row['pc_startTime'], 0, 2) + 0;
  $dispmin  = substr($row['pc_startTime'], 3, 2);
  if ($disphour >= 12) {
   $dispampm = "pm";
   if ($disphour > 12) $disphour -= 12;
  }
  echo "<a href='javascript:oldEvt(" . $row['pc_eid'] .
       ")'><b>$dayname " . $row['pc_eventDate'] . "</b><br>";
  echo "$disphour:$dispmin $dispampm " . $row['pc_title'] . "<br>\n";
  echo $row['fname'] . " " . $row['lname'] . "</a><br>&nbsp;<br>\n";
 }
}
?>
  </td>


 </tr>
</table>

<?php if ($GLOBALS['concurrent_layout'] && $_GET['set_pid']) { ?>
<script language='JavaScript'>
 parent.left_nav.setPatient(<?php echo "'" . $result['fname'] . " " . $result['lname'] . "',$pid,''"; ?>);
 parent.left_nav.setRadio(window.name, 'dem');
<?php if (!$_GET['is_new']) { // if new pt, do not load other frame ?>
 var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
 parent.left_nav.setRadio(othername, 'sum');
 parent.left_nav.loadFrame('sum1', othername, 'patient_file/summary/summary_bottom.php');
<?php } ?>
</script>
<?php } ?>
<?php
$patient_pics = pic_array();
foreach ($patient_pics as $var) {
  print $var;
}
?>
</body>
</html>
