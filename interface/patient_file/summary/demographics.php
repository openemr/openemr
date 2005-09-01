<?
 include_once("../../globals.php");
 include_once("$srcdir/patient.inc");
 include_once("$srcdir/acl.inc");
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
  echo "<p>(Demographics not authorized)</p>\n";
  echo "</body>\n</html>\n";
  exit();
 }

 if ($thisauth == 'write') {
  echo "<p><a href='demographics_full.php' target='Main'>" .
   "<font class='title'>Demographics</font>" .
   "<font class='more'>$tmore</font></a></p>\n";
 }
?>

<table border="0" width="100%">
 <tr>
  <td align="left" valign="top">
   <table border='0' cellpadding='0' width='100%'>
    <tr>
     <td valign='top'>
      <span class='bold'>Name: </span><span class='text'><?echo $result{"title"}?> <?echo $result{"fname"}?> <?echo $result{"mname"}?> <?echo $result{"lname"}?></span><br>
      <span class='bold'>Number: </span><span class='text'><?echo $result{"pubpid"}?></span>
     </td>
     <td valign='top'>
<?
 if ($result{"DOB"} && $result{"DOB"} != "0000-00-00") {
?>
      <span class='bold'>DOB: </span>
      <span class='text'>
<?
  echo $result{"DOB"};
 }
?>
      </span>
     </td>
     <td valign='top'><? if ($result{"sex"} != ""){?><span class='bold'>Sex: </span><?}?><span class='text'><?echo $result{"sex"}?></span></td>
     <td valign='top'><? if ($result{"ss"} != "") {?><span class='bold'>S.S.: </span><?}?><span class='text'><?echo $result{"ss"}?></span></td>
    </tr>
    <tr>
     <td valign='top'>
<? if (($result{"street"} != "") || ($result{"city"} != "") || ($result{"state"} != "") || ($result{"country_code"} != "") || ($result{"postal_code"} != "")) {?>
      <span class='bold'>Address: </span>
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
      <span class='bold'>Emergency Contact: </span><?}?><span class='text'><?echo $result{"contact_relationship"}?><?echo " "?>
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
		echo "<br>Email: </span>";
		echo '<a class=link_submit href="mailto:' . $result{"email"} . '">' . $result{"email"} . '</a>';
	}
?>
     </td>
     <td valign='top'>
<?
	if ($result{"status"} != "") {
		echo "<span class='bold'>Marital Status: </span>";
		echo "<span class='text'>" .  $result{"status"} . "</span>";
	}
?>
     </td>
     <td valign='top'></td>
    </tr>

<? if (! $GLOBALS['athletic_team']) { ?>
    <tr>
     <td colspan='4' valign='top'>
	<? 
		$result{"hipaa_mail"}=='NO' ? $opt_out='DOES NOT ALLOW' : $opt_out='ALLOWS' ;
		echo "<span class='text'>Patient $opt_out Mailed Information </span>";
	?>
     </td>
    </tr>
    <tr>
     <td colspan='4' valign='top'>
	<? 
		$result{"hipaa_voice"}=='NO' ? $opt_out='DOES NOT ALLOW' : $opt_out='ALLOWS' ;
		echo "<span class='text'>Patient $opt_out Voice Messages </span>";
	?>
     </td>
    </tr>
<? } else { ?>
    <tr>
     <td colspan='4' valign='top'>
      &nbsp;
     </td>
    </tr>
<? } ?>

    <tr>
     <td valign='top'>
<? if ($result{"occupation"} != "") {?><span class='bold'>Occupation: </span><span class='text'><?echo $result{"occupation"}?></span><br><?}?>
<? if ($result2{"name"} != "") {?><span class='bold'>Employer: </span><span class='text'><?echo $result2{"name"}?></span><?}?>
     </td>
     <td valign='top'>
<? if (($result2{"street"} != "") || ($result2{"city"} != "") || ($result2{"state"} != "") || ($result2{"country"} != "") || ($result2{"postal_code"} != "")) {?>
      <span class='bold'>Employer Address:</span>
<? } ?>
      <br>
      <span class='text'>
<?echo $result2{"street"}?><br><?echo $result2{"city"}?><?if($result2{"city"} != ""){echo ", ";}?><?echo $result2{"state"}?>
<?if($result2{"country"} != ""){echo ", ";}?><?echo $result2{"country"}?>
<?if($result2{"postal_code"} != ""){echo " ";}?>
<?echo $result2{"postal_code"}?>
      </span>
     </td>
     <td valign='top'>
