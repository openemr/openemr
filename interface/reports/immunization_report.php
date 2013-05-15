<?php
// Copyright (C) 2011 Ensoftek Inc.
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This report lists  patient immunizations for a given date range.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/formatting.inc.php");

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

function tr($a) {
  return (str_replace(' ','^',$a));
}

function format_cvx_code($cvx_code) {

	if ( $cvx_code < 10 ) {
		return "0$cvx_code"; 
	}
	
	return $cvx_code;
}

function format_phone($phone) {

	$phone = preg_replace("/[^0-9]/", "", $phone);
	switch (strlen($phone))
	{
		case 7:
			return tr(preg_replace("/([0-9]{3})([0-9]{4})/", "000 $1$2", $phone));
		case 10:
			return tr(preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "$1 $2$3", $phone));
		default:
			return tr("000 0000000");
	}
}

function format_ethnicity($ethnicity) {

	switch ($ethnicity)
	{
		 case "hisp_or_latin":
		   return ("H^Hispanic or Latino^HL70189");
		 case "not_hisp_or_latin":
		   return ("N^not Hispanic or Latino^HL70189");
		 default: // Unknown
		   return ("U^Unknown^HL70189");
	}
 }


  $query = 
  "select " .
  "i.patient_id as patientid, " .
  "p.language, ".
  "i.cvx_code , " ;
  if ($_POST['form_get_hl7']==='true') {
    $query .= 
      "DATE_FORMAT(p.DOB,'%Y%m%d') as DOB, ".
      "concat(p.street, '^^', p.city, '^', p.state, '^', p.postal_code) as address, ".
      "p.country_code, ".
      "p.phone_home, ".
      "p.phone_biz, ".
      "p.status, ".
      "p.sex, ".
      "p.ethnoracial, ".
      "p.race, ". 
      "p.ethnicity, ".   
      "c.code_text, ".
      "c.code, ".
      "c.code_type, ".
      "DATE_FORMAT(i.vis_date,'%Y%m%d') as immunizationdate, ".
      "DATE_FORMAT(i.administered_date,'%Y%m%d') as administered_date, ".
      "i.lot_number as lot_number, ".
      "i.manufacturer as manufacturer, ".
       "concat(p.fname, '^', p.lname) as patientname, ";
  } else {
    $query .= "concat(p.fname, ' ',p.mname,' ', p.lname) as patientname, ".
      "i.vis_date as immunizationdate, "  ;
  }
  $query .=
  "i.id as immunizationid, c.code_text_short as immunizationtitle ".
  "from immunizations i, patient_data p, codes c ".
  "left join code_types ct on c.code_type = ct.ct_id ".
  "where ".
  "ct.ct_key='CVX' and ";
  if($from_date!=0) {
    $query .= "i.vis_date >= '$from_date' " ;
  }
  if($from_date!=0 and $to_date!=0) {
    $query .= " and " ;
  }
  if($to_date!=0) {
    $query .= "i.vis_date <= '$to_date' ";
  }
  if($from_date!=0 or $to_date!=0) {
    $query .= " and " ;
  }
  $query .= "i.patient_id=p.pid and ".
  $query_codes .
  "i.cvx_code = c.code and ";
  
  //do not show immunization added erroneously
  $query .=  "i.added_erroneously = 0";

//echo "<p> DEBUG query: $query </p>\n"; // debugging
  

$D="\r";
$nowdate = date('Ymd');
$now = date('YmdGi');
$now1 = date('Y-m-d G:i');
$filename = "imm_reg_". $now . ".hl7";

