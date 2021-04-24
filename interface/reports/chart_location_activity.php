<?php

/**
 * This reports checkins and checkouts for a specified patient's chart.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2015 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\PatientService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_patient_id = trim($_POST['form_patient_id'] ?? '');
?>
<html>
<head>
    <title><?php echo xlt('Chart Location Activity'); ?></title>

    <?php Header::setupHeader(); ?>

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
            var win = top.printLogSetup ? top : opener.top;
            win.printLogSetup(document.getElementById('printbutton'));
        });
    </script>
</head>

<body class="body_top">

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Chart Location Activity'); ?></span>

<?php
$curr_pid = $pid;
$ptrow = array();
if (!empty($form_patient_id)) {
    $query = "SELECT pid, pubpid, fname, mname, lname FROM patient_data WHERE " .
    "pubpid = ? ORDER BY pid LIMIT 1";
    $ptrow = sqlQuery($query, array($form_patient_id));
    if (empty($ptrow)) {
        $curr_pid = 0;
        echo "<font color='red'>" . xlt('Chart ID') . " '" . text($form_patient_id) . "' " . xlt('not found!') . "</font><br />&nbsp;<br />";
    } else {
        $curr_pid = $ptrow['pid'];
    }
} elseif (!empty($curr_pid)) {
    $query = "SELECT pid, pubpid, fname, mname, lname FROM patient_data WHERE " .
    "pid = ?";
    $ptrow = sqlQuery($query, array($curr_pid));
    $form_patient_id = $ptrow['pubpid'];
}

if (!empty($ptrow)) {
    echo '<span class="title">' . xlt('for') . ' ';
    echo text($ptrow['lname']) . ', ' . text($ptrow['fname']) . ' ' . text($ptrow['mname']) . ' ';
    echo "(" . text($ptrow['pubpid']) . ")";
    echo "</span>\n";
}
?>

<div id="report_parameters_daterange">
</div>

<form name='theform' id='theform' method='post' action='chart_location_activity.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='200px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Patient ID'); ?>:
            </td>
            <td>
               <input type='text' name='form_patient_id' class='form-control' size='10' maxlength='31' value='<?php echo attr($form_patient_id) ?>'
                title='<?php echo xla('Patient ID'); ?>' />
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
                        <?php if (!empty($_POST['form_refresh']) || !empty($ptrow)) { ?>
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

</div> <!-- end of parameters -->

<?php
if (!empty($_POST['form_refresh']) || !empty($ptrow)) {
    ?>
<div id="report_results">
<table class='table'>
<thead class='thead-light'>
<th> <?php echo xlt('Time'); ?> </th>
<th> <?php echo xlt('Destination'); ?> </th>
</thead>
<tbody>
    <?php
    $row = array();
    if (!empty($ptrow)) {
        $res = PatientService::getChartTrackerInformationActivity($curr_pid);
        while ($row = sqlFetchArray($res)) {
            ?>
   <tr>
    <td>
            <?php echo text(oeFormatDateTime($row['ct_when'], "global", true)); ?>
  </td>
  <td>
            <?php
            if (!empty($row['ct_location'])) {
                echo generate_display_field(array('data_type' => '1','list_id' => 'chartloc'), $row['ct_location']);
            } elseif (!empty($row['ct_userid'])) {
                echo text($row['lname']) . ', ' . text($row['fname']) . ' ' . text($row['mname']);
            }
            ?>
  </td>
 </tr>
            <?php
        } // end while
    } // end if
    ?>
</tbody>
</table>
</div> <!-- end of results -->
<?php } else { ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php } ?>

</form>
</body>
</html>
