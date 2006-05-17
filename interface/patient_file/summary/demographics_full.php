<?
 include_once("../../globals.php");
 include_once("$srcdir/acl.inc");

 // Session pid must be right or bad things can happen when demographics are saved!
 //
 include_once("$srcdir/pid.inc");
 if ($_GET["set_pid"] && $_GET["set_pid"] != $_SESSION["pid"]) {
  setpid($_GET["set_pid"]);
 }
 else if ($_GET["pid"] && $_GET["pid"] != $_SESSION["pid"]) {
  setpid($_GET["pid"]);
 }

 include_once("$srcdir/patient.inc");

 $result = getPatientData($pid);
 $result2 = getEmployerData($pid);

 // Check authorization.
 $thisauth = acl_check('patients', 'demo');
 if ($pid) {
  if ($thisauth != 'write')
   die("Updating demographics is not authorized.");
  if ($result['squad'] && ! acl_check('squads', $result['squad']))
   die("You are not authorized to access this squad.");
 } else {
  if ($thisauth != 'write' && $thisauth != 'addonly')
   die("Adding demographics is not authorized.");
 }

$relats = array('','self','spouse','child','other');
$statii = array('married','single','divorced','widowed','separated','domestic partner');

$langi = getLanguages();
$ethnoraciali = getEthnoRacials();
$provideri = getProviderInfo();
$insurancei = getInsuranceProviders();
?>
<html>
<head>

<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">

<script type="text/javascript" src="../../../library/dialog.js"></script>
<script type="text/javascript" src="../../../library/textformat.js"></script>

<SCRIPT LANGUAGE="JavaScript"><!--

var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

//code used from http://tech.irt.org/articles/js037/
function replace(string,text,by) {
 // Replaces text with by in string
 var strLength = string.length, txtLength = text.length;
 if ((strLength == 0) || (txtLength == 0)) return string;

 var i = string.indexOf(text);
 if ((!i) && (text != string.substring(0,txtLength))) return string;
 if (i == -1) return string;

 var newstr = string.substring(0,i) + by;

 if (i+txtLength < strLength)
  newstr += replace(string.substring(i+txtLength,strLength),text,by);

 return newstr;
}

function upperFirst(string,text) {
 return replace(string,text,text.charAt(0).toUpperCase() + text.substring(1,text.length));
}

<? for ($i=1;$i<=3;$i++) { ?>
function auto_populate_employer_address<?=$i?>(){
 //alert(document.demographics_form.i<?=$i?>subscriber_relationship.options[document.demographics_form.i<?=$i?>subscriber_relationship.selectedIndex].value);
 if (document.demographics_form.i<?=$i?>subscriber_relationship.options[document.demographics_form.i<?=$i?>subscriber_relationship.selectedIndex].value == "self") {
  document.demographics_form.i<?=$i?>subscriber_fname.value=document.demographics_form.fname.value;
  document.demographics_form.i<?=$i?>subscriber_mname.value=document.demographics_form.mname.value;
  document.demographics_form.i<?=$i?>subscriber_lname.value=document.demographics_form.lname.value;
  document.demographics_form.i<?=$i?>subscriber_street.value=document.demographics_form.street.value;
  document.demographics_form.i<?=$i?>subscriber_city.value=document.demographics_form.city.value;
  document.demographics_form.i<?=$i?>subscriber_state.value=document.demographics_form.state.value;
  document.demographics_form.i<?=$i?>subscriber_postal_code.value=document.demographics_form.postal_code.value;
  document.demographics_form.i<?=$i?>subscriber_country.value=document.demographics_form.country_code.value;
  document.demographics_form.i<?=$i?>subscriber_phone.value=document.demographics_form.phone_home.value;
  document.demographics_form.i<?=$i?>subscriber_DOB.value=document.demographics_form.dob.value;
  document.demographics_form.i<?=$i?>subscriber_ss.value=document.demographics_form.ss.value;
  document.demographics_form.i<?=$i?>subscriber_sex.selectedIndex = document.demographics_form.sex.selectedIndex;
  document.demographics_form.i<?=$i?>subscriber_employer.value=document.demographics_form.ename.value;
  document.demographics_form.i<?=$i?>subscriber_employer_street.value=document.demographics_form.estreet.value;
  document.demographics_form.i<?=$i?>subscriber_employer_city.value=document.demographics_form.ecity.value;
  document.demographics_form.i<?=$i?>subscriber_employer_state.value=document.demographics_form.estate.value;
  document.demographics_form.i<?=$i?>subscriber_employer_postal_code.value=document.demographics_form.epostal_code.value;
  document.demographics_form.i<?=$i?>subscriber_employer_country.value=document.demographics_form.ecountry.value;
 }
}

<? } ?>

