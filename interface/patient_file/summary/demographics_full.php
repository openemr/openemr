<?
include_once("../../globals.php");

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

$relats = array('','self','spouse','child','other');
$statii = array('married','single','divorced','widowed','separated','domestic partner');
//$langi = array('english','spanish','french','tagalog','vietnamese');

$langi = getLanguages();
$ethnoraciali = getEthnoRacials();
$provideri = getProviderInfo();
$insurancei = getInsuranceProviders();
?>

<html>
<head>


<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<SCRIPT LANGUAGE="JavaScript"><!--
function update_phone_biz() {
document.demographics_form.phone_biz.value = '('+document.demographics_form.phone_biz_area.value+') '+document.demographics_form.phone_biz_first.value+'-'+document.demographics_form.phone_biz_last.value;
if (document.demographics_form.phone_biz.value == "() -"){
	document.demographics_form.phone_biz.value = "";
}
}

function update_phone_cell() {
document.demographics_form.phone_cell.value = '('+document.demographics_form.phone_cell_area.value+') '+document.demographics_form.phone_cell_first.value+'-'+document.demographics_form.phone_cell_last.value;
if (document.demographics_form.phone_cell.value == "() -"){
	document.demographics_form.phone_cell.value = "";
}
}



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


function update_phone_contact() {
document.demographics_form.phone_contact.value = '('+document.demographics_form.phone_contact_area.value+') '+document.demographics_form.phone_contact_first.value+'-'+document.demographics_form.phone_contact_last.value;
if (document.demographics_form.phone_contact.value == "() -"){
	document.demographics_form.phone_contact.value = "";
}
}
function update_phone_home() {
document.demographics_form.phone_home.value = '('+document.demographics_form.phone_home_area.value+') '+document.demographics_form.phone_home_first.value+'-'+document.demographics_form.phone_home_last.value;
if (document.demographics_form.phone_home.value == "() -"){
	document.demographics_form.phone_home.value = "";
}
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
document.demographics_form.i<?=$i?>subscriber_phone_area.value=document.demographics_form.phone_home_area.value;
document.demographics_form.i<?=$i?>subscriber_phone_first.value=document.demographics_form.phone_home_first.value;
document.demographics_form.i<?=$i?>subscriber_phone_last.value=document.demographics_form.phone_home_last.value;
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
function update_i<?=$i?>subscriber_phone() {
document.demographics_form.i<?=$i?>subscriber_phone.value = '('+document.demographics_form.i<?=$i?>subscriber_phone_area.value+') '+document.demographics_form.i<?=$i?>subscriber_phone_first.value+'-'+document.demographics_form.i<?=$i?>subscriber_phone_last.value;
if (document.demographics_form.i<?=$i?>subscriber_phone.value == "() -"){
	document.demographics_form.i<?=$i?>subscriber_phone.value = "";
}
}

<?}?>


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
//-->
</script>
</head>
<body <?echo $top_bg_line;?> topmargin=0 rightmargin=0 leftmargin=2 bottommargin=0 marginwidth=2 marginheight=0>
<?
$result = getPatientData($pid);
$result2 = getEmployerData($pid);

//extract formatting of phone number:
$pmatch_contact=array();
// preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$result{"phone_contact"},$pmatch_contact);
preg_match("/\D*(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)\D*$/",$result{"phone_contact"},$pmatch_contact);

//extract formatting of phone number:
$pmatch_home=array();
// preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$result{"phone_home"},$pmatch_home);
preg_match("/\D*(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)\D*$/",$result{"phone_home"},$pmatch_home);

//extract formatting of phone number:
$pmatch_biz=array();
// preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$result{"phone_biz"},$pmatch_biz);
preg_match("/\D*(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)\D*$/",$result{"phone_biz"},$pmatch_biz);

//extract formatting of phone number:
$pmatch_cell=array();
// preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$result{"phone_cell"},$pmatch_cell);
preg_match("/\D*(\d\d\d)\D*(\d\d\d)\D*(\d\d\d\d)\D*$/",$result{"phone_cell"},$pmatch_cell);

?>

