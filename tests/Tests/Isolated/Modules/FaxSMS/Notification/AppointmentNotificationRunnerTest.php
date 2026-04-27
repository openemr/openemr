<?php

/**
 * Isolated tests for AppointmentNotificationRunner.
 *
 * Covers two layers:
 *
 *   1. The pure static helpers (`computeRemainingHours`, `normalizePhone`,
 *      `renderMessage`) — pull them out and pin behavior independently
 *      so refactoring the orchestrator can't quietly change phone
 *      normalization or the cron-window math.
 *
 *   2. The `run()` orchestrator and the per-channel delivery helpers,
 *      exercised through fake `AppDispatch` / `EmailClient` subclasses
 *      that bypass the heavy real-world constructors. The fakes record
 *      what the runner asked them to do; stub global functions
 *      (`cron_InsertNotificationLogEntryFaxsms`,
 *      `rc_sms_notification_cron_update_entry`, `text`) capture the
 *      legacy SQL/log calls so we can assert that the dry-run path
 *      doesn't insert log rows and the happy path does.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\FaxSMS\Notification;

// Spy lives in the global namespace (declared in runner_test_stubs.php
// via bracketed namespace) so the in-namespace function stubs can write
// to it. Alias it locally for terse references in the test class.
use AppointmentNotificationRunnerTestSpy as RunnerTestSpy;
use OpenEMR\Modules\FaxSMS\Controller\AppDispatch;
use OpenEMR\Modules\FaxSMS\Controller\EmailClient;
use OpenEMR\Modules\FaxSMS\Enums\NotificationChannel;
use OpenEMR\Modules\FaxSMS\Exception\EmailSendFailedException;
use OpenEMR\Modules\FaxSMS\Exception\InvalidEmailAddressException;
use OpenEMR\Modules\FaxSMS\Exception\SmtpNotConfiguredException;
use OpenEMR\Modules\FaxSMS\Notification\AppointmentNotificationRunner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

// Module classes live under interface/modules/custom_modules/oe-module-faxsms/
// and aren't covered by the root composer autoloader. Require the chain
// the test actually touches; everything else stays lazy.
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/AppDispatch.php';
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Controller/EmailClient.php';
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Exception/EmailException.php';
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Exception/EmailSendFailedException.php';
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Exception/InvalidEmailAddressException.php';
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Exception/SmtpNotConfiguredException.php';
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Notification/DeliveryOutcome.php';
require_once __DIR__ . '/../../../../../../interface/modules/custom_modules/oe-module-faxsms/src/Notification/AppointmentNotificationRunner.php';

// `text()` and the two procedural helpers (`cron_InsertNotificationLogEntryFaxsms`,
// `rc_sms_notification_cron_update_entry`) are looked up unqualified by the
// runner, which lands in OpenEMR\Modules\FaxSMS\Notification first. The
// stubs and shared spy state live in `runner_test_stubs.php` because that
// file uses bracketed namespace syntax to declare symbols in both the
// runner's namespace and global at once — not possible from this file's
// single-namespace declaration.
require_once __DIR__ . '/runner_test_stubs.php';

/**
 * AppDispatch is abstract and constructs heavy session/ACL state;
 * skip its constructor entirely and only implement the surface the
 * runner actually calls.
 */
class FakeSmsClient extends AppDispatch
{
    public string|\Throwable $sendResult = '';
    public int $sendCalls = 0;

    public function __construct()
    {
        // Skip parent::__construct() — none of its session/crypto setup is needed.
    }

    public function sendSMS(string $toPhone = '', string $subject = '', string $message = '', string $from = ''): mixed
    {
        $this->sendCalls++;
        if ($this->sendResult instanceof \Throwable) {
            throw $this->sendResult;
        }
        return $this->sendResult;
    }

    // Remaining abstract surface — stubs that just satisfy the contract.
    public function authenticate(): string|int|bool
    {
        return true;
    }

    public function sendFax(): string|bool
    {
        return true;
    }

    public function sendEmail(): mixed
    {
        return true;
    }

    public function fetchReminderCount(): string|bool
    {
        return '0';
    }
}

class FakeEmailClient extends EmailClient
{
    public bool $emailIsValid = true;
    public ?\Throwable $emailReminderThrows = null;
    public int $emailReminderCalls = 0;

