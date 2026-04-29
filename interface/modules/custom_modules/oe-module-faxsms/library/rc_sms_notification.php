<?php

/**
 * Admin popup / argv entry for appointment-reminder scan-and-send.
 *
 * This file is the UI-facing entry point: admins trigger it from
 * `messageUI.php` / `setup_email.php` as a popup (dry-run and live
 * modes) and operators can invoke it from the CLI for one-shot runs
 * (see README-GUIDE.md for argv usage). Both paths keep the ACL gate
 * — a front-desk user without "patients/appt" access must not be
 * able to trigger reminder delivery, whether they land here through
 * a browser popup or an interactive shell.
 *
 * The `bin/console background:services run` entry point lives in
 * `run_notifications.php` and deliberately does NOT require this
 * file, because there is no interactive session to ACL-check and
 * because the HTML chrome below would just be written into the
 * cron log. See issue #11827 for the silent-success bug that
 * triggered this split.
 *
 * The scan-and-send pipeline is
 * `OpenEMR\Modules\FaxSMS\Notification\AppointmentNotificationRunner`.
 * The procedural SQL/formatting helpers used by both entry points
 * live in `rc_sms_notification_helpers.php`.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Unknown
 * @author    Larry Lart
 * @author    Jerry Padgett
 * @author    Robert Down
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Unknown
 * @copyright Copyright (c) 2008 Larry Lart
 * @copyright Copyright (c) 2018-2024 Jerry Padgett
 * @copyright Copyright (c) 2021 Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @codeCoverageIgnore Top-level admin popup / argv entry point. Mixes
 *     globals.php bootstrap, raw `$_GET` writes for site/type, ACL gating
 *     against the live session, and HTML chrome — none of which is
 *     reachable from an isolated test. The reusable scan-and-send logic
 *     lives in `AppointmentNotificationRunner` and is covered there.
 */

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;
use OpenEMR\Modules\FaxSMS\Notification\AppointmentNotificationRunner;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
$_SERVER['SERVER_NAME'] = 'localhost';

// Parse CLI argv (key=value pairs) into $runtime.
$runtime = [];
$argc ??= 0;
$argv ??= [];
if ($argc > 1) {
    foreach ($argv as $k => $v) {
        if ($k === 0) {
            continue;
        }
        $args = explode('=', $v);
        if (count($args) > 1) {
            $runtime[trim($args[0])] = trim($args[1]);
        }
    }
}

if (php_sapi_name() === 'cli') {
    $_SERVER["HTTP_HOST"] = "localhost";
    $ignoreAuth = true;
}

// So the service can set some settings if needed on init.
$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../../globals.php");
require_once(OEGlobalsBag::getInstance()->getSrcDir() . "/appointments.inc.php");
require_once(__DIR__ . "/rc_sms_notification_helpers.php");

if ($argc > 1 && (in_array('--help', $argv) || in_array('-h', $argv))) {
    displayHelp();
    exit(0);
}

$runtimeSite = $runtime['site'] ?? '';
if ($runtimeSite !== '') {
    $_GET['site'] = $runtimeSite;
} else {
    $sessionSite = $session->get('site_id');
    $querySite = $_GET['site'] ?? '';
    $sessionSiteIsBlank = !is_string($sessionSite) || $sessionSite === '';
    $querySiteIsBlank = !is_string($querySite) || $querySite === '';
    if ($sessionSiteIsBlank && $querySiteIsBlank) {
        echo xlt("Missing Site Id using default") . "\n";
        $_GET['site'] = $runtime['site'] = 'default';
    }
}

$runtimeType = $runtime['type'] ?? '';
if ($runtimeType !== '') {
    $TYPE = strtoupper($runtimeType);
} elseif (($_GET['type'] ?? '') === 'email') {
    $TYPE = $runtime['type'] = "EMAIL";
} else {
    $TYPE = $runtime['type'] = "SMS"; // default
}

$channel = NotificationChannel::fromLegacyType($TYPE);
$taskManager = new NotificationTaskManager();
$cronIntervalHours = $taskManager->getTaskHours(strtolower($TYPE));

