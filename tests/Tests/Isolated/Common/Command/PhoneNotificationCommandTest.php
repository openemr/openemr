<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Command;

use MaviqClient;
use OpenEMR\Common\Command\PhoneNotificationCommand;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RestResponse;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

// MaviqClient and RestResponse are legacy globals defined procedurally; PHPUnit
// resolves class-level `extends` clauses at file-load time, so load the file
// before the stub class declarations below.
// @codeCoverageIgnoreStart
if (!class_exists(MaviqClient::class, false)) {
    require_once __DIR__ . '/../../../../../library/maviq_phone_api.php';
}
// @codeCoverageIgnoreEnd

#[Group('isolated')]
#[Group('phone-notifications')]
class PhoneNotificationCommandTest extends TestCase
{
    /**
     * @codeCoverageIgnore PHPUnit lifecycle hook; bootstrap only.
     */
    public static function setUpBeforeClass(): void
    {
        $helpers = realpath(__DIR__ . '/../../../../../library/htmlspecialchars.inc.php');
        if ($helpers !== false && !function_exists('xlt')) {
            require_once $helpers;
        }
        // xl() reaches into the database via sqlStatementNoLog to look up
        // translations. Short-circuit it for isolated tests.
        $GLOBALS['disable_translation'] = true;
    }

    private function createTester(PhoneNotificationCommandStub $command): CommandTester
    {
        $app = new Application();
        $app->addCommand($command);
        return new CommandTester($app->find('notifications:phone'));
    }

    /**
     * @return array<string, mixed>
     */
    private static function makePatient(
        string $pid = '42',
        string $pcEid = '100',
        string $firstName = 'Alice',
        string $lastName = 'Smith',
        string $phoneHome = '555-1234',
        string $eventDate = '2026-04-20',
        string $startTime = '09:30:00',
        int $facility = 3,
    ): array {
        return [
            'pid' => $pid,
            'pc_eid' => $pcEid,
            'fname' => $firstName,
            'lname' => $lastName,
            'mname' => '',
            'title' => 'Mr',
            'phone_home' => $phoneHome,
            'pc_eventDate' => $eventDate,
            'pc_endDate' => $eventDate,
            'pc_startTime' => $startTime,
            'pc_endTime' => '10:00:00',
            'pc_facility' => $facility,
            'pc_aid' => 'Dr. Who',
        ];
    }

