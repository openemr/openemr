<?
 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");

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
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<script type="text/javascript" src="../../../library/dialog.js"></script>
<script language="JavaScript">

 function oldEvt(eventid) {
  dlgopen('../../main/calendar/add_edit_event.php?eid=' + eventid, '_blank', 550, 270);
 }

 function refreshme() {
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
  top.location.href = '../main/main_screen.php';
<?php } ?>
 }

</script>
</head>

<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<?
 $result = getPatientData($pid);
 $result2 = getEmployerData($pid);

 $thisauth = acl_check('patients', 'demo');
 if ($thisauth) {
  if ($result['squad'] && ! acl_check('squads', $result['squad']))
   $thisauth = 0;
 }

 if (!$thisauth) {
  echo "<p>(".xl('Demographics not authorized').")</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 if ($thisauth == 'write') {
  echo "<p><a href='demographics_full.php'";
   if (! $GLOBALS['concurrent_layout']) echo " target='Main'";
   echo "><font class='title'>" . xl('Demographics') . "</font>" .
   "<font class='more'>$tmore</font></a>";
  if (acl_check('admin', 'super')) {
   echo "&nbsp;&nbsp;<a href='' onclick='return deleteme()'>" .
    "<font class='more' style='color:red'>(".xl('Delete').")</font></a>";
  }
  echo "</p>\n";
 }
?>

<table border="0" width="100%">
 <tr>
  <td align="left" valign="top">
   <table border='0' cellpadding='0' width='100%'>
    <tr>
     <td valign='top'>
      <span class='bold'><? xl('Name','e'); ?>: </span><span class='text'><?echo $result{"title"}?> <?echo $result{"fname"}?> <?echo $result{"mname"}?> <?echo $result{"lname"}?></span><br>
      <span class='bold'><? xl('Number','e'); ?>: </span><span class='text'><?echo $result{"pubpid"}?></span>
     </td>
     <td valign='top'>
<?
 if ($result{"DOB"} && $result{"DOB"} != "0000-00-00") {
?>
      <span class='bold'><? xl('DOB','e'); ?>: </span>
      <span class='text'>
<?
  echo $result{"DOB"};
 }
?>
      </span>
     </td>
     <td valign='top'><? if ($result{"sex"} != ""){?><span class='bold'><? xl('Sex','e'); ?>: </span><?}?><span class='text'><?echo $result{"sex"}?></span></td>
     <td valign='top'><? if ($result{"ss"} != "") {?><span class='bold'><? xl('S.S.','e'); ?>: </span><?}?><span class='text'><?echo $result{"ss"}?></span></td>
    </tr>
    <tr>
     <td valign='top'>
<? if (($result{"street"} != "") || ($result{"city"} != "") || ($result{"state"} != "") || ($result{"country_code"} != "") || ($result{"postal_code"} != "")) {?>
      <span class='bold'><? xl('Address','e'); ?>: </span>
<?}?>
      <br><span class='text'><?echo $result{"street"}?><br><?echo $result{"city"}?><?if($result{"city"} != ""){echo ", ";}?><?echo $result{"state"};?>
<? if($result{"country_code"} != ""){ echo ", "; }?><?echo $result{"country_code"}?>
<?echo " ";
echo $result{"postal_code"}?>
      </span>
     </td>
     <td valign='top'>
<?
	if (	($result{"contact_relationship"} != "") ||
		($result{"phone_contact"} != "") ||
		($result{"phone_home"} != "") ||
		($result{"phone_biz"} != "") ||
		($result{"email"} != "")  ||
		($result{"phone_cell"} != "")    ){
?>
      <span class='bold'><? xl('Emergency Contact','e'); ?>: </span><?}?><span class='text'><?echo $result{"contact_relationship"}?><?echo " "?>
<?
	if ($result{"phone_contact"} != "") {
		echo " " . $result{"phone_contact"};
	}
	if ($result{"phone_home"} != "") {
		echo "<br>Home: ";
		echo $result{"phone_home"};
	}
	if ($result{"phone_biz"} != "") {
		echo "<br>Work: ";
		echo $result{"phone_biz"};
	}
	if ($result{"phone_cell"} != "") {
		echo "<br>Mobile: ";
		echo $result{"phone_cell"};
	}
	if ($result{"email"} != "") {
		echo "<br>".xl('Email').": </span>";
		echo '<a class=link_submit href="mailto:' . $result{"email"} . '">' . $result{"email"} . '</a>';
	}
?>
     </td>
     <td valign='top'>