<form action="demographics_save.php" name=demographics_form method=post>
<input type=hidden name=mode value=save>
<input type=hidden name=phone_contact value="<?echo $result{"phone_contact"}?>">
<input type=hidden name=phone_biz value="<?echo $result{"phone_biz"}?>">
<input type=hidden name=phone_cell value="<?echo $result{"phone_cell"}?>">

<a href="patient_summary.php" target=Main><font class=title>Demographics</font><font class=back><?echo $tback;?></font></a>

<table border="0" cellpadding="0">
<tr><td valign="top">
<span class=required>Name: </span></td>
<td colspan="6">
<select name=title tabindex="1">
	<option value="<?echo $result{"title"}?>"><?echo $result{"title"}?></option>
	<option value="Mr.">Mr.</option>
	<option value="Mrs.">Mrs.</option>
	<option value="Ms.">Ms.</option>
	<option value="Dr.">Dr.</option>
</select>
<input tabindex="2" type=entry size=15 name=fname value="<?echo $result{"fname"}?>"> <input tabindex="3" type=entry size=3 name=mname value="<?echo $result{"mname"}?>"> <input tabindex="4" type=entry size=15 name=lname value="<?echo $result{"lname"}?>">
</td>

</tr>
<tr>
<td valign=top>
<span class=required>DOB: </span>
<td><input tabindex="5" type=entry size=11 name=dob value="
<?
	if (!empty($result['DOB_TS'])) {
	  echo $result['DOB_TS'];
	}
	else {
	  echo "MM/DD/YYYY";
	}
?>
"></td><td rowspan="12">&nbsp;</td>
<td><span class=bold>Patient Number: </span></td><td rowspan="12">&nbsp;</td><td><input type=entry size=10 name=pubpid value="<?echo $result{"pubpid"}?>"></td>
</tr>
<tr>
<td>
<span class=required>Sex: </span></td><td>
<select name=sex tabindex="6">
<option value="Male" <?if ($result{"sex"} == "Male") {echo "selected";};?>>Male</option>
<option value="Female" <?if ($result{"sex"} == "Female") {echo "selected";};?>>Female</option>
</select>
</td>
<td><span class=bold>Emergency Contact: </span></td><td><input type=entry size=10 name=contact_relationship value="<?echo $result{"contact_relationship"}?>"></td>

</tr>
<tr>
	<td><span class=bold>S.S.: </span></td><td><input tabindex="7" type=entry size=11 name=ss value="<?echo $result{"ss"}?>"></td>
	<td><span class=bold>Emergency Phone:</span></td><td>(<input type=entry size=3 name=phone_contact_area onblur="javascript:update_phone_contact();" value="<?echo $pmatch_contact[1]?>">) <input type=entry size=3 name=phone_contact_first onblur="javascript:update_phone_contact();" value="<?echo $pmatch_contact[2]?>">-<input type=entry size=4 name=phone_contact_last onblur="javascript:update_phone_contact();" value="<?echo $pmatch_contact[3]?>"></td>
</tr>
<tr>
	<td><span class=required>Address: </span></td><td><input tabindex="8" type=entry size=25 name=street value="<?echo $result{"street"}?>"></td>
	<td><span class=bold>Home Phone: <input type=hidden name=phone_home value="<?echo $result{"phone_home"}?>"></span></td><td>(<input type=entry size=3 name=phone_home_area onblur="javascript:update_phone_home();" value="<?echo $pmatch_home[1]?>">) <input type=entry size=3 name=phone_home_first onblur="javascript:update_phone_home();" value="<?echo $pmatch_home[2]?>">-<input type=entry size=4 name=phone_home_last onblur="javascript:update_phone_home();" value="<?echo $pmatch_home[3]?>"></td>
</tr>
<tr>
	<td><span class=required>City: </span></td><td><input tabindex="9" type=entry size=15 name=city value="<?echo $result{"city"}?>"></td>
	<td><span class=bold>Work Phone:</span></td><td>(<input type=entry size=3 name=phone_biz_area onblur="javascript:update_phone_biz();" value="<?echo $pmatch_biz[1]?>">) <input type=entry size=3 name=phone_biz_first onblur="javascript:update_phone_biz();" value="<?echo $pmatch_biz[2]?>">-<input type=entry size=4 name=phone_biz_last onblur="javascript:update_phone_biz();" value="<?echo $pmatch_biz[3]?>"></td>
