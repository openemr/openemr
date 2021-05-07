<?php

/**
 * To be run by cron hourly, sending phone reminders
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @author  Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2012 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Jason 'Toolbox' Oettinger <jason@oettinger.email>
 * @copyright Copyright (c) 2017 Robert Down <robertdown@live.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once($GLOBALS['srcdir'] . "/maviq_phone_api.php");
require_once($GLOBALS['srcdir'] . "/reminders.php");
require_once($GLOBALS['srcdir'] . "/report_database.inc");

use OpenEMR\Core\Header;

//Remove time limit, since script can take many minutes
set_time_limit(0);

// If report_id, then just going to show the report log
$report_id = ($_GET['report_id']) ? $_GET['report_id'] : "";

// Set the "nice" level of the process for this script when. When the "nice" level
// is increased, this cpu intensive script will have less affect on the performance
// of other server activities, albeit it may negatively impact the performance
// of this script (note this is only applicable for linux).
if (empty($report_id) && !empty($GLOBALS['pat_rem_clin_nice'])) {
    proc_nice($GLOBALS['pat_rem_clin_nice']);
}
?>

<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Patient Reminder Batch Job') ?></title>
</head>
<body class="body_top container">
    <header class="row">
        <?php require_once("batch_navigation.php");?>
        <h1 class="col-md-12">
            <a href="batchcom.php"><?php echo xlt('Batch Communication Tool'); ?></a>
            <small><?php echo xlt('Patient Reminder Batch Job'); ?></small>
        </h1>
    </header>
    <?php
    // Collect the sender information
    // TODO
    // $sender_name
    // $email_address
    //
    ?>
    <main class="row mx-4">
        <div class="col-md-12">
            <table class="table table-striped table-bordered">
            <tr>
              <td class='text' align='left' colspan="3"><br />

                <?php
                if ($report_id) {
                // collect log from a previous run to show
                    $results_log = collectReportDatabase($report_id);
                    $data_log = json_decode($results_log['data'], true);
                    $update_rem_log = $data_log[0];
                    if ($results_log['type'] == "process_send_reminders") {
                        $send_rem_log = $data_log[1];
                    }

                    echo "<span class='text'>" . xlt("Date of Report") . ": " . text($results_log['date_report']) . "</span><br /><br />";
                } else {
                    $update_rem_log = update_reminders_batch_method();
                    $send_rem_log = send_reminders();
                }
                ?>

                <span class="text"><?php echo xlt('The patient reminders have been updated') . ":"?></span><br />
                <span class="text"><?php echo xlt('Total active actions') . ": " . text($update_rem_log['total_active_actions']); ?></span><br />
                <span class="text"><?php echo xlt('Total active reminders before update') . ": " . text($update_rem_log['total_pre_active_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total unsent reminders before update') . ": " . text($update_rem_log['total_pre_unsent_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total active reminders after update') . ": " . text($update_rem_log['total_post_active_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total unsent reminders after update') . ": " . text($update_rem_log['total_post_unsent_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total new reminders') . ": " . text($update_rem_log['number_new_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total updated reminders') . ": " . text($update_rem_log['number_updated_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total inactivated reminders') . ": " . text($update_rem_log['number_inactivated_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total unchanged reminders') . ": " . text($update_rem_log['number_unchanged_reminders']); ?></span><br />

                <?php if ($results_log['type'] != "process_reminders") { ?>
                <br /><span class="text"><?php echo xlt('The patient reminders have been sent') . ":"?></span><br />
                <span class="text"><?php echo xlt('Total unsent reminders before sending process') . ": " . text($send_rem_log['total_pre_unsent_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total unsent reminders after sending process') . ": " . text($send_rem_log['total_post_unsent_reminders']); ?></span><br />
                <span class="text"><?php echo xlt('Total successful reminders sent via email') . ": " . text($send_rem_log['number_success_emails']); ?></span><br />
                <span class="text"><?php echo xlt('Total failed reminders sent via email') . ": " . text($send_rem_log['number_failed_emails']); ?></span><br />
                <span class="text"><?php echo xlt('Total successful reminders sent via phone') . ": " . text($send_rem_log['number_success_calls']); ?></span><br />
                <span class="text"><?php echo xlt('Total failed reminders sent via phone') . ": " . text($send_rem_log['number_unchanged_reminders'] ?? ''); ?></span><br />

                <br /><span class="text"><?php echo xlt('(Email delivery is immediate, while automated VOIP is sent to the service provider for further processing.)')?></span><br />
                <?php } // end of ($results_log['type'] != "process_reminders") ?>

                <?php if ($report_id) { ?>
                <br /><input type="button" value="<?php echo xlt('Back'); ?>" onClick="top.restoreSession(); window.open('../reports/report_results.php','_self',false)"><br /><br /><br />
                <?php } else { ?>
                <input type="button" value="<?php echo xlt('Close'); ?>" onClick="window.close()">
                <?php } ?>
              </td>
            </tr>
            </table>
        </div>
    </main>
</body>
</html>

