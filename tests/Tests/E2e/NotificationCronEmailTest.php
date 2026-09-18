<?php

/**
 * E2E test for the faxsms notification cron's dedup behavior.
 *
 * Verifies that marking an appointment as notified (email or SMS) sets the
 * per-channel alert flag without clobbering pc_apptstatus, and that the
 * dedup query correctly excludes already-notified appointments.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\E2e;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;
use OpenEMR\Modules\FaxSMS\Enums\ServiceType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NotificationCronEmailTest extends TestCase
{
    private const TEST_EMAIL = 'test-notification-cron@example.com';
    private const NOTIFICATION_HOURS = 24;

    private static bool $functionsLoaded = false;

    private int $testPatientPid = 0;
    private int $testEventEid = 0;

    /**
     * Load the cron script once to define its global functions.
     *
     * The script outputs HTML and initializes SMTP services as a side
     * effect, so we run it in dryrun mode with output buffering.
     */
    public static function setUpBeforeClass(): void
    {
        self::markTestSkipped(
            'Loading rc_sms_notification.php pulls in the full notification '
            . 'cron pipeline, which requires the faxsms module schema '
            . '(module_faxsms_credentials etc.), a configured email service, '
            . 'an authenticated session, and ACL setup. The module is not '
            . 'installed in the CI database. Re-enable once either the test '
            . 'env installs the faxsms module or the dedup logic is testable '
            . 'without require_once-ing the cron script.'
        );

        if (self::$functionsLoaded) {
            return;
        }

        $globals = OEGlobalsBag::getInstance();
        $faxSmsPath = $globals->getString('fileroot')
            . '/interface/modules/custom_modules/oe-module-faxsms';

        // Module source files are not in the main autoloader
        require_once $faxSmsPath . '/src/Enums/NotificationChannel.php';
        require_once $faxSmsPath . '/src/Enums/ServiceType.php';
        require_once $faxSmsPath . '/src/Controller/AppDispatch.php';
        require_once $faxSmsPath . '/src/Controller/EmailClient.php';
        require_once $faxSmsPath . '/src/Controller/NotificationTaskManager.php';
        require_once $faxSmsPath . '/src/BootstrapService.php';
        require_once $faxSmsPath . '/src/Exception/EmailException.php';
        require_once $faxSmsPath . '/src/Exception/SmtpNotConfiguredException.php';
        require_once $faxSmsPath . '/src/Exception/InvalidEmailAddressException.php';
        require_once $faxSmsPath . '/src/Exception/EmailSendFailedException.php';

        require_once $globals->getString('srcdir') . '/appointments.inc.php';

        // SMTP globals — point at Mailpit so the script initializes
        $GLOBALS['EMAIL_METHOD'] = 'SMTP';
        $GLOBALS['SMTP_HOST'] = getenv('OPENEMR_SETTING_SMTP_HOST') ?: 'mailpit';
        $GLOBALS['SMTP_PORT'] = getenv('OPENEMR_SETTING_SMTP_PORT') ?: '1025';
        $GLOBALS['SMTP_USER'] = getenv('OPENEMR_SETTING_SMTP_USER') ?: 'openemr';
        $GLOBALS['SMTP_PASS'] = getenv('OPENEMR_SETTING_SMTP_PASS') ?: 'openemr';
        $GLOBALS['SMTP_SECURE'] = getenv('OPENEMR_SETTING_SMTP_SECURE') ?: 'none';
        $GLOBALS['SMTP_Auth'] = getenv('OPENEMR_SETTING_SMTP_Auth') ?: 'TRUE';
        $GLOBALS['practice_return_email_path'] = 'noreply@openemr.local';
        $GLOBALS['patient_reminder_sender_name'] = 'OpenEMR Test';
        $GLOBALS['oe_enable_email'] = true;
        // AppDispatch::getServiceType() reads the enable flag from the
        // OEGlobalsBag, not $GLOBALS, and the factory map keys on
        // ServiceType::EMAIL->value — setting the bag entry is what makes
        // getApiService('email') resolve to EmailClient.
        OEGlobalsBag::getInstance()->set('oe_enable_email', ServiceType::EMAIL->value);

        // Session state for ACL check inside the script
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        $session->set('authUser', 'admin');
        $session->set('authUserID', 1);
        $session->set('site_id', 'default');

        // Dryrun prevents the main loop from sending or updating
        $_REQUEST['dryrun'] = '1';
        $_GET['type'] = 'email';
        $_GET['site'] = 'default';

        ob_start();
        require_once $faxSmsPath . '/library/rc_sms_notification.php';
        ob_end_clean();

        self::$functionsLoaded = true;
    }

    protected function setUp(): void
    {
        // The update function reads global $bTestRun — ensure it's off
        $GLOBALS['bTestRun'] = 0;

        $this->insertTestPatient();
        $this->insertTestAppointment();
    }

    protected function tearDown(): void
    {
        if ($this->testEventEid > 0) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM openemr_postcalendar_events WHERE pc_eid = ?',
                [$this->testEventEid],
            );
        }
        if ($this->testPatientPid > 0) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM notification_log WHERE pid = ? AND pc_eid = ?',
                [$this->testPatientPid, $this->testEventEid],
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM patient_data WHERE pid = ?',
                [$this->testPatientPid],
            );
        }
    }

    #[Test]
    public function emailDedupDoesNotClobberAppointmentStatus(): void
    {
        $this->assertInitialState();

        $this->assertAppointmentFoundBy(NotificationChannel::EMAIL);

        rc_sms_notification_cron_update_entry(
            NotificationChannel::EMAIL,
            $this->testPatientPid,
            $this->testEventEid,
        );

        $row = $this->fetchEvent();
        $this->assertSame('-', $row['pc_apptstatus'], 'pc_apptstatus must not be overwritten');
        $this->assertSame('YES', $row['pc_sendalertemail'], 'pc_sendalertemail should be YES');
        $this->assertSame('NO', $row['pc_sendalertsms'], 'pc_sendalertsms should remain NO');

        $this->assertAppointmentNotFoundBy(
            NotificationChannel::EMAIL,
            'Already-notified appointment must be excluded by dedup',
        );
    }

    #[Test]
    public function smsDedupDoesNotClobberAppointmentStatus(): void
    {
        $this->assertInitialState();

        $this->assertAppointmentFoundBy(NotificationChannel::SMS);

        rc_sms_notification_cron_update_entry(
            NotificationChannel::SMS,
            $this->testPatientPid,
            $this->testEventEid,
        );

        $row = $this->fetchEvent();
        $this->assertSame('-', $row['pc_apptstatus'], 'pc_apptstatus must not be overwritten');
        $this->assertSame('YES', $row['pc_sendalertsms'], 'pc_sendalertsms should be YES');
        $this->assertSame('NO', $row['pc_sendalertemail'], 'pc_sendalertemail should remain NO');

        $this->assertAppointmentNotFoundBy(
            NotificationChannel::SMS,
            'Already-notified appointment must be excluded by dedup',
        );
    }

    #[Test]
    public function cancelledAppointmentIsExcludedFromNotification(): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE openemr_postcalendar_events SET pc_apptstatus = 'x' WHERE pc_eid = ?",
            [$this->testEventEid],
        );

        $this->assertAppointmentNotFoundBy(
            NotificationChannel::EMAIL,
            'Cancelled appointment must be excluded',
        );
        $this->assertAppointmentNotFoundBy(
            NotificationChannel::SMS,
            'Cancelled appointment must be excluded',
        );
    }

    #[Test]
    public function emailDedupDoesNotAffectSmsDedupAndViceVersa(): void
    {
        // Mark as email-notified
        rc_sms_notification_cron_update_entry(
            NotificationChannel::EMAIL,
            $this->testPatientPid,
            $this->testEventEid,
        );

        // SMS query should still find the appointment (only email flag is set)
        $this->assertAppointmentFoundBy(NotificationChannel::SMS);
        $this->assertAppointmentNotFoundBy(NotificationChannel::EMAIL);

        // Now mark as SMS-notified too
        rc_sms_notification_cron_update_entry(
            NotificationChannel::SMS,
            $this->testPatientPid,
            $this->testEventEid,
        );

        // Both channels should now exclude it
        $this->assertAppointmentNotFoundBy(NotificationChannel::EMAIL);
        $this->assertAppointmentNotFoundBy(NotificationChannel::SMS);

        // Appointment status still untouched
        $row = $this->fetchEvent();
        $this->assertSame('-', $row['pc_apptstatus']);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function insertTestPatient(): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data"
            . " (fname, lname, email, hipaa_allowemail, hipaa_allowsms, phone_cell, title)"
            . " VALUES ('TestCron', 'Patient', ?, 'YES', 'YES', '5551234567', 'Mr.')",
            [self::TEST_EMAIL],
        );
        $pid = QueryUtils::fetchSingleValue(
            'SELECT MAX(pid) AS v FROM patient_data WHERE email = ?',
            'v',
            [self::TEST_EMAIL],
        );
        $this->assertIsNumeric($pid);
        $this->testPatientPid = (int) $pid;
        $this->assertGreaterThan(0, $this->testPatientPid);
    }

    private function insertTestAppointment(): void
    {
        // Compute the date faxsms_getAlertPatientData() will query for
        $adjHour = (int) date('H') + self::NOTIFICATION_HOURS;
        $timestamp = mktime($adjHour, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'));
        $this->assertIsInt($timestamp);
        $eventDate = date('Y-m-d', $timestamp);

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO openemr_postcalendar_events"
            . " (pc_pid, pc_aid, pc_eventDate, pc_endDate,"
            . "  pc_startTime, pc_endTime, pc_duration, pc_catid,"
            . "  pc_apptstatus, pc_sendalertemail, pc_sendalertsms,"
            . "  pc_recurrtype, pc_facility, pc_title)"
            . " VALUES (?, 1, ?, ?, '12:00:00', '12:30:00', 1800, 5,"
            . "         '-', 'NO', 'NO', 0, 3, 'Test Notification Cron')",
            [$this->testPatientPid, $eventDate, $eventDate],
        );
        $eid = QueryUtils::fetchSingleValue(
            'SELECT MAX(pc_eid) AS v FROM openemr_postcalendar_events WHERE pc_pid = ?',
            'v',
            [$this->testPatientPid],
        );
        $this->assertIsNumeric($eid);
        $this->testEventEid = (int) $eid;
        $this->assertGreaterThan(0, $this->testEventEid);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchEvent(): array
    {
        $row = QueryUtils::querySingleRow(
            'SELECT pc_apptstatus, pc_sendalertemail, pc_sendalertsms'
            . ' FROM openemr_postcalendar_events WHERE pc_eid = ?',
            [$this->testEventEid],
        );
        $this->assertIsArray($row);
        /** @var array{pc_apptstatus: string, pc_sendalertemail: string, pc_sendalertsms: string} $row */
        return $row;
    }

    private function assertInitialState(): void
    {
        $row = $this->fetchEvent();
        $this->assertSame('-', $row['pc_apptstatus']);
        $this->assertSame('NO', $row['pc_sendalertemail']);
        $this->assertSame('NO', $row['pc_sendalertsms']);
    }

    private function assertAppointmentFoundBy(NotificationChannel $channel): void
    {
        $appointments = faxsms_getAlertPatientData($channel, self::NOTIFICATION_HOURS);
        $this->assertTrue(
            $this->eventInResults($appointments),
            "Appointment should be found by {$channel->value} query",
        );
    }

    private function assertAppointmentNotFoundBy(
        NotificationChannel $channel,
        string $message = '',
    ): void {
        $appointments = faxsms_getAlertPatientData($channel, self::NOTIFICATION_HOURS);
        $this->assertFalse(
            $this->eventInResults($appointments),
            $message ?: "Appointment should NOT be found by {$channel->value} query",
        );
    }

    /**
     * @param list<array<mixed>> $appointments
     */
    private function eventInResults(array $appointments): bool
    {
        foreach ($appointments as $appt) {
            $eid = $appt['pc_eid'] ?? 0;
            if (is_numeric($eid) && (int) $eid === $this->testEventEid) {
                return true;
            }
        }
        return false;
    }
}