function popUp(URL) {
 day = new Date();
 id = day.getTime();
 eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=400,height=300,left = 440,top = 362');");
}

function checkNum () {
 var re= new RegExp();
 re = /^\d*\.?\d*$/;
 str=document.demographics_form.monthly_income.value;
 if(re.exec(str))
 {
 }else{
  alert("Please enter a dollar amount using only numbers and a decimal point.");
 }
}

// Indicates which insurance slot is being updated.
var insurance_index = 0;

// The OnClick handler for searching/adding the insurance company.
function ins_search(ins) {
 insurance_index = ins;
 dlgopen('../../practice/ins_search.php', '_blank', 550, 400);
 return false;
}

// The ins_search.php window calls this to set the selected insurance.
function set_insurance(ins_id, ins_name) {
 var thesel = document.forms[0]['i' + insurance_index + 'provider'];
 var theopts = thesel.options; // the array of Option objects
 var i = 0;
 for (; i < theopts.length; ++i) {
  if (theopts[i].value == ins_id) {
   theopts[i].selected = true;
   return;
  }
 }
 // no matching option was found so create one, append it to the
 // end of the list, and select it.
 theopts[i] = new Option(ins_name, ins_id, false, true);
}

//-->

</script>
</head>

<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>

<form action='demographics_save.php' name='demographics_form' method='post'>
<input type=hidden name=mode value=save>

<a href="patient_summary.php" target=Main><font class=title><? xl('Demographics','e'); ?></font><font class=back><?echo $tback;?></font></a>