<?
 // This stuff only applies to athletic team use of OpenEMR:
 if ($GLOBALS['athletic_team']) {
  $fitcolors = array('#00ff00', '#ffff00', '#ff8800', '#ff3333');
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
      <span class='bold'>Fitness to Play:</span><br>
      <select name='form_fitness' onchange='document.forms[0].submit()' style='background-color:<? echo $fitcolor ?>'>
       <option value='1'<? if ($fitness == 1) echo ' selected' ?>>Full Play</option>
       <option value='2'<? if ($fitness == 2) echo ' selected' ?>>Full Training</option>
       <option value='3'<? if ($fitness == 3) echo ' selected' ?>>Restricted Training</option>
       <option value='4'<? if ($fitness == 4) echo ' selected' ?>>Injured Out</option>
      </select>
      </form>
<? } ?>
     </td>
     <td valign='top'></td>
    </tr>
    <tr>
     <td valign='top'>
<? if (! $GLOBALS['athletic_team']) { ?>
<? if ($result{"ethnoracial"} != "")  { ?><span class='bold'>Race/Ethnicity: </span><span class='text'><?echo $result{"ethnoracial"};?></span><br><? } ?>
<? if ($result{"language"} != "")     { ?><span class='bold'>Language: </span><span class='text'><?echo ucfirst($result{"language"});?></span><br><? } ?>
<? if ($result{"interpretter"} != "") { ?><span class='bold'>Interpreter: </span><span class='text'><?echo $result{"interpretter"};?></span><br><? } ?>
<? if ($result{"family_size"} != "")  { ?><span class='bold'>Family Size: </span><span class='text'><?echo $result{"family_size"};?></span><br><? } ?>
<? } ?>
     </td>
     <td valign='top'>