<?
	if ($result{"status"} != "") {
		echo "<span class='bold'>".xl('Marital Status').": </span>";
		echo "<span class='text'>" .  $result{"status"} . "</span>";
	}
?>
     </td>
     <td valign='top'></td>
    </tr>

<?php if (!$GLOBALS['athletic_team']) { ?>
    <tr>
     <td colspan='4' valign='top'>
	<?php
		$opt_out = ($result{"hipaa_mail"} == 'YES') ? 'ALLOWS' : 'DOES NOT ALLOW';
		echo "<span class='text'>Patient $opt_out Mailed Information </span>";
	?>
     </td>
    </tr>
    <tr>
     <td colspan='2' valign='top'>
	<?php
		$opt_out = ($result{"hipaa_voice"} == 'YES') ? 'ALLOWS' : 'DOES NOT ALLOW';
		echo "<span class='text'>Patient $opt_out Voice Messages </span>";
	?>
     </td>
     <td colspan='2' valign='top'>
	<?php if ($result['genericname2'] == 'Billing') echo "<span class='bold'>" . xl('Billing Note') . ":</span>"; ?>
     </td>
    </tr>
    <tr>
     <td colspan='2' valign='top'>
	<?php
		$opt_out = ($result{"hipaa_notice"} == 'YES') ? 'RECEIVED' : 'DID NOT RECEIVE';
		echo "<span class='text'>Patient $opt_out Notice Information </span>";
	?>
     </td>
     <td colspan='2' valign='top'>
	<?php if ($result['genericname2'] == 'Billing') echo "<span class='bold'><font color='red'>" . $result['genericval2'] . "</font></span>"; ?>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top'>
	<?php
		if ( $result["hipaa_message"] == "" ) {
			echo "<span class='text'><b>Leave a message with :</b> " .
				$result{"fname"} . " " . $result{"mname"} . " " .
				$result{"lname"} . "</span>";
		}
		else {
			echo "<span class='text'><b>Leave a message with :</b> " .
				$result{"hipaa_message"} . "</span>";
		}
	?>
     </td>
    </tr>

<?php } else { ?>
    <tr>
     <td colspan='4' valign='top'>
      &nbsp;
     </td>
    </tr>
<?php } ?>