<table border="0" cellpadding="0" width='100%'>
 <tr>
  <td valign="top"><span class=required><? xl('Name','e'); ?>: </span></td>
  <td colspan="4" nowrap>
   <select name=title tabindex="1">
    <option value="<?echo $result{"title"}?>"><?echo $result{"title"}?></option>
    <option value="Mr."><? xl('Mr','e'); ?>.</option>
    <option value="Mrs."><? xl('Mrs','e'); ?>.</option>
    <option value="Ms."><? xl('Ms','e'); ?>.</option>
    <option value="Dr."><? xl('Dr','e'); ?>.</option>
   </select>
   <input tabindex="2" type=entry size=15 name=fname value="<?echo $result{"fname"}?>">
   <input tabindex="3" type=entry size=3 name=mname value="<?echo $result{"mname"}?>">
   <input tabindex="4" type=entry size=15 name=lname value="<?echo $result{"lname"}?>">
   &nbsp;
   <span class='bold'><? echo ($GLOBALS['athletic_team']) ? 'OID ' : '' ?><? xl('Number','e'); ?>: </span>
  </td>
  <td><input type='entry' size='10' name='pubpid' value="<?echo $result{"pubpid"}?>"></td>
 </tr>
 <tr>
  <td valign='top'><span class='required'><? xl('DOB','e'); ?>: </span></td>
  <td>
   <input tabindex='5' type='entry' size='11' name='dob'
    value='<? echo $result['DOB'] ?>' onkeyup='datekeyup(this,mypcc)'
    onblur='dateblur(this,mypcc)' title='yyyy-mm-dd' />
  </td>
  <td rowspan="12">&nbsp;</td>
  <td><span class='bold'><? xl('Emergency Contact','e'); ?>: </span></td>
  <td rowspan="12">&nbsp;</td>
  <td><input type='entry' size='10' name='contact_relationship' value="<?echo $result{"contact_relationship"}?>"></td>
 </tr>
 <tr>
  <td><span class=required><? xl('Sex','e'); ?>: </span></td>
  <td>
   <select name=sex tabindex="6">
    <option value="Male" <?if ($result{"sex"} == "Male") {echo "selected";};?>><? xl('Male','e'); ?></option>
    <option value="Female" <?if ($result{"sex"} == "Female") {echo "selected";};?>><? xl('Female','e'); ?></option>
   </select>
  </td>
  <td><span class=bold><? xl('Emergency Phone','e'); ?>:</span></td>
  <td><input type='text' size='20' name='phone_contact' value='<?echo $result['phone_contact'] ?>' onkeyup='phonekeyup(this,mypcc)' /></td>
 </tr>
 <tr>
  <td><span class=bold><? xl('S.S.','e'); ?>: </span></td>
  <td><input tabindex="7" type=entry size=11 name=ss value="<?echo $result{"ss"}?>"></td>
  <td><span class='bold'><? xl('Home Phone','e'); ?>: </span></td>
  <td><input type='text' size='20' name='phone_home' value='<?echo $result['phone_home'] ?>' onkeyup='phonekeyup(this,mypcc)' /></td>
 </tr>
 <tr>
  <td><span class=required><? xl('Address','e'); ?>: </span></td>
  <td><input tabindex="8" type=entry size=25 name=street value="<?echo $result{"street"}?>"></td>
  <td><span class=bold><? xl('Work Phone','e'); ?>:</span></td>
  <td><input type='text' size='20' name='phone_biz' value='<?echo $result['phone_biz'] ?>' onkeyup='phonekeyup(this,mypcc)' /></td>
 </tr>
 <tr>
  <td><span class=required><? xl('City','e'); ?>: </span></td>
  <td><input tabindex="9" type=entry size=15 name=city value="<?echo $result{"city"}?>"></td>
  <td><span class=bold><? xl('Mobile Phone','e'); ?>: </span></td>
  <td><input type='text' size='20' name='phone_cell' value='<?echo $result['phone_cell'] ?>' onkeyup='phonekeyup(this,mypcc)' /></td>
 </tr>
 <tr>
  <td><span class=required><? echo ($GLOBALS['phone_country_code'] == '1') ? 'State' : 'Locality' ?>: </span></td>
  <td><input tabindex="10" type=entry size=15 name=state value="<?echo $result{"state"}?>"></td>
  <td><span class=bold><? xl('Pharmacy','e'); ?>: </span></td>
  <td>
   <!--
   <input type='text' size='20' name='phone_pharmacy' value='<?echo $result['phone_pharmacy'] ?>' onkeyup='phonekeyup(this,mypcc)' />
   -->
   <select name='pharmacy_id'>
    <option value='0'></option>
    <?php
     $pres = sqlStatement("SELECT id, name FROM pharmacies ORDER BY name");
     while ($prow = sqlFetchArray($pres)) {
      $key = $prow['id'];
      $value = $prow['name'];
      echo "    <option value='$key'";
      if ($result['pharmacy_id'] == $key) echo " selected";
      echo ">" . $prow['name'] . "</option>\n";
     }
    ?>
   </select>
   <!-- conversion helper: -->
   <?php if (!$result['pharmacy_id']) echo $result['phone_pharmacy']; ?>
  </td>
 </tr>
 <tr>
  <td><span class=required><? echo ($GLOBALS['phone_country_code'] == '1') ? 'Zip' : 'Postal' ?> <? xl('Code','e'); ?>: </span></td>
  <td><input tabindex="11" type=entry size=6 name=postal_code value="<?echo $result{"postal_code"}?>"></td>
  <td><span class='bold'><? xl('Contact Email','e'); ?>: </span></td><td><input type=entry size=30 name=email value="<?echo $result{"email"}?>"></td>
 </tr>
 <tr>
  <td><span class='required'><? xl('Country','e'); ?>: </span></td>
  <td><input tabindex="13" type='entry' size='10' name='country_code' value="<?echo $result{"country_code"}?>"></td>
  <td><span class='bold' colspan='2'> </span></td>
 </tr>
 <tr>
  <td><span class='bold'><? xl('License/ID','e'); ?>: </span></td>
  <td><input tabindex="12" type='entry' size='15' name='drivers_license' value="<?echo $result{"drivers_license"}?>"></td>
  <td><span class='bold' colspan='2'><? xl('User Defined Fields','e'); ?></span></td>
 </tr>
 <tr>
  <td><span class=required><? xl('Marital Status','e'); ?>: </span></td>
  <td>
   <select name=status tabindex="14">
<?php
 print "<!-- ".$result["status"]." -->\n";
 foreach ($statii as $s) {
  if ($s == "unassigned") {
   echo "<option value=''";
  } else {
   echo "<option value='".$s."'";
  }

  if ($s == $result["status"])
   echo " selected";

  echo ">".ucwords($s)."</option>\n";
 }
