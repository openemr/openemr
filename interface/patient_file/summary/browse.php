<?php 
include_once("../../globals.php");
include_once("$srcdir/patient.inc");
include_once("$srcdir/options.inc.php");

//the maximum number of patient records to display:
$M = 100;

$browsenum = (is_numeric($_REQUEST['browsenum'])) ? $_REQUEST['browsenum'] : 1;
?>
<html>
<head>
<?php html_header_show();?>
<link rel='stylesheet' href="<?php echo $css_header;?>" type="text/css">
</head>

<body class="body_top">

<a href="javascript:window.close();"><font class=title><?php  xl('Browse for Record','e'); ?></font><font class=back><?php echo $tback;?></font></a>

<form border='0' method='post' name="find_patient" action="browse.php?browsenum=<?php echo $browsenum?>">

<?php //<a href="javascript:document.find_patient.action='finder/patient_finder_keyboard.php';document.find_patient.submit();" class=link>Find Patient:</a>?>
<input type='entry' size='10' name='patient'>
<select name="findBy" size='1'>
 <option value="ID"><?php  xl('ID','e'); ?></option>
 <option value="Last" selected><?php  xl('Last Name','e'); ?></option>
 <option value="SSN"><?php  xl('SSN','e'); ?></option>
 <option value="DOB"><?php  xl('DOB','e'); ?></option>
</select>
<a href="javascript:document.find_patient.submit();" class=link><?php xl('Find','e'); ?></a>&nbsp;&nbsp;
<a href="javascript:auto_populate_employer_address();" class=link_submit><?php xl('Copy Values','e'); ?></a>
</form>

<?php 
if (isset($_GET{set_pid})) {
  if (!isset($_POST{insurance})){
    $insurance = "primary";
  } else {
    $insurance = $_POST{insurance};
  }
  $result = getPatientData($_GET{set_pid});
  // $result2 = getEmployerData($_GET{set_pid}); // not used!
  $result3 = getInsuranceData($_GET{set_pid},$insurance);
?>

<script language=javascript>
<!--
function auto_populate_employer_address(){
 var df = opener.document.demographics_form;
 df.i<?php echo $browsenum?>subscriber_fname.value='<?php echo $result3{subscriber_fname};?>';
 df.i<?php echo $browsenum?>subscriber_mname.value='<?php echo $result3{subscriber_mname};?>';
 df.i<?php echo $browsenum?>subscriber_lname.value='<?php echo $result3{subscriber_lname};?>';
 df.i<?php echo $browsenum?>subscriber_street.value='<?php echo $result3{subscriber_street};?>';
 df.i<?php echo $browsenum?>subscriber_city.value='<?php echo $result3{subscriber_city};?>';
 df.i<?php echo $browsenum?>subscriber_state.value='<?php echo $result3{subscriber_state};?>';
 df.i<?php echo $browsenum?>subscriber_postal_code.value='<?php echo $result3{subscriber_postal_code};?>';
 if (df.i<?php echo $browsenum?>subscriber_country) // in case this is commented out
  df.i<?php echo $browsenum?>subscriber_country.value='<?php echo $result3{subscriber_country};?>';
 df.i<?php echo $browsenum?>subscriber_phone.value='<?php echo $result3{subscriber_phone};?>';
 df.i<?php echo $browsenum?>subscriber_DOB.value='<?php echo $result3{subscriber_DOB};?>';
 df.i<?php echo $browsenum?>subscriber_ss.value='<?php echo $result3{subscriber_ss};?>';
 df.form_i<?php echo $browsenum?>subscriber_sex.value='<?php echo $result3{subscriber_sex};?>';

 df.i<?php echo $browsenum?>plan_name.value='<?php echo $result3{plan_name};?>';
 df.i<?php echo $browsenum?>policy_number.value='<?php echo $result3{policy_number};?>';
 df.i<?php echo $browsenum?>group_number.value='<?php echo $result3{group_number};?>';
 df.i<?php echo $browsenum?>provider.value='<?php echo $result3{provider};?>';

 // One clinic comments out the subscriber employer stuff.
 if (df.i<?php echo $browsenum?>subscriber_employer) {
  df.i<?php echo $browsenum?>subscriber_employer.value='<?php echo $result3{subscriber_employer};?>';
  df.i<?php echo $browsenum?>subscriber_employer_street.value='<?php echo $result3{subscriber_employer_street};?>';
  df.i<?php echo $browsenum?>subscriber_employer_city.value='<?php echo $result3{subscriber_employer_city};?>';
  df.i<?php echo $browsenum?>subscriber_employer_state.value='<?php echo $result3{subscriber_employer_state};?>';
  df.i<?php echo $browsenum?>subscriber_employer_postal_code.value='<?php echo $result3{subscriber_employer_postal_code};?>';
  df.i<?php echo $browsenum?>subscriber_employer_country.value='<?php echo $result3{subscriber_employer_country};?>';
 }
}
//-->
</script>

<form method=post name=insurance_form action=browse.php?browsenum=<?php echo $browsenum?>&set_pid=<?php echo $_GET{set_pid};?>>
<input type="hidden" name="browsenum" value="<?php echo $browsenum?>">
<span class=bold> <?php  xl('Insurance Provider','e'); ?>:</span>
<select name=insurance onchange="javascript:document.insurance_form.submit();">
<option value="primary"><?php  xl('Primary','e'); ?></option>
<option value="secondary"><?php  xl('Secondary','e'); ?></option>
<option value="tertiary"><?php  xl('Tertiary','e'); ?></option>
</select>

</form>
<table>
<tr>
<td><span class=text><?php  xl('First Name','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_fname};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Middle Name','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_mname};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Last Name','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_lname};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Address','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_street};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('City','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_city};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('State','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_state};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Zip Code','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_postal_code};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Country','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_country};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Phone','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_phone};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('DOB','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_DOB};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('SS','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_ss};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Primary Insurance Provider','e'); ?>:</span></td><td><span class=text><?php echo $result3{provider_name};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Plan Name','e'); ?>:</span></td><td><span class=text><?php echo $result3{plan_name};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Group Number','e'); ?>:</span></td><td><span class=text><?php echo $result3{group_number};?></span></td>
</tr>
<tr>
<tr>
<td><span class=text><?php  xl('Policy Number','e'); ?>:</span></td><td><span class=text><?php echo $result3{policy_number};?></span></td>
</tr>