    public function testMissingGatewayUrlFails(): void
    {
        $command = new PhoneNotificationCommandStub(
            globalsOverrides: ['phone_gateway_url' => ''],
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Phone gateway is not configured', $tester->getDisplay());
    }

    public function testMissingUsernameFails(): void
    {
        $command = new PhoneNotificationCommandStub(
            globalsOverrides: ['phone_gateway_username' => ''],
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Phone gateway is not configured', $tester->getDisplay());
    }

    public function testMissingPasswordFails(): void
    {
        $command = new PhoneNotificationCommandStub(
            globalsOverrides: ['phone_gateway_password' => ''],
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Phone gateway is not configured', $tester->getDisplay());
    }

    public function testNoPatientsReturnsSuccess(): void
    {
        $command = new PhoneNotificationCommandStub(patients: []);
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Total Records Found: 0', $tester->getDisplay());
        $this->assertSame([], $command->markedSent);
        $this->assertSame([], $command->insertedLogs);
    }

    public function testSuccessfulReminderRecordsSideEffects(): void
    {
        $patient = self::makePatient(pid: '42', pcEid: '100', firstName: 'Alice');
        $command = new PhoneNotificationCommandStub(
            patients: [$patient],
            facilitiesMap: [
                'phone_map' => [3 => '555-0000'],
                'msg_map'   => [3 => 'Facility greeting'],
            ],
            client: new FakeMaviqClient([new FakeRestResponse(false)]),
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $display = $tester->getDisplay();
        $this->assertStringContainsString('Total Records Found: 1', $display);
        // Success and error lines go to the per-day cron log, not stdout.
        $this->assertCount(1, $command->logEntries);
        $this->assertStringContainsString('Phone reminder sent successfully', $command->logEntries[0]);
        $this->assertStringContainsString('Alice', $command->logEntries[0]);

        $this->assertCount(1, $command->markedSent);
        $this->assertSame(
            ['type' => 'Phone', 'pid' => '42', 'pc_eid' => '100'],
            $command->markedSent[0],
        );
        $this->assertCount(1, $command->insertedLogs);
        $this->assertSame('Facility greeting', $command->insertedLogs[0]['message']);
        $this->assertSame('https://example.test', $command->insertedLogs[0]['gateway']);
    }

    public function testFacilityGreetingFallsBackToDefault(): void
    {
        // Facility has no greeting configured so the command should fall back
        // to phone_appt_message['Default'].
        $patient = self::makePatient(facility: 99);
        $command = new PhoneNotificationCommandStub(
            patients: [$patient],
            facilitiesMap: ['phone_map' => [], 'msg_map' => []],
            globalsOverrides: ['phone_appt_message' => ['Default' => 'Default hello']],
            client: new FakeMaviqClient([new FakeRestResponse(false)]),
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame('Default hello', $command->insertedLogs[0]['message']);
    }

    public function testGatewayErrorReturnsFailureAndSkipsSideEffects(): void
    {
        $patient = self::makePatient();
        $command = new PhoneNotificationCommandStub(
            patients: [$patient],
            client: new FakeMaviqClient([new FakeRestResponse(true, 'gateway boom')]),
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('1 phone reminder(s) failed', $tester->getDisplay());
        // Error detail is captured in the per-day cron log, not on stdout.
        $this->assertCount(1, $command->logEntries);
        $this->assertStringContainsString('Error starting phone call', $command->logEntries[0]);
        $this->assertStringContainsString('gateway boom', $command->logEntries[0]);
        // Failed calls must not advance cron state or insert an audit row.
        $this->assertSame([], $command->markedSent);
        $this->assertSame([], $command->insertedLogs);
    }

    public function testMixedOutcomesReportTotalErrorsAndOnlyAdvanceSuccesses(): void
    {
        $ok = self::makePatient(pid: '1', pcEid: '10', firstName: 'Ok');
        $bad = self::makePatient(pid: '2', pcEid: '20', firstName: 'Bad');
        $command = new PhoneNotificationCommandStub(
            patients: [$ok, $bad],
            client: new FakeMaviqClient([
                new FakeRestResponse(false),
                new FakeRestResponse(true, 'nope'),
            ]),
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('1 phone reminder(s) failed', $tester->getDisplay());
        // Only the successful reminder advanced cron state.
        $this->assertCount(1, $command->markedSent);
        $this->assertSame('1', $command->markedSent[0]['pid']);
    }

    public function testTriggerHoursFallsBackToDefaultWhenGlobalIsZero(): void
    {
        $command = new PhoneNotificationCommandStub(
            patients: [],
            globalsOverrides: ['phone_notification_hour' => 0],
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame(72, $command->lastTriggerHours);
    }

    public function testTriggerHoursUsesGlobalWhenPositive(): void
    {
        $command = new PhoneNotificationCommandStub(
            patients: [],
            globalsOverrides: ['phone_notification_hour' => 24],
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(24, $command->lastTriggerHours);
    }

    public function testNonScalarRowFieldsAndMalformedDateFallBackSafely(): void
    {
        // Force the defensive fallbacks in formatApptDate, defaultMessage,
        // stringField, and intField to execute in one end-to-end run.
        $patient = self::makePatient();
        $patient['pc_eventDate'] = '';      // formatApptDate: fewer than 3 parts → ''
        $patient['pc_facility'] = 'not-n';  // intField: non-numeric → 0
        $patient['pid'] = null;             // stringField: non-string/int/float → ''

        $command = new PhoneNotificationCommandStub(
            patients: [$patient],
            facilitiesMap: ['phone_map' => [], 'msg_map' => []],
            // Array without a Default entry → defaultMessage returns ''
            globalsOverrides: ['phone_appt_message' => []],
            client: new FakeMaviqClient([new FakeRestResponse(false)]),
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        // defaultMessage fallback (''): logged greeting is empty.
        $this->assertSame('', $command->insertedLogs[0]['message']);
        // stringField fallback for pid: marked with empty pid.
        $this->assertSame('', $command->markedSent[0]['pid']);
    }

    public function testStringFieldCoercesNumericScalars(): void
    {
        // Covers stringField's is_int/is_float branch: numeric scalars in
        // row fields get cast to string rather than falling through to ''.
        $patient = self::makePatient();
        $patient['pid'] = 42;       // int → '42'
        $patient['pc_eid'] = 3.14;  // float → '3.14'

        $command = new PhoneNotificationCommandStub(
            patients: [$patient],
            client: new FakeMaviqClient([new FakeRestResponse(false)]),
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame('42', $command->markedSent[0]['pid']);
        $this->assertSame('3.14', $command->markedSent[0]['pc_eid']);
    }

    public function testLogDirProducesPerDayCronLogPath(): void
    {
        $logDir = sys_get_temp_dir() . '/phone-reminder-test-' . uniqid();
        $capture = new CapturingLogCommandStub(
            patients: [self::makePatient()],
            client: new FakeMaviqClient([new FakeRestResponse(false)]),
            globalsOverrides: ['phone_reminder_log_dir' => $logDir],
        );
        $tester = $this->createTester($capture);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $expectedPath = $logDir . '/phone_reminder_cronlog_' . date('Ymd') . '.html';
        $this->assertSame($expectedPath, $capture->logPath);
    }

    public function testFakeMaviqClientThrowsWhenOutOfResponses(): void
    {
        $client = new FakeMaviqClient([]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('FakeMaviqClient ran out of canned responses');

        $client->sendRequest('appointment', 'POST', []);
    }

    public function testDecryptedPasswordFlowsIntoMaviqClient(): void
    {
        $client = new FakeMaviqClient([]);
        $command = new PhoneNotificationCommandStub(
            patients: [],
            decryptedPassword: 'plaintext-token',
            client: $client,
        );
        $tester = $this->createTester($command);

        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertSame('plaintext-token', $client->token);
        $this->assertSame('user', $client->siteId);
        $this->assertSame('https://example.test', $client->endpoint);
    }
}

/**
 * Stub that replaces every external side effect with a fixture.
 */
class PhoneNotificationCommandStub extends PhoneNotificationCommand
{
    /** @var list<array{type: string, pid: string, pc_eid: string}> */
    public array $markedSent = [];

    /** @var list<array{row: array<string, mixed>, message: string, gateway: string}> */
    public array $insertedLogs = [];

    /** @var list<string> */
    public array $logEntries = [];

    public int $lastTriggerHours = 0;

    /**
     * @param list<array<string, mixed>> $patients
     * @param array{phone_map: array<int, string>, msg_map: array<int, string>} $facilitiesMap
     * @param array<string, mixed> $globalsOverrides
     */
    public function __construct(
        private readonly array $patients = [],
        private readonly array $facilitiesMap = ['phone_map' => [], 'msg_map' => []],
        private readonly string $decryptedPassword = 'decrypted',
        private readonly ?FakeMaviqClient $client = null,
        array $globalsOverrides = [],
    ) {
        parent::__construct();
        $this->setGlobalsBag(new OEGlobalsBag(array_replace([
            'phone_gateway_url' => 'https://example.test',
            'phone_gateway_username' => 'user',
            'phone_gateway_password' => 'encrypted-blob',
            'phone_notification_hour' => 72,
            'phone_time_range' => '9-17',
            'phone_appt_message' => ['Default' => 'default hello'],
            'phone_reminder_log_dir' => '',
        ], $globalsOverrides)));
    }

    protected function loadHelpers(): void
    {
        // Skip legacy helper loading in tests.
    }

    protected function decryptPassword(string $encrypted): string
    {
        return $this->decryptedPassword;
    }

    protected function fetchFacilitiesMap(): array
    {
        return $this->facilitiesMap;
    }

    protected function fetchPendingPatients(string $type, int $triggerHours): array
    {
        $this->lastTriggerHours = $triggerHours;
        return $this->patients;
    }

    protected function createMaviqClient(string $id, string $token, string $url): MaviqClient
    {
        $client = $this->client ?? new FakeMaviqClient([]);
        $client->siteId = $id;
        $client->token = $token;
        $client->endpoint = $url;
        return $client;
    }

    protected function markReminderSent(string $type, string $pid, string $pcEid): void
    {
        $this->markedSent[] = ['type' => $type, 'pid' => $pid, 'pc_eid' => $pcEid];
    }

    protected function insertLogEntry(array $row, string $phoneMsg, string $phoneGateway): void
    {
        $this->insertedLogs[] = ['row' => $row, 'message' => $phoneMsg, 'gateway' => $phoneGateway];
    }

    protected function writeLog(?string $path, string $data): void
    {
        $this->logEntries[] = $data;
    }
}

/**
 * Stub variant that captures the log path the command would write to without
 * actually touching the filesystem.
 */
class CapturingLogCommandStub extends PhoneNotificationCommandStub
{
    public ?string $logPath = null;

    protected function writeLog(?string $path, string $data): void
    {
        $this->logPath = $path;
        parent::writeLog($path, $data);
    }
}

/**
 * Stand-in for MaviqClient that returns canned RestResponse objects without
 * touching the network. Supports multiple calls via an indexed queue.
 */
class FakeMaviqClient extends MaviqClient
{
    public string $siteId = '';
    public string $token = '';
    public string $endpoint = '';
    public int $calls = 0;

    /**
     * @param list<RestResponse> $responses
     */
    public function __construct(private readonly array $responses)
    {
        // Intentionally skip parent constructor — tests configure properties
        // directly via PhoneNotificationCommandStub::createMaviqClient().
    }

    /**
     * @param mixed $path
     * @param mixed $method
     * @param mixed $vars
     */
    public function sendRequest($path, $method = 'POST', $vars = []): RestResponse
    {
        $index = $this->calls;
        $this->calls++;
        if (!isset($this->responses[$index])) {
            throw new \RuntimeException('FakeMaviqClient ran out of canned responses');
        }
        return $this->responses[$index];
    }
}

/**
 * RestResponse double whose constructor skips the real XML/curl parsing
 * and only sets the properties the command consumes.
 */
class FakeRestResponse extends RestResponse
{
    public function __construct(bool $isError, string $errorMessage = '')
    {
        // Deliberately skip parent constructor (which parses XML from curl
        // output). The command reads only IsError and ErrorMessage.
        $this->IsError = $isError;
        $this->ErrorMessage = $errorMessage;
    }
}