// Resolve the client for this channel. AppDispatch::getApiService()
// constructs the vendor-specific client, which in turn runs the ACL
// check against the active session. Keep the die-on-deny behavior
// here: this path is always invoked by an interactive admin, so a
// visible "Not Authorised!" page is the expected response.
if ($channel === NotificationChannel::SMS) {
    $session->set('authUser', $runtime['user'] ?? $session->get('authUser'));
    $clientApp = AppDispatch::getApiService('sms');
    $cred = $clientApp->getCredentials();

    if (!$clientApp->verifyAcl('patients', 'appt', $runtime['user'] ?? '')) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
} else {
    $session->set('authUser', $runtime['user'] ?? $session->get('authUser'));
    $clientApp = AppDispatch::getApiService('email');
    $cred = $clientApp->getEmailSetup();

    if (!$clientApp->verifyAcl('patients', 'appt', $runtime['user'] ?? '')) {
        die("<h3>" . xlt("Not Authorised!") . "</h3>");
    }
}

// Close session writes so a long-running scan doesn't block other tabs.
session_write_close();
set_time_limit(0);

$credArray = is_array($cred) ? $cred : [];
$notificationHoursRaw = $credArray['smsHours'] ?? $credArray['notification_hours'] ?? 24;
$notificationHours = is_numeric($notificationHoursRaw) ? (int) $notificationHoursRaw : 24;
$messageTemplateRaw = $credArray['smsMessage'] ?? $credArray['email_message'] ?? '';
$messageTemplate = is_string($messageTemplateRaw) ? $messageTemplateRaw : '';
$vendor = AppDispatch::getModuleVendor();
$gatewayType = is_string($vendor) ? $vendor : '';

$dryRun = isset($_REQUEST['dryrun']) || ($runtime['testrun'] ?? '') !== '';

// Exposed via the globals bag because `rc_sms_notification_cron_update_entry()`
// in rc_sms_notification_helpers.php still reads `bTestRun` to short-circuit
// dry-run updates. Removing that dependency is follow-up work.
OEGlobalsBag::getInstance()->set('bTestRun', $dryRun);

$runner = new AppointmentNotificationRunner(
    channel: $channel,
    client: $clientApp,
    notificationHours: $notificationHours,
    cronIntervalHours: $cronIntervalHours,
    dryRun: $dryRun,
    messageTemplate: $messageTemplate,
    gatewayType: $gatewayType,
);
?>
    <!DOCTYPE html>
    <html lang="eng">
    <head>
        <title><?php echo xlt("Notifications") ?></title>
        <?php Header::setupHeader(); ?>
    </head>
    <style>
      html {
        font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
        font-size: 14px;
      }
    </style>
    <body>
        <div class="container-fluid">
            <div>
                <div class="text-center mt-2"><h2><?php echo xlt("Working and may take a few minutes to finish.") ?></h2></div>
            </div>
            <?php
            if ($dryRun) {
                echo xlt("We are in Test Mode and no reminders will be sent. This test will check what reminders will be sent in when running Live Mode.");
            }
            echo "<h3>======================== " . text($TYPE) . " | " . text(date("Y-m-d H:i:s")) . " =========================</h3>";
            ob_flush();
            flush();

            $result = $runner->run();
            ?>
            <ul>
                <li><?php echo xlt('Candidates scanned'); ?>: <strong><?php echo text((string) $result->scanned); ?></strong></li>
                <li><?php echo xlt('In send window'); ?>: <strong><?php echo text((string) $result->inWindow); ?></strong></li>
                <li><?php echo xlt('Sent'); ?>: <strong><?php echo text((string) $result->sent); ?></strong></li>
                <li><?php echo xlt('Skipped (invalid recipient)'); ?>: <strong><?php echo text((string) $result->skippedInvalid); ?></strong></li>
                <li><?php echo xlt('Failed'); ?>: <strong><?php echo text((string) $result->failed); ?></strong></li>
            </ul>
            <?php if ($result->hasFailures()) : ?>
                <p class="text-danger"><?php echo xlt('One or more sends failed. Check the PHP error log for details.'); ?></p>
            <?php endif; ?>
            <h2><?php echo xlt("Done!"); ?></h2>
        </div>
    </body>
    </html>
<?php
unset($clientApp);

function displayHelp(): void
{
    $help = <<<HELP

Usage:   php rc_sms_notification.php [options]
Example: php rc_sms_notification.php site=default user=admin type=sms testrun=1
--help  Display this help message
Options:
  site={site_id}    Site
  user={authUser}   Authorized username not id.
  type={sms}        Send method SMS or email.
  testrun={1}       Test run set to 1

HELP;

    echo text($help);
}