<?php if ($GLOBALS['omit_employers']) { /////////////////////////////////// ?>

    <tr>
     <td valign='top'>
      <table>
       <tr>
        <td><span class='bold'>Listed Family Members:</span></td><td>&nbsp;</td>
       </tr>
       <tr>
        <td><?php if ($result{"genericname1"} != "") { ?><span class='text'>&nbsp;&nbsp;&nbsp;<?=$result{"genericname1"}?></span><?php } ?></td>
        <td><?php if ($result{"genericval1"} != "") { ?><span class='text'>&nbsp;&nbsp;&nbsp;<?=$result{"genericval1"}?></span><?php } ?></td>
       </tr>
       <tr>
        <td><?php if ($result{"genericname2"} != "") { ?><span class='text'>&nbsp;&nbsp;&nbsp;<?=$result{"genericname2"}?></span><?php } ?></td>
        <td><?php if ($result{"genericval2"} != "") { ?><span class='text'>&nbsp;&nbsp;&nbsp;<?=$result{"genericval2"}?></span><?php } ?></td>
       </tr>
      </table>
     </td>
     <td valign='top'></td>
    </tr>

<?php } else { ///// end omit_employers ///// ?>

    <tr>
     <td valign='top'>
<?php if ($result{"occupation"} != "") {?><span class='bold'><? xl('Occupation','e'); ?>: </span><span class='text'><?echo $result{"occupation"}?></span><br><?}?>
<?php if ($result2{"name"} != "") {?><span class='bold'><? xl('Employer','e'); ?>: </span><span class='text'><?echo $result2{"name"}?></span><?}?>
     </td>
     <td valign='top'>
<?php if (($result2{"street"} != "") || ($result2{"city"} != "") || ($result2{"state"} != "") || ($result2{"country"} != "") || ($result2{"postal_code"} != "")) {?>
      <span class='bold'><? xl('Employer Address','e'); ?>:</span>
<?php } ?>
      <br>
      <span class='text'>
<?echo $result2{"street"}?><br><?echo $result2{"city"}?><?if($result2{"city"} != ""){echo ", ";}?><?echo $result2{"state"}?>
<?if($result2{"country"} != ""){echo ", ";}?><?echo $result2{"country"}?>
<?if($result2{"postal_code"} != ""){echo " ";}?>
<?echo $result2{"postal_code"}?>
      </span>
     </td>
     <td valign='top'>
<?php
 // This stuff only applies to athletic team use of OpenEMR:
 if ($GLOBALS['athletic_team']) {
  //                  blue       dk green   yellow     red        orange
  $fitcolors = array('#6677ff', '#00cc00', '#ffff00', '#ff3333', '#ff8800', '#ffeecc', '#ffccaa');
  $fitcolor = $fitcolors[0];
  $fitness = $_POST['form_fitness'];
  if ($fitness) {
   sqlStatement("UPDATE patient_data SET fitness = '$fitness' WHERE pid = '$pid'");
  } else {
   $fitness = $result['fitness'];
   if (! $fitness) $fitness = 1;
  }
  $fitcolor = $fitcolors[$fitness - 1];
?>
      <form method='post' action='demographics.php'>
      <span class='bold'><? xl('Fitness to Play','e'); ?>:</span><br>
      <select name='form_fitness' onchange='document.forms[0].submit()' style='background-color:<? echo $fitcolor ?>'>
       <option value='1'<? if ($fitness == 1) echo ' selected' ?>><? xl('Full Play','e'); ?></option>
       <option value='2'<? if ($fitness == 2) echo ' selected' ?>><? xl('Full Training','e'); ?></option>
       <option value='3'<? if ($fitness == 3) echo ' selected' ?>><? xl('Restricted Training','e'); ?></option>
       <option value='4'<? if ($fitness == 4) echo ' selected' ?>><? xl('Injured Out','e'); ?></option>
       <option value='5'<? if ($fitness == 5) echo ' selected' ?>><? xl('Rehabilitation','e'); ?></option>
       <option value='6'<? if ($fitness == 6) echo ' selected' ?>><? xl('Illness','e'); ?></option>
       <option value='7'<? if ($fitness == 7) echo ' selected' ?>><? xl('International Duty','e'); ?></option>
      </select>
      </form>
<?php } // end athletic team ?>
     </td>
     <td valign='top'></td>
    </tr>
    <tr>
     <td valign='top'>
<?php if (! $GLOBALS['athletic_team']) { ?>
<?php if ($result{"ethnoracial"} != "")  { ?><span class='bold'><? xl('Race/Ethnicity','e'); ?>: </span><span class='text'><?echo $result{"ethnoracial"};?></span><br><? } ?>
<?php if ($result{"language"} != "")     { ?><span class='bold'><? xl('Language','e'); ?>: </span><span class='text'><?echo ucfirst($result{"language"});?></span><br><? } ?>
<?php if ($result{"interpretter"} != "") { ?><span class='bold'><? xl('Interpreter','e'); ?>: </span><span class='text'><?echo $result{"interpretter"};?></span><br><? } ?>
<?php if ($result{"family_size"} != "")  { ?><span class='bold'><? xl('Family Size','e'); ?>: </span><span class='text'><?echo $result{"family_size"};?></span><br><? } ?>
<?php } ?>
     </td>
     <td valign='top'>
<?php if (! $GLOBALS['athletic_team']) { ?>
<?php if ($result{"financial_review"} != "0000-00-00 00:00:00") {?><span class='bold'><? xl('Financial Review Date','e'); ?>: </span><span class='text'><?echo date("n/j/Y",strtotime($result{"financial_review"}));?></span><br><?}?>
<?php if ($result{"monthly_income"} != "") {?><span class='bold'><? xl('Monthly Income','e'); ?>: </span><span class='text'><?echo print_as_money($result{"monthly_income"});?></span><br><?}?>
<?php if ($result{"migrantseasonal"} != "") {?><span class='bold'><? xl('Migrant/Seasonal','e'); ?>: </span><span class='text'><?echo $result{"migrantseasonal"};?></span><br><?}?>
<?php if ($result{"homeless"} != "") {?><span class='bold'><? xl('Homeless, etc','e'); ?>.: </span><span class='text'><?echo $result{"homeless"};?></span><br><?}?>
<?php } ?>
     </td>
     <td valign='top'>
      <table>
       <tr>
        <td><? if ($result{"genericname1"} != "") {?><span class='bold'><?=$result{"genericname1"}?></span>:<?}?> </td>
        <td><? if ($result{"genericval1"} != "") {?><span class='text'><?=$result{"genericval1"}?></span><?}?></td>
       </tr>
       <tr>
        <td><? if ($result{"genericname2"} != "") {?><span class='bold'><?=$result{"genericname2"}?></span>:<?}?> </td>
        <td><? if ($result{"genericval2"} != "") {?><span class='text'><?=$result{"genericval2"}?></span><?}?></td>
       </tr>
      </table>
     </td>
     <td valign='top'></td>
    </tr>

<?php } ///// end not omit_employers ///// ?>