    public function __construct()
    {
        // Skip parent::__construct() — module-enable check + crypto setup are not needed.
    }

    public function validEmail(mixed $email): bool
    {
        return $this->emailIsValid;
    }

    public function emailReminder(mixed $email, mixed $body): void
    {
        $this->emailReminderCalls++;
        if ($this->emailReminderThrows !== null) {
            throw $this->emailReminderThrows;
        }
    }
}

/**
 * Subclass that returns a canned set of "pending appointments" instead
 * of querying the database. `fetchPendingAppointments()` is `protected`
 * on the base runner specifically to support this.
 */
class TestableRunner extends AppointmentNotificationRunner
{
    /** @var list<array<mixed>> */
    public array $stubAppointments = [];

    protected function fetchPendingAppointments(): array
    {
        return $this->stubAppointments;
    }
}

/**
 * Deterministic clock for cron-window math.
 */
class FixedClock implements ClockInterface
{
    public function __construct(private readonly \DateTimeImmutable $now)
    {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }
}

class AppointmentNotificationRunnerTest extends TestCase
{
    protected function setUp(): void
    {
        RunnerTestSpy::reset();
    }

    #[DataProvider('remainingHoursProvider')]
    public function testComputeRemainingHours(
        int $appointmentTimestamp,
        int $nowTimestamp,
        int $notificationHours,
        int $expected,
    ): void {
        $this->assertSame(
            $expected,
            AppointmentNotificationRunner::computeRemainingHours(
                $appointmentTimestamp,
                $nowTimestamp,
                $notificationHours,
            ),
        );
    }

