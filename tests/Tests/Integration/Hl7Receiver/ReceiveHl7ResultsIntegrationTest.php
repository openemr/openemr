<?php

/**
 * Native OpenEMR HL7 receiver regression tests.
 *
 * Run with OpenEMR's phpunit.xml and its full tests/bootstrap.php, using a
 * writable native session prepared before PHPUnit emits output. These tests
 * deliberately use the application's globals, database, session and audit
 * logger. No replacement functions, miniature schema or outer transaction
 * hide the receiver's actual commit/rollback behaviour.
 *
 * Only the disposable hl7_fixture database on port 13387 is accepted.
 * All message content and patient identifiers are synthetic.
 *
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\ProcedureOrder;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Session\Storage\ReadAndCloseNativeSessionStorage;
use OpenEMR\Common\Session\WriteThroughSession;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;

final class ReceiveHl7ResultsIntegrationTest extends TestCase
{
    private const DATABASE = 'hl7_fixture';
    private const PORT = 13387;
    private const LAB_ID = 7;
    private const FACILITY_ID = 11545596;
    private const ACTOR = 'HL7-FIXTURE-native-test';
    private const GROUP = 'HL7-FIXTURE-group';
    private const MARKER = 'HL7-FIXTURE-native';
    private const BASE_CODE = 'HL7-FIXTURE-BASE';
    private const REFLEX_CODE = 'HL7-FIXTURE-REFLEX';
    private const OBSERVATION_CODE = 'HL7-FIXTURE-OBS';
    private const FAILURE_CODE = 'HL7-FIXTURE-FAIL';
    private const TRIGGER = 'hl7_fixture_result_failure';
    private const DATE = '2026-09-30 10:00:00';
    private const HL7_DATE = '20260930100000';

    /** order ID => [patient ID, encounter ID] */
    private const ORDERS = [
        175 => [501, 9001],
        42 => [502, 9002],
        11545596 => [503, 9003],
    ];

    /** @var array<string, array{bool, mixed}> */
    private static array $savedSession = [];
    private static int $providerId = 0;
    private bool $ownsFixtures = false;
    private bool $ownsTrigger = false;

    public static function setUpBeforeClass(): void
    {
        self::assertTrue(function_exists('sqlQuery'), 'OpenEMR full bootstrap was not loaded');
        self::assertSame(self::DATABASE, self::scalar('SELECT DATABASE() AS value'));
        self::assertSame(self::PORT, self::integer(self::scalar('SELECT @@port AS value')));
        self::assertSame('127.0.0.1', self::options()->host);

        $sessionFactory = SessionWrapperFactory::getInstance();
        $session = $sessionFactory->getActiveSession();
        self::assertInstanceOf(WriteThroughSession::class, $session);
        self::assertInstanceOf(ReadAndCloseNativeSessionStorage::class, $sessionFactory->getActiveStorage());
        self::assertSame(
            PHP_SESSION_ACTIVE,
            session_status(),
            'The bootstrap wrapper must set $sessionAllowWrite = true before loading tests/bootstrap.php'
        );

        $provider = self::rows('SELECT id FROM users WHERE authorized = 1 ORDER BY id LIMIT 1');
        self::assertCount(1, $provider, 'The full installer must have created its synthetic initial administrator');
        self::$providerId = self::integer($provider[0]['id']);
        foreach (['authUser', 'authProvider', 'authUserID'] as $key) {
            self::$savedSession[$key] = [$session->has($key), $session->get($key)];
        }
        $session->set('authUser', self::ACTOR);
        $session->set('authProvider', self::GROUP);
        $session->set('authUserID', self::$providerId);

        EventAuditLogger::getInstance();
        self::assertTrue(OEGlobalsBag::getInstance()->getBoolean('enable_auditlog'));
        self::assertFalse(OEGlobalsBag::getInstance()->getBoolean('enable_atna_audit'));

        $projectDir = OEGlobalsBag::getInstance()->getProjectDir();
        require_once $projectDir . '/interface/orders/receive_hl7_results.inc.php';
        self::assertTrue(function_exists('receive_hl7_results'));

        // A full fresh install has no patients or orders. A previous run may
        // have left our precisely marked fixtures, but no clinical data is OK.
        self::assertSame(0, self::integer(self::scalar(
            'SELECT COUNT(*) AS value FROM patient_data WHERE pid NOT IN (501, 502, 503)'
        )));
        self::assertSame(0, self::integer(self::scalar(
            'SELECT COUNT(*) AS value FROM procedure_order WHERE procedure_order_id NOT IN (175, 42, 11545596)'
        )));
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$savedSession === []) {
            return;
        }
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        foreach (self::$savedSession as $key => [$existed, $value]) {
            if ($existed) {
                $session->set($key, $value);
            } else {
                $session->remove($key);
            }
        }
        self::$savedSession = [];
    }

    protected function setUp(): void
    {
        self::assertSame(self::DATABASE, self::scalar('SELECT DATABASE() AS value'));
        self::assertSame(self::PORT, self::integer(self::scalar('SELECT @@port AS value')));
        self::assertSame(0, self::integer(self::scalar('SELECT @@in_transaction AS value')));
        $this->validateFixtureOwnership();
        $this->ownsFixtures = true;
        $this->cleanupFixtures();
        self::assertSame(0, self::integer(self::scalar(
            'SELECT COUNT(*) AS value FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME = ?',
            [self::TRIGGER]
        )), 'Refuse to replace an existing database trigger');

        self::execute(
            'INSERT INTO facility (id, name) VALUES (?, ?)',
            [self::FACILITY_ID, self::MARKER . '-facility']
        );
        self::execute(
            'INSERT INTO procedure_providers (ppid, name, npi, send_fac_id, direction) VALUES (?, ?, ?, ?, ?)',
            [self::LAB_ID, self::MARKER . '-lab', '', (string) self::FACILITY_ID, 'B']
        );
        foreach (self::ORDERS as $orderId => [$patientId, $encounterId]) {
            self::execute(
                'INSERT INTO patient_data (pid, fname, lname, DOB, sex, pubpid) VALUES (?, ?, ?, ?, ?, ?)',
                [$patientId, self::firstName($patientId), self::lastName($patientId), '2000-01-01', 'Unassigned', self::MARKER . '-patient-' . $patientId]
            );
            self::execute(
                'INSERT INTO form_encounter (pid, encounter, date, reason, facility, facility_id, provider_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$patientId, $encounterId, self::DATE, self::MARKER . '-encounter', self::MARKER . '-facility', self::FACILITY_ID, self::$providerId]
            );
            self::execute(
                'INSERT INTO procedure_order (procedure_order_id, patient_id, encounter_id, provider_id, lab_id, date_ordered, clinical_hx, external_id, control_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$orderId, $patientId, $encounterId, self::$providerId, self::LAB_ID, self::DATE, self::MARKER . '-order', 'HL7-FIXTURE-' . $orderId, '']
            );
            self::execute(
                'INSERT INTO procedure_order_code (procedure_order_id, procedure_order_seq, procedure_code, procedure_name, procedure_source) VALUES (?, 1, ?, ?, ?)',
                [$orderId, self::BASE_CODE, self::MARKER . '-base-test', '1']
            );
        }
        OEGlobalsBag::getInstance()->set('lab_npi', '');
        OEGlobalsBag::getInstance()->set('orphanLog', self::MARKER);
    }

    protected function tearDown(): void
    {
        if (!$this->ownsFixtures) {
            return;
        }
        if ($this->ownsTrigger) {
            self::execute('DROP TRIGGER `' . self::TRIGGER . '`');
            $this->ownsTrigger = false;
        }
        $this->cleanupFixtures();
    }

    public function testCompoundPlacerIdentifierTargetsOrder175AndPatient501(): void
    {
        $return = $this->receive($this->message([
            $this->group('11545596-0175', self::BASE_CODE),
        ]));
        $this->assertSuccess($return);
        $this->assertOneResultFor(175, 501);
        self::assertSame('HL7-FIXTURE-FILLER-1', $this->controlId(175));
        self::assertSame('', $this->controlId(11545596), 'Numeric facility prefix must never select another patient\'s order');
    }

    public function testEiNamespaceDashDoesNotChangeEntityIdentifier(): void
    {
        $return = $this->receive($this->message([
            $this->group('0175^OTHER-0042', self::BASE_CODE),
        ]));
        $this->assertSuccess($return);
        $this->assertOneResultFor(175, 501);
        self::assertSame('', $this->controlId(42), 'EI authority components must not choose order 42');
    }

    public function testConflictingOrcAndObrIdentifiersProduceNoResultsOrControlUpdates(): void
    {
        $group = $this->group('175', self::BASE_CODE);
        $group['orc'] = '42';
        $return = $this->receive($this->message([$group]));
        $this->assertFatal($return);
        $this->assertNoReportsOrResults();
        $this->assertEmptyControlIds();
        $this->assertOnlyOriginalOrderCodes();
    }

    public function testDryRunWithEmptyControlIdDoesNotUpdateDatabase(): void
    {
        $before = $this->clinicalSnapshot();
        $return = $this->receive($this->message([
            $this->group('175', self::REFLEX_CODE),
        ]), dryRun: true);
        $this->assertSuccess($return);
        self::assertSame($before, $this->clinicalSnapshot(), 'Dry run changed clinical tables');
        $this->assertEmptyControlIds();
    }

    public function testDirectDryRunReadExceptionReturnsFatalWithoutOutputOrClinicalWrites(): void
    {
        $this->expectOutputString('');
        $before = $this->clinicalSnapshot();
        self::execute('RENAME TABLE categories TO hl7_fixture_unavailable_categories');
        try {
            $return = $this->receive($this->message([$this->group('175', self::BASE_CODE)]), dryRun: true);
            $this->assertFatal($return);
            self::assertSame($before, $this->clinicalSnapshot());
        } finally {
            self::execute('RENAME TABLE hl7_fixture_unavailable_categories TO categories');
        }
    }

    public function testLateInvalidIdentifierRollsBackEarlierControlReportAndResult(): void
    {
        // The second MSH forces the legacy receiver to flush the first
        // report before reaching the invalid later order identifier.
        $message = $this->message([$this->group('175', self::BASE_CODE)])
            . $this->message([$this->group('NOT-AN-ORDER', self::BASE_CODE)], 2);
        $return = $this->receive($message);
        $this->assertFatal($return);
        $this->assertEmptyControlIds();
        $this->assertNoReportsOrResults();
        $this->assertOnlyOriginalOrderCodes();
    }

    public function testPendingReflexCodeIsNotPersistedWhenLaterIdentifierIsInvalid(): void
    {
        $before = $this->clinicalSnapshot();
        $return = $this->receive($this->message([
            $this->group('175', self::REFLEX_CODE),
            $this->group('NOT-AN-ORDER', self::BASE_CODE),
        ]));
        $this->assertFatal($return);
        self::assertSame($before, $this->clinicalSnapshot(), 'Fatal preflight persisted a pending reflex code or control ID');
        $this->assertOnlyOriginalOrderCodes();
    }

    public function testSuccessfulReflexCodeIsPersistedExactlyOnceAcrossRepeatedReports(): void
    {
        $message = $this->message([$this->group('175', self::REFLEX_CODE)]);
        $this->assertSuccess($this->receive($message));
        $this->assertSuccess($this->receive($message));
        $codes = self::rows(
            'SELECT procedure_order_seq, procedure_code, procedure_source FROM procedure_order_code WHERE procedure_order_id = ? ORDER BY procedure_order_seq',
            [175]
        );
        self::assertCount(2, $codes);
        self::assertSame(self::REFLEX_CODE, $codes[1]['procedure_code']);
        self::assertSame('2', self::text($codes[1]['procedure_source']));
        self::assertSame(2, self::integer($codes[1]['procedure_order_seq']));
        $results = $this->resultRows();
        self::assertCount(2, $results);
        foreach ($results as $result) {
            self::assertSame(175, self::integer($result['procedure_order_id']));
            self::assertSame(501, self::integer($result['patient_id']));
            self::assertSame(2, self::integer($result['procedure_order_seq']));
            self::assertSame(self::OBSERVATION_CODE, $result['result_code']);
            self::assertSame('7.2', $result['result']);
        }
    }

    public function testInjectedResultInsertExceptionReturnsFatalAndRollsBackAllClinicalWrites(): void
    {
        $this->installFailureTrigger();
        $first = $this->group('175', self::BASE_CODE);
        $second = $this->group('42', self::REFLEX_CODE);
        $second['result_code'] = self::FAILURE_CODE;
        $return = $this->receive($this->message([$first, $second]));
        $this->assertFatal($return);
        $this->assertEmptyControlIds();
        $this->assertNoReportsOrResults();
        $this->assertOnlyOriginalOrderCodes();
        self::assertSame(0, self::integer(self::scalar('SELECT @@in_transaction AS value')), 'Receiver left its transaction open');
    }

    public function testFailureAuditEventSurvivesClinicalTransactionRollback(): void
    {
        $this->installFailureTrigger();
        $group = $this->group('175', self::REFLEX_CODE);
        $group['result_code'] = self::FAILURE_CODE;
        $return = $this->receive($this->message([$group]));
        $this->assertFatal($return);
        $this->assertNoReportsOrResults();
        $this->assertEmptyControlIds();
        $this->assertOnlyOriginalOrderCodes();
        $audit = self::rows(
            'SELECT l.event, l.user, l.success, l.comments, e.log_id, e.version FROM log l JOIN log_comment_encrypt e ON e.log_id = l.id WHERE l.user = ? AND l.event = ? ORDER BY l.id',
            [self::ACTOR, 'lab-results-error']
        );
        self::assertCount(1, $audit, 'Fatal result must be audited once on the independent production audit connection');
        self::assertSame(0, self::integer($audit[0]['success']));
        self::assertSame(self::ACTOR, $audit[0]['user']);
        self::assertGreaterThan(0, self::integer($audit[0]['log_id']));
        self::assertSame('4', self::text($audit[0]['version']));
        self::assertNotSame('', self::text($audit[0]['comments']));
    }

    public function testActualFatalAfterSuccessfulPreflightRollsBackReflexAndControlWrites(): void
    {
        // Simulate a relevant order change after preflight by changing order 42
        // during the actual insertion of a previously absent reflex code.
        self::execute(
            'CREATE TRIGGER `' . self::TRIGGER . '` AFTER INSERT ON procedure_order_code FOR EACH ROW '
            . "BEGIN IF NEW.procedure_code = '" . self::REFLEX_CODE . "' THEN "
            . 'UPDATE procedure_order SET lab_id = 8 WHERE procedure_order_id = 42; END IF; END'
        );
        $this->ownsTrigger = true;
        $return = $this->receive($this->message([
            $this->group('175', self::REFLEX_CODE),
            $this->group('42', self::BASE_CODE),
        ]));
        $this->assertFatal($return);
        self::assertStringContainsString('different lab', implode(' ', self::messages($return)));
        $this->assertEmptyControlIds();
        $this->assertNoReportsOrResults();
        $this->assertOnlyOriginalOrderCodes();
        self::assertSame(7, self::integer(self::scalar('SELECT lab_id AS value FROM procedure_order WHERE procedure_order_id=42')));
        self::assertSame(0, self::integer(self::scalar('SELECT @@in_transaction AS value')));
    }

    public function testReflexCatalogReadExceptionIsCaughtAndEarlierUpdatesAreRolledBack(): void
    {
        $this->expectOutputString('');
        // Dry-run can anticipate a new reflex code without reading the catalog.
        // The actual catalog lookup must throw, not exit through HelpfulDie.
        self::execute('RENAME TABLE procedure_type TO hl7_fixture_unavailable_catalog');
        try {
            $return = $this->receive($this->message([$this->group('175', self::REFLEX_CODE)]));
            $this->assertFatal($return);
            $this->assertEmptyControlIds();
            $this->assertNoReportsOrResults();
            $this->assertOnlyOriginalOrderCodes();
            self::assertSame(0, self::integer(self::scalar('SELECT @@in_transaction AS value')));
        } finally {
            self::execute('RENAME TABLE hl7_fixture_unavailable_catalog TO procedure_type');
        }
    }

    public function testResultsOnlyDirectionReusesExistingOrderWithoutChangingItsControlId(): void
    {
        self::execute('UPDATE procedure_order SET control_id = ? WHERE procedure_order_id = ?', ['175', 175]);
        $return = $this->receive($this->message([
            $this->group('175', self::BASE_CODE),
        ]), direction: 'R');
        $this->assertSuccess($return);
        $this->assertOneResultFor(175, 501);
        self::assertSame('175', $this->controlId(175));
        self::assertSame(3, self::integer(self::scalar('SELECT COUNT(*) AS value FROM procedure_order')));
        self::assertSame(3, self::integer(self::scalar('SELECT COUNT(*) AS value FROM patient_data')));
        self::assertSame(3, self::integer(self::scalar('SELECT COUNT(*) AS value FROM form_encounter')));
        self::assertSame(0, self::integer(self::scalar('SELECT COUNT(*) AS value FROM pnotes WHERE pid IN (501, 502, 503)')));
    }

    public function testRealConnectionLossAtCommitReturnsFatalAndRollsBackClinicalWrites(): void
    {
        $connection = self::connection();
        $mainConnectionId = self::integer(self::scalar('SELECT CONNECTION_ID() AS value'));
        self::assertGreaterThan(0, $mainConnectionId);
        $auditLogger = EventAuditLogger::getInstance();

        // These credentials belong only to the disposable, guarded local
        // database server. KILL targets this test's exact main connection.
        $control = new \PDO(
            'mysql:host=127.0.0.1;port=' . self::PORT . ';dbname=' . self::DATABASE . ';charset=utf8mb4',
            'root',
            '',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION, \PDO::ATTR_EMULATE_PREPARES => false]
        );
        $statement = $control->query('SELECT DATABASE() AS db, @@port AS port, CONNECTION_ID() AS id');
        self::assertNotFalse($statement);
        $identity = $statement->fetch(\PDO::FETCH_ASSOC);
        self::assertIsArray($identity);
        self::assertSame(self::DATABASE, $identity['db']);
        self::assertSame(self::PORT, self::integer($identity['port']));
        self::assertNotSame($mainConnectionId, self::integer($identity['id']));

        $previousExecuteHook = $connection->fnExecute;
        $commitReached = false;
        $connectionKilled = false;
        $pendingWrites = [];
        $connection->fnExecute = function ($actualConnection, $sql, $parameters) use (
            $connection,
            $control,
            $mainConnectionId,
            $previousExecuteHook,
            &$commitReached,
            &$connectionKilled,
            &$pendingWrites
        ): mixed {
            if (!$connectionKilled && is_string($sql) && strtoupper(trim($sql)) === 'COMMIT') {
                $commitReached = true;
                $connection->fnExecute = $previousExecuteHook;
                // Inspect genuine, uncommitted rows on the same connection.
                // These ordinary queries still delegate to the real driver.
                $pendingWrites = [
                    'control' => $this->controlId(175),
                    'codes' => self::integer(self::scalar('SELECT COUNT(*) AS value FROM procedure_order_code WHERE procedure_order_id = 175')),
                    'reports' => self::integer(self::scalar('SELECT COUNT(*) AS value FROM procedure_report WHERE procedure_order_id = 175')),
                    'results' => count($this->resultRows()),
                ];
                $control->exec('KILL CONNECTION ' . $mainConnectionId);
                $connectionKilled = true;
                $connection->fnExecute = $previousExecuteHook;
                // null tells ADOdb to execute the real COMMIT. It fails
                // against the server-killed mysqli connection, not a stub.
                return null;
            }
            return is_callable($previousExecuteHook)
                ? $previousExecuteHook($actualConnection, $sql, $parameters)
                : null;
        };

        try {
            $return = $this->receive($this->message([$this->group('175', self::REFLEX_CODE)]));
        } finally {
            $connection->fnExecute = $previousExecuteHook;
            if ($connectionKilled) {
                $this->restoreRealMainSqlConnection($connection, $previousExecuteHook);
            }
        }

        self::assertTrue($commitReached, 'Receiver never attempted the real COMMIT');
        self::assertTrue($connectionKilled, 'Control connection did not kill the main server connection');
        self::assertSame(
            ['control' => 'HL7-FIXTURE-FILLER-1', 'codes' => 2, 'reports' => 1, 'results' => 1],
            $pendingWrites,
            'The failure must occur after control, reflex, report and result writes, at COMMIT'
        );
        $this->assertFatal($return);
        self::assertNotSame($mainConnectionId, self::integer(self::scalar('SELECT CONNECTION_ID() AS value')));
        self::assertSame(0, self::integer(self::scalar('SELECT @@in_transaction AS value')));
        $this->assertEmptyControlIds();
        $this->assertNoReportsOrResults();
        $this->assertOnlyOriginalOrderCodes();
        self::assertSame($auditLogger, EventAuditLogger::getInstance(), 'Main reconnect must preserve the independent audit logger');
        $audit = self::rows(
            'SELECT l.success, e.log_id FROM log l JOIN log_comment_encrypt e ON e.log_id = l.id WHERE l.user = ? AND l.event = ?',
            [self::ACTOR, 'lab-results-error']
        );
        self::assertCount(1, $audit, 'Commit failure must survive in the independent production audit connection');
        self::assertSame(0, self::integer($audit[0]['success']));
        self::assertGreaterThan(0, self::integer($audit[0]['log_id']));
    }

    /** Reconnect using the real production factory, restoring both legacy and bag references. */
    private function restoreRealMainSqlConnection(\ADODB_mysqli_log $previousConnection, bool|callable $previousExecuteHook): void
    {
        $bag = OEGlobalsBag::getInstance();
        $config = \OpenEMR\BC\DatabaseConnectionOptions::forSite($bag->getString('OE_SITE_DIR'));
        self::assertSame(self::DATABASE, $config->dbname);
        self::assertSame('127.0.0.1', $config->host);
        self::assertSame(self::PORT, $config->port);
        $previousConnection->Close();
        self::assertTrue($previousConnection->Connect(
            $config->host . ':' . $config->port, $config->user, $config->password, $config->dbname
        ));
        $previousConnection->SetFetchMode(ADODB_FETCH_ASSOC);
        $previousConnection->fnExecute = $previousExecuteHook;
        $bag->set('dbh', $previousConnection->_connectionID);
        $bag->set('last_mysql_error', '');
        $bag->set('last_mysql_error_no', 0);
    }

    public function testActiveCallerTransactionIsRejectedAndRemainsRollbackable(): void
    {
        $connection = self::connection();
        $marker = self::MARKER . '-caller-control';
        self::assertTrue($connection->BeginTrans());
        try {
            self::execute('UPDATE procedure_order SET control_id = ? WHERE procedure_order_id = ?', [$marker, 175]);
            $transactionCount = $connection->transCnt;
            $transactionOffset = $connection->transOff;
            self::assertGreaterThan(0, $transactionCount);
            $return = $this->receive($this->message([$this->group('175', self::BASE_CODE)]));
            $this->assertFatal($return);
            self::assertSame($transactionCount, $connection->transCnt, 'Receiver changed the caller\'s transaction nesting');
            self::assertSame($transactionOffset, $connection->transOff);
            self::assertSame(1, self::integer(self::scalar('SELECT @@in_transaction AS value')));
            self::assertSame($marker, $this->controlId(175));
            $this->assertNoReportsOrResults();
            $this->assertOnlyOriginalOrderCodes();
        } finally {
            $connection->RollbackTrans();
        }
        self::assertSame('', $this->controlId(175), 'Caller work was committed instead of remaining rollbackable');
        self::assertSame(0, self::integer(self::scalar('SELECT @@in_transaction AS value')));
    }

    /** The sole entry point used by every case is the complete production receiver. */
    /** @return array<mixed> */
    private function receive(string $message, bool $dryRun = false, string $direction = 'B'): array
    {
        $matchRequests = [];
        $return = \receive_hl7_results($message, $matchRequests, self::LAB_ID, $direction, $dryRun);
        self::assertFalse((bool) ($return['needmatch'] ?? false), 'Synthetic patient unexpectedly needed manual matching');
        self::assertSame([], $matchRequests);
        return $return;
    }

    /** @return array{orc: string, order: string, code: string, result_code: string} */
    private function group(string $order, string $code): array
    {
        return ['order' => $order, 'orc' => $order, 'code' => $code, 'result_code' => self::OBSERVATION_CODE];
    }

    /** @param list<array{orc: string, order: string, code: string, result_code: string}> $groups */
    private function message(array $groups, int $messageNumber = 1): string
    {
        $segments = [
            'MSH|^~\\&|HL7-FIXTURE-LAB|11545596|HL7-FIXTURE-EMR|HL7-FIXTURE-SITE|' . self::HL7_DATE . '||ORU^R01|HL7-FIXTURE-MSG-' . $messageNumber . '|T|2.3',
            self::segment('PID', [1 => '1', 3 => self::MARKER . '-patient-501', 5 => self::lastName(501) . '^' . self::firstName(501), 7 => '20000101', 8 => 'U'], 13),
        ];
        foreach ($groups as $index => $group) {
            $sequence = $index + 1;
            $segments[] = self::segment('ORC', [1 => 'RE', 2 => $group['orc']], 2);
            $segments[] = self::segment('OBR', [
                1 => (string) $sequence,
                2 => $group['order'],
                3 => 'HL7-FIXTURE-FILLER-' . $sequence,
                4 => $group['code'] . '^HL7-FIXTURE-Test',
                7 => self::HL7_DATE,
                22 => self::HL7_DATE,
                25 => 'F',
            ], 29);
            $segments[] = self::segment('OBX', [
                1 => '1', 2 => 'NM', 3 => $group['result_code'] . '^HL7-FIXTURE-Observation',
                5 => '7.2', 6 => 'mmol/L', 7 => '4-8', 8 => 'N', 11 => 'F', 14 => self::HL7_DATE,
            ], 25);
        }
        return implode("\r", $segments) . "\r";
    }

    /** @param array<int, string> $values */
    private static function segment(string $type, array $values, int $lastField): string
    {
        $fields = array_fill(0, $lastField + 1, '');
        $fields[0] = $type;
        foreach ($values as $field => $value) {
            $fields[$field] = $value;
        }
        return implode('|', $fields);
    }

    private function installFailureTrigger(): void
    {
        self::execute(
            'CREATE TRIGGER `' . self::TRIGGER . '` BEFORE INSERT ON procedure_result FOR EACH ROW '
            . "BEGIN IF NEW.result_code = '" . self::FAILURE_CODE . "' THEN SIGNAL SQLSTATE '45000' "
            . "SET MESSAGE_TEXT = '" . self::MARKER . "-forced-insert-failure'; END IF; END"
        );
        $this->ownsTrigger = true;
    }

    /** @param array<mixed> $return */
    private function assertSuccess(array $return): void
    {
        self::assertFalse((bool) ($return['fatal'] ?? false), implode(' ', self::messages($return)));
    }

    /** @param array<mixed> $return */
    private function assertFatal(array $return): void
    {
        self::assertTrue((bool) ($return['fatal'] ?? false), 'Receiver accepted a message that must be rejected');
        self::assertNotEmpty($return['mssgs'] ?? []);
    }

    private function assertOneResultFor(int $orderId, int $patientId): void
    {
        $rows = $this->resultRows();
        self::assertCount(1, $rows);
        self::assertSame($orderId, self::integer($rows[0]['procedure_order_id']));
        self::assertSame($patientId, self::integer($rows[0]['patient_id']));
        self::assertSame(self::OBSERVATION_CODE, $rows[0]['result_code']);
        self::assertSame('7.2', $rows[0]['result']);
        self::assertSame('mmol/L', $rows[0]['units']);
        self::assertSame('final', $rows[0]['result_status']);
    }

    /** @return list<array<mixed>> */
    private function resultRows(): array
    {
        return self::rows(
            'SELECT p.procedure_order_id, p.patient_id, r.procedure_order_seq, v.result_code, v.result, v.units, v.result_status FROM procedure_result v JOIN procedure_report r ON r.procedure_report_id = v.procedure_report_id JOIN procedure_order p ON p.procedure_order_id = r.procedure_order_id WHERE p.procedure_order_id IN (175, 42, 11545596) ORDER BY v.procedure_result_id'
        );
    }

    private function assertNoReportsOrResults(): void
    {
        self::assertSame(0, self::integer(self::scalar('SELECT COUNT(*) AS value FROM procedure_report WHERE procedure_order_id IN (175, 42, 11545596)')));
        self::assertSame(0, self::integer(self::scalar(
            'SELECT COUNT(*) AS value FROM procedure_result WHERE result_code IN (?, ?)',
            [self::OBSERVATION_CODE, self::FAILURE_CODE]
        )));
    }

    private function assertOnlyOriginalOrderCodes(): void
    {
        $rows = self::rows('SELECT procedure_order_id, procedure_order_seq, procedure_code, procedure_source FROM procedure_order_code WHERE procedure_order_id IN (175, 42, 11545596) ORDER BY procedure_order_id');
        self::assertCount(3, $rows);
        foreach ($rows as $row) {
            self::assertSame(1, self::integer($row['procedure_order_seq']));
            self::assertSame(self::BASE_CODE, $row['procedure_code']);
            self::assertSame('1', self::text($row['procedure_source']));
        }
    }

    private function assertEmptyControlIds(): void
    {
        foreach (array_keys(self::ORDERS) as $orderId) {
            self::assertSame('', $this->controlId($orderId), 'Control ID was persisted for order ' . $orderId);
        }
    }

    private function controlId(int $orderId): string
    {
        return self::text(self::scalar('SELECT control_id AS value FROM procedure_order WHERE procedure_order_id = ?', [$orderId]));
    }

    /** @return array<string, list<array<mixed>>> */
    private function clinicalSnapshot(): array
    {
        return [
            'orders' => self::rows('SELECT * FROM procedure_order WHERE procedure_order_id IN (175, 42, 11545596) ORDER BY procedure_order_id'),
            'codes' => self::rows('SELECT * FROM procedure_order_code WHERE procedure_order_id IN (175, 42, 11545596) ORDER BY procedure_order_id, procedure_order_seq'),
            'reports' => self::rows('SELECT * FROM procedure_report WHERE procedure_order_id IN (175, 42, 11545596) ORDER BY procedure_report_id'),
            'results' => $this->resultRows(),
        ];
    }

    private function validateFixtureOwnership(): void
    {
        foreach ([
            ['patient_data', 'pid', [501, 502, 503], 'pubpid'],
            ['procedure_order', 'procedure_order_id', [175, 42, 11545596], 'clinical_hx'],
            ['form_encounter', 'encounter', [9001, 9002, 9003], 'reason'],
            ['facility', 'id', [self::FACILITY_ID], 'name'],
            ['procedure_providers', 'ppid', [self::LAB_ID], 'name'],
        ] as [$table, $column, $ids, $markerColumn]) {
            $placeholders = implode(', ', array_fill(0, count($ids), '?'));
            $rows = self::rows('SELECT `' . $markerColumn . '` AS marker FROM `' . $table . '` WHERE `' . $column . '` IN (' . $placeholders . ')', $ids);
            foreach ($rows as $row) {
                self::assertStringStartsWith(self::MARKER, self::text($row['marker']), 'Refuse to delete a non-fixture row from ' . $table);
            }
        }
    }

    private function cleanupFixtures(): void
    {
        self::execute('DELETE FROM procedure_result WHERE procedure_report_id IN (SELECT procedure_report_id FROM procedure_report WHERE procedure_order_id IN (175, 42, 11545596))');
        self::execute('DELETE FROM procedure_report WHERE procedure_order_id IN (175, 42, 11545596)');
        self::execute('DELETE FROM procedure_order_code WHERE procedure_order_id IN (175, 42, 11545596)');
        self::execute('DELETE FROM procedure_order WHERE procedure_order_id IN (175, 42, 11545596)');
        self::execute('DELETE FROM form_encounter WHERE encounter IN (9001, 9002, 9003) AND reason = ?', [self::MARKER . '-encounter']);
        self::execute('DELETE FROM patient_data WHERE pid IN (501, 502, 503)');
        self::execute('DELETE FROM procedure_providers WHERE ppid = ? AND name = ?', [self::LAB_ID, self::MARKER . '-lab']);
        self::execute('DELETE FROM facility WHERE id = ? AND name = ?', [self::FACILITY_ID, self::MARKER . '-facility']);
        self::execute('DELETE FROM log_comment_encrypt WHERE log_id IN (SELECT id FROM log WHERE user = ?)', [self::ACTOR]);
        self::execute('DELETE FROM log WHERE user = ?', [self::ACTOR]);
    }

    private static function firstName(int $patientId): string
    {
        return 'HL7-FIXTURE-First-' . $patientId;
    }

    private static function lastName(int $patientId): string
    {
        return 'HL7-FIXTURE-Last-' . $patientId;
    }

    private static function options(): \OpenEMR\BC\DatabaseConnectionOptions
    {
        return \OpenEMR\BC\DatabaseConnectionOptions::forSite(OEGlobalsBag::getInstance()->getString('OE_SITE_DIR'));
    }

    private static function connection(): \ADODB_mysqli_log
    {
        $adodb = OEGlobalsBag::getInstance()->get('adodb');
        self::assertIsArray($adodb);
        $connection = $adodb['db'];
        self::assertInstanceOf(\ADODB_mysqli_log::class, $connection);
        return $connection;
    }

    private static function integer(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }
        throw new \UnexpectedValueException('Expected an integer database value');
    }

    private static function text(mixed $value): string
    {
        if (!is_string($value) && !is_int($value)) {
            throw new \UnexpectedValueException('Expected a text database value');
        }
        return (string) $value;
    }

    /** @param array<mixed> $return
     * @return list<string>
     */
    private static function messages(array $return): array
    {
        $values = $return['mssgs'] ?? [];
        self::assertIsArray($values);
        $messages = [];
        foreach ($values as $value) {
            self::assertIsString($value);
            $messages[] = $value;
        }
        return $messages;
    }

    /** Assertions read through the real ADODB/QueryUtils connection, without creating audit noise. */
    /** @param list<mixed> $parameters
     * @return list<array<mixed>>
     */
    private static function rows(string $sql, array $parameters = []): array
    {
        return QueryUtils::fetchRecordsNoLog($sql, $parameters);
    }

    /** @param list<mixed> $parameters */
    private static function scalar(string $sql, array $parameters = []): int|string|null
    {
        $rows = self::rows($sql, $parameters);
        self::assertCount(1, $rows);
        $value = $rows[0]['value'];
        if (!is_int($value) && !is_string($value) && $value !== null) {
            throw new \UnexpectedValueException('Expected a database scalar');
        }
        return $value;
    }

    /** Seeding and cleanup bypass only audit emission, never the actual database implementation. */
    /** @param list<mixed> $parameters */
    private static function execute(string $sql, array $parameters = []): void
    {
        QueryUtils::sqlStatementThrowException($sql, $parameters, noLog: true);
    }
}