</tr>
<tr>
	<td><span class=required>State: </span></td><td><input tabindex="10" type=entry size=15 name=state value="<?echo $result{"state"}?>"></td>
	<td><span class=bold>Mobile Phone: </span></td><td>(<input type=entry size=3 name=phone_cell_area onblur="javascript:update_phone_cell();" value="<?echo $pmatch_cell[1]?>">) <input type=entry size=3 name=phone_cell_first onblur="javascript:update_phone_cell();" value="<?echo $pmatch_cell[2]?>">-<input type=entry size=4 name=phone_cell_last onblur="javascript:update_phone_cell();" value="<?echo $pmatch_cell[3]?>"></td>
</tr>
<tr>
	<td><span class=required>Zip Code: </span></td><td><input tabindex="11" type=entry size=6 name=postal_code value="<?echo $result{"postal_code"}?>"></td>
	<td><span class=bold>Contact Email: </span></td><td><input type=entry size=30 name=email value="<?echo $result{"email"}?>"></td>
</tr>
<tr>
	<td><span class=required>Country: </span></td><td><input tabindex="12" type=entry size=10 name=country_code value="<?echo $result{"country_code"}?>"></td>
	<td><span class=bold>User Defined Fields</span></td>
</tr>
<tr>
	<td><span class=required>Marital Status: </span></td>
	<td>

		<select name=status tabindex="13">
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
		<td><input name="genericname1" size=20 value="<?echo $result{"genericname1"};?>" /></td><td><input name="genericval1" size=20 value="<?echo $result{"genericval1"};?>" /></td>
	</tr>
<tr>
	<td><span class=required>Provider: </span></td>
<td>
	<select tabindex="14" name="providerID" onchange="javascript:document.demographics_form.referrer.value=upperFirst(this.options[this.selectedIndex].text,this.options[this.selectedIndex].text);">
	<option value=''>Unassigned</option>
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
	<td><input name="genericname2" size=20 value="<?echo $result{"genericname2"};?>" /></td><td><input name="genericval2" size=20 value="<?echo $result{"genericval2"};?>" /></td>
</tr>
<tr>
	<td colspan=6>
		<a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[Save Patient Demographics]</a>
		<hr>
	</td>
</tr>
</table>

<table class=bold>
<th>HIPAA Choices:</th>
<tr>
	<td>Allow Mail:</td>
	<td>
	<select name="hipaa_mail">
		<?
		echo ('<option>NO</option>');
		$result{"hipaa_mail"}=='YES' ? $opt_out='<option selected>YES</option>' : $opt_out='<option>YES</option>' ;
		echo $opt_out;
		?>
	</select>
	<td>Allow Voice Msg:</td>
	<td>
	<select name="hipaa_voice">
		<?
		echo ('<option>NO</option>');
		$result{"hipaa_voice"}=='YES' ? $opt_out='<option selected>YES</option>' : $opt_out='<option>YES</option>' ;
		echo $opt_out;
		?>
	</select>
	</td>
	<td></td>
	<td></td>
</tr>
</table>


<table><tr>
<td colspan=4>
<a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[Save Patient Demographics]</a><hr></td>
</tr>


<tr>
<td valign=top>
<input type=hidden size=30 name=referrer value="<?echo ucfirst($result{"referrer"});?>">
<input type=hidden size=20 name=referrerID value="<?echo $result{"referrerID"}?>">
<input type=hidden size=20 name=db_id value="<?echo $result{"id"}?>">
<table>
  <tr>
  <td><span class=bold>Occupation: </span></td>
  <td><input type=entry size=20 name=occupation value="<?echo $result{"occupation"}?>"></td>
  </tr>
  <tr>
  <td><span class=bold>Employer: <br>(enter Student, PT Student, or leave blank for unemployed)</span></td><td><input type=entry size=20 name=ename value="<?echo $result2{"name"}?>"></td>
  </tr>
