<?
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
?>
<html>

<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
</head>

<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<p><a href="demographics_full.php" target=Main><font class=title>Demographics</font><font class=more><?echo $tmore;?></font></a></p>

<table border="0" width="100%">
 <tr>
  <td align="left" valign="top">
   <table border='0' cellpadding='0' width='100%'>
<?
$result = getPatientData($pid);
$result2 = getEmployerData($pid);
?>
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
<? if ($result{"ethnoracial"} != "") {?><span class='bold'>Race/Ethnicity: </span><span class='text'><?echo $result{"ethnoracial"};?></span><br><?}?>
<? if ($result{"language"} != "") {?><span class='bold'>Language: </span><span class='text'><?echo ucfirst($result{"language"});?></span><br><?}?>
<? if ($result{"interpretter"} != "") {?><span class='bold'>Interpretter: </span><span class='text'><?echo $result{"interpretter"};?></span><br><?}?>
<? if ($result{"family_size"} != "") {?><span class='bold'>Family Size: </span><span class='text'><?echo $result{"family_size"};?></span><br><?}?>
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
<? if ($result{"financial_review"} != "0000-00-00 00:00:00") {?><span class='bold'>Financial Review Date: </span><span class='text'><?echo date("n/j/Y",strtotime($result{"financial_review"}));?></span><br><?}?>
<? if ($result{"monthly_income"} != "") {?><span class='bold'>Monthly Income: </span><span class='text'><?echo print_as_money($result{"monthly_income"});?></span><br><?}?>
<? if ($result{"migrantseasonal"} != "") {?><span class='bold'>Migrant/Seasonal: </span><span class='text'><?echo $result{"migrantseasonal"};?></span><br><?}?>
<? if ($result{"homeless"} != "") {?><span class='bold'>Homeless, etc.: </span><span class='text'><?echo $result{"homeless"};?></span><br><?}?>
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
  <td align="right" valign="top">
<?php
// I can't believe this crap.  It generates a whole new document with
// <html> tag and everything, and then terminates our script prematurely!
// So I disabled it.  BTW the comments below re postnuke are not mine.
//  -- Rod 2005-06-16
//
if (false && isset($pid)) { // was: if (isset($pid)) {
//postnuke doesn't make it easy to set globals/get/post
//didn't want to use an ifram here so I had to fake a page
//load environment by setting the things that would have
//been passed in the querystring

include_once("$srcdir/calendar.inc");

unset($func);
unset($module);
unset($Date);
$_GET['module'] = "PostCalendar";
$_GET['func']	= "search";
$_GET['Date']	= pc_getDate();
$_GET['no_nav'] = 2;
$_GET['patient_id'] = intval($pid);
$_GET['submit'] = "listapps";
global $func,$Date,$module;
$module = "PostCalendar";
$func = "search";
$Date = $_GET['Date'];
$submit = "listapps";
$no_nav = 2;
$patient_id = $_GET['patient_id'];

//now that the environment is set, include the page, it will
//behave as though it was loaded in an iframe with the querystring
//variables set
chdir("../../main/calendar");
include("index.php");
}
?>
  </td>
 </tr>
</table>

</body>
</html>