<?php if (empty($GLOBALS['omit_employers'])) { ?>

<tr>
<td><span class=text><?php  xl('Subscriber Employer','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_employer};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Subscriber Employer Address','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_employer_street};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Subscriber Employer Zip Code','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_employer_postal_code};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Subscriber Employer City','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_employer_city};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Subscriber Employer State','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_employer_state};?></span></td>
</tr>
<tr>
<td><span class=text><?php  xl('Subscriber Employer Country','e'); ?>:</span></td><td><span class=text><?php echo $result3{subscriber_employer_country};?></span></td>
</tr>

<?php } ?>

<tr>
<td><span class=text><?php  xl('Subscriber Sex','e'); ?>:</span></td><td><span class=text><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'sex'),$result3{subscriber_sex}); ?></span></td>
</tr>
</table>

<br>
<a href="javascript:auto_populate_employer_address();" class=link_submit>Copy Values</a>

<?php 
} else {
?>

<table border=0 cellpadding=5 cellspacing=0>
<tr>
<td>
<span class=bold><?php  xl('Name','e'); ?></span>
</td><td>
<span class=bold><?php  xl('SS','e'); ?></span>
</td><td>
<span class=bold><?php  xl('DOB','e'); ?></span>
</td><td>
<span class=bold><?php  xl('ID','e'); ?></span>
</td></tr>
<?php 

$count=0;
$total=0;

if ($findBy == "Last" && $result = getPatientLnames("$patient","*,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"DOB_TS"}."</a></td>";
		} else {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";

		$total++;
	}
}

if ($findBy == "ID" && $result = getPatientId("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"DOB_TS"}."</a></td>";
		} else {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&vset_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";

		$total++;
	}
}

if ($findBy == "DOB" && $result = getPatientDOB("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"DOB_TS"}."</a></td>";
		} else {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";
		
		$total++;
	}
}

if ($findBy == "SSN" && $result = getPatientSSN("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"lname"}.", ".$iter{"fname"}."</td></a>\n";
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"ss"}."</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"DOB_TS"}."</a></td>";
		} else {
			print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='browse.php?browsenum=$browsenum&set_pid=".$iter{"pid"}."'>".$iter{"pubpid"}."</a></td>";
		
		$total++;
	}
}
?>
</table>
<?php 
}
?>
</body>
</html>