// GENERATE HL7 FILE
if ($_POST['form_get_hl7']==='true') {
	$content = ''; 

  $res = sqlStatement($query);

  while ($r = sqlFetchArray($res)) {
    $content .= "MSH|^~\&|OPENEMR||||$nowdate||".
      "VXU^V04^VXU_V04|OPENEMR-110316102457117|P|2.5.1" .
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
        $r['patientid']. "^^^MPI&2.16.840.1.113883.19.3.2.1&ISO^MR" . "|". // 3. (R) Patient indentifier list. TODO: Hard-coded the OID from NIST test. 
        "|" . // 4. (B) Alternate PID
        $r['patientname']."|" . // 5.R. Name
        "|" . // 6. Mather Maiden Name
        $r['DOB']."|" . // 7. Date, time of birth
        $r['sex']."|" . // 8. Sex
        "|" . // 9.B Patient Alias
        "2106-3^" . $r['race']. "^HL70005" . "|" . // 10. Race // Ram change
        $r['address'] . "^^M" . "|" . // 11. Address. Default to address type  Mailing Address(M)
        "|" . // 12. county code
        "^PRN^^^^" . format_phone($r['phone_home']) . "|" . // 13. Phone Home. Default to Primary Home Number(PRN)
        "^WPN^^^^" . format_phone($r['phone_biz']) . "|" . // 14. Phone Work.
        "|" . // 15. Primary language
        $r['status']."|" . // 16. Marital status
        "|" . // 17. Religion
        "|" . // 18. patient Account Number
        "|" . // 19.B SSN Number
        "|" . // 20.B Driver license number
        "|" . // 21. Mathers Identifier
        format_ethnicity($r['ethnicity']) . "|" . // 22. Ethnic Group
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
    $content .= "ORC" . // ORC mandatory for RXA
        "|" . 
        "RE" .
        "$D" ;
    $content .= "RXA|" . 
        "0|" . // 1. Give Sub-ID Counter
        "1|" . // 2. Administrattion Sub-ID Counter
    	$r['administered_date']."|" . // 3. Date/Time Start of Administration
    	$r['administered_date']."|" . // 4. Date/Time End of Administration
        format_cvx_code($r['code']). "^" . $r['immunizationtitle'] . "^" . "CVX" ."|" . // 5. Administration Code(CVX)
        "999|" . // 6. Administered Amount. TODO: Immunization amt currently not captured in database, default to 999(not recorded)
        "|" . // 7. Administered Units
        "|" . // 8. Administered Dosage Form
        "|" . // 9. Administration Notes
        "|" . // 10. Administering Provider
        "|" . // 11. Administered-at Location
        "|" . // 12. Administered Per (Time Unit)
        "|" . // 13. Administered Strength
        "|" . // 14. Administered Strength Units
    	$r['lot_number']."|" . // 15. Substance Lot Number
        "|" . // 16. Substance Expiration Date
    	"MSD" . "^" . $r['manufacturer']. "^" . "HL70227" . "|" . // 17. Substance Manufacturer Name
        "|" . // 18. Substance/Treatment Refusal Reason
        "|" . // 19.Indication
        "|" . // 20.Completion Status
        "A" . // 21.Action Code - RXA
        "$D" ;
        
}

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
<title><?php xl('Immunization Registry','e'); ?></title>
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

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Immunization Registry','e'); ?></span>

<div id="report_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='immunization_report.php'
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
            <?php xl('Codes','e'); ?>:
          </td>
          <td>
<?php
 // Build a drop-down list of codes.
 //
 $query1 = "select id, concat('CVX:',code) as name from codes ".
   " left join code_types ct on codes.code_type = ct.ct_id ".
   " where ct.ct_key='CVX' ORDER BY name";
 $cres = sqlStatement($query1);
 echo "   <select multiple='multiple' size='3' name='form_code[]'>\n";
 //echo "    <option value=''>-- " . xl('All Codes') . " --\n";
 while ($crow = sqlFetchArray($cres)) {
  $codeid = $crow['id'];
  echo "    <option value='$codeid'";
  if (in_array($codeid, $form_code)) echo " selected";
  echo ">" . $crow['name'] . "\n";
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
  <th> <?php xl('Immunization Code','e'); ?> </th>
  <th> <?php xl('Immunization Title','e'); ?> </th>
  <th> <?php xl('Immunization Date','e'); ?> </th>
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
   <?php echo htmlspecialchars($row['cvx_code']) ?>
  </td>
  <td>
   <?php echo htmlspecialchars($row['immunizationtitle']) ?>
  </td>
  <td>
   <?php echo htmlspecialchars($row['immunizationdate']) ?>
  </td>
 </tr>
<?php
   ++$total;
  }
?>
 <tr class="report_totals">
  <td colspan='9'>
   <?php xl('Total Number of Immunizations','e'); ?>
   :
   <?php echo $total ?>
  </td>
 </tr>

</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
  <?php echo xl('Click Refresh to view all results, or please input search criteria above to view specific results.', 'e' ); ?>
</div>
<?php } ?>
</form>

<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</body>
</html>
