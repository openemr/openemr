<?php

/**
 * Superbill Report
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__file__) . "/../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/report.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

if (!AclMain::aclCheckCore('encounters', 'coding_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Superbill")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$facilityService = new FacilityService();

$startdate = $enddate = "";
if (empty($_POST['start']) || empty($_POST['end'])) {
    // set some default dates
    $startdate = date('Y-m-d', (time() - 30 * 24 * 60 * 60));
    $enddate = date('Y-m-d', time());
} else {
    // set dates
    $startdate = DateToYYYYMMDD($_POST['start']);
    $enddate = DateToYYYYMMDD($_POST['end']);
}

//Patient related stuff
if (!empty($_POST["form_patient"])) {
    $form_patient = isset($_POST['form_patient']) ? $_POST['form_patient'] : '';
}

$form_pid = isset($_POST['form_pid']) ? $_POST['form_pid'] : '';
if (empty($form_patient)) {
    $form_pid = '';
}
?>
<html>

<head>

<?php Header::setupHeader('datetime-picker'); ?>

<style>

@media print {
    .title {
        visibility: hidden;
    }
    .pagebreak {
        page-break-after: always;
        border: none;
        visibility: hidden;
    }

    #superbill_description {
        visibility: hidden;
    }

    #report_parameters {
        visibility: hidden;
    }
    #superbill_results {
       margin-top: -30px;
    }
}

@media screen {
    .title {
        visibility: visible;
    }
    #superbill_description {
        visibility: visible;
    }
    .pagebreak {
        width: 100%;
        border: 2px dashed var(--black);
    }
    #report_parameters {
        visibility: visible;
    }
}
#superbill_description,
#superbill_startingdate,
#superbill_endingdate {
    margin: 10px;
}
#superbill_patientdata h1 {
    font-weight: bold;
    font-size: 1.2rem;
    margin: 0px;
    padding: 5px;
    width: 100%;
    background-color: var(--gray200);
    border: 1px solid var(--black);
}
#superbill_insurancedata {
    margin-top: 10px;
}
#superbill_insurancedata h1 {
    font-weight: bold;
    font-size: 1.2rem;
    margin: 0px;
    padding: 5px;
    width: 100%;
    background-color: var(--gray200);
    border: 1px solid var(--black);
}
#superbill_insurancedata h2 {
    font-weight: bold;
    font-size: 1.0rem;
    margin: 0px;
    padding: 0px;
    width: 100%;
    background-color: var(--gray200);
}
#superbill_billingdata {
    margin-top: 10px;
}
#superbill_billingdata h1 {
    font-weight: bold;
    font-size: 1.2rem;
    margin: 0px;
    padding: 5px;
    width: 100%;
    background-color: var(--gray200);
    border: 1px solid var(--black);
}
</style>

<script>
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

// CapMinds :: invokes  find-patient popup.
 function sel_patient() {
  dlgopen('../main/calendar/find_patient_popup.php?pflag=0', '_blank', 500, 400);
 }

// CapMinds :: callback by the find-patient popup.
 function setpatient(pid, lname, fname, dob) {
  var f = document.theform;
  f.form_patient.value = lname + ', ' + fname;
  f.form_pid.value = pid;

 }
</script>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Reports'); ?> - <?php echo xlt('Superbill'); ?></span>

<div id="superbill_description" class='text'>
<?php echo xlt('Superbills, sometimes referred to as Encounter Forms or Routing Slips, are an essential part of most medical practices.'); ?>
</div>

<div id="report_parameters">

<form method="post" name="theform" id='theform' action="custom_report_range.php">
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='650px'>
    <div style='float: left'>

    <table class='text'>
        <tr>
            <td class='label_custom'>
                <?php echo xlt('Start Date'); ?>:
            </td>
            <td>
               <input type='text' class='form-control datepicker' name='start' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($startdate)); ?>' />
            </td>
            <td class='label_custom'>
                <?php echo xlt('End Date'); ?>:
            </td>
            <td>
               <input type='text' class='form-control datepicker' name='end' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($enddate)); ?>' />
            </td>

            <td>
            &nbsp;&nbsp;<span class='text'><?php echo xlt('Patient'); ?>: </span>
            </td>
            <td>
            <input type='text' class='form-control' size='20' name='form_patient' style='width:100%;cursor:pointer;' value='<?php echo (!empty($form_patient)) ? attr($form_patient) : xla('Click To Select'); ?>' onclick='sel_patient()' title='<?php echo xla('Click to select patient'); ?>' />
            <input type='hidden' name='form_pid' value='<?php echo attr($form_pid); ?>' />
            </td>
            </tr>
            <tr><td>
        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class="w-100 h-100" style='border-left:1px solid;' >
        <tr>
            <td>
                <div style='margin-left:15px'>
                    <a href='#' class='btn btn-primary' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                    <span>
                        <?php echo xlt('Submit'); ?>
                    </span>
                    </a>

                    <?php if (!empty($_POST['form_refresh'])) { ?>
                    <a href='#' class='btn btn-primary' id='printbutton'>
                        <span>
                            <?php echo xlt('Print'); ?>
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

</form>

<div id="superbill_results">

<?php
if (!(empty($_POST['start']) || empty($_POST['end']))) {
    $facility = $facilityService->getPrimaryBillingLocation();
    ?>
<p>
<h2><?php echo text($facility['name'] ?? '')?></h2>
    <?php echo text($facility['street'] ?? '')?><br />
    <?php echo text($facility['city'] ?? '')?>, <?php echo text($facility['state'] ?? '')?> <?php echo text($facility['postal_code'] ?? '')?><br />

</p>
    <?php
        $sqlBindArray = array();
        $res_query =    "select * from forms where " .
                        "form_name = 'New Patient Encounter' and " .
                        "date between ? and ? " ;
                array_push($sqlBindArray, $startdate, $enddate);
    if ($form_pid) {
        $res_query .= " and pid=? ";
        array_push($sqlBindArray, $form_pid);
    }

        $res_query .=     " order by date DESC" ;
        $res = sqlStatement($res_query, $sqlBindArray);

    while ($result = sqlFetchArray($res)) {
        if ($result["form_name"] == "New Patient Encounter") {
            $newpatient[] = $result["form_id"] . ":" . $result["encounter"];
            $pids[] = $result["pid"];
        }
    }

    $N = 6;

    function postToGet($newpatient, $pids)
    {
        $getstring = "";
        $serialnewpatient = serialize($newpatient);
        $serialpids = serialize($pids);
        $getstring = "newpatient=" . urlencode($serialnewpatient) . "&pids=" . urlencode($serialpids);

        return $getstring;
    }

    $iCounter = 0;
    if (empty($newpatient)) {
        $newpatient = array();
    }

    foreach ($newpatient as $patient) {
        /*
        $inclookupres = sqlStatement("select distinct formdir from forms where pid='".$pids[$iCounter]."'");
        while($result = sqlFetchArray($inclookupres)) {
        include_once("{$GLOBALS['incdir']}/forms/" . $result["formdir"] . "/report.php");
        }
        */

        print "<div id='superbill_patientdata'>";
        print "<h1>" . xlt('Patient Data') . ":</h1>";
        printRecDataOne($patient_data_array, getRecPatientData($pids[$iCounter]), $N);
        print "</div>";

        print "<div id='superbill_insurancedata'>";
        print "<h1>" . xlt('Insurance Data') . ":</h1>";
        print "<h2>" . xlt('Primary') . ":</h2>";
        printRecDataOne($insurance_data_array, getRecInsuranceData($pids[$iCounter], "primary"), $N);
        print "<h2>" . xlt('Secondary') . ":</h2>";
        printRecDataOne($insurance_data_array, getRecInsuranceData($pids[$iCounter], "secondary"), $N);
        print "<h2>" . xlt('Tertiary') . ":</h2>";
        printRecDataOne($insurance_data_array, getRecInsuranceData($pids[$iCounter], "tertiary"), $N);
        print "</div>";

        print "<div id='superbill_billingdata'>";
        print "<h1>" . xlt('Billing Information') . ":</h1>";
        if (!empty($patient) && is_array($patient) && count($patient) > 0) {
            $billings = array();
            echo "<table class='table w-100'>";
            echo "<tr>";
            echo "<td class='bold' width='10%'>" . xlt('Date') . "</td>";
            echo "<td class='bold' width='20%'>" . xlt('Provider') . "</td>";
            echo "<td class='bold' width='40%'>" . xlt('Code') . "</td>";
            echo "<td class='bold' width='10%'>" . xlt('Fee') . "</td></tr>\n";
            $total = 0.00;
            $copays = 0.00;
            //foreach ($patient as $be) {

            $ta = explode(":", $patient);
            $billing = getPatientBillingEncounter($pids[$iCounter], $ta[1]);

            $billings[] = $billing;
            foreach ($billing as $b) {
                // grab the date to reformat it in the output
                $bdate = strtotime($b['date']);

                echo "<tr>\n";
                echo "<td class='text' style='font-size: 0.8em'>" . text(oeFormatShortDate(date("Y-m-d", $bdate))) . "<BR>" . date("h:i a", $bdate) . "</td>";
                echo "<td class='text'>" . text($b['provider_name']) . "</td>";
                echo "<td class='text'>";
                echo text($b['code_type']) . ":\t" . text($b['code']) . "&nbsp;" . text($b['modifier']) . "&nbsp;&nbsp;&nbsp;" . text($b['code_text']) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                echo "</td>\n";
                echo "<td class='text'>";
                echo oeFormatMoney($b['fee']);
                echo "</td>\n";
                echo "</tr>\n";
                $total += $b['fee'];
            }

            // Calculate the copay for the encounter
            $copays = BillingUtilities::getPatientCopay($pids[$iCounter], $ta[1]);
            //}
            echo "<tr><td>&nbsp;</td></tr>";
            echo "<tr><td class='font-weight-bold text-right' colspan='3'>" . xlt('Sub-Total') . "</td><td class='text'>" . text(oeFormatMoney($total + abs($copays))) . "</td></tr>";
            echo "<tr><td class='font-weight-bold text-right' colspan='3'>" . xlt('Copay Paid') . "</td><td class='text'>" . text(oeFormatMoney(abs($copays))) . "</td></tr>";
            echo "<tr><td class='font-weight-bold text-right' colspan='3'>" . xlt('Total') . "</td><td class='text'>" . text(oeFormatMoney($total)) . "</td></tr>";
            echo "</table>";
            echo "<pre>";
            //print_r($billings);
            echo "</pre>";
        }

        echo "</div>";

        ++$iCounter;
        print "<br/><br/>" . xlt('Physician Signature') . ":  _______________________________________________";
        print "<hr class='pagebreak' />";
    }
}
?>
</div>

    </body>

</html>
