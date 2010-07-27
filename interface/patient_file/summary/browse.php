<?php 

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

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

<a href="javascript:window.close();"><font class=title><?php echo htmlspecialchars( xl('Browse for Record'), ENT_NOQUOTES); ?></font><font class=back><?php echo htmlspecialchars( $tback, ENT_NOQUOTES);?></font></a>

<form border='0' method='post' name="find_patient" action="browse.php?browsenum=<?php echo ".htmlspecialchars( $browsenum, ENT_QUOTES)."?>">

<?php //<a href="javascript:document.find_patient.action='finder/patient_finder_keyboard.php';document.find_patient.submit();" class=link>Find Patient:</a>?>
<input type='entry' size='10' name='patient'>
<select name="findBy" size='1'>
 <option value="ID"><?php echo htmlspecialchars( xl('ID'), ENT_NOQUOTES); ?></option>
 <option value="Last" selected><?php echo htmlspecialchars( xl('Last Name'), ENT_NOQUOTES); ?></option>
 <option value="SSN"><?php echo htmlspecialchars( xl('SSN'), ENT_NOQUOTES); ?></option>
 <option value="DOB"><?php echo htmlspecialchars( xl('DOB'), ENT_NOQUOTES); ?></option>
</select>
<a href="javascript:document.find_patient.submit();" class=link><?php echo htmlspecialchars( xl('Find'), ENT_NOQUOTES); ?></a>&nbsp;&nbsp;
<a href="javascript:auto_populate_employer_address();" class=link_submit><?php echo htmlspecialchars( xl('Copy Values'), ENT_NOQUOTES); ?></a>
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
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_fname.value='<?php echo htmlspecialchars( $result3{subscriber_fname}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_mname.value='<?php echo htmlspecialchars( $result3{subscriber_mname}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_lname.value='<?php echo htmlspecialchars( $result3{subscriber_lname}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_street.value='<?php echo htmlspecialchars( $result3{subscriber_street}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_city.value='<?php echo htmlspecialchars( $result3{subscriber_city}, ENT_QUOTES);?>';
 df.form_i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_state.value='<?php echo htmlspecialchars( $result3{subscriber_state}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_postal_code.value='<?php echo htmlspecialchars( $result3{subscriber_postal_code}, ENT_QUOTES);?>';
 if (df.form_i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_country) // in case this is commented out
  df.form_i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_country.value='<?php echo htmlspecialchars( $result3{subscriber_country}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_phone.value='<?php echo htmlspecialchars( $result3{subscriber_phone}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_DOB.value='<?php echo htmlspecialchars( $result3{subscriber_DOB}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_ss.value='<?php echo htmlspecialchars( $result3{subscriber_ss}, ENT_QUOTES);?>';
 df.form_i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_sex.value='<?php echo htmlspecialchars( $result3{subscriber_sex}, ENT_QUOTES);?>';

 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>plan_name.value='<?php echo htmlspecialchars( $result3{plan_name}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>policy_number.value='<?php echo htmlspecialchars( $result3{policy_number}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>group_number.value='<?php echo htmlspecialchars( $result3{group_number}, ENT_QUOTES);?>';
 df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>provider.value='<?php echo htmlspecialchars( $result3{provider}, ENT_QUOTES);?>';

 // One clinic comments out the subscriber employer stuff.
 if (df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_employer) {
  df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_employer.value='<?php echo htmlspecialchars( $result3{subscriber_employer}, ENT_QUOTES);?>';
  df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_employer_street.value='<?php echo htmlspecialchars( $result3{subscriber_employer_street}, ENT_QUOTES);?>';
  df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_employer_city.value='<?php echo htmlspecialchars( $result3{subscriber_employer_city}, ENT_QUOTES);?>';
  df.form_i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_employer_state.value='<?php echo htmlspecialchars( $result3{subscriber_employer_state}, ENT_QUOTES);?>';
  df.i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_employer_postal_code.value='<?php echo htmlspecialchars( $result3{subscriber_employer_postal_code}, ENT_QUOTES);?>';
  df.form_i<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>subscriber_employer_country.value='<?php echo htmlspecialchars( $result3{subscriber_employer_country}, ENT_QUOTES);?>';
 }
}
//-->
</script>

<form method=post name=insurance_form action=browse.php?browsenum=<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>&set_pid=<?php echo htmlspecialchars( $_GET{set_pid}, ENT_QUOTES);?>>
<input type="hidden" name="browsenum" value="<?php echo htmlspecialchars( $browsenum, ENT_QUOTES);?>">
<span class=bold> <?php echo htmlspecialchars( xl('Insurance Provider'), ENT_NOQUOTES); ?>:</span>
<select name=insurance onchange="javascript:document.insurance_form.submit();">
    <option value="primary" <?php echo ($insurance == "primary") ? "selected" : ""?>><?php echo htmlspecialchars( xl('Primary'), ENT_NOQUOTES); ?></option>
    <option value="secondary" <?php echo ($insurance == "secondary") ? "selected" : ""?>><?php echo htmlspecialchars( xl('Secondary'), ENT_NOQUOTES); ?></option>
    <option value="tertiary" <?php echo ($insurance == "tertiary") ? "selected" : ""?>><?php echo htmlspecialchars( xl('Tertiary'), ENT_NOQUOTES); ?></option>
</select>

</form>
<table>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('First Name'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_fname}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Middle Name'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_mname}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Last Name'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_lname}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Address'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_street}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('City'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_city}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('State'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text>
 <?php
  //Modified 7/2009 by BM to incorporate data types
  echo generate_display_field(array('data_type'=>$GLOBALS['state_data_type'],'list_id'=>$GLOBALS['state_list']),$result3{subscriber_state});
 ?>
</span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Zip Code'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_postal_code}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Country'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text>
 <?php
  //Modified 7/2009 by BM to incorporate data types
  echo generate_display_field(array('data_type'=>$GLOBALS['country_data_type'],'list_id'=>$GLOBALS['country_list']),$result3{subscriber_country});
 ?>
</span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Phone'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_phone}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('DOB'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_DOB}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('SS'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_ss}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Primary Insurance Provider'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{provider_name}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Plan Name'), ENT_NOQUOTES); ?>:</span>
</td><td><span class=text><?php echo htmlspecialchars( $result3{plan_name}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Group Number'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{group_number}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Policy Number'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{policy_number}, ENT_NOQUOTES);?></span></td>
</tr>

<?php if (empty($GLOBALS['omit_employers'])) { ?>

<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Subscriber Employer'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_employer}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Subscriber Employer Address'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_employer_street}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Subscriber Employer Zip Code'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_employer_postal_code}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Subscriber Employer City'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo htmlspecialchars( $result3{subscriber_employer_city}, ENT_NOQUOTES);?></span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Subscriber Employer State'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text>
 <?php
  //Modified 7/2009 by BM to incorporate data types
  echo generate_display_field(array('data_type'=>$GLOBALS['state_data_type'],'list_id'=>$GLOBALS['state_list']),$result3{subscriber_employer_state});
 ?>
</span></td>
</tr>
<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Subscriber Employer Country'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text>
  <?php
   //Modified 7/2009 by BM to incorporate data types 
   echo generate_display_field(array('data_type'=>$GLOBALS['country_data_type'],'list_id'=>$GLOBALS['country_list']),$result3{subscriber_employer_country});
  ?>
</span></td>
</tr>

<?php } ?>

<tr>
<td><span class=text><?php echo htmlspecialchars( xl('Subscriber Sex'), ENT_NOQUOTES); ?>:</span></td>
<td><span class=text><?php echo generate_display_field(array('data_type'=>'1','list_id'=>'sex'),$result3{subscriber_sex}); ?></span></td>
</tr>
</table>

<br>
<a href="javascript:auto_populate_employer_address();" class=link_submit><?php echo htmlspecialchars( xl('Copy Values'), ENT_NOQUOTES); ?></a>

<?php 
} else {
?>

<table border=0 cellpadding=5 cellspacing=0>
<tr>
<td>
<span class=bold><?php echo htmlspecialchars( xl('Name'), ENT_NOQUOTES); ?></span>
</td><td>
<span class=bold><?php echo htmlspecialchars( xl('SS'), ENT_NOQUOTES); ?></span>
</td><td>
<span class=bold><?php echo htmlspecialchars( xl('DOB'), ENT_NOQUOTES); ?></span>
</td><td>
<span class=bold><?php echo htmlspecialchars( xl('ID'), ENT_NOQUOTES); ?></span>
</td></tr>
<?php 

$count=0;
$total=0;

$findby = $_POST['findBy'];
$patient = $_POST['patient'];
if ($findby == "Last" && $result = getPatientLnames("$patient","*,DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='browse.php?browsenum=" .
	                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
	                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
	                htmlspecialchars( $iter{"lname"}.", ".$iter{"fname"}, ENT_NOQUOTES) .
	                "</td></a>\n";
		print "<td><a class=text target=_top href='browse.php?browsenum=" .
	                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
	                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
	                htmlspecialchars( $iter{"ss"}, ENT_NOQUOTES) . "</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='browse.php?browsenum=" .
		                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
		                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
		                htmlspecialchars( $iter{"DOB_TS"}, ENT_NOQUOTES) . "</a></td>";
		} else {
			print "<td><a class=text target=_top href='browse.php?browsenum=" .
		                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
		                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='browse.php?browsenum=" .
	                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
	                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
	                htmlspecialchars( $iter{"pubpid"}, ENT_NOQUOTES) . "</a></td>";

		$total++;
	}
}

if ($findby == "ID" && $result = getPatientId("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
		print "<tr><td><a class=text target=_top href='browse.php?browsenum=" .
	                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
	                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
	                htmlspecialchars( $iter{"lname"}.", ".$iter{"fname"}, ENT_NOQUOTES) .
	                "</td></a>\n";
		print "<td><a class=text target=_top href='browse.php?browsenum=" .
	                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
	                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
	                htmlspecialchars( $iter{"ss"}, ENT_NOQUOTES) . "</a></td>";
		if ($iter{"DOB"} != "0000-00-00 00:00:00") {
			print "<td><a class=text target=_top href='browse.php?browsenum=" .
		                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
		                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
		                htmlspecialchars( $iter{"DOB_TS"}, ENT_NOQUOTES) . "</a></td>";
		} else {
			print "<td><a class=text target=_top href='browse.php?browsenum=" .
		                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
		                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>&nbsp;</a></td>";
		}
		print "<td><a class=text target=_top href='browse.php?browsenum=" .
	                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
	                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
	                htmlspecialchars( $iter{"pubpid"}, ENT_NOQUOTES) . "</a></td>";

		$total++;
	}
}

if ($findby == "DOB" && $result = getPatientDOB("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
                print "<tr><td><a class=text target=_top href='browse.php?browsenum=" .
                        htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                        htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                        htmlspecialchars( $iter{"lname"}.", ".$iter{"fname"}, ENT_NOQUOTES) .
                        "</td></a>\n";
                print "<td><a class=text target=_top href='browse.php?browsenum=" .
                        htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                        htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                        htmlspecialchars( $iter{"ss"}, ENT_NOQUOTES) . "</a></td>";
                if ($iter{"DOB"} != "0000-00-00 00:00:00") {
                        print "<td><a class=text target=_top href='browse.php?browsenum=" .
                                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                                htmlspecialchars( $iter{"DOB_TS"}, ENT_NOQUOTES) . "</a></td>";
                } else {
                        print "<td><a class=text target=_top href='browse.php?browsenum=" .
                                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>&nbsp;</a></td>";
                }
                print "<td><a class=text target=_top href='browse.php?browsenum=" .
                        htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                        htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                        htmlspecialchars( $iter{"pubpid"}, ENT_NOQUOTES) . "</a></td>";

		$total++;
	}
}

if ($findby == "SSN" && $result = getPatientSSN("$patient","*, DATE_FORMAT(DOB,'%m/%d/%Y') as DOB_TS") ) {
	foreach ($result as $iter) {

		if ($total >= $M) {
			break;
		}
                print "<tr><td><a class=text target=_top href='browse.php?browsenum=" .
                        htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                        htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                        htmlspecialchars( $iter{"lname"}.", ".$iter{"fname"}, ENT_NOQUOTES) .
                        "</td></a>\n";
                print "<td><a class=text target=_top href='browse.php?browsenum=" .
                        htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                        htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                        htmlspecialchars( $iter{"ss"}, ENT_NOQUOTES) . "</a></td>";
                if ($iter{"DOB"} != "0000-00-00 00:00:00") {
                        print "<td><a class=text target=_top href='browse.php?browsenum=" .
                                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                                htmlspecialchars( $iter{"DOB_TS"}, ENT_NOQUOTES) . "</a></td>";
                } else {
                        print "<td><a class=text target=_top href='browse.php?browsenum=" .
                                htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                                htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>&nbsp;</a></td>";
                }
                print "<td><a class=text target=_top href='browse.php?browsenum=" .
                        htmlspecialchars( $browsenum, ENT_QUOTES) . "&set_pid=" .
                        htmlspecialchars( $iter{"pid"}, ENT_QUOTES) . "'>" .
                        htmlspecialchars( $iter{"pubpid"}, ENT_NOQUOTES) . "</a></td>";

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