?>
   </select>
  </td>
  <td><input name="genericname1" size=20 value="<? echo $result{"genericname1"} ?>" /></td>
  <td><input name="genericval1" size='20' value="<? echo $result{"genericval1"} ?>" /></td>
 </tr>
 <tr>
  <td><span class=required><? xl('Provider','e'); ?>: </span></td>
  <td>
   <select tabindex="15" name="providerID" onchange="javascript:document.demographics_form.referrer.value=upperFirst(this.options[this.selectedIndex].text,this.options[this.selectedIndex].text);">
    <option value=''><? xl('Unassigned','e'); ?></option>
<?php
 foreach ($provideri as $s) {
  //echo "defined provider is: " .trim($s['fname']." ".$s['lname']). " compared to : " .$result["referrer"] . "<br />";

  echo "<option value='".$s['id']."'";

  if ($s['id'] == $result["providerID"])
   echo " selected";
   echo ">".ucwords($s['fname']." ".$s['lname'])."</option>\n";
 }
?>
   </select>
  </td>
  <td><input name="genericname2" size='20' value="<?echo $result{"genericname2"};?>" /></td>
  <td><input name="genericval2" size='20' value="<?echo $result{"genericval2"};?>" /></td>
 </tr>
 <tr>
  <td colspan='6'>
   <a href="javascript:document.demographics_form.submit();" target='Main' class='link_submit'>[<? xl('Save Patient Demographics','e'); ?>]</a>
   <hr>
  </td>
 </tr>
</table>

<? if (! $GLOBALS['athletic_team']) { ?>

<table width='100%'>
 <tr>
  <th colspan='4' align='left' class='bold'><? xl('HIPAA Choices','e'); ?>:</th>
 </tr>
 <tr>
  <td class='bold' width='10%' nowrap><? xl('Allow Mail','e'); ?>:</td>
  <td class='bold'>
   <select name="hipaa_mail">
    <?
     echo ('<option>NO</option>');
     $result{"hipaa_mail"}=='YES' ? $opt_out='<option selected>YES</option>' : $opt_out='<option>YES</option>' ;
     echo $opt_out;
    ?>
   </select>
  </td>
  <td class='bold' width='10%' nowrap><? xl('Allow Voice Msg','e'); ?>:</td>
  <td class='bold'>
   <select name="hipaa_voice">
    <?
     echo ('<option>NO</option>');
     $result{"hipaa_voice"}=='YES' ? $opt_out='<option selected>YES</option>' : $opt_out='<option>YES</option>' ;
     echo $opt_out;
    ?>
   </select>
  </td>
 </tr>
 <tr>
  <td colspan='4'>
   <a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[<? xl('Save Patient Demographics','e'); ?>]</a>
   <br><hr>
  </td>
 </tr>
</table>

<? } ?>

<table width='100%'>
 <tr>
  <td valign=top>
   <input type=hidden size=30 name=referrer value="<?echo ucfirst($result{"referrer"});?>">
   <input type=hidden size=20 name=referrerID value="<?echo $result{"referrerID"}?>">
   <input type=hidden size=20 name=db_id value="<?echo $result{"id"}?>">
   <table>
    <tr>
     <td><span class=bold><? xl('Occupation','e'); ?>: </span></td>
     <td><input type=entry size=20 name=occupation value="<?echo $result{"occupation"}?>"></td>
    </tr>
    <tr>
     <td class='bold'><? xl('Employer','e'); ?>:</td>
     <td><input type=entry size=20 name=ename value="<?echo $result2{"name"}?>"></td>
    </tr>
    <tr>
     <td colspan='2' class='bold' style='font-weight:normal'>(<? xl('if unemployed enter Student, PT Student, or leave blank','e'); ?>)</td>
    </tr>
