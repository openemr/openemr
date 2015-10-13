<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
// Copyright (C) 2010 Tomasz Wyderka <wyderkat@cofoh.com>
// Copyright (C) 2015 Ensoftek <rammohan@ensoftek.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report lists non reported patient diagnoses for a given date range.
// Ensoftek: Jul-2015: Modified HL7 generation to 2.5.1 spec and MU2 compliant.
// This implementation is only for the A01 profile which will suffice for MU2 certification.                   


require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("../../custom/code_types.inc.php");


// Ensoftek: Jul-2015: Get the facility of the logged in user.
function getLoggedInUserFacility(){
	$sql = "SELECT f.name, f.facility_npi FROM users AS u LEFT JOIN facility AS f ON u.facility_id = f.id WHERE u.id=?";
	$res = sqlStatement($sql, array($_SESSION['authUserID']) );
	 while ($arow = sqlFetchArray($res)) {
		return $arow;
	}
    return null;
}

// Ensoftek: Jul-2015: Map codes to confirm to HL7.
function mapCodeType($incode){
	$outcode = null;
    $code = explode(":", $incode);
	switch ($code[0]) {
		 case "ICD9":
			 $outcode = "I9CDX";
			 break;
		 case "ICD10":
			 $outcode = "I10";
			 break;
		 case "SNOMED-CT":
			 $outcode = "SCT";
			 break;
		 case "US Ext SNOMEDCT":
			 $outcode = "SCT";
			 break;
		 default:
			 $outcode = "I9CDX"; // default to ICD9
			 break;		 
			 // Only ICD9, ICD10 and SNOMED codes allowed in Syndromic Surveillance
    }
    return $outcode;	
}


if(isset($_POST['form_from_date'])) {
  $from_date = $_POST['form_from_date'] !== "" ? 
    fixDate($_POST['form_from_date'], date('Y-m-d')) :
    0;
}
if(isset($_POST['form_to_date'])) {
  $to_date =$_POST['form_to_date'] !== "" ? 
    fixDate($_POST['form_to_date'], date('Y-m-d')) :
    0;
}
//
$form_code = isset($_POST['form_code']) ? $_POST['form_code'] : Array();
//
if (empty ($form_code) ) {
  $query_codes = '';
} else {
  $query_codes = 'c.id in (';
      foreach( $form_code as $code ){ $query_codes .= $code . ","; }
      $query_codes = substr($query_codes ,0,-1);
      $query_codes .= ') and ';
}
//
function tr($a) {
  return (str_replace(' ','^',$a));
}

  $query = 
  "select " .
  "l.pid as patientid, " .
  "p.language, ".
  "l.diagnosis , " ;
  if ($_POST['form_get_hl7']==='true') {
    $query .= 
      "DATE_FORMAT(p.DOB,'%Y%m%d') as DOB, ".
      "concat(p.street, '^',p.postal_code,'^', p.city, '^', p.state) as address, ".
      "p.country_code, ".
      "p.phone_home, ".
      "p.phone_biz, ".
      "p.status, ".
      "p.sex, ".
      "p.ethnoracial, ".
      "c.code_text, ".
      "c.code, ".
      "c.code_type, ".
      "DATE_FORMAT(l.date,'%Y%m%d') as issuedate, ".
      "concat(p.fname, '^',p.mname,'^', p.lname) as patientname, ";
  } else {
    $query .= "concat(p.fname, ' ',p.mname,' ', p.lname) as patientname, ".
      "l.date as issuedate, "  ;
  }
  $query .=
  "l.id as issueid, l.title as issuetitle, DATE_FORMAT(l.begdate,'%Y%m%d%H%i') as begin_date ". // Ensoftek: Jul-2015: Get begin date
  "from lists l, patient_data p, codes c ".
  "where ".
  "c.reportable=1 and ".
  "l.id not in (select lists_id from syndromic_surveillance) and ";
  if($from_date!=0) {
    $query .= "l.date >= '$from_date' " ;
  }
  if($from_date!=0 and $to_date!=0) {
    $query .= " and " ;
  }
  if($to_date!=0) {
    $query .= "l.date <= '$to_date' ";
  }
  if($from_date!=0 or $to_date!=0) {
    $query .= " and " ;
  }
  $query .= "l.pid=p.pid and ".
  $query_codes .
  "l.diagnosis LIKE 'ICD9:%' and ".
  "substring(l.diagnosis,6) = c.code ";

//echo "<p> DEBUG query: $query </p>\n"; // debugging

$D="\r";
$nowdate = date('YmdHi');
$now = date('YmdGi');
$now1 = date('Y-m-d G:i');
$filename = "syn_sur_". $now . ".hl7";


// Ensoftek: Jul-2015: Get logged in user's facility to be used in the MSH segment
$facility_info = getLoggedInUserFacility();

