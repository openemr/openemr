<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
// Copyright (C) 2010 Tomasz Wyderka <wyderkat@cofoh.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report lists non reported patient diagnoses for a given date range.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("../../custom/code_types.inc.php");

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
  "l.id as issueid, l.title as issuetitle ".
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
$nowdate = date('Ymd');
$now = date('YmdGi');
$now1 = date('Y-m-d G:i');
$filename = "syn_sur_". $now . ".hl7";

// GENERATE HL7 FILE
if ($_POST['form_get_hl7']==='true') {
  $content = ''; 
  //$content.="FHS|^~\&|OPENEMR||||$now||$filename||||$D";
  //$content.="BHS|^~\&|OPENEMR||||$now||SyndromicSurveillance||||$D";

  $res = sqlStatement($query);

  while ($r = sqlFetchArray($res)) {
    $content .= "MSH|^~\&|OPENEMR||||$nowdate||".
      "ADT^A08|$nowdate|P^T|2.5.1|||||||||$D";
    $content .= "EVN|" . // [[ 3.69 ]]
        "A08|" . // 1.B Event Type Code
        "$now|" . // 2.R Recorded Date/Time
        "|" . // 3. Date/Time Planned Event
        "|" . // 4. Event Reason Cod
        "|" . // 5. Operator ID
        "|" . // 6. Event Occurred
        "" . // 7. Event Facility
        "$D" ;
    if ($r['sex']==='Male') $r['sex'] = 'M';
    if ($r['sex']==='Female') $r['sex'] = 'F';
    if ($r['status']==='married') $r['status'] = 'M';
    if ($r['status']==='single') $r['status'] = 'S';
    if ($r['status']==='divorced') $r['status'] = 'D';
    if ($r['status']==='widowed') $r['status'] = 'W';
    if ($r['status']==='separated') $r['status'] = 'A';
    if ($r['status']==='domestic partner') $r['status'] = 'P';
    $content .= "PID|" . // [[ 3.72 ]]
        "|" . // 1. Set id
        "|" . // 2. (B)Patient id
        $r['patientid']."|". // 3. (R) Patient indentifier list
        "|" . // 4. (B) Alternate PID
        $r['patientname']."|" . // 5.R. Name
        "|" . // 6. Mather Maiden Name
        $r['DOB']."|" . // 7. Date, time of birth
        $r['sex']."|" . // 8. Sex
        "|" . // 9.B Patient Alias
        //$r['ethnoracial']."|" . // 10. Race
        "|" . // 10. Race
        $r['address']."|" . // 11. Address
        $r['country_code']."|" . // 12. country code
        $r['phone_home']."|" . // 13. Phone Home
        $r['phone_biz']."|" . // 14. Phone Bussines
        "|" . // 15. Primary language
        $r['status']."|" . // 16. Marital status
        "|" . // 17. Religion
        "|" . // 18. patient Account Number
        "|" . // 19.B SSN Number
        "|" . // 20.B Driver license number
        "|" . // 21. Mathers Identifier
        "|" . // 22. Ethnic Group
        "|" . // 23. Birth Plase
        "|" . // 24. Multiple birth indicator
        "|" . // 25. Birth order
        "|" . // 26. Citizenship
        "|" . // 27. Veteran military status
        "|" . // 28.B Nationality
        "|" . // 29. Patient Death Date and Time
        "|" . // 30. Patient Death Indicator
        "|" . // 31. Identity Unknown Indicator
        "|" . // 32. Identity Reliability Code
        "|" . // 33. Last Update Date/Time
        "|" . // 34. Last Update Facility
        "|" . // 35. Species Code
        "|" . // 36. Breed Code
        "|" . // 37. Breed Code
        "|" . // 38. Production Class Code
        ""  . // 39. Tribal Citizenship
        "$D" ;
    $content .= "PV1|" . // [[ 3.86 ]]
        "|" . // 1. Set ID
        "U|" . // 2.R Patient Class (U - unknown)
        "" . // 3. ... 52.
        "$D" ;
    $content .= "DG1|" . // [[ 6.24 ]]
        "1|" . // 1. Set ID
        $r['diagnosis']."|" . // 2.B.R Diagnosis Coding Method
        $r['code']."|" . // 3. Diagnosis Code - DG1
        $r['code_text']."|" . // 4.B Diagnosis Description
        $r['issuedate']."|" . // 5. Diagnosis Date/Time
        "W|" . // 6.R Diagnosis Type  // A - Admiting, W - working
        "|" . // 7.B Major Diagnostic Category
        "|" . // 8.B Diagnostic Related Group
        "|" . // 9.B DRG Approval Indicator 
        "|" . // 10.B DRG Grouper Review Code
        "|" . // 11.B Outlier Type 
        "|" . // 12.B Outlier Days
        "|" . // 13.B Outlier Cost
        "|" . // 14.B Grouper Version And Type 
        "|" . // 15. Diagnosis Priority
        "|" . // 16. Diagnosing Clinician
        "|" . // 17. Diagnosis Classification
        "|" . // 18. Confidential Indicator
        "|" . // 19. Attestation Date/Time
        "|" . // 20.C Diagnosis Identifier
        "" . // 21.C Diagnosis Action Code
        "$D" ;
        
        // mark if issues generated/sent
        $query_insert = "insert into syndromic_surveillance(lists_id,submission_date,filename) " .
         "values (" . $r['issueid'] . ",'" . $now1 . "','" . $filename . "')"; 
        sqlStatement($query_insert);
}
  //$content.="BTS|||$D";
  //$content.="FTS||$D";

  $content = tr($content);
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
              <a href='#' class='css_button' onclick='window.print()'>
                <span>
                  <?php xl('Print','e'); ?>
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