<? if ($GLOBALS['athletic_team']) { ?>
    <tr>
     <td colspan='2' class='bold' style='font-weight:normal'>&nbsp;</td>
    </tr>
    <tr>
     <td><span class='bold'><? xl('Squad','e'); ?>: </span></td>
     <td>
      <select name='squad'>
       <option value=''>&nbsp;</option>
<?
 $squads = acl_get_squads();
 if ($squads) {
  foreach ($squads as $key => $value) {
   echo "       <option value='$key'";
   if ($result['squad'] == $key) echo " selected";
   echo ">" . $value[3] . "</option>\n";
  }
 }
?>
      </select>
     </td>
    </tr>
<? } ?>
   </table>
  </td>
  <td valign=top>
   <table>
    <tr>
     <td><span class=bold><? xl('Employer Address','e'); ?></span></td><td><span class=bold></span><input type=entry size=25 name=estreet value="<?echo $result2{"street"}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? xl('City','e'); ?>: </span></td><td><input type=entry size=15 name=ecity value="<?echo $result2{"city"}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? echo ($GLOBALS['phone_country_code'] == '1') ? 'State' : 'Locality' ?>: </span></td><td><input type=entry size=15 name=estate value="<?echo $result2{"state"}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? echo ($GLOBALS['phone_country_code'] == '1') ? 'Zip' : 'Postal' ?> <? xl('Code','e'); ?>: </span></td><td><input type=entry size=10 name=epostal_code value="<?echo $result2{"postal_code"}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? xl('Country','e'); ?>: </span></td><td><input type=entry size=10 name=ecountry value="<?echo $result2{"country"}?>"></td>
    </tr>
   </table>
  </td>
  <td valign=top></td>
 </tr>

 <tr>
  <td colspan=4>
   <a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[<? xl('Save Patient Demographics','e'); ?>]</a><hr></td>
 </tr>

<? if (! $GLOBALS['athletic_team']) { ?>

 <tr>
  <td valign='top'>
   <span class='bold'><? xl('Language','e'); ?>: </span><br>
   <select onchange="javascript:document.demographics_form.language.value=upperFirst(this.options[this.selectedIndex].value,this.options[this.selectedIndex].value);">
<?php
 foreach ($langi as $s) {
  if ($s == "unassigned") {
   echo "<option value=''";
  } else {
   echo "<option value='".$s."'";
  }
  if ($s == strtolower($result["language"]))
   echo " selected";
  echo ">".ucwords($s)."</option>\n";
 }
?>
   </select><br>
   <input type=entry size=30 name=language value="<?echo ucfirst($result{"language"});?>"><br><br />
   <span class=bold><? xl('Race/Ethnicity','e'); ?>: </span><br>
   <select onchange="javascript:document.demographics_form.ethnoracial.value=upperFirst(this.options[this.selectedIndex].value,this.options[this.selectedIndex].value);">
<?php
 foreach ($ethnoraciali as $s) {
  if ($s == "unassigned") {
   echo "<option value=''";
  } else {
   echo "<option value='".$s."'";
  }
  if ($s == strtolower($result["ethnoracial"]))
   echo " selected";
  echo ">".ucwords($s)."</option>\n";
 }
?>
   </select>
   <br>
   <input type=entry size=30 name=ethnoracial value="<?echo ucfirst($result{"ethnoracial"});?>"><br>
  </td>
  <td valign=top>
   <table>
    <tr>
     <td><span class=bold><? xl('Financial Review Date','e'); ?>: </span></td><td><input type=entry size=11 name=financial_review value="<?if ($result{"financial_review"} != "0000-00-00 00:00:00") {echo date("m/d/Y",strtotime($result{"financial_review"}));} else {echo "MM/DD/YYYY";}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? xl('Family Size','e'); ?>: </span></td><td><input type=entry size=20 name=family_size value="<?echo $result{"family_size"}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? xl('Monthly Income','e'); ?>: </span></td><td><input type=entry size=20 name=monthly_income onblur="javascript:checkNum();" value="<?echo $result{"monthly_income"}?>"><span class=small>(<? xl('Numbers only','e'); ?>)</span></td>
    </tr>
    <tr>
     <td><span class=bold><? xl('Homeless, etc.','e'); ?>: </span></td><td><input type=entry size=20 name=homeless value="<?echo $result{"homeless"}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? xl('Interpreter','e'); ?>: </span></td><td><input type=entry size=20 name=interpretter value="<?echo $result{"interpretter"}?>"></td>
    </tr>
    <tr>
     <td><span class=bold><? xl('Migrant/Seasonal','e'); ?>: </span></td><td><input type=entry size=20 name=migrantseasonal value="<?echo $result{"migrantseasonal"}?>"></td>
    </tr>
   </table>
  </td>
  <td valign=top></td>
 </tr>

 <tr>
  <td colspan=4>
   <a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[<? xl('Save Patient Demographics','e'); ?>]</a>
   <hr>
  </td>
 </tr>

<? } ?>