<?php
//////////////////////////////////REFERRAL SECTION
if ($result{"referrer"} != "" || $result{"referrerID"} != "")
{
?>
    <tr>
     <td valign='top'>
      <span class='bold'><? xl('Primary Provider','e'); ?>: </span><span class='text'><?=getProviderName($result['providerID'])?></span><br>
      <!--<span class='bold'>Primary Provider ID: </span><span class='text'><?=$result{"referrerID"}?></span>-->
     </td>
     <td valign='top'></td>
     <td valign='top'></td>
     <td valign='top'></td>
    </tr>
<?php
}

/////////////////////////////////INSURANCE SECTION
$result3 = getInsuranceData($pid, "primary");
if ($result3{"provider"}) {
?>
    <tr>
     <td valign='top'>
      <span class='bold'><? xl('Primary Insurance Provider','e'); ?>:</span><br><span class='text'><?echo $result3{"provider_name"}?></span><br>
      <span class='text'><? xl('Policy Number','e'); ?>: <?echo $result3{"policy_number"}?><br>
      Plan Name: <?=$result3{"plan_name"}?><br>
      Group Number: <?echo $result3{"group_number"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber','e'); ?>: </span><br><span class='text'><?=$result3{"subscriber_fname"}?> <?=$result3{"subscriber_mname"}?> <?=$result3{"subscriber_lname"}?> <?if ($result3{"subscriber_relationship"} != "") {echo "(".$result3{"subscriber_relationship"}.")";}?><br>
      S.S.: <?echo $result3{"subscriber_ss"}?> <? xl('D.O.B.','e'); ?>: <?if ($result3{"subscriber_DOB"} != "0000-00-00 00:00:00") {echo $result3{"subscriber_DOB"};}?><br>
      Phone: <? echo $result3{"subscriber_phone"}?>
      </span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber Address','e'); ?>: </span><br><span class='text'><?echo $result3{"subscriber_street"}?><br><?echo $result3{"subscriber_city"}?><?if($result3{"subscriber_state"} != ""){echo ", ";}?><?echo $result3{"subscriber_state"}?><?if($result3{"subscriber_country"} != ""){echo ", ";}?><?echo $result3{"subscriber_country"}?> <?echo " ".$result3{"subscriber_postal_code"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber Employer','e'); ?>: </span><br><span class='text'><?echo $result3{"subscriber_employer"}?><br><?echo $result3{"subscriber_employer_street"}?><br><?echo $result3{"subscriber_employer_city"}?><?if($result3{"subscriber_employer_city"} != ""){echo ", ";}?><?echo $result3{"subscriber_employer_state"}?><?if($result3{"subscriber_employer_country"} != ""){echo ", ";}?><?echo $result3{"subscriber_employer_country"}?> <?echo " ".$result3{"subscriber_employer_postal_code"}?></span>
     </td>
    </tr>
    <tr>
     <td><? if ($result3{"copay"} != "") {?><span class='bold'><? xl('CoPay','e'); ?>: </span><span class='text'><?=$result3{"copay"}?></span><?}?></td>
     <td valign='top'></td>
     <td valign='top'></td>
     <td valign='top'></td>
   </tr>
<? } ?>
<?
$result4 = getInsuranceData($pid, "secondary");
if ($result4{"provider"} != "") {
?>
    <tr>
     <td valign='top'>
      <span class='bold'><? xl('Secondary Insurance Provider','e'); ?>:</span><br><span class='text'><?echo $result4{"provider_name"}?></span><br>
      <span class='text'><? xl('Policy Number','e'); ?>: <?echo $result4{"policy_number"}?><br>
      Plan Name: <?=$result4{"plan_name"}?><br>
      Group Number: <?echo $result4{"group_number"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber','e'); ?>: </span><br><span class='text'><?=$result4{"subscriber_fname"}?> <?=$result4{"subscriber_mname"}?> <?=$result4{"subscriber_lname"}?> <?if ($result4{"subscriber_relationship"} != "") {echo "(".$result4{"subscriber_relationship"}.")";}?><br>
      S.S.: <?echo $result4{"subscriber_ss"}?> <? xl('D.O.B.','e'); ?>: <?if ($result4{"subscriber_DOB"} != "0000-00-00 00:00:00") {echo $result4{"subscriber_DOB"};}?><br>
      Phone: <? echo $result4{"subscriber_phone"}?>
      </span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber Address','e'); ?>: </span><br><span class='text'><?echo $result4{"subscriber_street"}?><br><?echo $result4{"subscriber_city"}?><?if($result4{"subscriber_state"} != ""){echo ", ";}?><?echo $result4{"subscriber_state"}?><?if($result4{"subscriber_country"} != ""){echo ", ";}?><?echo $result4{"subscriber_country"}?> <?echo " ".$result4{"subscriber_postal_code"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber Employer','e'); ?>: </span><br><span class='text'><?echo $result4{"subscriber_employer"}?><br><?echo $result4{"subscriber_employer_street"}?><br><?echo $result4{"subscriber_employer_city"}?><?if($result4{"subscriber_employer_city"} != ""){echo ", ";}?><?echo $result4{"subscriber_employer_state"}?><?if($result4{"subscriber_employer_country"} != ""){echo ", ";}?><?echo $result4{"subscriber_employer_country"}?> <?echo " ".$result4{"subscriber_employer_postal_code"}?></span>
     </td>
    </tr>
    <tr>
     <td>
      <? if ($result4{"copay"} != "") {?><span class='bold'><? xl('CoPay','e'); ?>: </span><span class='text'><?=$result4{"copay"}?></span><?}?>
     </td>
     <td valign='top'></td>
     <td valign='top'></td>
     <td valign='top'></td>
    </tr>
<? } ?>
<?
$result5 = getInsuranceData($pid, "tertiary");
if ($result5{"provider"}) {
?>
    <tr>
     <td valign='top'>
      <span class='bold'><? xl('Tertiary Insurance Provider','e'); ?>:</span><br><span class='text'><?echo $result5{"provider_name"}?></span><br>
      <span class='text'><? xl('Policy Number','e'); ?>: <?echo $result5{"policy_number"}?><br>
      Plan Name: <?=$result5{"plan_name"}?><br>
      Group Number: <?echo $result5{"group_number"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber','e'); ?>: </span><br><span class='text'><?=$result5{"subscriber_fname"}?> <?=$result5{"subscriber_mname"}?> <?=$result5{"subscriber_lname"}?> <?if ($result5{"subscriber_relationship"} != "") {echo "(".$result5{"subscriber_relationship"}.")";}?><br>
      S.S.: <?echo $result5{"subscriber_ss"}?> <? xl('D.O.B.','e'); ?>: <?if ($result5{"subscriber_DOB"} != "0000-00-00 00:00:00") {echo $result5{"subscriber_DOB"};}?><br>
      Phone: <? echo $result5{"subscriber_phone"}?>
      </span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber Address','e'); ?>: </span><br><span class='text'><?echo $result5{"subscriber_street"}?><br><?echo $result5{"subscriber_city"}?><?if($result5{"subscriber_state"} != ""){echo ", ";}?><?echo $result5{"subscriber_state"}?><?if($result5{"subscriber_country"} != ""){echo ", ";}?><?echo $result5{"subscriber_country"}?> <?echo " ".$result5{"subscriber_postal_code"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'><? xl('Subscriber Employer','e'); ?>: </span><br><span class='text'><?echo $result5{"subscriber_employer"}?><br><?echo $result5{"subscriber_employer_street"}?><br><?echo $result5{"subscriber_employer_city"}?><?if($result5{"subscriber_employer_city"} != ""){echo ", ";}?><?echo $result5{"subscriber_employer_state"}?><?if($result5{"subscriber_employer_country"} != ""){echo ", ";}?><?echo $result5{"subscriber_employer_country"}?> <?echo " ".$result5{"subscriber_employer_postal_code"}?></span>
     </td>
    </tr>
    <tr>
     <td>
      <? if ($result5{"copay"} != "") {?><span class='bold'><? xl('CoPay','e'); ?>: </span><span class='text'><?=$result5{"copay"}?></span><?}?>
     </td>
     <td valign='top'></td>
     <td valign='top'></td>
     <td valign='top'></td>
    </tr>
<?
}
?>
   </table>
  </td>
  <td valign="top" class="text">
<?php
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
 parent.left_nav.loadFrame(othername, 'patient_file/summary/summary_bottom.php');
<?php } ?>
</script>
<?php } ?>

</body>
</html>
