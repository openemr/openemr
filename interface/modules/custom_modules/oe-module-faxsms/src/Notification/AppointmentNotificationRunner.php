<?php

/**
 * Scans pending appointments and delivers email or SMS reminders.
 *
 * Extracted from the top-level scan-and-send loop in
 * `library/rc_sms_notification.php` so background services running under
 * `bin/console background:services run` can invoke the delivery pipeline
 * directly — without the HTML-popup chrome, the session-bound ACL gate,
 * and the `die("Not Authorised!")` silent-success that #11827 reported.
 *
 * The runner owns only the "scan + decide window + send + log" pipeline.
 * Transport construction (EmailClient / *SMSClient), credential loading,
 * and ACL gating stay outside: the calling entry point resolves those and
 * hands the runner a fully configured client.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\FaxSMS\Notification;

use OpenEMR\Appointment\Reminder\ReminderRunResult;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Controller\EmailClient;
use OpenEMR\Modules\FaxSMS\Controller\NotificationTaskManager;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;
use OpenEMR\Modules\FaxSMS\Exception\EmailSendFailedException;
use OpenEMR\Modules\FaxSMS\Exception\InvalidEmailAddressException;
use OpenEMR\Modules\FaxSMS\Exception\SmtpNotConfiguredException;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AppointmentNotificationRunner
{
    private readonly LoggerInterface $logger;
    private readonly ClockInterface $clock;

    /**
     * @param NotificationChannel $channel             Which reminder channel to process.
     * @param AppDispatch         $client              Configured email or SMS client.
     * @param int                 $notificationHours   Appointment lead time (hours ahead).
     * @param int                 $cronIntervalHours   Background-service execution interval, in hours.
     * @param bool                $dryRun              When true, scans and logs but sends nothing.
     * @param string              $messageTemplate     Raw template with ***PLACEHOLDER*** tokens.
     * @param string              $gatewayType         Vendor slug persisted to notification_log.
     * @param LoggerInterface|null $logger             PSR-3 logger (NullLogger when omitted).
     * @param ClockInterface|null  $clock              Injected clock for deterministic tests.
     */
    public function __construct(
        private readonly NotificationChannel $channel,
        private readonly AppDispatch $client,
        private readonly int $notificationHours,
        private readonly int $cronIntervalHours,
        private readonly bool $dryRun,
        private readonly string $messageTemplate,
        private readonly string $gatewayType,
        ?LoggerInterface $logger = null,
        ?ClockInterface $clock = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->clock = $clock ?? new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable();
            }
        };
    }

    public function run(): ReminderRunResult
    {
        $appointments = $this->fetchPendingAppointments();
        $scanned = count($appointments);
        $inWindow = 0;
        $sent = 0;
        $skippedInvalid = 0;
        $failed = 0;

        foreach ($appointments as $row) {
            if (!$this->isInCronWindow($row)) {
                continue;
            }
            $inWindow++;

            $outcome = $this->deliver($row);
            match ($outcome) {
                DeliveryOutcome::Sent => $sent++,
                DeliveryOutcome::SkippedInvalid => $skippedInvalid++,
                DeliveryOutcome::Failed => $failed++,
                DeliveryOutcome::DryRun => null,
            };
        }

        $result = new ReminderRunResult(
            scanned: $scanned,
            inWindow: $inWindow,
            sent: $sent,
            skippedInvalid: $skippedInvalid,
            failed: $failed,
        );

        $this->logger->info('Appointment notification run complete.', [
            'channel' => $this->channel->value,
            'dry_run' => $this->dryRun,
            ...$result->toArray(),
        ]);

        return $result;
    }

    /**
     * Compute the signed remainder-to-send-window, in hours. Negative when
     * the ideal send time has passed, 0 at the exact send moment.
     */
    public static function computeRemainingHours(
        int $appointmentTimestamp,
        int $nowTimestamp,
        int $notificationHours,
    ): int {
        $apptHour = (int) round($appointmentTimestamp / 3600);
        $nowHour = (int) round($nowTimestamp / 3600);
        $remainingApptHour = $apptHour - $nowHour;
        return (int) round($remainingApptHour - $notificationHours);
    }

    /**
     * Substitute ***NAME*** / ***PROVIDER*** / ***DATE*** / ***STARTTIME*** /
     * ***ENDTIME*** / ***ORG*** tokens in the message template with row data.
     * Pure; callable from isolated tests without a DB.
     *
     * @param array<mixed> $row Event + patient row from faxsms_getAlertPatientData().
     */
    public static function renderMessage(string $template, array $row): string
    {
        $name = trim(sprintf(
            '%s %s %s %s',
            self::stringFromRow($row, 'title'),
            self::stringFromRow($row, 'fname'),
            self::stringFromRow($row, 'mname'),
            self::stringFromRow($row, 'lname'),
        ));
        $provider = trim(sprintf(
            '%s %s %s',
            self::stringFromRow($row, 'utitle'),
            self::stringFromRow($row, 'ufname'),
            self::stringFromRow($row, 'ulname'),
        ));
        $org = self::stringFromRow($row, 'name');
        $eventDate = self::stringFromRow($row, 'pc_eventDate');
        $startTime = self::stringFromRow($row, 'pc_startTime');
        $endTime = self::stringFromRow($row, 'pc_endTime');

        $dt = strtotime(sprintf('%s %s', $eventDate, $startTime));
        $dateStr = $dt !== false ? date('l F j, Y', $dt) : $eventDate;
        $startStr = $dt !== false ? date('g:i A', $dt) : $startTime;

        $replacements = [
            '***NAME***' => $name,
            '***PROVIDER***' => $provider,
            '***DATE***' => $dateStr,
            '***STARTTIME***' => $startStr,
            '***ENDTIME***' => $endTime,
            '***ORG***' => $org,
        ];

        return text(strtr($template, $replacements));
    }

    /**
     * Pull a string value out of an event/patient row whose values are
     * `mixed` per the type contract on the legacy fetchEvents() helpers.
     * String values pass through verbatim; numeric values are stringified
     * (lossless for ints and floats); anything else (null, bool, array,
     * object) becomes the empty string. Centralizes the narrow-don't-cast
     * logic so renderMessage and the delivery helpers don't have to.
     *
     * @param array<mixed> $row
     */
    private static function stringFromRow(array $row, string $key): string
    {
        $value = $row[$key] ?? null;
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return '';
    }

    /**
     * Normalize a phone string to 10 digits, stripping a leading country
     * code of 1. Returns the normalized digits, or null when the input does
     * not yield exactly 10 digits after cleanup.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $phone ?? '');
        if (!is_string($digits)) {
            return null;
        }
        if (strlen($digits) === 11 && $digits[0] === '1') {
            $digits = substr($digits, 1);
        }
        return strlen($digits) === 10 ? $digits : null;
    }

    /**
     * @return list<array<mixed>>
     */
    protected function fetchPendingAppointments(): array
    {
        return faxsms_getAlertPatientData($this->channel, $this->notificationHours);
    }

    /**
     * @param array<mixed> $row
     */
    private function isInCronWindow(array $row): bool
    {
        $eventDate = self::stringFromRow($row, 'pc_eventDate');
        $startTime = self::stringFromRow($row, 'pc_startTime');
        $apptTs = strtotime(sprintf('%s %s', $eventDate, $startTime));
        if ($apptTs === false) {
            return false;
        }
        $nowTs = $this->clock->now()->getTimestamp();
        $remainHour = self::computeRemainingHours($apptTs, $nowTs, $this->notificationHours);
        return NotificationTaskManager::isWithinCronWindow($remainHour, $this->cronIntervalHours);
    }

    /**
     * @param array<mixed> $row
     */
    private function deliver(array $row): DeliveryOutcome
    {
        $message = self::renderMessage($this->messageTemplate, $row);
        $logData = [
            'type' => $this->channel->value,
            'sms_gateway_type' => $this->gatewayType,
            'message' => $message,
            'email_sender' => '',
            'email_subject' => '',
        ];

        return match ($this->channel) {
            NotificationChannel::SMS => $this->deliverSms($row, $message, $logData),
            NotificationChannel::EMAIL => $this->deliverEmail($row, $message, $logData),
        };
    }

    /**
     * @param array<mixed> $row
     * @param array<mixed> $logData
     */
    private function deliverSms(array $row, string $message, array $logData): DeliveryOutcome
    {
        $phone = self::stringFromRow($row, 'phone_cell');
        $normalized = self::normalizePhone($phone === '' ? null : $phone);
        if ($normalized === null) {
            $this->logger->warning('Skipping SMS reminder: invalid phone number.', [
                'pid' => $row['pid'] ?? null,
                'pc_eid' => $row['pc_eid'] ?? null,
            ]);
            if (!$this->dryRun) {
                cron_InsertNotificationLogEntryFaxsms($this->channel->value, $row, array_merge($logData, [
                    'message' => 'Error: INVALID Mobile Phone',
                ]));
            }
            return DeliveryOutcome::SkippedInvalid;
        }

        if ($this->dryRun) {
            return DeliveryOutcome::DryRun;
        }

        try {
            /** @phpstan-ignore-next-line method.notFound (SMS client subclasses override this with 4-arg signature) */
            $error = $this->client->sendSMS(
                $normalized,
                self::stringFromRow($logData, 'email_subject'),
                $message,
                self::stringFromRow($logData, 'email_sender'),
            );
        } catch (\RuntimeException $e) {
            // Per-appointment delivery is best-effort: a runtime
            // failure for one recipient (vendor ACL gate, transport
            // error) records a Failed outcome and lets the batch keep
            // processing. Anything not a RuntimeException — \Error
            // subclasses or unexpected exception types — propagates
            // so the operator sees a real run-level failure instead
            // of an identical error stacking up across every row.
            $this->logger->error('SMS reminder send threw an exception.', [
                'pid' => $row['pid'] ?? null,
                'pc_eid' => $row['pc_eid'] ?? null,
                'exception' => $e,
            ]);
            return DeliveryOutcome::Failed;
        }

        if (is_string($error) && stripos($error, 'error') !== false) {
            $this->logger->error('SMS reminder send reported an error.', [
                'pid' => $row['pid'] ?? null,
                'pc_eid' => $row['pc_eid'] ?? null,
                'provider_error' => $error,
            ]);
            return DeliveryOutcome::Failed;
        }

        cron_InsertNotificationLogEntryFaxsms($this->channel->value, $row, $logData);
        $this->markNotified($row);
        return DeliveryOutcome::Sent;
    }

    /**
     * @param array<mixed> $row
     * @param array<mixed> $logData
     */
    private function deliverEmail(array $row, string $message, array $logData): DeliveryOutcome
    {
        $emailClient = $this->client;
        if (!$emailClient instanceof EmailClient) {
            throw new \LogicException('Email channel requires an EmailClient instance.');
        }

        $email = self::stringFromRow($row, 'email');
        if (!$emailClient->validEmail($email)) {
            $this->logger->warning('Skipping email reminder: invalid recipient.', [
                'pid' => $row['pid'] ?? null,
                'pc_eid' => $row['pc_eid'] ?? null,
            ]);
            if (!$this->dryRun) {
                cron_InsertNotificationLogEntryFaxsms($this->channel->value, $row, array_merge($logData, [
                    'message' => 'Error: INVALID EMAIL',
                ]));
            }
            return DeliveryOutcome::SkippedInvalid;
        }

        if ($this->dryRun) {
            return DeliveryOutcome::DryRun;
        }

        try {
            $emailClient->emailReminder($email, $message);
        } catch (InvalidEmailAddressException) {
            return DeliveryOutcome::SkippedInvalid;
        } catch (SmtpNotConfiguredException $e) {
            $this->logger->error('Email reminder failed: SMTP not configured.', [
                'exception' => $e,
            ]);
            return DeliveryOutcome::Failed;
        } catch (EmailSendFailedException | PHPMailerException $e) {
            $this->logger->error('Email reminder send threw an exception.', [
                'pid' => $row['pid'] ?? null,
                'pc_eid' => $row['pc_eid'] ?? null,
                'exception' => $e,
            ]);
            return DeliveryOutcome::Failed;
        }

        cron_InsertNotificationLogEntryFaxsms($this->channel->value, $row, $logData);
        $this->markNotified($row);
        return DeliveryOutcome::Sent;
    }

    /**
     * @param array<mixed> $row
     */
    private function markNotified(array $row): void
    {
        if ($this->dryRun) {
            return;
        }
        $pid = $row['pid'] ?? null;
        $pcEid = $row['pc_eid'] ?? null;
        if (!is_numeric($pid) || !is_numeric($pcEid)) {
            return;
        }
        $recur = self::stringFromRow($row, 'pc_recurrtype');
        rc_sms_notification_cron_update_entry($this->channel, (int) $pid, (int) $pcEid, $recur);
    }
}