    /**
     * @return array<string, array{int, int, int, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function remainingHoursProvider(): array
    {
        $now = 1_745_000_000; // arbitrary anchor
        return [
            'appointment 24h out, lead time 24h, send now' => [$now + 86_400, $now, 24, 0],
            'appointment 25h out, lead time 24h, 1h early' => [$now + 90_000, $now, 24, 1],
            'appointment 23h out, lead time 24h, 1h late'  => [$now + 82_800, $now, 24, -1],
            'lead time 0, appointment 5h out'              => [$now + 18_000, $now, 0, 5],
            'lead time 48h, appointment 24h out'           => [$now + 86_400, $now, 48, -24],
        ];
    }

    #[DataProvider('phoneProvider')]
    public function testNormalizePhone(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, AppointmentNotificationRunner::normalizePhone($input));
    }

    /**
     * @return array<string, array{?string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function phoneProvider(): array
    {
        return [
            'plain ten digits'             => ['2125551234', '2125551234'],
            'leading 1 stripped'           => ['12125551234', '2125551234'],
            'formatted with parens dashes' => ['(212) 555-1234', '2125551234'],
            'plus-prefixed E.164'          => ['+12125551234', '2125551234'],
            'too short'                    => ['555-1234', null],
            'eleven-digit not starting 1'  => ['22125551234', null],
            'empty string'                 => ['', null],
            'null input'                   => [null, null],
            'letters only'                 => ['no-digits-here', null],
        ];
    }

    public function testRenderMessageSubstitutesAllTokens(): void
    {
        $template = '***NAME*** has an appointment with ***PROVIDER*** at ***ORG*** on ***DATE*** from ***STARTTIME*** to ***ENDTIME***.';
        $row = [
            'title' => 'Mr.',
            'fname' => 'Pat',
            'mname' => 'Q',
            'lname' => 'Patient',
            'utitle' => 'Dr.',
            'ufname' => 'Alex',
            'ulname' => 'Provider',
            'name' => 'Acme Clinic',
            'pc_eventDate' => '2026-04-15',
            'pc_startTime' => '10:30:00',
            'pc_endTime' => '11:00:00',
        ];

        $rendered = AppointmentNotificationRunner::renderMessage($template, $row);

        $this->assertStringContainsString('Mr. Pat Q Patient', $rendered);
        $this->assertStringContainsString('Dr. Alex Provider', $rendered);
        $this->assertStringContainsString('Acme Clinic', $rendered);
        $this->assertStringContainsString('Wednesday April 15, 2026', $rendered);
        $this->assertStringContainsString('10:30 AM', $rendered);
        $this->assertStringContainsString('11:00:00', $rendered);
    }

    public function testRenderMessageHandlesMissingFields(): void
    {
        // Missing optional fields should render as empty rather than
        // surface "Notice: undefined index" warnings.
        $template = 'Hello ***NAME***, see you ***DATE***.';
        $rendered = AppointmentNotificationRunner::renderMessage($template, []);

        $this->assertStringContainsString('Hello', $rendered);
        $this->assertStringContainsString('see you', $rendered);
    }

    public function testRunReturnsZeroResultWhenNoAppointmentsPending(): void
    {
        $runner = $this->makeSmsRunner(client: new FakeSmsClient(), dryRun: false);

        $result = $runner->run();

        $this->assertSame(0, $result->scanned);
        $this->assertSame(0, $result->inWindow);
        $this->assertSame(0, $result->sent);
        $this->assertSame(0, $result->failed);
        $this->assertFalse($result->hasFailures());
    }

    public function testRunSkipsAppointmentsOutsideCronWindow(): void
    {
        // Lead time = 24h, cron window = 1h. Appointment 100h out — way
        // outside the send-now window.
        $client = new FakeSmsClient();
        $runner = $this->makeSmsRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['phone_cell' => '2125551234', 'pc_eventDate' => '2030-01-01', 'pc_startTime' => '10:00:00'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->scanned);
        $this->assertSame(0, $result->inWindow);
        $this->assertSame(0, $client->sendCalls);
    }

    public function testRunSmsHappyPathSendsAndLogsAndMarks(): void
    {
        $client = new FakeSmsClient();
        $client->sendResult = '';
        $runner = $this->makeSmsRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['phone_cell' => '2125551234'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->scanned);
        $this->assertSame(1, $result->inWindow);
        $this->assertSame(1, $result->sent);
        $this->assertSame(0, $result->failed);
        $this->assertSame(1, $client->sendCalls);
        $this->assertCount(1, RunnerTestSpy::$logCalls);
        $this->assertCount(1, RunnerTestSpy::$markCalls);
    }

    public function testRunSmsInvalidPhoneIsSkippedAndLoggedAsInvalid(): void
    {
        $client = new FakeSmsClient();
        $runner = $this->makeSmsRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['phone_cell' => 'not-a-phone'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->skippedInvalid);
        $this->assertSame(0, $client->sendCalls);
        $this->assertCount(1, RunnerTestSpy::$logCalls);
        $this->assertSame('Error: INVALID Mobile Phone', RunnerTestSpy::$logCalls[0]['logData']['message']);
        $this->assertCount(0, RunnerTestSpy::$markCalls);
    }

    public function testRunSmsProviderErrorStringIsRecordedAsFailure(): void
    {
        $client = new FakeSmsClient();
        $client->sendResult = 'error: gateway rejected';
        $runner = $this->makeSmsRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['phone_cell' => '2125551234'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->failed);
        $this->assertTrue($result->hasFailures());
        $this->assertCount(0, RunnerTestSpy::$markCalls);
    }

    public function testRunSmsRuntimeExceptionIsCaughtAndRecordedAsFailure(): void
    {
        $client = new FakeSmsClient();
        $client->sendResult = new \RuntimeException('vendor ACL denied');
        $runner = $this->makeSmsRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['phone_cell' => '2125551234'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->failed);
    }

    public function testRunSmsDryRunNeitherSendsNorLogs(): void
    {
        $client = new FakeSmsClient();
        $runner = $this->makeSmsRunner(
            client: $client,
            dryRun: true,
            appointments: [$this->makeRow(['phone_cell' => '2125551234'])],
        );

        $result = $runner->run();

        // DryRun outcome counts toward inWindow but neither sent nor failed.
        $this->assertSame(1, $result->inWindow);
        $this->assertSame(0, $result->sent);
        $this->assertSame(0, $result->failed);
        $this->assertSame(0, $client->sendCalls);
        $this->assertCount(0, RunnerTestSpy::$logCalls);
        $this->assertCount(0, RunnerTestSpy::$markCalls);
    }

    public function testRunEmailHappyPathSendsAndLogsAndMarks(): void
    {
        $client = new FakeEmailClient();
        $runner = $this->makeEmailRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['email' => 'pat@example.com'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->sent);
        $this->assertSame(0, $result->failed);
        $this->assertSame(1, $client->emailReminderCalls);
        $this->assertCount(1, RunnerTestSpy::$logCalls);
        $this->assertCount(1, RunnerTestSpy::$markCalls);
    }

    public function testRunEmailInvalidEmailIsSkippedAndLogged(): void
    {
        $client = new FakeEmailClient();
        $client->emailIsValid = false;
        $runner = $this->makeEmailRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['email' => 'not-an-email'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->skippedInvalid);
        $this->assertSame(0, $client->emailReminderCalls);
        $this->assertSame('Error: INVALID EMAIL', RunnerTestSpy::$logCalls[0]['logData']['message']);
    }

    public function testRunEmailInvalidAddressExceptionIsTreatedAsSkippedInvalid(): void
    {
        $client = new FakeEmailClient();
        $client->emailReminderThrows = new InvalidEmailAddressException('bad address');
        $runner = $this->makeEmailRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['email' => 'pat@example.com'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->skippedInvalid);
        $this->assertSame(0, $result->failed);
    }

    public function testRunEmailSmtpNotConfiguredIsRecordedAsFailure(): void
    {
        $client = new FakeEmailClient();
        $client->emailReminderThrows = new SmtpNotConfiguredException('SMTP not configured');
        $runner = $this->makeEmailRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['email' => 'pat@example.com'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->failed);
    }

    public function testRunEmailSendFailedExceptionIsRecordedAsFailure(): void
    {
        $client = new FakeEmailClient();
        $client->emailReminderThrows = new EmailSendFailedException('transport failed');
        $runner = $this->makeEmailRunner(
            client: $client,
            dryRun: false,
            appointments: [$this->makeRow(['email' => 'pat@example.com'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->failed);
    }

    public function testRunEmailDryRunDoesNotSend(): void
    {
        $client = new FakeEmailClient();
        $runner = $this->makeEmailRunner(
            client: $client,
            dryRun: true,
            appointments: [$this->makeRow(['email' => 'pat@example.com'])],
        );

        $result = $runner->run();

        $this->assertSame(1, $result->inWindow);
        $this->assertSame(0, $result->sent);
        $this->assertSame(0, $client->emailReminderCalls);
        $this->assertCount(0, RunnerTestSpy::$logCalls);
    }

    /**
     * Build an SMS runner anchored to a clock that puts the row exactly
     * at the send-now moment (lead time 24h, appt 24h out, cron 1h).
     *
     * @param list<array<mixed>> $appointments
     */
    private function makeSmsRunner(
        FakeSmsClient $client,
        bool $dryRun,
        array $appointments = [],
    ): TestableRunner {
        return $this->makeRunner(NotificationChannel::SMS, $client, $dryRun, $appointments);
    }