</table>
</td>
<td valign=top>
<table>
  <tr>
    <td><span class=bold>Employer Address</span></td><td><span class=bold></span><input type=entry size=25 name=estreet value="<?echo $result2{"street"}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>City: </span></td><td><input type=entry size=15 name=ecity value="<?echo $result2{"city"}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>State: </span></td><td><input type=entry size=15 name=estate value="<?echo $result2{"state"}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>Zip Code: </span></td><td><input type=entry size=10 name=epostal_code value="<?echo $result2{"postal_code"}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>Country: </span></td><td><input type=entry size=10 name=ecountry value="<?echo $result2{"country"}?>"></td>
  </tr>
</table>
</td>
<td valign=top></td>
</tr>

<tr>
<td colspan=4>
<a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[Save Patient Demographics]</a><hr></td>
</tr>


<tr>
<td valign=top>
<span class=bold>Language: </span><br>
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


<span class=bold>Race/Ethnicity: </span><br>
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
    <td><span class=bold>Financial Review Date: </span></td><td><input type=entry size=11 name=financial_review value="<?if ($result{"financial_review"} != "0000-00-00 00:00:00") {echo date("m/d/Y",strtotime($result{"financial_review"}));} else {echo "MM/DD/YYYY";}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>Family Size: </span></td><td><input type=entry size=20 name=family_size value="<?echo $result{"family_size"}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>Monthly Income: </span></td><td><input type=entry size=20 name=monthly_income onblur="javascript:checkNum();" value="<?echo $result{"monthly_income"}?>"><span class=small>(Numbers only)</span></td>
  </tr>
  <tr>
    <td><span class=bold>Homeless, etc.: </span></td><td><input type=entry size=20 name=homeless value="<?echo $result{"homeless"}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>Interpretter: </span></td><td><input type=entry size=20 name=interpretter value="<?echo $result{"interpretter"}?>"></td>
  </tr>
  <tr>
    <td><span class=bold>Migrant/Seasonal: </span></td><td><input type=entry size=20 name=migrantseasonal value="<?echo $result{"migrantseasonal"}?>"></td>
  </tr>
</table>







</td>
<td valign=top></td>
</tr>

<tr>
<td colspan=4>
<a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[Save Patient Demographics]</a>
<hr></td>
</tr>

<?
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
<td colspan="5"><span class=required><?=$insurance_headings[$i -1]?></span></td>
<tr></tr>
<td colspan="5"><select name="i<?=$i?>provider">
<option value="">Unassigned</option>
<?php

foreach ($insurancei as $iid => $iname) {
        	echo "<option value='".$iid."'";
        if (strtolower($iid) == strtolower($result3{"provider"}))
                echo " selected";
        echo ">".$iname."</option>\n";
}
?>
</select>&nbsp;<a href="<? echo  $GLOBALS['webroot'] ?>/controller.php?practice_settings&insurance_company&action=edit">Add New Insurer</a>
</td>
</tr>
<tr>
	<td><span class=required>Plan Name: </span></td><td><input type=entry size=20 name=i<?=$i?>plan_name value="<?echo $result3{"plan_name"}?>"></td>
</tr>
<tr>
	<td><span class=required>Policy Number: </span></td><td><input type=entry size=16 name=i<?=$i?>policy_number value="<?echo $result3{"policy_number"}?>"></td>
</tr>
<tr>
	<td><span class=required>Group Number: </span></td><td><input type=entry size=16 name=i<?=$i?>group_number value="<?echo $result3{"group_number"}?>"></td>
</tr>
<tr>
	<td><span class=required>Subscriber Employer (SE) <br>(enter Student, PT Student, or leave blank for unemployed): </span></td><td><input type=entry size=25 name=i<?=$i?>subscriber_employer value="<?echo $result3{"subscriber_employer"}?>"></td>
</tr>
<tr>
	<td><span class=required>SE Address: </span></td><td><input type=entry size=25 name=i<?=$i?>subscriber_employer_street value="<?echo $result3{"subscriber_employer_street"}?>"></td>
