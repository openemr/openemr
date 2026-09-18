<?php

/**
 * CLI entry for the `Notification_Email_Task` / `Notification_SMS_Task`
 * background services.
 *
 * `BackgroundServiceRunner::executeService()` (driven by `bin/console
 * background:services run` and the legacy
 * `library/ajax/execute_background_services.php` cron entry) dispatches
 * background jobs by symbolic function name from the
 * `background_services` table, so the two task entry points must live
 * in the global namespace. Everything past CLI-to-legacy translation
 * lives in `OpenEMR\Modules\FaxSMS\Notification\BackgroundReminderTask`.
 *
 * Both task functions populate `$_GET['site']` / `$_GET['type']` for
 * legacy code paths (AppDispatch and the procedural helpers) that
 * read them as their request context. The active site is resolved
 * from the session (populated by `bin/console` or the legacy cron
 * entry), falling back to 'default' so a misconfigured cron can't
 * silently target the wrong site.
 *
 * Unlike the admin popup entry `rc_sms_notification.php`, this path:
 *
 *   - Does NOT render HTML; results are reported via PSR-3 log
 *     context and surfaced to the operator through the service
 *     status in `background_services` plus Symfony console output.
 *   - Does NOT run an ACL check. The original scan-and-send page
 *     called `die("Not Authorised!")` when invoked without a
 *     populated interactive session, which `BackgroundServiceRunner`
 *     counted as a successful run — the silent failure reported in
 *     issue #11827. Background workers always bootstrap with
 *     `$ignoreAuth = true`, which the FaxSMS `AppDispatch`
 *     constructor honors to skip the session-scoped ACL gate.
 *   - Throws when the runner reports failures so the orchestrator
 *     records the service as `error`, not `executed`.
 *
 * The procedural helpers (SQL, dedup, log insert) live in
 * `rc_sms_notification_helpers.php`.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2018-2024 Jerry Padgett
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @codeCoverageIgnore CLI-bootstrap shim that exists solely to bridge the
 *     global-namespace function dispatch in `BackgroundServiceRunner` to
 *     the typed `BackgroundReminderTask`. It writes `$_GET['site']` /
 *     `$_GET['type']` for downstream legacy code, opens the active
 *     session, and requires `globals.php` — none of which is reachable
 *     in an isolated test. The reminder pipeline below the bridge is
 *     covered in `AppointmentNotificationRunnerTest`. Removing this
 *     file is tracked in #11848.
 */

use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;
use OpenEMR\Modules\FaxSMS\Notification\BackgroundReminderTask;

require_once(__DIR__ . '/rc_sms_notification_helpers.php');

if (!function_exists('doEmailNotificationTask')) {
    function doEmailNotificationTask(): void
    {
        $sessionSite = SessionWrapperFactory::getInstance()->getActiveSession()->get('site_id');
        $_GET['site'] = is_string($sessionSite) && $sessionSite !== '' ? $sessionSite : 'default';
        $_GET['type'] = 'email';
        BackgroundReminderTask::run(NotificationChannel::EMAIL);
    }
}

if (!function_exists('doSmsNotificationTask')) {
    function doSmsNotificationTask(): void
    {
        $sessionSite = SessionWrapperFactory::getInstance()->getActiveSession()->get('site_id');
        $_GET['site'] = is_string($sessionSite) && $sessionSite !== '' ? $sessionSite : 'default';
        $_GET['type'] = 'sms';
        BackgroundReminderTask::run(NotificationChannel::SMS);
    }
}
