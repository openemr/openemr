<?php

/**
 * AMC Tracking.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/amc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Automated Measure Calculations (AMC) Tracking")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// Collect form parameters (set defaults if empty)
$begin_date = (isset($_POST['form_begin_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_begin_date'])) : "";
$end_date = (isset($_POST['form_end_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_end_date'])) : "";
$rule = (isset($_POST['form_rule'])) ? trim($_POST['form_rule']) : "";
$provider  = trim($_POST['form_provider'] ?? '');

?>

<html>

<head>

<title><?php echo xlt('Automated Measure Calculations (AMC) Tracking'); ?></title>

<?php Header::setupHeader('datetime-picker') ?>

<script>

 $(function () {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = true; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
 });

 function send_sum(patient_id,transaction_id) {
   if ( $('#send_sum_flag_' + patient_id + '_' + transaction_id).prop('checked') ) {
     var mode = "add";
   }
   else {
     var mode = "remove";
   }
   top.restoreSession();
   $.post( "../../library/ajax/amc_misc_data.php",
     { amc_id: "send_sum_amc",
       complete: true,
       mode: mode,
       patient_id: patient_id,
       object_category: "transactions",
       object_id: transaction_id,
       csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
     }
   );
 }

 function send_sum_elec(patient_id,transaction_id) {
   if ( $('#send_sum_elec_flag_' + patient_id + '_' + transaction_id).prop('checked') ) {
     if ( !$('#send_sum_flag_' + patient_id + '_' + transaction_id).prop('checked') ) {
       $('#send_sum_elec_flag_' + patient_id + '_' + transaction_id).prop("checked", false);
       alert(<?php echo xlj('Can not set this unless the Summary of Care Sent toggle is set.'); ?>);
       return false;
     }
     var mode = "add";
   }
   else {
     var mode = "remove";
   }
   top.restoreSession();
   $.post( "../../library/ajax/amc_misc_data.php",
     { amc_id: "send_sum_elec_amc",
       complete: true,
       mode: mode,
       patient_id: patient_id,
       object_category: "transactions",
       object_id: transaction_id,
       csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
     }
   );
 }

 function provide_rec_pat(patient_id,date_created) {
   if ( $('#provide_rec_pat_flag_' + patient_id ).prop('checked') ) {
     var mode = "complete_safe";
   }
   else {
     var mode = "uncomplete_safe";
   }
   top.restoreSession();
   $.post( "../../library/ajax/amc_misc_data.php",
     { amc_id: "provide_rec_pat_amc",
       complete: true,
       mode: mode,
       date_created: date_created,
       patient_id: patient_id,
       csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
     }
   );
 }

 function provide_sum_pat(patient_id,encounter_id) {
   if ( $('#provide_sum_pat_flag_' + patient_id + '_' + encounter_id).prop('checked') ) {
     var mode = "add";
   }
   else {
     var mode = "remove";
   }
   top.restoreSession();
   $.post( "../../library/ajax/amc_misc_data.php",
     { amc_id: "provide_sum_pat_amc",
       complete: true,
       mode: mode,
       patient_id: patient_id,
       object_category: "form_encounter",
       object_id: encounter_id,
       csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
     }
   );
 }

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
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> -

<?php echo xlt('Automated Measure Calculations (AMC) Tracking'); ?></span>

<form method='post' name='theform' id='theform' action='amc_tracking.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<table>
 <tr>
  <td width='470px'>
    <div style='float:left'>

    <table class='text'>

                 <tr>
                      <td class='col-form-label'>
                        <?php echo xlt('Begin Date'); ?>:
                      </td>
                      <td>
                         <input type='text' name='form_begin_date' id="form_begin_date" size='20' value='<?php echo attr(oeFormatDateTime($begin_date, 0, true)); ?>' class='datepicker form-control' />
                      </td>
                 </tr>

                <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('End Date'); ?>:
                        </td>
                        <td>
                           <input type='text' name='form_end_date' id="form_end_date" size='20' value='<?php echo attr(oeFormatDateTime($end_date, 0, true)); ?>' class='datepicker form-control' />
                        </td>
                </tr>

                <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Rule'); ?>:
                        </td>
                        <td>
                            <select name='form_rule' class='form-control'>
                                <option value='send_sum_amc' <?php echo ($rule == "send_sum_amc") ? "selected" : ""; ?>><?php echo xlt('Send Summaries with Referrals'); ?></option>
                                <option value='provide_rec_pat_amc' <?php echo ($rule == "provide_rec_pat_amc") ? "selected" : ""; ?>><?php echo xlt('Patient Requested Medical Records'); ?></option>
                                <option value='provide_sum_pat_amc' <?php echo ($rule == "provide_sum_pat_amc") ? "selected" : ""; ?>><?php echo xlt('Provide Records to Patient for Visit'); ?></option>
                            </select>
                        </td>
                </tr>

                <tr>
            <td class='col-form-label'>
                <?php echo xlt('Provider'); ?>:
            </td>
            <td>
                <?php

                 // Build a drop-down list of providers.
                 //

                 $query = "SELECT id, lname, fname FROM users WHERE " .
                  "authorized = 1 ORDER BY lname, fname"; //(CHEMED) facility filter

                 $ures = sqlStatement($query);

                 echo "   <select name='form_provider' class='form-control'>\n";
                 echo "    <option value=''>-- " . xlt('All') . " --\n";

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if ($provid == ($_POST['form_provider'] ?? '')) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname'] . ", " . $urow['fname']) . "\n";
                }

                 echo "   </select>\n";

                ?>
                        </td>
        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left: 1px solid;'>
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
            <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); top.restoreSession(); $("#theform").submit();'>
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

</div>  <!-- end of search parameters -->

<br />

<?php
if (!empty($_POST['form_refresh'])) {
    ?>


<div id="report_results">
<table class='table'>

<thead class='thead-light'>
 <th>
    <?php echo xlt('Patient Name'); ?>
 </th>

 <th>
    <?php echo xlt('Patient ID'); ?>
 </th>

 <th>
    <?php
    if ($rule == "send_sum_amc") {
        echo xlt('Referral Date');
    } elseif ($rule == "provide_rec_pat_amc") {
        echo xlt('Record Request Date');
    } else { // $rule == "provide_sum_pat_amc"
        echo xlt('Encounter Date');
    }
    ?>
  </th>

  <th>
    <?php
    if ($rule == "send_sum_amc") {
        echo xlt('Referral ID');
    } elseif ($rule == "provide_rec_pat_amc") {
        echo "&nbsp";
    } else { // $rule == "provide_sum_pat_amc"
        echo xlt('Encounter ID');
    }
    ?>
  </th>

  <th>
    <?php
    if ($rule == "provide_rec_pat_amc") {
        echo xlt('Medical Records Sent');
    } elseif ($rule == "send_sum_amc") {
        echo xlt('Summary of Care Sent');
    } else { // $rule == "provide_sum_pat_amc"
        echo xlt('Medical Summary Given');
    }
    ?>
  </th>
    <?php
    if ($rule == "send_sum_amc") {
        echo "<th>";
        echo xlt('Summary of Care Sent Electronically');
        echo "<th>";
    }
    ?>

 </thead>
 <tbody>  <!-- added for better print-ability -->
    <?php

// Send the request for information
    $resultsArray = amcTrackingRequest($rule, $begin_date, $end_date, $provider);

    ?>

    <?php
    foreach ($resultsArray as $result) {
        echo "<tr bgcolor='" . attr($bgcolor ?? '') . "'>";
        echo "<td>" . text($result['lname'] . "," . $result['fname']) . "</td>";
        echo "<td>" . text($result['pid']) . "</td>";
        echo "<td>" . text(oeFormatDateTime($result['date'], "global", true)) . "</td>";
        if ($rule == "send_sum_amc" || $rule == "provide_sum_pat_amc") {
            echo "<td>" . text($result['id']) . "</td>";
        } else { //$rule == "provide_rec_pat_amc"
            echo "<td>&nbsp</td>";
        }

        if ($rule == "send_sum_amc") {
            echo "<td><input type='checkbox' id='send_sum_flag_" . attr($result['pid']) . "_" . attr($result['id']) . "' onclick='send_sum(" . attr_js($result['pid']) . "," . attr_js($result['id']) . ")'>" . xlt('Yes') . "</td>";
            echo "<td><input type='checkbox' id='send_sum_elec_flag_" . attr($result['pid']) . "_" . attr($result['id']) . "' onclick='send_sum_elec(" . attr_js($result['pid']) . "," . attr_js($result['id']) . ")'>" . xlt('Yes') . "</td>";
        } elseif ($rule == "provide_rec_pat_amc") {
            echo "<td><input type='checkbox' id='provide_rec_pat_flag_" . attr($result['pid']) . "' onclick='provide_rec_pat(" . attr_js($result['pid']) . "," . attr_js($result['date']) . ")'>" . xlt('Yes') . "</td>";
        } else { //$rule == "provide_sum_pat_amc"
            echo "<td><input type='checkbox' id='provide_sum_pat_flag_" . attr($result['pid']) . "_" . attr($result['id']) . "' onclick='provide_sum_pat(" . attr_js($result['pid']) . "," . attr_js($result['id']) . ")'>" . xlt('Yes') . "</td>";
        }

        echo "</tr>";
    }
    ?>

</tbody>
</table>
</div>  <!-- end of search results -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>

</form>

</body>

</html>