</tr>
<tr>
	<td colspan="2">
	<table>
	  <tr>
	    <td><span class=required>SE City: </span></td>
		<td><input type=entry size=15 name=i<?=$i?>subscriber_employer_city value="<?echo $result3{"subscriber_employer_city"}?>"></td>
	    <td><span class=required>SE State: </span></td>
		<td><input type=entry size=15 name=i<?=$i?>subscriber_employer_state value="<?echo $result3{"subscriber_employer_state"}?>"></td>
	  </tr>
	  <tr>
	    <td><span class=required>SE Zip Code: </span></td>
		<td><input type=entry size=10 name=i<?=$i?>subscriber_employer_postal_code value="<?echo $result3{"subscriber_employer_postal_code"}?>"></td>
	    <td><span class=required>SE Country: </span></td>
		<td><input type=entry size=25 name=i<?=$i?>subscriber_employer_country value="<?echo $result3{"subscriber_employer_country"}?>"></td>
	  </tr>
	</table>
	</td>
</tr>
</table>
</td>

<td valign=top>
<span class=required>Subscriber: </span><input type=entry size=10 name=i<?=$i?>subscriber_fname value="<?echo $result3{"subscriber_fname"}?>"><input type=entry size=3 name=i<?=$i?>subscriber_mname value="<?echo $result3{"subscriber_mname"}?>"><input type=entry size=10 name=i<?=$i?>subscriber_lname value="<?echo $result3{"subscriber_lname"}?>">

<br>

<span class=required>Relationship: </span>
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
<a href="javascript:popUp('browse.php?browsenum=<?=$i?>')" class=text>(Browse)</a><br />
<span class=bold>D.O.B.: </span><input type=entry size=11 name=i<?=$i?>subscriber_DOB value="
<?
	if (!empty($result3['subscriber_DOB'])) {
	  echo $result3['subscriber_DOB'];
	}
	else {
	  echo "MM/DD/YYYY";
	}
?>">
<span class=bold>S.S.: </span><input type=entry size=11 name=i<?=$i?>subscriber_ss value="<?echo $result3{"subscriber_ss"}?> ">&nbsp;
<span class=bold>Sex: </span>
<select name=i<?=$i?>subscriber_sex>
<option value="Male" <? if (strtolower($result3{"subscriber_sex"}) == "male") echo "selected"?>>Male</option>
<option value="Female" <? if (strtolower($result3{"subscriber_sex"}) == "female") echo "selected"?>>Female</option>
</select>
<br>
<span class=required>Subscriber Address: </span><input type=entry size=25 name=i<?=$i?>subscriber_street value="<?echo $result3{"subscriber_street"}?>"><br>
<span class=required>City: </span><input type=entry size=15 name=i<?=$i?>subscriber_city value="<?echo $result3{"subscriber_city"}?>">
<span class=required>State: </span><input type=entry size=15 name=i<?=$i?>subscriber_state value="<?echo $result3{"subscriber_state"}?>"><br>
<span class=required>Zip Code: </span><input type=entry size=10 name=i<?=$i?>subscriber_postal_code value="<?echo $result3{"subscriber_postal_code"}?>">
<span class=required>Country: </span><input type=entry size=10 name=i<?=$i?>subscriber_country value="<?echo $result3{"subscriber_country"}?>"><br>


<span class=bold>Subscriber Phone: <input type=hidden name=i<?=$i?>subscriber_phone value="<?echo $result3{"subscriber_phone"}?>">

<?
//extract formatting of phone number:
$pmatch=array();
preg_match("/^\((.*?)\)\s(.*?)\-(.*?)$/",$result3{"subscriber_phone"},$pmatch);
?>
(<input type=entry size=3 name=i<?=$i?>subscriber_phone_area onblur="javascript:update_i<?=$i?>subscriber_phone();" value="<?echo $pmatch[1]?>">) <input type=entry size=3 name=i<?=$i?>subscriber_phone_first onblur="javascript:update_i<?=$i?>subscriber_phone();" value="<?echo $pmatch[2]?>">-<input type=entry size=4 name=i<?=$i?>subscriber_phone_last onblur="javascript:update_i<?=$i?>subscriber_phone();" value="<?echo $pmatch[3]?>">
</span><br />
<span class=bold>CoPay: <input type=text size="6" name=i<?=$i?>copay value="<?echo $result3{"copay"}?>">
</td>

</tr>
</table>
<a href="javascript:document.demographics_form.submit();" target=Main class=link_submit>[Save Patient Demographics]</a
<hr>
</td></tr>
</table>
<?} //end insurer for loop ?>

</td></tr>
</table>

</form>

<br><br>

</body>
</html>
