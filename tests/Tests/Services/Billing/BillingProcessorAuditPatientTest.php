<?php

/**
 * Database test: Billing Manager claim processing audits each claim's
 * statements against that claim's patient, not the chart open in the session.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\BillingProcessor\BillingLogger;
use OpenEMR\Billing\BillingProcessor\BillingProcessor;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class BillingProcessorAuditPatientTest extends TestCase
{
    /** Synthetic ids, clear of the demo data. */
    private const OPEN_CHART_PID = 99990041;
    private const CLAIM_PID = 99990042;
    private const CLAIM_ENCOUNTER = 99990142;
    private const SECOND_PID = 99990043;
    private const SECOND_ENCOUNTER = 99990143;
    private const PAYER = 99990033;
    private const X12_PARTNER = 99990051;

    private const SESSION_KEYS = ['authUser', 'authUserID', 'pid'];

    /** @var array<string, mixed> */
    private array $savedGlobals = [];

    /** @var array<string, mixed> */
    private array $savedSession = [];

    protected function setUp(): void
    {
        $globals = OEGlobalsBag::getInstance();
        foreach (['enable_auditlog', 'audit_events_patient-record'] as $name) {
            $this->savedGlobals[$name] = $globals->get($name);
            $globals->set($name, '1');
        }
        self::resetAuditLogger();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (self::SESSION_KEYS as $key) {
            if ($session->has($key)) {
                $this->savedSession[$key] = $session->get($key);
            }
        }
        $session->set('authUser', 'admin');
        $session->set('authUserID', QueryUtils::fetchSingleValue('SELECT id FROM users WHERE username = ?', 'id', ['admin']));
        $session->set('pid', self::OPEN_CHART_PID);
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        foreach ($this->savedGlobals as $name => $value) {
            OEGlobalsBag::getInstance()->set($name, $value);
        }
        self::resetAuditLogger();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (self::SESSION_KEYS as $key) {
            if (array_key_exists($key, $this->savedSession)) {
                $session->set($key, $this->savedSession[$key]);
            } else {
                $session->remove($key);
            }
        }
    }

    public function testEachClaimIsAuditedAgainstItsOwnPatient(): void
    {
        $before = $this->lastLogId();

        // Billing Manager: "Mark as Cleared" on the other patient's claim.
        (new BillingProcessor([
            'bn_mark' => '1',
            'claims' => [self::CLAIM_PID . '-' . self::CLAIM_ENCOUNTER => ['bill' => '1', 'partner' => '-1', 'payer' => 'p0']],
        ]))->execute();

        $patients = QueryUtils::fetchTableColumn(
            "SELECT DISTINCT patient_id FROM log WHERE id > ? AND event LIKE 'patient-record-%'",
            'patient_id',
            [$before]
        );
        $this->assertNotEmpty($patients, 'Marking the claim cleared writes patient-record audit events');
        $this->assertSame([(string) self::CLAIM_PID], array_map(fn(mixed $id): string => is_scalar($id) ? (string) $id : '', $patients), "The claim's patient, not the open chart");

        // After the batch, statements fall back to the session's open chart again.
        $after = $this->lastLogId();
        QueryUtils::sqlStatementThrowException('UPDATE billing SET activity = activity WHERE pid = ?', [self::CLAIM_PID]);
        $patient = QueryUtils::fetchSingleValue(
            "SELECT patient_id FROM log WHERE id > ? AND event LIKE 'patient-record-%' ORDER BY id DESC LIMIT 1",
            'patient_id',
            [$after]
        );
        $this->assertSame((string) self::OPEN_CHART_PID, is_scalar($patient) ? (string) $patient : null);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidPatientIdProvider(): array
    {
        return [
            'not a number' => ['x' . self::CLAIM_PID],
            'beyond PHP_INT_MAX' => ['99999999999999999999'],
        ];
    }

    #[DataProvider('invalidPatientIdProvider')]
    public function testClaimWithoutAValidPatientIdIsSkipped(string $pid): void
    {
        $before = $this->lastLogId();

        $logger = (new BillingProcessor([
            'bn_mark' => '1',
            'claims' => [$pid . '-' . self::CLAIM_ENCOUNTER => ['bill' => '1', 'partner' => '-1', 'payer' => 'p0']],
        ]))->execute();

        $this->assertSame(
            [],
            QueryUtils::fetchTableColumn("SELECT patient_id FROM log WHERE id > ? AND event LIKE 'patient-record-%'", 'patient_id', [$before]),
            'No patient-record audit rows are logged for it'
        );
        $this->assertInstanceOf(BillingLogger::class, $logger);
        $messages = $logger->bill_info();
        $this->assertIsArray($messages);
        $this->assertContains('Skipped a claim with an invalid patient ID: ' . $pid, $messages);
    }

    public function testX12ClaimsReadOnlyTheirOwnPatientsRowsUnderTheirPatient(): void
    {
        // X12 Direct (one batch per partner), with SELECTs audited too.
        $this->setGlobal('gen_x12_based_on_ins_co', '1');
        $this->setGlobal('audit_events_query', '1');
        self::resetAuditLogger();
        $this->insertX12Claims();
        $before = $this->lastLogId();

        $claim = ['bill' => '1', 'partner' => (string) self::X12_PARTNER, 'payer' => 'p' . self::PAYER];
        (new BillingProcessor([
            'bn_x12' => '1',
            'btn-validate' => '1',
            'claims' => [
                self::CLAIM_PID . '-' . self::CLAIM_ENCOUNTER => $claim,
                self::SECOND_PID . '-' . self::SECOND_ENCOUNTER => $claim,
            ],
        ]))->execute();

        // The log keeps each statement, with its binds, base64-encoded in comments.
        $readsOf = function (int $auditedAs, int $readPid) use ($before): int {
            $statements = QueryUtils::fetchTableColumn(
                "SELECT comments FROM log WHERE id > ? AND event = 'patient-record-select' AND patient_id = ?",
                'comments',
                [$before, $auditedAs]
            );
            $reads = 0;
            foreach ($statements as $comments) {
                $statement = is_string($comments) ? base64_decode($comments, true) : false;
                if (is_string($statement) && str_contains($statement, 'FROM `patient_data` WHERE pid = ?') && str_contains($statement, "'" . $readPid . "'")) {
                    $reads++;
                }
            }
            return $reads;
        };
        $this->assertGreaterThan(0, $readsOf(self::CLAIM_PID, self::CLAIM_PID), "The first claim's own reads are audited against its patient");
        $this->assertSame(0, $readsOf(self::SECOND_PID, self::CLAIM_PID), "The first claim's patient isn't read while the second claim's patient is audited");
    }

    private function setGlobal(string $name, string $value): void
    {
        $globals = OEGlobalsBag::getInstance();
        if (!array_key_exists($name, $this->savedGlobals)) {
            $this->savedGlobals[$name] = $globals->get($name);
        }
        $globals->set($name, $value);
    }

    /**
     * Two patients, each with an encounter billed to the same payer through
     * the same X12 partner. The first patient isn't the subscriber, so the
     * second claim's hierarchy depends on the first claim's relationship.
     */
    private function insertX12Claims(): void
    {
        $facilityId = QueryUtils::fetchSingleValue('SELECT id FROM facility ORDER BY id LIMIT 1', 'id');
        $providerId = QueryUtils::fetchSingleValue('SELECT id FROM users ORDER BY id LIMIT 1', 'id');
        QueryUtils::sqlStatementThrowException('INSERT INTO x12_partners (id, name) VALUES (?, ?)', [self::X12_PARTNER, 'TEST X12 PARTNER']);
        QueryUtils::sqlStatementThrowException('INSERT INTO insurance_companies (id, name, cms_id, ins_type_code) VALUES (?, ?, ?, 1)', [self::PAYER, 'TEST X12 PAYER', 'TX001']);
        foreach ([[self::CLAIM_PID, self::CLAIM_ENCOUNTER, 'spouse'], [self::SECOND_PID, self::SECOND_ENCOUNTER, 'self']] as [$pid, $encounter, $relationship]) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, 'JANE', 'AUDITTEST', '1950-01-01', 'Female', NOW())",
                [$pid, (string) $pid]
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO insurance_data (pid, type, provider, policy_number, date, subscriber_relationship) VALUES (?, 'primary', ?, 'POL123', '2020-01-01', ?)",
                [$pid, self::PAYER, $relationship]
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO form_encounter (pid, encounter, date, reason, facility_id, provider_id) VALUES (?, ?, '2026-09-01', 'test', ?, ?)",
                [$pid, $encounter, $facilityId, $providerId]
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO billing (date, code_type, code, pid, encounter, provider_id, user, groupname, authorized, " .
                "activity, billed, units, fee, code_text, payer_id, x12_partner_id) " .
                "VALUES (NOW(), 'CPT4', '99213', ?, ?, ?, 1, 'Default', 1, 1, 0, 1, 100.00, 'Office visit', ?, ?)",
                [$pid, $encounter, $providerId, self::PAYER, self::X12_PARTNER]
            );
        }
    }

    /**
     * The logger reads the audit settings when it is created, so drop the
     * cached one whenever the test changes them.
     */
    private static function resetAuditLogger(): void
    {
        (new ReflectionProperty(EventAuditLogger::class, 'instances'))->setValue(null, []);
    }

    private function lastLogId(): int
    {
        $id = QueryUtils::fetchSingleValue('SELECT COALESCE(MAX(id), 0) AS id FROM log', 'id');
        $this->assertIsNumeric($id);
        return (int) $id;
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM claims WHERE patient_id = ? AND encounter_id = ?',
            [self::CLAIM_PID, self::CLAIM_ENCOUNTER]
        );
        foreach (['billing', 'form_encounter', 'insurance_data', 'patient_data'] as $table) {
            QueryUtils::sqlStatementThrowException("DELETE FROM $table WHERE pid IN (?, ?)", [self::CLAIM_PID, self::SECOND_PID]);
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_companies WHERE id = ?', [self::PAYER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM x12_partners WHERE id = ?', [self::X12_PARTNER]);
    }
}