    /**
     * @param list<array<mixed>> $appointments
     */
    private function makeEmailRunner(
        FakeEmailClient $client,
        bool $dryRun,
        array $appointments = [],
    ): TestableRunner {
        return $this->makeRunner(NotificationChannel::EMAIL, $client, $dryRun, $appointments);
    }

    /**
     * @param list<array<mixed>> $appointments
     */
    private function makeRunner(
        NotificationChannel $channel,
        AppDispatch $client,
        bool $dryRun,
        array $appointments,
    ): TestableRunner {
        // Anchor "now" so each row's pc_eventDate/pc_startTime sits 24h
        // ahead — exactly inside the cron window for lead time 24h.
        $now = new \DateTimeImmutable('2026-04-14 10:00:00');
        $runner = new TestableRunner(
            channel: $channel,
            client: $client,
            notificationHours: 24,
            cronIntervalHours: 1,
            dryRun: $dryRun,
            messageTemplate: 'Hi ***NAME***, your visit is on ***DATE***.',
            gatewayType: 'fake',
            logger: null,
            clock: new FixedClock($now),
        );
        $runner->stubAppointments = $appointments;
        return $runner;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function makeRow(array $overrides = []): array
    {
        return array_merge([
            'pid' => 42,
            'pc_eid' => 7,
            'pc_eventDate' => '2026-04-15',
            'pc_startTime' => '10:00:00',
            'pc_endTime' => '10:30:00',
            'pc_recurrtype' => '0',
            'fname' => 'Pat',
            'lname' => 'Patient',
            'name' => 'Acme Clinic',
        ], $overrides);
    }
}
