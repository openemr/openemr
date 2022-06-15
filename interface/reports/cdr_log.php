<?php

/**
 * CDR trigger log report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/clinical_rules.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Alerts Log")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_begin_date = DateTimeToYYYYMMDDHHMMSS($_POST['form_begin_date'] ?? '');
$form_end_date = DateTimeToYYYYMMDDHHMMSS($_POST['form_end_date'] ?? '');
?>

<html>

<head>
    <title><?php echo xlt('Alerts Log'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

    <script>
        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = true; ?>
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

<span class='title'><?php echo xlt('Alerts Log'); ?></span>

<form method='post' name='theform' id='theform' action='cdr_log.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
<input type="hidden" name="search" value="1" />

<div id="report_parameters">

<table>
 <tr>
  <td width='470px'>
    <div style='float: left'>

    <table class='text'>

                   <tr>
                      <td class='col-form-label'>
                            <?php echo xlt('Begin Date'); ?>:
                      </td>
                      <td>
                         <input type='text' name='form_begin_date' id='form_begin_date' size='20' value='<?php echo attr(oeFormatDateTime($form_begin_date, 0, true)); ?>'
                            class='datepicker form-control'>
                      </td>
                   </tr>

                <tr>
                        <td class='col-form-label'>
                                <?php echo xlt('End Date'); ?>:
                        </td>
                        <td>
                           <input type='text' name='form_end_date' id='form_end_date' size='20' value='<?php echo attr(oeFormatDateTime($form_end_date, 0, true)); ?>'
                                class='datepicker form-control'>
                        </td>
                </tr>
    </table>
    </div>

  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left: 1px solid; width:100%; height:100%' >
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
            <a id='search_button' href='#' class='btn btn-secondary btn-search' onclick='top.restoreSession(); $("#theform").submit()'>
                            <?php echo xlt('Search'); ?>
            </a>
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

<?php if (!empty($_POST['search']) && ($_POST['search'] == 1)) { ?>
 <div id="report_results">
 <table class="table">

 <thead>
  <th align='center'>
    <?php echo xlt('Date'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('Patient ID'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('User ID'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('Category'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('All Alerts'); ?>
  </th>

  <th align='center'>
    <?php echo xlt('New Alerts'); ?>
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
    <?php
    $res = listingCDRReminderLog($form_begin_date, $form_end_date);

    while ($row = sqlFetchArray($res)) {
        //Create category title
        if ($row['category'] == 'clinical_reminder_widget') {
            $category_title = xl("Passive Alert");
        } elseif ($row['category'] == 'active_reminder_popup') {
            $category_title = xl("Active Alert");
        } elseif ($row['category'] == 'allergy_alert') {
            $category_title = xl("Allergy Warning");
        } else {
            $category_title = $row['category'];
        }

        //Prepare the targets
        $all_alerts = json_decode($row['value'], true);
        if (!empty($row['new_value'])) {
            $new_alerts = json_decode($row['new_value'], true);
        }
        ?>
     <tr>
       <td><?php echo text(oeFormatDateTime($row['date'], "global", true)); ?></td>
    <td><?php echo text($row['pid']); ?></td>
    <td><?php echo text($row['uid']); ?></td>
    <td><?php echo text($category_title); ?></td>
    <td>
        <?php
         //list off all targets with rule information shown when hover
        foreach ($all_alerts as $targetInfo => $alert) {
            if (($row['category'] == 'clinical_reminder_widget') || ($row['category'] == 'active_reminder_popup')) {
                $rule_title = getListItemTitle("clinical_rules", $alert['rule_id']);
                $catAndTarget = explode(':', $targetInfo);
                $category = $catAndTarget[0];
                $target = $catAndTarget[1];
                echo "<span title='" . attr($rule_title) . "'>" .
                  generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $category) .
                  ": " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $target) .
                  " (" . generate_display_field(array('data_type' => '1','list_id' => 'rule_reminder_due_opt'), $alert['due_status']) . ")" .
                  "<span><br />";
            } else { // $row['category'] == 'allergy_alert'
                 echo $alert . "<br />";
            }
        }
        ?>
       </td>
       <td>
        <?php
        if (!empty($row['new_value'])) {
         //list new targets with rule information shown when hover
            foreach ($new_alerts as $targetInfo => $alert) {
                if (($row['category'] == 'clinical_reminder_widget') || ($row['category'] == 'active_reminder_popup')) {
                    $rule_title = getListItemTitle("clinical_rules", $alert['rule_id']);
                    $catAndTarget = explode(':', $targetInfo);
                    $category = $catAndTarget[0];
                    $target = $catAndTarget[1];
                    echo "<span title='" . attr($rule_title) . "'>" .
                      generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $category) .
                      ": " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $target) .
                      " (" . generate_display_field(array('data_type' => '1','list_id' => 'rule_reminder_due_opt'), $alert['due_status']) . ")" .
                      "<span><br />";
                } else { // $row['category'] == 'allergy_alert'
                    echo $alert . "<br />";
                }
            }
        } else {
            echo "&nbsp;";
        }
        ?>
       </td>
     </tr>

        <?php
    } // $row = sqlFetchArray($res) while
    ?>
 </tbody>
 </table>
 </div>  <!-- end of search results -->

<?php } // end of if search button clicked ?>

</form>

</body>

</html>