</table>

<?
 // if (! $GLOBALS['athletic_team']) {
  $insurance_headings = array("Primary Insurance Provider:", "Secondary Insurance Provider", "Tertiary Insurance provider");
  $insurance_info = array();
  $insurance_info[1] = getInsuranceData($pid,"primary");
  $insurance_info[2] = getInsuranceData($pid,"secondary");
  $insurance_info[3] = getInsuranceData($pid,"tertiary");
  for($i=1;$i<=3;$i++) {
   $result3 = $insurance_info[$i];
?>
<table border="0">
 <tr>
  <td valign=top>
   <table border="0">
    <tr>
     <td colspan="5"><span class='required'><?=$insurance_headings[$i -1]?></span></td>
    </tr>
    <tr>
     <td colspan="5">
      <select name="i<?=$i?>provider">
       <option value=""><? xl('Unassigned','e'); ?></option>
<?php
 foreach ($insurancei as $iid => $iname) {
  echo "<option value='" . $iid . "'";
  if (strtolower($iid) == strtolower($result3{"provider"}))
   echo " selected";
  echo ">" . $iname . "</option>\n";
 }
?>
      </select>&nbsp;<a href='' onclick='return ins_search(<?=$i?>)'><? xl('Search/Add Insurer','e'); ?></a>
     </td>
    </tr>
    <tr>
     <td><span class=required><? xl('Plan Name','e'); ?>: </span></td><td><input type=entry size=20 name=i<?=$i?>plan_name value="<?echo $result3{"plan_name"}?>"></td>
    </tr>
    <tr>
     <td><span class=required><? xl('Policy Number','e'); ?>: </span></td><td><input type=entry size=16 name=i<?=$i?>policy_number value="<?echo $result3{"policy_number"}?>"></td>
    </tr>
    <tr>
     <td><span class=required><? xl('Group Number','e'); ?>: </span></td><td><input type=entry size=16 name=i<?=$i?>group_number value="<?echo $result3{"group_number"}?>"></td>
    </tr>
    <tr>
     <td class='required'><? xl('Subscriber Employer (SE)','e'); ?><br><span style='font-weight:normal'>
      (<? xl('if unemployed enter Student','e'); ?>,<br><? xl('PT Student, or leave blank','e'); ?>): </span></td>
     <td><input type=entry size=25 name=i<?=$i?>subscriber_employer value="<?echo $result3{"subscriber_employer"}?>"></td>
    </tr>
    <tr>
     <td><span class=required><? xl('SE Address','e'); ?>: </span></td><td><input type=entry size=25 name=i<?=$i?>subscriber_employer_street value="<?echo $result3{"subscriber_employer_street"}?>"></td>
    </tr>
    <tr>
     <td colspan="2">
      <table>
       <tr>
        <td><span class=required><? xl('SE City','e'); ?>: </span></td>
        <td><input type=entry size=15 name=i<?=$i?>subscriber_employer_city value="<?echo $result3{"subscriber_employer_city"}?>"></td>
        <td><span class=required><? xl('SE','e'); ?> <? echo ($GLOBALS['phone_country_code'] == '1') ? 'State' : 'Locality' ?>: </span></td>
        <td><input type=entry size=15 name=i<?=$i?>subscriber_employer_state value="<?echo $result3{"subscriber_employer_state"}?>"></td>
       </tr>
       <tr>
        <td><span class=required><? xl('SE','e'); ?> <? echo ($GLOBALS['phone_country_code'] == '1') ? 'Zip' : 'Postal' ?> <? xl('Code','e'); ?>: </span></td>
        <td><input type=entry size=10 name=i<?=$i?>subscriber_employer_postal_code value="<?echo $result3{"subscriber_employer_postal_code"}?>"></td>
        <td><span class=required><? xl('SE Country','e'); ?>: </span></td>
        <td><input type=entry size=25 name=i<?=$i?>subscriber_employer_country value="<?echo $result3{"subscriber_employer_country"}?>"></td>
       </tr>
      </table>
     </td>
    </tr>
   </table>
  </td>

  <td valign=top>
   <span class=required><? xl('Subscriber','e'); ?>: </span><input type=entry size=10 name=i<?=$i?>subscriber_fname value="<?echo $result3{"subscriber_fname"}?>"><input type=entry size=3 name=i<?=$i?>subscriber_mname value="<?echo $result3{"subscriber_mname"}?>"><input type=entry size=10 name=i<?=$i?>subscriber_lname value="<?echo $result3{"subscriber_lname"}?>">
   <br>
   <span class=required><? xl('Relationship','e'); ?>: </span>
   <select name=i<?=$i?>subscriber_relationship onchange="javascript:auto_populate_employer_address<?=$i?>();">
<?php
 foreach ($relats as $s) {
  if ($s == "unassigned") {
   echo "<option value=''";
  } else {
   echo "<option value='".$s."'";
  }
  if ($s == $result3{"subscriber_relationship"})
   echo " selected";
  echo ">".ucfirst($s)."</option>\n";
}
?>
   </select>
   <a href="javascript:popUp('browse.php?browsenum=<?=$i?>')" class=text>(<? xl('Browse','e'); ?>)</a><br />
   <span class=bold><? xl('D.O.B.','e'); ?>: </span>
   <input type='entry' size='11' name='i<?=$i?>subscriber_DOB'
    value='<? echo $result3['subscriber_DOB'] ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
    title='yyyy-mm-dd' />
   <span class=bold><? xl('S.S.','e'); ?>: </span><input type=entry size=11 name=i<?=$i?>subscriber_ss value="<?echo $result3{"subscriber_ss"}?> ">&nbsp;
   <span class=bold><? xl('Sex','e'); ?>: </span>
   <select name=i<?=$i?>subscriber_sex>
    <option value="Male" <? if (strtolower($result3{"subscriber_sex"}) == "male") echo "selected"?>><? xl('Male','e'); ?></option>
    <option value="Female" <? if (strtolower($result3{"subscriber_sex"}) == "female") echo "selected"?>><? xl('Female','e'); ?></option>
   </select>
   <br>
   <span class=required><? xl('Subscriber Address','e'); ?>: </span><input type=entry size=25 name=i<?=$i?>subscriber_street value="<?echo $result3{"subscriber_street"}?>"><br>
   <span class=required><? xl('City','e'); ?>: </span><input type=entry size=15 name=i<?=$i?>subscriber_city value="<?echo $result3{"subscriber_city"}?>">
   <span class=required><? echo ($GLOBALS['phone_country_code'] == '1') ? 'State' : 'Locality' ?>: </span><input type=entry size=15 name=i<?=$i?>subscriber_state value="<?echo $result3{"subscriber_state"}?>"><br>
   <span class=required><? echo ($GLOBALS['phone_country_code'] == '1') ? 'Zip' : 'Postal' ?> <? xl('Code','e'); ?>: </span><input type=entry size=10 name=i<?=$i?>subscriber_postal_code value="<?echo $result3{"subscriber_postal_code"}?>">
   <span class=required><? xl('Country','e'); ?>: </span><input type=entry size=10 name=i<?=$i?>subscriber_country value="<?echo $result3{"subscriber_country"}?>"><br>
   <span class=bold><? xl('Subscriber Phone','e'); ?>: 
   <input type='text' size='20' name='i<?=$i?>subscriber_phone' value='<?echo $result3["subscriber_phone"] ?>' onkeyup='phonekeyup(this,mypcc)' />
   </span><br />
   <span class=bold><? xl('CoPay','e'); ?>: <input type=text size="6" name=i<?=$i?>copay value="<?echo $result3{"copay"}?>">
  </td>
 </tr>
</table>
<a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[<? xl('Save Patient Demographics','e'); ?>]</a>
<hr>

<?
  } //end insurer for loop
 // } // end of "if not athletic team"
?>

</form>

<br>

<script language="JavaScript">
 // fix inconsistently formatted phone numbers from the database
 var f = document.forms[0];
 phonekeyup(f.phone_contact,mypcc);
 phonekeyup(f.phone_home,mypcc);
 phonekeyup(f.phone_biz,mypcc);
 phonekeyup(f.phone_cell,mypcc);
 phonekeyup(f.phone_pharmacy,mypcc);
 phonekeyup(f.i1subscriber_phone,mypcc);
 phonekeyup(f.i2subscriber_phone,mypcc);
 phonekeyup(f.i3subscriber_phone,mypcc);
</script>

</body>
</html>
