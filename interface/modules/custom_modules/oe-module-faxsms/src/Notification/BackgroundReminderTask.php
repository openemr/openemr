<?php

/**
 * Bridge between BackgroundServiceRunner and AppointmentNotificationRunner.
 *
 * `Notification_Email_Task` / `Notification_SMS_Task` are dispatched via
 * the `background_services` table by symbolic function name, so the
 * task entry points must live in the global namespace
 * (`doEmailNotificationTask`, `doSmsNotificationTask` in
 * `library/run_notifications.php`). Everything else they need —
 * resolving the channel client, narrowing credentials, raising on
 * failure so the orchestrator records `error` — lives here so the
 * loose-function entry points stay tiny.
 *
 * This class deliberately does NOT touch `$_GET` / `$_REQUEST` / other
 * superglobals; the caller in `library/run_notifications.php` is the
 * system boundary that owns CLI-to-legacy translation. Keep this class
 * superglobal-free so it can be exercised from a unit test and reused
 * from any other entry point that already has a populated request
 * context.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 * @codeCoverageIgnore Thin bridge: resolves the channel client through
 *     `AppDispatch::getApiService()` (DB-backed), narrows credentials,
 *     and constructs the runner. Each step pulls in live module
 *     bootstrap state that an isolated test cannot supply. The
 *     scan-and-send pipeline below this bridge is covered in
 *     `AppointmentNotificationRunnerTest`. The follow-up issue #11848
 *     proposes replacing this class with a PSR-11-resolved job, at
 *     which point the bridge itself goes away.
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Notification;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Controller\EmailClient;
use OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;

final class BackgroundReminderTask
{
    /**
     * Shared body for the email and SMS background tasks.
     *
     * Throws on runner failures so `BackgroundServiceRunner` reports
     * the service as `error` instead of silently recording `executed`.
     */
    public static function run(NotificationChannel $channel): void
    {
        $logger = ServiceContainer::getLogger();
        $globals = OEGlobalsBag::getInstance();

        // Publish ignoreAuth before resolving the client.
        // AppDispatch::__construct() reads it via OEGlobalsBag and
        // skips the session-scoped ACL gate when it's true — there is
        // no interactive session for AclMain to check against. See
        // issue #11827.
        $globals->set('ignoreAuth', true);

        $client = AppDispatch::getApiService($channel === NotificationChannel::EMAIL ? 'email' : 'sms');
        if (!$client instanceof AppDispatch) {
            throw new \RuntimeException(sprintf(
                'Failed to resolve %s client for appointment reminders.',
                $channel->value,
            ));
        }
        $credentials = $client instanceof EmailClient
            ? $client->getEmailSetup()
            : $client->getCredentials();

        // getCredentials() / getEmailSetup() are typed `mixed` because the
        // various vendor clients return their own credential shapes.
        // Narrow defensively so a misconfigured credential row can't
        // smuggle non-scalar junk through.
        $credentialsArray = is_array($credentials) ? $credentials : [];
        $notificationHoursRaw = $credentialsArray['smsHours'] ?? $credentialsArray['notification_hours'] ?? 24;
        $notificationHours = is_numeric($notificationHoursRaw) ? (int) $notificationHoursRaw : 24;
        $messageTemplateRaw = $credentialsArray['smsMessage'] ?? $credentialsArray['email_message'] ?? '';
        $messageTemplate = is_string($messageTemplateRaw) ? $messageTemplateRaw : '';
        $vendor = AppDispatch::getModuleVendor();
        $gatewayType = is_string($vendor) ? $vendor : '';

        $cronIntervalHours = (new NotificationTaskManager())->getTaskHours(strtolower($channel->value));

        // The background path never runs in dry-run mode; live delivery
        // is the whole point of the scheduled task. Keep bTestRun in
        // sync for the legacy helper that still reads it.
        $globals->set('bTestRun', false);

        set_time_limit(0);

        $runner = new AppointmentNotificationRunner(
            channel: $channel,
            client: $client,
            notificationHours: $notificationHours,
            cronIntervalHours: $cronIntervalHours,
            dryRun: false,
            messageTemplate: $messageTemplate,
            gatewayType: $gatewayType,
            logger: $logger,
        );

        $result = $runner->run();

        if ($result->hasFailures()) {
            throw new \RuntimeException(sprintf(
                'Appointment reminder run for %s completed with %d failure(s); see log for details.',
                $channel->value,
                $result->failed,
            ));
        }
    }
}