<?
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
<? if (! $GLOBALS['athletic_team']) { ?>
<? if ($result{"financial_review"} != "0000-00-00 00:00:00") {?><span class='bold'>Financial Review Date: </span><span class='text'><?echo date("n/j/Y",strtotime($result{"financial_review"}));?></span><br><?}?>
<? if ($result{"monthly_income"} != "") {?><span class='bold'>Monthly Income: </span><span class='text'><?echo print_as_money($result{"monthly_income"});?></span><br><?}?>
<? if ($result{"migrantseasonal"} != "") {?><span class='bold'>Migrant/Seasonal: </span><span class='text'><?echo $result{"migrantseasonal"};?></span><br><?}?>
<? if ($result{"homeless"} != "") {?><span class='bold'>Homeless, etc.: </span><span class='text'><?echo $result{"homeless"};?></span><br><?}?>
<? } ?>
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
<?php
//////////////////////////////////REFERRAL SECTION
if ($result{"referrer"} != "" || $result{"referrerID"} != "")
{
?>
    <tr>
     <td valign='top'>
      <span class='bold'>Primary Provider: </span><span class='text'><?=getProviderName($result['providerID'])?></span><br>
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
      <span class='bold'>Primary Insurance Provider:</span><br><span class='text'><?echo $result3{"provider_name"}?></span><br>
      <span class='text'>Policy Number: <?echo $result3{"policy_number"}?><br>
      Plan Name: <?=$result3{"plan_name"}?><br>
      Group Number: <?echo $result3{"group_number"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber: </span><br><span class='text'><?=$result3{"subscriber_fname"}?> <?=$result3{"subscriber_mname"}?> <?=$result3{"subscriber_lname"}?> <?if ($result3{"subscriber_relationship"} != "") {echo "(".$result3{"subscriber_relationship"}.")";}?><br>
      S.S.: <?echo $result3{"subscriber_ss"}?> D.O.B.: <?if ($result3{"subscriber_DOB"} != "0000-00-00 00:00:00") {echo $result3{"subscriber_DOB"};}?><br>
      Phone: <? echo $result3{"subscriber_phone"}?>
      </span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber Address: </span><br><span class='text'><?echo $result3{"subscriber_street"}?><br><?echo $result3{"subscriber_city"}?><?if($result3{"subscriber_state"} != ""){echo ", ";}?><?echo $result3{"subscriber_state"}?><?if($result3{"subscriber_country"} != ""){echo ", ";}?><?echo $result3{"subscriber_country"}?> <?echo " ".$result3{"subscriber_postal_code"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber Employer: </span><br><span class='text'><?echo $result3{"subscriber_employer"}?><br><?echo $result3{"subscriber_employer_street"}?><br><?echo $result3{"subscriber_employer_city"}?><?if($result3{"subscriber_employer_city"} != ""){echo ", ";}?><?echo $result3{"subscriber_employer_state"}?><?if($result3{"subscriber_employer_country"} != ""){echo ", ";}?><?echo $result3{"subscriber_employer_country"}?> <?echo " ".$result3{"subscriber_employer_postal_code"}?></span>
     </td>
    </tr>
    <tr>
     <td><? if ($result3{"copay"} != "") {?><span class='bold'>CoPay: </span><span class='text'><?=$result3{"copay"}?></span><?}?></td>
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
      <span class='bold'>Secondary Insurance Provider:</span><br><span class='text'><?echo $result4{"provider_name"}?></span><br>
      <span class='text'>Policy Number: <?echo $result4{"policy_number"}?><br>
      Plan Name: <?=$result4{"plan_name"}?><br>
      Group Number: <?echo $result4{"group_number"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber: </span><br><span class='text'><?=$result4{"subscriber_fname"}?> <?=$result4{"subscriber_mname"}?> <?=$result4{"subscriber_lname"}?> <?if ($result4{"subscriber_relationship"} != "") {echo "(".$result4{"subscriber_relationship"}.")";}?><br>
      S.S.: <?echo $result4{"subscriber_ss"}?> D.O.B.: <?if ($result4{"subscriber_DOB"} != "0000-00-00 00:00:00") {echo $result4{"subscriber_DOB"};}?><br>
      Phone: <? echo $result4{"subscriber_phone"}?>
      </span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber Address: </span><br><span class='text'><?echo $result4{"subscriber_street"}?><br><?echo $result4{"subscriber_city"}?><?if($result4{"subscriber_state"} != ""){echo ", ";}?><?echo $result4{"subscriber_state"}?><?if($result4{"subscriber_country"} != ""){echo ", ";}?><?echo $result4{"subscriber_country"}?> <?echo " ".$result4{"subscriber_postal_code"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber Employer: </span><br><span class='text'><?echo $result4{"subscriber_employer"}?><br><?echo $result4{"subscriber_employer_street"}?><br><?echo $result4{"subscriber_employer_city"}?><?if($result4{"subscriber_employer_city"} != ""){echo ", ";}?><?echo $result4{"subscriber_employer_state"}?><?if($result4{"subscriber_employer_country"} != ""){echo ", ";}?><?echo $result4{"subscriber_employer_country"}?> <?echo " ".$result4{"subscriber_employer_postal_code"}?></span>
     </td>
    </tr>
    <tr>
     <td>
      <? if ($result4{"copay"} != "") {?><span class='bold'>CoPay: </span><span class='text'><?=$result4{"copay"}?></span><?}?>
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
      <span class='bold'>Tertiary Insurance Provider:</span><br><span class='text'><?echo $result5{"provider_name"}?></span><br>
      <span class='text'>Policy Number: <?echo $result5{"policy_number"}?><br>
      Plan Name: <?=$result5{"plan_name"}?><br>
      Group Number: <?echo $result5{"group_number"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber: </span><br><span class='text'><?=$result5{"subscriber_fname"}?> <?=$result5{"subscriber_mname"}?> <?=$result5{"subscriber_lname"}?> <?if ($result5{"subscriber_relationship"} != "") {echo "(".$result5{"subscriber_relationship"}.")";}?><br>
      S.S.: <?echo $result5{"subscriber_ss"}?> D.O.B.: <?if ($result5{"subscriber_DOB"} != "0000-00-00 00:00:00") {echo $result5{"subscriber_DOB"};}?><br>
      Phone: <? echo $result5{"subscriber_phone"}?>
      </span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber Address: </span><br><span class='text'><?echo $result5{"subscriber_street"}?><br><?echo $result5{"subscriber_city"}?><?if($result5{"subscriber_state"} != ""){echo ", ";}?><?echo $result5{"subscriber_state"}?><?if($result5{"subscriber_country"} != ""){echo ", ";}?><?echo $result5{"subscriber_country"}?> <?echo " ".$result5{"subscriber_postal_code"}?></span>
     </td>
     <td valign='top'>
      <span class='bold'>Subscriber Employer: </span><br><span class='text'><?echo $result5{"subscriber_employer"}?><br><?echo $result5{"subscriber_employer_street"}?><br><?echo $result5{"subscriber_employer_city"}?><?if($result5{"subscriber_employer_city"} != ""){echo ", ";}?><?echo $result5{"subscriber_employer_state"}?><?if($result5{"subscriber_employer_country"} != ""){echo ", ";}?><?echo $result5{"subscriber_employer_country"}?> <?echo " ".$result5{"subscriber_employer_postal_code"}?></span>
     </td>
    </tr>
    <tr>
     <td>
      <? if ($result5{"copay"} != "") {?><span class='bold'>CoPay: </span><span class='text'><?=$result5{"copay"}?></span><?}?>
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

</body>
</html>
