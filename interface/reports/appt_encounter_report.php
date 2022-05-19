<?php

/**
 * This report cross-references appointments with encounters.
 * For a given date, show a line for each appointment with the
 * matching encounter, and also for each encounter that has no
 * matching appointment.  This helps to catch these errors:
 *
 * * Appointments with no encounter
 * * Encounters with no appointment
 * * Codes not justified
 * * Codes not authorized
 * * Procedure codes without a fee
 * * Fees assigned to diagnoses (instead of procedures)
 * * Encounters not billed
 *
 * For decent performance the following indexes are highly recommended:
 *   openemr_postcalendar_events.pc_eventDate
 *   forms.encounter
 *   billing.pid_encounter
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2005-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Billing\BillingUtilities;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Appointments and Encounters")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$facilityService = new FacilityService();

$errmsg  = "";
$alertmsg = ''; // not used yet but maybe later
$grand_total_charges    = 0;
$grand_total_copays     = 0;
$grand_total_encounters = 0;

function postError($msg)
{
    global $errmsg;
    if ($errmsg) {
        $errmsg .= '<br />';
    }

    $errmsg .= text($msg);
}

function bucks($amount)
{
    if ($amount) {
        return oeFormatMoney($amount);
    }
}

function endDoctor(&$docrow)
{
    global $grand_total_charges, $grand_total_copays, $grand_total_encounters;
    if (!$docrow['docname']) {
        return;
    }

    echo " <tr class='report_totals'>\n";
    echo "  <td colspan='5'>\n";
    echo "   &nbsp;" . xlt('Totals for') . ' ' . text($docrow['docname']) . "\n";
    echo "  </td>\n";
    echo "  <td align='right'>\n";
    echo "   &nbsp;" . text($docrow['encounters']) . "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td align='right'>\n";
    echo "   &nbsp;";
    echo text(bucks($docrow['charges']));
    echo "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td align='right'>\n";
    echo "   &nbsp;";
    echo text(bucks($docrow['copays']));
    echo "&nbsp;\n";
    echo "  </td>\n";
    echo "  <td colspan='2'>\n";
    echo "   &nbsp;\n";
    echo "  </td>\n";
    echo " </tr>\n";

    $grand_total_charges     += $docrow['charges'];
    $grand_total_copays      += $docrow['copays'];
    $grand_total_encounters  += $docrow['encounters'];

    $docrow['charges']     = 0;
    $docrow['copays']      = 0;
    $docrow['encounters']  = 0;
}

$form_facility  = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
$form_from_date = (isset($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = (isset($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
if (!empty($_POST['form_refresh'])) {
    // MySQL doesn't grok full outer joins so we do it the hard way.
    //
    $sqlBindArray = array();
    $query = "( " .
    "SELECT " .
    "e.pc_eventDate, e.pc_startTime, " .
    "fe.encounter, fe.date AS encdate, " .
    "f.authorized, " .
    "p.fname, p.lname, p.pid, p.pubpid, " .
    "CONCAT( u.lname, ', ', u.fname ) AS docname " .
    "FROM openemr_postcalendar_events AS e " .
    "LEFT OUTER JOIN form_encounter AS fe " .
    "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid " .
    "LEFT OUTER JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
    // "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
    "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
    if ($form_to_date) {
        $query .= "e.pc_eventDate >= ? AND e.pc_eventDate <= ? ";
        array_push($sqlBindArray, $form_from_date, $form_to_date);
    } else {
        $query .= "e.pc_eventDate = ? ";
        array_push($sqlBindArray, $form_from_date);
    }

    if ($form_facility !== '') {
        $query .= "AND e.pc_facility = ? ";
        array_push($sqlBindArray, $form_facility);
    }

    // $query .= "AND ( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
    $query .= "AND e.pc_pid != '' AND e.pc_apptstatus != ? " .
    ") UNION ( " .
    "SELECT " .
    "e.pc_eventDate, e.pc_startTime, " .
    "fe.encounter, fe.date AS encdate, " .
    "f.authorized, " .
    "p.fname, p.lname, p.pid, p.pubpid, " .
    "CONCAT( u.lname, ', ', u.fname ) AS docname " .
    "FROM form_encounter AS fe " .
    "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
    "ON fe.date = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
    // "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
    "e.pc_pid != '' AND e.pc_apptstatus != ? " .
    "LEFT OUTER JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
    // "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
    "LEFT OUTER JOIN users AS u ON u.id = fe.provider_id WHERE ";
    array_push($sqlBindArray, '?', '?');
    if ($form_to_date) {
        // $query .= "LEFT(fe.date, 10) >= '$form_from_date' AND LEFT(fe.date, 10) <= '$form_to_date' ";
        $query .= "fe.date >= ? AND fe.date <= ? ";
        array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_to_date . ' 23:59:59');
    } else {
       // $query .= "LEFT(fe.date, 10) = '$form_from_date' ";
        $query .= "fe.date >= ? AND fe.date <= ? ";
        array_push($sqlBindArray, $form_from_date . ' 00:00:00', $form_from_date . ' 23:59:59');
    }

    if ($form_facility !== '') {
        $query .= "AND fe.facility_id = ? ";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= ") ORDER BY docname, IFNULL(pc_eventDate, encdate), pc_startTime";

    $res = sqlStatement($query, $sqlBindArray);
}
?>
<html>
<head>
    <title><?php echo xlt('Appointments and Encounters'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

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
        }
    </style>

    <script>
        $(function () {
            oeFixedHeaderSetup(document.getElementById('mymaintable'));
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
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Appointments and Encounters'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form method='post' id='theform' action='appt_encounter_report.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<table>
 <tr>
  <td width='630px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
                <?php
                 // Build a drop-down list of facilities.
                 //
                $fres = $facilityService->getAllFacility();
                echo "   <select name='form_facility' class='form-control'>\n";
                echo "    <option value=''>-- " . xlt('All Facilities') . " --\n";
                foreach ($fres as $frow) {
                    $facid = $frow['id'];
                    echo "    <option value='" . attr($facid) . "'";
                    if ($facid == $form_facility) {
                        echo " selected";
                    }
                    echo ">" . text($frow['name']) . "\n";
                }

                echo "    <option value='0'";
                if ($form_facility === '0') {
                    echo " selected";
                }

                 echo ">-- " . xlt('Unspecified') . " --\n";
                 echo "   </select>\n";
                ?>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('DOS'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' >
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php  echo attr(oeFormatShortDate($form_to_date)); ?>' >
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>
        <div class="checkbox">
                <label><input type='checkbox' name='form_details'
                  value='1'<?php echo (!empty($_POST['form_details'])) ? " checked" : ""; ?>><?php echo xlt('Details') ?></label>
        </div>
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
                    <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                    </a>
                    <?php if (!empty($_POST['form_refresh'])) { ?>
                      <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                            <?php echo xlt('Print'); ?>
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

</div> <!-- end apptenc_report_parameters -->

<?php
if (!empty($_POST['form_refresh'])) {
    ?>
<div id="report_results">
<table class='table' id='mymaintable'>

<thead class='thead-light'>
<th> &nbsp;<?php echo xlt('Practitioner'); ?> </th>
<th> &nbsp;<?php echo xlt('Date/Appt'); ?> </th>
<th> &nbsp;<?php echo xlt('Patient'); ?> </th>
<th> &nbsp;<?php echo xlt('ID'); ?> </th>
<th align='right'> <?php echo xlt('Chart'); ?>&nbsp; </th>
<th align='right'> <?php echo xlt('Encounter'); ?>&nbsp; </th>
<th align='right'> <?php echo xlt('Charges'); ?>&nbsp; </th>
<th align='right'> <?php echo xlt('Copays'); ?>&nbsp; </th>
<th> <?php echo xlt('Billed'); ?> </th>
<th> &nbsp;<?php echo xlt('Error'); ?> </th>
</thead>
<tbody>
    <?php
    if ($res) {
        $docrow = array('docname' => '', 'charges' => 0, 'copays' => 0, 'encounters' => 0);

        while ($row = sqlFetchArray($res)) {
            $patient_id = $row['pid'];
            $encounter  = $row['encounter'];
            $docname    = $row['docname'] ? $row['docname'] : xl('Unknown');

            if ($docname != $docrow['docname']) {
                endDoctor($docrow);
            }

            $errmsg  = "";
            $billed  = "Y";
            $charges = 0;
            $copays  = 0;
            $gcac_related_visit = false;

            // Scan the billing items for status and fee total.
            //
            $query = "SELECT code_type, code, modifier, authorized, billed, fee, justify " .
            "FROM billing WHERE " .
            "pid = ? AND encounter = ? AND activity = 1";
            $bres = sqlStatement($query, array($patient_id, $encounter));
            //
            while ($brow = sqlFetchArray($bres)) {
                $code_type = $brow['code_type'];
                if ($code_types[$code_type]['fee'] && !$brow['billed']) {
                    $billed = "";
                }

                if (!$GLOBALS['simplified_demographics'] && !$brow['authorized']) {
                    postError(xl('Needs Auth'));
                }

                if ($code_types[$code_type]['just']) {
                    if (! $brow['justify']) {
                        postError(xl('Needs Justify'));
                    }
                }

                if ($code_types[$code_type]['fee']) {
                    $charges += $brow['fee'];
                    if ($brow['fee'] == 0 && !$GLOBALS['ippf_specific']) {
                        postError(xl('Missing Fee'));
                    }
                } else {
                    if ($brow['fee'] != 0) {
                        postError(xl('Fee is not allowed'));
                    }
                }

                // Custom logic for IPPF to determine if a GCAC issue applies.
                if ($GLOBALS['ippf_specific']) {
                    if (!empty($code_types[$code_type]['fee'])) {
                        $sqlBindArray = array();
                        $query = "SELECT related_code FROM codes WHERE code_type = ? AND code = ? AND ";
                        array_push($sqlBindArray, $code_types[$code_type]['id'], $brow['code']);
                        if ($brow['modifier']) {
                            $query .= "modifier = ?";
                            array_push($sqlBindArray, $brow['modifier']);
                        } else {
                            $query .= "(modifier IS NULL OR modifier = '')";
                        }

                        $query .= " LIMIT 1";
                        $tmp = sqlQuery($query, $sqlBindArray);
                        $relcodes = explode(';', $tmp['related_code']);
                        foreach ($relcodes as $codestring) {
                            if ($codestring === '') {
                                continue;
                            }

                            list($codetype, $code) = explode(':', $codestring);
                            if ($codetype !== 'IPPF') {
                                continue;
                            }

                            if (preg_match('/^25222/', $code)) {
                                $gcac_related_visit = true;
                            }
                        }
                    }
                } // End IPPF stuff
            } // end while

            $copays -= BillingUtilities::getPatientCopay($patient_id, $encounter);

           // The following is removed, perhaps temporarily, because gcac reporting
           // no longer depends on gcac issues.  -- Rod 2009-08-11
           /******************************************************************
         // More custom code for IPPF.  Generates an error message if a
         // GCAC issue is required but is not linked to this visit.
         if (!$errmsg && $gcac_related_visit) {
          $grow = sqlQuery("SELECT l.id, l.title, l.begdate, ie.pid " .
            "FROM lists AS l " .
            "LEFT JOIN issue_encounter AS ie ON ie.pid = l.pid AND " .
            "ie.encounter = '$encounter' AND ie.list_id = l.id " .
            "WHERE l.pid = '$patient_id' AND " .
            "l.activity = 1 AND l.type = 'ippf_gcac' " .
            "ORDER BY ie.pid DESC, l.begdate DESC LIMIT 1");
          // Note that reverse-ordering by ie.pid is a trick for sorting
          // issues linked to the encounter (non-null values) first.
          if (empty($grow['pid'])) { // if there is no linked GCAC issue
            if (empty($grow)) { // no GCAC issue exists
            $errmsg = "GCAC issue does not exist";
            }
            else { // there is one but none is linked
            $errmsg = "GCAC issue is not linked";
            }
          }
         }
           ******************************************************************/
            if ($gcac_related_visit) {
                 $grow = sqlQuery("SELECT COUNT(*) AS count FROM forms " .
                 "WHERE pid = ? AND encounter = ? AND " .
                 "deleted = 0 AND formdir = 'LBFgcac'", array($patient_id, $encounter));
                if (empty($grow['count'])) { // if there is no gcac form
                      postError(xl('GCAC visit form is missing'));
                }
            } // end if
           /*****************************************************************/

            if (!$billed) {
                postError($GLOBALS['simplified_demographics'] ?
                xl('Not checked out') : xl('Not billed'));
            }

            if (!$encounter) {
                postError(xl('No visit'));
            }

            if (! $charges) {
                $billed = "";
            }

            $docrow['charges'] += $charges;
            $docrow['copays']  += $copays;
            if ($encounter) {
                ++$docrow['encounters'];
            }

            if (!empty($_POST['form_details'])) {
                ?>
         <tr>
          <td>
            &nbsp;<?php echo ($docname == $docrow['docname']) ? "" : text($docname); ?>
   </td>
   <td>
      &nbsp;<?php
         /*****************************************************************
         if ($form_to_date) {
            echo $row['pc_eventDate'] . '<br />';
            echo substr($row['pc_startTime'], 0, 5);
         }
         *****************************************************************/
        if (empty($row['pc_eventDate'])) {
            echo text(oeFormatShortDate(substr($row['encdate'], 0, 10)));
        } else {
            echo text(oeFormatShortDate($row['pc_eventDate'])) . ' ' . text(substr($row['pc_startTime'], 0, 5));
        }
        ?>
         </td>
         <td>
          &nbsp;<?php echo text($row['fname']) . " " . text($row['lname']); ?>
         </td>
         <td>
          &nbsp;<?php echo text($row['pubpid']); ?>
         </td>
         <td align='right'>
                <?php echo text($row['pid']); ?>&nbsp;
         </td>
         <td align='right'>
                <?php echo text($encounter); ?>&nbsp;
         </td>
         <td align='right'>
                <?php echo text(bucks($charges)); ?>&nbsp;
         </td>
         <td align='right'>
                <?php echo text(bucks($copays)); ?>&nbsp;
         </td>
         <td>
                <?php echo text($billed); ?>
         </td>
         <td style='color:#cc0000'>
                <?php echo $errmsg; ?>&nbsp;
         </td>
        </tr>
                <?php
            } // end of details line

            $docrow['docname'] = $docname;
        } // end of row

        endDoctor($docrow);

        echo " <tr class='report_totals'>\n";
        echo "  <td colspan='5'>\n";
        echo "   &nbsp;" . xlt('Grand Totals') . "\n";
        echo "  </td>\n";
        echo "  <td align='right'>\n";
        echo "   &nbsp;" . text($grand_total_encounters) . "&nbsp;\n";
        echo "  </td>\n";
        echo "  <td align='right'>\n";
        echo "   &nbsp;";
        echo text(bucks($grand_total_charges));
        echo "&nbsp;\n";
        echo "  </td>\n";
        echo "  <td align='right'>\n";
        echo "   &nbsp;";
        echo text(bucks($grand_total_copays));
        echo "&nbsp;\n";
        echo "  </td>\n";
        echo "  <td colspan='2'>\n";
        echo "   &nbsp;\n";
        echo "  </td>\n";
        echo " </tr>\n";
    }
    ?>
</tbody>
</table>
</div> <!-- end the apptenc_report_results -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>
<script>
<?php if ($alertmsg) {
    echo " alert(" . js_escape($alertmsg) . ");\n";
} ?>
</script>
</body>

</html>
