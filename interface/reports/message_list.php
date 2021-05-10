<?php

/**
 * This report lists prescriptions and their dispensations according
 * to various input selection criteria.
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
require_once("$srcdir/options.inc.php");
require_once("../drugs/drugs.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\UserService;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$userService = new UserService();

$form_from_date  = (!empty($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$form_to_date    = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
?>
<html>
<head>

<title><?php echo xlt('Message List'); ?></title>

<?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

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

    // The OnClick handler for receipt display.
    function show_receipt(payid) {
        // dlgopen('../patient_file/front_payment.php?receipt=1&payid=' + payid, '_blank', 550, 400);
        return false;
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
<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Message List'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' id='theform' method='post' action='message_list.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<table>
 <tr>
  <td width='640px'>
    <div style='float: left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('From'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>' />
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>' />
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
</div> <!-- end of parameters -->

<?php
if (!empty($_POST['form_refresh'])) { ?>
<div id="report_results">
<table class='table' id='mymaintable'>
<thead class='thead-light'>
<th> <?php echo xlt('Date'); ?> </th>
<th> <?php echo xlt('User'); ?> </th>
<th> <?php echo xlt('Patient'); ?> </th>
<th> <?php echo xlt('DOB'); ?> </th>
<th> <?php echo xlt('Type'); ?> </th>
<th> <?php echo xlt('Status'); ?> </th>
<th> <?php echo xlt('Updated By'); ?> </th>
<th> <?php echo xlt('Last Update'); ?> </th>
</thead>
<tbody>
    <?php
    if ($_POST['form_refresh']) {
        $sqlBindArray = array();

        $where = "pn.date >= ? AND pn.date <= ?";
        array_push($sqlBindArray, $form_from_date, $form_to_date);

        if ($form_patient_id) {
            $where .= " AND p.pubpid = ?";
            array_push($sqlBindArray, $form_patient_id);
        }

        $query = "SELECT pn.id, pn.date, pn.body, pn.pid, pn.user, pn.groupname, " .
        "pn.activity, pn.authorized, pn.title, pn.assigned_to, pn.deleted, pn.message_status, " .
        "pn.portal_relation, pn.is_msg_encrypted, pn.update_by, pn.update_date, " .
        "p.pubpid, p.fname, p.lname, p.mname, p.dob, " .
        "u.username " .
        "FROM pnotes AS pn " .
        "LEFT OUTER JOIN patient_data AS p ON p.pid = pn.pid " .
        "LEFT OUTER JOIN users AS u ON u.id = pn.user " .
        "WHERE $where " .
        "ORDER BY p.lname, p.fname, p.pubpid";

        $res = sqlStatement($query, $sqlBindArray);

        while ($row = sqlFetchArray($res)) {
            $msg_date        = $row['date'];
            $user            = $row['user'];
            $patient_name    = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
            $patient_id      = $row['pubpid'];
            $patient_dob     = $row['dob'];
            $msg_type        = $row['title'];
            $msg_status      = $row['message_status'];
            $username        = $userService->getUser($row['update_by']);
            $update_by       = $username['username'];
            $update_date     = $row['update_date'];
            ?>
   <tr>
    <td>
            <?php echo text($msg_date); ?>
  </td>
  <td>
            <?php echo text($user); ?>
  </td>
  <td>
            <?php echo text($patient_name); ?>
  </td>
  <td>
            <?php echo text($patient_dob); ?>
  </td>
  <td>
            <?php echo text($msg_type); ?>
  </td>
  <td>
            <?php echo text($msg_status); ?>
  </td>
  <td>
            <?php echo text($update_by); ?>
  </td>
  <td>
            <?php echo text($update_date); ?>
  </td>  
 </tr>     
            <?php
        } // end while
    } // end if post refresh ?>
       
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