// GENERATE HL7 FILE
if ($_POST['form_get_hl7']==='true') {
  $content = ''; 

  $res = sqlStatement($query);

  while ($r = sqlFetchArray($res)) {
    // MSH
    $content .= "MSH|^~\&|".strtoupper($openemr_name).
		"|" . $facility_info['name'] . "^" . $facility_info['facility_npi'] . "^NPI" . 
		"|||$now||".
		"ADT^A01^ADT_A01" . // Hard-code to A01: Patient visits provider/facility
		"|$nowdate|P^T|2.5.1|||||||||PH_SS-NoAck^SS Sender^2.16.840.1.114222.4.10.3^ISO" . // No acknowlegement
		"$D";
	  	  
	// EVN
    $content .= "EVN|" .
        "|" . // 1.B Event Type Code
        "$now" . // 2.R Recorded Date/Time
        "||||" .
		"|" . $facility_info['name'] . "^" . $facility_info['facility_npi'] . "^NPI" .
        "$D" ;
		
    if ($r['sex']==='Male') $r['sex'] = 'M';
    if ($r['sex']==='Female') $r['sex'] = 'F';
    if ($r['status']==='married') $r['status'] = 'M';
    if ($r['status']==='single') $r['status'] = 'S';
    if ($r['status']==='divorced') $r['status'] = 'D';
    if ($r['status']==='widowed') $r['status'] = 'W';
    if ($r['status']==='separated') $r['status'] = 'A';
    if ($r['status']==='domestic partner') $r['status'] = 'P';
	
	// PID
    $content .= "PID|" . 
        "1|" . // 1. Set id
        "|" . 
        $r['patientid']."^^^^MR"."|". // 3. (R) Patient indentifier list
        "|" . // 4. (B) Alternate PID
        "^^^^^^~^^^^^^S"."|" . // 5.R. Name
        "|" . // 6. Mather Maiden Name
        $r['DOB']."|" . // 7. Date, time of birth
        $r['sex'] . // 8. Sex
		"|||^^^||||||||||||||||||||||||||||" .
        "$D" ;
		
    $content .= "PV1|" . 
        "1|" . // 1. Set ID
        "|||||||||||||||||" .
		// Restrict the string to 15 characters. Will fail if longer.
		substr($now . "_" . $r['patientid'], 0, 15) . "^^^^VN" . // Supposed to be visit number. Since, we don't have any encounter, we'll use the format 'date_pid' to make it unique
		"|||||||||||||||||||||||||" .
		$r['begin_date'] . 
        "$D" ;
		
	// OBX: Records chief complaint in LOINC code
    $content .= "OBX|" . 
        "1|" . // 1. Set ID
		"CWE|8661-1^^LN||" . // LOINC code for chief complaint
		"^^^^^^^^" . $r['issuetitle'] .
		"||||||" .
		"F" . 
        "$D" ;
		
	// DG1
	$r['diagnosis'] = mapCodeType($r['diagnosis']);  // Only ICD9, ICD10 and SNOMED
	$r['code'] = str_replace(".", "", $r['code']); // strip periods code

    $content .= "DG1|" . 
        "1|" . // 1. Set ID
		"|" .
		$r['code'] . "^" . $r['code_text'] . "^" . $r['diagnosis'] .
		"|||W" .
        "$D" ;
		
        
        // mark if issues generated/sent
        $query_insert = "insert into syndromic_surveillance(lists_id,submission_date,filename) " .
         "values (" . $r['issueid'] . ",'" . $now1 . "','" . $filename . "')"; 
        sqlStatement($query_insert);
}

  // Ensoftek: Jul-2015: No need to tr the content
  //$content = tr($content);

  // send the header here
  header('Content-type: text/plain');
  header('Content-Disposition: attachment; filename=' . $filename );

  // put the content in the file
  echo($content);
  exit;
}
?>

<html>
<head>
<?php html_header_show();?>
<title><?php xl('Syndromic Surveillance - Non Reported Issues','e'); ?></title>
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>

<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 $(document).ready(function() {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));
 });

</script>

<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<style type="text/css">
/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
		margin-bottom: 10px;
    }
    #report_results table {
       margin-top: 0px;
    }
}
/* specifically exclude some from the screen */
@media screen {
	#report_parameters_daterange {
		visibility: hidden;
		display: none;
	}
	#report_results {
		width: 100%;
	}
}
</style>
</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Syndromic Surveillance - Non Reported Issues','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='non_reported.php'
onsubmit='return top.restoreSession()'>
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_get_hl7' id='form_get_hl7' value=''/>
<table>
 <tr>
  <td width='410px'>
    <div style='float:left'>
      <table class='text'>
        <tr>
          <td class='label'>
            <?php xl('Diagnosis','e'); ?>:
          </td>
          <td>
