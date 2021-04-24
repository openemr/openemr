<?php

/**
 * This report lists non reported patient diagnoses for a given date range.
 * Ensoftek: Jul-2015: Modified HL7 generation to 2.5.1 spec and MU2 compliant.
 * This implementation is only for the A01 profile which will suffice for MU2 certification.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Tomasz Wyderka <wyderkat@cofoh.com>
 * @author    Ensoftek <rammohan@ensoftek.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2010 Tomasz Wyderka <wyderkat@cofoh.com>
 * @copyright Copyright (c) 2015 Ensoftek <rammohan@ensoftek.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Ensoftek: Jul-2015: Get the facility of the logged in user.
function getLoggedInUserFacility()
{
    $sql = "SELECT f.name, f.facility_npi FROM users AS u LEFT JOIN facility AS f ON u.facility_id = f.id WHERE u.id=?";
    $res = sqlStatement($sql, array($_SESSION['authUserID']));
    while ($arow = sqlFetchArray($res)) {
        return $arow;
    }

    return null;
}

// Ensoftek: Jul-2015: Map codes to confirm to HL7.
function mapCodeType($incode)
{
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


$from_date = (!empty($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : '';
$to_date = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : '';

//
function tr($a)
{
    return (str_replace(' ', '^', $a));
}

  $sqlBindArray = array();
  $query =
  "select " .
  "l.pid as patientid, " .
  "p.language, " .
  "l.diagnosis , " ;
if (!empty($_POST['form_get_hl7']) && ($_POST['form_get_hl7'] === 'true')) {
    $query .=
    "DATE_FORMAT(p.DOB,'%Y%m%d') as DOB, " .
    "concat(p.street, '^',p.postal_code,'^', p.city, '^', p.state) as address, " .
    "p.country_code, " .
    "p.phone_home, " .
    "p.phone_biz, " .
    "p.status, " .
    "p.sex, " .
    "p.ethnoracial, " .
    "c.code_text, " .
    "c.code, " .
    "c.code_type, " .
    "DATE_FORMAT(l.date,'%Y%m%d') as issuedate, " .
    "concat(p.fname, '^',p.mname,'^', p.lname) as patientname, ";
} else {
    $query .= "concat(p.fname, ' ',p.mname,' ', p.lname) as patientname, " .
    "l.date as issuedate, "  ;
}

  $query .=
  "l.id as issueid, l.title as issuetitle, DATE_FORMAT(l.begdate,'%Y%m%d%H%i') as begin_date " . // Ensoftek: Jul-2015: Get begin date
  "from lists l, patient_data p, codes c " .
  "where " .
  "c.reportable=1 and " .
  "l.id not in (select lists_id from syndromic_surveillance) and ";
if (!empty($from_date)) {
    $query .= "l.date >= ? " ;
    array_push($sqlBindArray, $from_date);
}

if (!empty($from_date) && !empty($to_date)) {
    $query .= " and " ;
}

if (!empty($to_date)) {
    $query .= "l.date <= ? ";
    array_push($sqlBindArray, $to_date);
}

if (!empty($from_date) || !empty($to_date)) {
    $query .= " and " ;
}

$form_code = isset($_POST['form_code']) ? $_POST['form_code'] : array();
if (empty($form_code)) {
    $query_codes = '';
} else {
    $query_codes = 'c.id in (';
    foreach ($form_code as $code) {
        $query_codes .= '?,';
        array_push($sqlBindArray, $code);
    }
    $query_codes = substr($query_codes, 0, -1);
    $query_codes .= ') and ';
}

  $query .= "l.pid=p.pid and " .
  $query_codes .
  "l.diagnosis LIKE 'ICD9:%' and " .
  "substring(l.diagnosis,6) = c.code ";

//echo "<p> DEBUG query: $query </p>\n"; // debugging

$D = "\r";
$nowdate = date('YmdHi');
$now = date('YmdGi');
$now1 = date('Y-m-d G:i');
$filename = "syn_sur_" . $now . ".hl7";


// Ensoftek: Jul-2015: Get logged in user's facility to be used in the MSH segment
$facility_info = getLoggedInUserFacility();

// GENERATE HL7 FILE
if (!empty($_POST['form_get_hl7']) && ($_POST['form_get_hl7'] === 'true')) {
    $content = '';

    $res = sqlStatement($query, $sqlBindArray);

    while ($r = sqlFetchArray($res)) {
        // MSH
        $content .= "MSH|^~\&|" . strtoupper($openemr_name) .
        "|" . $facility_info['name'] . "^" . $facility_info['facility_npi'] . "^NPI" .
        "|||$now||" .
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

        if ($r['sex'] === 'Male') {
            $r['sex'] = 'M';
        }

        if ($r['sex'] === 'Female') {
            $r['sex'] = 'F';
        }

        if ($r['status'] === 'married') {
            $r['status'] = 'M';
        }

        if ($r['status'] === 'single') {
            $r['status'] = 'S';
        }

        if ($r['status'] === 'divorced') {
            $r['status'] = 'D';
        }

        if ($r['status'] === 'widowed') {
            $r['status'] = 'W';
        }

        if ($r['status'] === 'separated') {
            $r['status'] = 'A';
        }

        if ($r['status'] === 'domestic partner') {
            $r['status'] = 'P';
        }

        // PID
        $content .= "PID|" .
        "1|" . // 1. Set id
        "|" .
        $r['patientid'] . "^^^^MR" . "|" . // 3. (R) Patient indentifier list
        "|" . // 4. (B) Alternate PID
        "^^^^^^~^^^^^^S" . "|" . // 5.R. Name
        "|" . // 6. Mather Maiden Name
        $r['DOB'] . "|" . // 7. Date, time of birth
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
        $query_insert = "insert into syndromic_surveillance(lists_id, submission_date, filename) " .
         "values (?, ?, ?)";
        sqlStatement($query_insert, array($r['issueid'], $now1, $filename));
    }

  // Ensoftek: Jul-2015: No need to tr the content
  //$content = tr($content);

  // send the header here
    header('Content-type: text/plain');
    header('Content-Disposition: attachment; filename=' . $filename);

  // put the content in the file
    echo($content);
    exit;
}
?>

<html>
<head>
    <title><?php echo xlt('Syndromic Surveillance - Non Reported Issues'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

    <script>

        <?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

        $(function () {
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));

            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });

    </script>

    <style>
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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Syndromic Surveillance - Non Reported Issues'); ?></span>

<div id="report_parameters_daterange">
<?php echo text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt('to{{Range}}')  . "&nbsp; " . text(oeFormatShortDate($to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='non_reported.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_get_hl7' id='form_get_hl7' value=''/>
<table>
 <tr>
  <td width='410px'>
    <div style='float:left'>
      <table class='text'>
        <tr>
          <td class='col-form-label'>
            <?php echo xlt('Diagnosis'); ?>:
          </td>
          <td>
<?php
 // Build a drop-down list of codes.
 //
 $query1 = "select id, code as name, code_type from codes " .
   " where reportable=1 ORDER BY name";
 $cres = sqlStatement($query1);
 echo "   <select multiple='multiple' size='3' name='form_code[]' class='form-control'>\n";
 //echo "    <option value=''>-- " . xl('All Codes') . " --\n";
while ($crow = sqlFetchArray($cres)) {
    if (convert_type_id_to_key($crow['code_type']) == "ICD9") {
       // This report currently only works for ICD9 codes. Need to make this work for other
       // diagnosis code sets in the future.
        $crow['name'] = convert_type_id_to_key($crow['code_type']) . ":" . $crow['name'];
        $codeid = $crow['id'];
        echo "    <option value='" . attr($codeid) . "'";
        if (in_array($codeid, $form_code)) {
            echo " selected";
        }

        echo ">" . text($crow['name']) . "\n";
    }
}

 echo "   </select>\n";
?>
          </td>
          <td class='col-form-label'>
            <?php echo xlt('From'); ?>:
          </td>
          <td>
            <input type='text' name='form_from_date' id="form_from_date"
            class='datepicker form-control'
            size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
          </td>
          <td class='col-form-label'>
            <?php echo xlt('To{{Range}}'); ?>:
          </td>
          <td>
            <input type='text' name='form_to_date' id="form_to_date"
            class='datepicker form-control'
            size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
          </td>
        </tr>
      </table>
    </div>
  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
      <tr>
        <td>
          <div class="text-center">
            <div class="btn-group" role="group">
              <a href='#' class='btn btn-secondary btn-refresh'
                onclick='
                  $("#form_refresh").attr("value","true");
                  $("#form_get_hl7").attr("value","false");
                  $("#theform").submit();
                '>
                <?php echo xlt('Refresh'); ?>
              </a>
                <?php if (!empty($_POST['form_refresh'])) { ?>
                <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                    <?php echo xlt('Print'); ?>
                </a>
                <a href='#' class='btn btn-secondary btn-transmit' onclick=
                  "if(confirm(<?php echo xlj('This step will generate a file which you have to save for future use. The file cannot be generated again. Do you want to proceed?'); ?>)) {
                    $('#form_get_hl7').attr('value','true');
                    $('#theform').submit();
                  }">
                    <?php echo xlt('Get HL7'); ?>
                </a>
                <?php } ?>
            </div>
          </div>
        </td>
      </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->


<?php
if (!empty($_POST['form_refresh'])) {
    ?>
<div id="report_results">
<table class='table'>
<thead class='thead-light' align="left">
<th> <?php echo xlt('Patient ID'); ?> </th>
<th> <?php echo xlt('Patient Name'); ?> </th>
<th> <?php echo xlt('Diagnosis'); ?> </th>
<th> <?php echo xlt('Issue ID'); ?> </th>
<th> <?php echo xlt('Issue Title'); ?> </th>
<th> <?php echo xlt('Issue Date'); ?> </th>
</thead>
<tbody>
    <?php
    $total = 0;
//echo "<p> DEBUG query: $query </p>\n"; // debugging
    $res = sqlStatement($query, $sqlBindArray);


    while ($row = sqlFetchArray($res)) {
        ?>
<tr>
<td>
        <?php echo text($row['patientid']) ?>
</td>
<td>
        <?php echo text($row['patientname']) ?>
</td>
<td>
        <?php echo text($row['diagnosis']) ?>
</td>
<td>
        <?php echo text($row['issueid']) ?>
</td>
<td>
        <?php echo text($row['issuetitle']) ?>
</td>
<td>
        <?php echo text($row['issuedate']) ?>
</td>
</tr>
        <?php
        ++$total;
    }
    ?>
<tr class="report_totals">
 <td colspan='9'>
    <?php echo xlt('Total Number of Issues'); ?>
  :
    <?php echo text($total); ?>
 </td>
</tr>

</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Click Refresh to view all results, or please input search criteria above to view specific results.'); ?><br />
  (<?php echo xlt('This report currently only works for ICD9 codes.'); ?>)
</div>
<?php } ?>
</form>

</body>
</html>