<?php
 // Build a drop-down list of codes.
 //
 $query1 = "select id, code as name, code_type from codes ".
   " where reportable=1 ORDER BY name";
 $cres = sqlStatement($query1);
 echo "   <select multiple='multiple' size='3' name='form_code[]'>\n";
 //echo "    <option value=''>-- " . xl('All Codes') . " --\n";
 while ($crow = sqlFetchArray($cres)) {
  if (convert_type_id_to_key($crow['code_type']) == "ICD9") {
   // This report currently only works for ICD9 codes. Need to make this work for other
   // diagnosis code sets in the future.
   $crow['name'] = convert_type_id_to_key($crow['code_type']) . ":" . $crow['name'];
   $codeid = $crow['id'];
   echo "    <option value='$codeid'";
   if (in_array($codeid, $form_code)) echo " selected";
   echo ">" . $crow['name'] . "\n";
  }
 }
 echo "   </select>\n";
?>
          </td>
          <td class='label'>
            <?php xl('From','e'); ?>:
          </td>
          <td>
            <input type='text' name='form_from_date' id="form_from_date"
            size='10' value='<?php echo $form_from_date ?>'
            onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' 
            title='yyyy-mm-dd'>
            <img src='../pic/show_calendar.gif' align='absbottom' 
            width='24' height='22' id='img_from_date' border='0' 
            alt='[?]' style='cursor:pointer'
            title='<?php xl('Click here to choose a date','e'); ?>'>
          </td>
          <td class='label'>
            <?php xl('To','e'); ?>:
          </td>
          <td>
            <input type='text' name='form_to_date' id="form_to_date" 
            size='10' value='<?php echo $form_to_date ?>'
            onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' 
            title='yyyy-mm-dd'>
            <img src='../pic/show_calendar.gif' align='absbottom' 
            width='24' height='22' id='img_to_date' border='0' 
            alt='[?]' style='cursor:pointer'
            title='<?php xl('Click here to choose a date','e'); ?>'>
          </td>
        </tr>
      </table>
    </div>
  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
      <tr>
        <td>
          <div style='margin-left:15px'>
            <a href='#' class='css_button' 
            onclick='
            $("#form_refresh").attr("value","true"); 
            $("#form_get_hl7").attr("value","false"); 
            $("#theform").submit();
            '>
            <span>
              <?php xl('Refresh','e'); ?>
            </spain>
            </a>
            <?php if ($_POST['form_refresh']) { ?>
              <a href='#' class='css_button' id='printbutton'>
                <span>
                  <?php echo xlt('Print'); ?>
                </span>
              </a>
              <a href='#' class='css_button' onclick=
              "if(confirm('<?php xl('This step will generate a file which you have to save for future use. The file cannot be generated again. Do you want to proceed?','e'); ?>')) {
                     $('#form_get_hl7').attr('value','true'); 
                     $('#theform').submit();
              }">
                <span>
                  <?php xl('Get HL7','e'); ?>
                </span>
              </a>
            <?php } ?>
          </div>
        </td>
      </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->


<?php
 if ($_POST['form_refresh']) {
?>
<div id="report_results">
<table>
 <thead align="left">
  <th> <?php xl('Patient ID','e'); ?> </th>
  <th> <?php xl('Patient Name','e'); ?> </th>
  <th> <?php xl('Diagnosis','e'); ?> </th>
  <th> <?php xl('Issue ID','e'); ?> </th>
  <th> <?php xl('Issue Title','e'); ?> </th>
  <th> <?php xl('Issue Date','e'); ?> </th>
 </thead>
 <tbody>
<?php
  $total = 0;
  //echo "<p> DEBUG query: $query </p>\n"; // debugging
  $res = sqlStatement($query);


  while ($row = sqlFetchArray($res)) {
?>
 <tr>
  <td>
  <?php echo htmlspecialchars($row['patientid']) ?>
  </td>
  <td>
   <?php echo htmlspecialchars($row['patientname']) ?>
  </td>
  <td>
   <?php echo htmlspecialchars($row['diagnosis']) ?>
  </td>
  <td>
   <?php echo htmlspecialchars($row['issueid']) ?>
  </td>
  <td>
   <?php echo htmlspecialchars($row['issuetitle']) ?>
  </td>
  <td>
   <?php echo htmlspecialchars($row['issuedate']) ?>
  </td>
 </tr>
<?php
   ++$total;
  }
?>
 <tr class="report_totals">
  <td colspan='9'>
   <?php xl('Total Number of Issues','e'); ?>
   :
   <?php echo $total ?>
  </td>
 </tr>

</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
  <?php echo xlt('Click Refresh to view all results, or please input search criteria above to view specific results.'); ?><br>
  (<?php echo xlt('This report currently only works for ICD9 codes.'); ?>)
</div>
<?php } ?>
</form>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</body>
</html>
