<?php

/**
 * Integration tests for DefaultHl7ResultParser using real HL7 ORU^R01 sample messages.
 *
 * These tests exercise the full receive_hl7_results() code path with real HL7 fixture
 * data from the OpenEMR wiki (Hl7_sample.zip). The samples contain compound placer
 * order numbers with dashes (e.g., "310137493-L700107") which directly exercise the
 * #8130/#7762 fix for intval() extraction of order IDs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Orders;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Orders\DefaultHl7ResultParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class Hl7ResultParserIntegrationTest extends TestCase
{
    private const FIXTURE_DIR = __DIR__ . '/../../Fixtures/HL7/';

    /** @var array<string, string> Fixture filename => OBR-2 placer number (used as control_id) */
    private const FIXTURES = [
        'vitl-ptt-1295771.hl7'       => '310137493-L700107',
        'vitl-potassium-1295772.hl7' => '310137531-L700027',
        'vitl-glucose-1295773.hl7'   => '310137535-L700011',
        'vitl-sodium-1295774.hl7'    => '310137533-L700024',
        'vitl-ptt-1295775.hl7'       => '310137537-L700107',
    ];

    /** @var array<string, string> Fixture filename => procedure code */
    private const PROCEDURE_CODES = [
        'vitl-ptt-1295771.hl7'       => 'L700107',
        'vitl-potassium-1295772.hl7' => 'L700027',
        'vitl-glucose-1295773.hl7'   => 'L700011',
        'vitl-sodium-1295774.hl7'    => 'L700024',
        'vitl-ptt-1295775.hl7'       => 'L700107',
    ];

    private DefaultHl7ResultParser $parser;
    private int $labId = 0;
    private int $labIdB = 0;

    /** @var list<int> Patient PIDs created by this test */
    private array $patientPids = [];

    /** @var list<int> procedure_order IDs created by this test */
    private array $orderIds = [];

    /** @var array<string, int> Fixture filename => procedure_order_id */
    private array $fixtureOrderMap = [];

    /** procedure_order_id for the B-direction compound placer test */
    private int $bidirectionalOrderId = 0;

    protected function setUp(): void
    {
        /** @var string $fileroot */
        $fileroot = $GLOBALS['fileroot'] ?? '';

        // The procedural functions require these includes
        require_once($fileroot . '/interface/orders/receive_hl7_results.inc.php');

        // Ensure session variables exist for EventAuditLogger used by rhl7LogMsg
        if (!isset($_SESSION['authUser']) || $_SESSION['authUser'] === '') {
            $_SESSION['authUser'] = 'test-fixture';
        }
        if (!isset($_SESSION['authProvider']) || $_SESSION['authProvider'] === '') {
            $_SESSION['authProvider'] = 'Default';
        }
        if (!isset($_SESSION['authUserID']) || $_SESSION['authUserID'] === '') {
            $_SESSION['authUserID'] = 1;
        }

        $this->parser = new DefaultHl7ResultParser();

        // Create Patient 1: VITLDOS ZZZHUBTEST, F, 1952-01-01 (used by 1295771)
        $this->patientPids[] = $this->createPatient('VITLDOS', 'ZZZHUBTEST', '1952-01-01', 'Female');

        // Create Patient 2: VITL ZZZHUBTEST, M, 1984-03-03 (used by 1295772-1295775)
        $this->patientPids[] = $this->createPatient('VITL', 'ZZZHUBTEST', '1984-03-03', 'Male');

        // Create procedure_providers (lab) fixture
        $this->labId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_providers SET " .
            "`name` = 'test-fixture-vitllab', " .
            "`send_app_id` = 'VITLLAB', " .
            "`send_fac_id` = 'RRMC', " .
            "`recv_app_id` = 'CMSOEMR', " .
            "`protocol` = 'DL', " .
            "`direction` = 'R'"
        );

        // Create B-direction lab for bidirectional compound placer test
        $this->labIdB = QueryUtils::sqlInsert(
            "INSERT INTO procedure_providers SET " .
            "`name` = 'test-fixture-bidir', " .
            "`send_app_id` = 'TESTFAC', " .
            "`send_fac_id` = 'TESTFAC', " .
            "`recv_app_id` = 'OPENEMR', " .
            "`protocol` = 'DL', " .
            "`direction` = 'B'"
        );

        // Create procedure_order + procedure_order_code for each fixture.
        // The R-direction parser matches orders by control_id + lab_id.
        $patient1Pid = $this->patientPids[0];
        $patient2Pid = $this->patientPids[1];

        foreach (self::FIXTURES as $file => $controlId) {
            $pid = ($file === 'vitl-ptt-1295771.hl7') ? $patient1Pid : $patient2Pid;
            $orderId = QueryUtils::sqlInsert(
                "INSERT INTO procedure_order SET " .
                "`provider_id` = 0, " .
                "`patient_id` = ?, " .
                "`lab_id` = ?, " .
                "`control_id` = ?, " .
                "`date_ordered` = '2016-01-21', " .
                "`clinical_hx` = ?",
                [$pid, $this->labId, $controlId, 'test-fixture-' . $file]
            );
            $this->orderIds[] = $orderId;
            $this->fixtureOrderMap[$file] = $orderId;

            QueryUtils::sqlInsert(
                "INSERT INTO procedure_order_code SET " .
                "`procedure_order_id` = ?, " .
                "`procedure_order_seq` = 1, " .
                "`procedure_code` = ?, " .
                "`procedure_name` = ?",
                [$orderId, self::PROCEDURE_CODES[$file], self::PROCEDURE_CODES[$file]]
            );
        }

        // Create order for bidirectional compound placer test (uses Patient 2)
        $this->bidirectionalOrderId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_order SET " .
            "`provider_id` = 0, " .
            "`patient_id` = ?, " .
            "`lab_id` = ?, " .
            "`date_ordered` = '2026-02-25', " .
            "`clinical_hx` = 'test-fixture-bidir'",
            [$patient2Pid, $this->labIdB]
        );
        $this->orderIds[] = $this->bidirectionalOrderId;

        QueryUtils::sqlInsert(
            "INSERT INTO procedure_order_code SET " .
            "`procedure_order_id` = ?, " .
            "`procedure_order_seq` = 1, " .
            "`procedure_code` = 'CBC', " .
            "`procedure_name` = 'Complete Blood Count'",
            [$this->bidirectionalOrderId]
        );
    }

    protected function tearDown(): void
    {
        // Delete results and reports linked to our test orders
        if ($this->orderIds !== []) {
            $placeholders = implode(',', array_fill(0, count($this->orderIds), '?'));

            QueryUtils::sqlStatementThrowException(
                "DELETE pr FROM procedure_result pr " .
                "INNER JOIN procedure_report prp ON prp.procedure_report_id = pr.procedure_report_id " .
                "WHERE prp.procedure_order_id IN ($placeholders)",
                $this->orderIds
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_report WHERE procedure_order_id IN ($placeholders)",
                $this->orderIds
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_order_code WHERE procedure_order_id IN ($placeholders)",
                $this->orderIds
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_order WHERE procedure_order_id IN ($placeholders)",
                $this->orderIds
            );
        }

        // Delete the labs
        foreach ([$this->labId, $this->labIdB] as $lid) {
            if ($lid !== 0) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM procedure_providers WHERE ppid = ?",
                    [$lid]
                );
            }
        }

        // Delete test patients and related skeleton patient rows
        if ($this->patientPids !== []) {
            $placeholders = implode(',', array_fill(0, count($this->patientPids), '?'));
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM patient_data WHERE pid IN ($placeholders)",
                $this->patientPids
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM employer_data WHERE pid IN ($placeholders)",
                $this->patientPids
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM history_data WHERE pid IN ($placeholders)",
                $this->patientPids
            );
        }
    }

    // ---------------------------------------------------------------
    // Dryrun / results-only tests (parsing correctness, no DB writes)
    // ---------------------------------------------------------------

    #[Test]
    public function testParseAllSamplesDryrunDoesNotFatal(): void
    {
        foreach (self::FIXTURES as $file => $_controlId) {
            $hl7 = $this->loadFixture($file);
            $matchReq = [];

            $result = $this->parser->receiveResults(
                $hl7,
                $matchReq,
                $this->labId,
                'R',
                true
            );

            $this->assertFalse(
                $result->fatal,
                "Fixture $file triggered a fatal parse error: " . implode('; ', $result->messages)
            );
        }
    }

    #[Test]
    public function testParseDryrunMatchesPatient(): void
    {
        // Use Patient 2 fixture — unique fname 'VITL' minimizes ambiguity
        $hl7 = $this->loadFixture('vitl-potassium-1295772.hl7');
        $matchReq = [];

        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labId,
            'R',
            true
        );

        $this->assertFalse($result->fatal, implode('; ', $result->messages));

        // If the demo database happens to have patients with matching DOB or names,
        // match_patient() may return -1 (ambiguous) and set needsMatch. Only assert
        // needsMatch=false when the matchReq array is empty (unambiguous match).
        if ($matchReq === []) {
            $this->assertFalse(
                $result->needsMatch,
                'Patient should be matched (exists in DB) but needsMatch was true'
            );
        } else {
            // Ambiguous match due to demo data — still validates parser didn't fatal
            $this->addToAssertionCount(1);
        }
    }

    #[Test]
    public function testParseMalformedMessageReturnsFatal(): void
    {
        $hl7 = 'NOT_HL7_DATA';
        $matchReq = [];

        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            0,
            'R',
            true
        );

        $this->assertTrue($result->fatal, 'Malformed input should be flagged as fatal');
    }

    // ---------------------------------------------------------------
    // Full write-path tests (results written to DB)
    // ---------------------------------------------------------------

    #[Test]
    public function testParseAndWriteResultsToDatabase(): void
    {
        $hl7 = $this->loadFixture('vitl-ptt-1295771.hl7');
        $matchReq = [];

        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labId,
            'R',
            false
        );

        $this->assertFalse($result->fatal, implode('; ', $result->messages));

        $orderId = $this->fixtureOrderMap['vitl-ptt-1295771.hl7'];

        // Verify procedure_report was written
        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report, 'procedure_report row should exist');
        $this->assertEquals('final', $report['report_status']);
        $this->assertEquals('2016-01-21 13:39:00', $report['date_collected']);
        $this->assertEquals('2016-01-21 13:40:37', $report['date_report']);
        /** @var string $reportNotes */
        $reportNotes = $report['report_notes'] ?? '';
        $this->assertStringContainsString('Rutland Regional', $reportNotes);

        // Verify procedure_result was written
        $resultRow = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result " .
            "WHERE procedure_report_id = ? AND result_code = 'L800371' LIMIT 1",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultRow, 'procedure_result row for L800371 (PTT) should exist');
        $this->assertEquals('33.0', $resultRow['result']);
        $this->assertEquals('second(s)', $resultRow['units']);
        $this->assertEquals('23.0-37.0', $resultRow['range']);
        $this->assertEquals('no', $resultRow['abnormal']);
        $this->assertEquals('PTT', $resultRow['result_text']);
        $this->assertEquals('2016-01-21 13:40:35', $resultRow['date']);
    }

    #[Test]
    public function testParseMultipleResultsWriteCorrectly(): void
    {
        // Parse Potassium, Glucose, Sodium for Patient 2
        $filesToParse = [
            'vitl-potassium-1295772.hl7',
            'vitl-glucose-1295773.hl7',
            'vitl-sodium-1295774.hl7',
        ];

        foreach ($filesToParse as $file) {
            $hl7 = $this->loadFixture($file);
            $matchReq = [];

            $result = $this->parser->receiveResults(
                $hl7,
                $matchReq,
                $this->labId,
                'R',
                false
            );

            $this->assertFalse($result->fatal, "Fatal error parsing $file: " . implode('; ', $result->messages));
        }

        // Verify each order has a report and result
        $expectedResults = [
            'vitl-potassium-1295772.hl7' => [
                'code' => 'L800515', 'result' => '4.0', 'units' => 'mmol/L',
                'result_text' => 'Potassium Level', 'range' => '3.5-5.1', 'abnormal' => 'no',
            ],
            'vitl-glucose-1295773.hl7' => [
                'code' => 'L800495', 'result' => '99', 'units' => 'mg/dL',
                'result_text' => 'Glucose Level', 'range' => '74-106', 'abnormal' => 'no',
            ],
            'vitl-sodium-1295774.hl7' => [
                'code' => 'L800382', 'result' => '140', 'units' => 'mmol/L',
                'result_text' => 'Sodium Level', 'range' => '136-145', 'abnormal' => 'no',
            ],
        ];

        foreach ($expectedResults as $file => $expected) {
            $orderId = $this->fixtureOrderMap[$file];

            $report = QueryUtils::querySingleRow(
                "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
                [$orderId]
            );
            $this->assertIsArray($report, "procedure_report should exist for $file");
            $this->assertEquals('final', $report['report_status'], "report_status for $file");

            $resultRow = QueryUtils::querySingleRow(
                "SELECT * FROM procedure_result " .
                "WHERE procedure_report_id = ? AND result_code = ? LIMIT 1",
                [$report['procedure_report_id'], $expected['code']]
            );
            $this->assertIsArray($resultRow, "procedure_result for {$expected['code']} should exist ($file)");
            $this->assertEquals($expected['result'], $resultRow['result'], "result value for $file");
            $this->assertEquals($expected['units'], $resultRow['units'], "units for $file");
            $this->assertEquals($expected['result_text'], $resultRow['result_text'], "result_text for $file");
            $this->assertEquals($expected['range'], $resultRow['range'], "range for $file");
            $this->assertEquals($expected['abnormal'], $resultRow['abnormal'], "abnormal for $file");
        }
    }

    #[Test]
    public function testParseResultWithNtePreservesNotes(): void
    {
        $hl7 = $this->loadFixture('vitl-ptt-1295771.hl7');
        $matchReq = [];

        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labId,
            'R',
            false
        );

        $this->assertFalse($result->fatal, implode('; ', $result->messages));

        $orderId = $this->fixtureOrderMap['vitl-ptt-1295771.hl7'];

        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report);

        $resultRow = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result " .
            "WHERE procedure_report_id = ? AND result_code = 'L800371' LIMIT 1",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultRow);

        /** @var string $comments */
        $comments = $resultRow['comments'] ?? '';
        $this->assertNotSame('', $comments, 'Result comments should not be empty');
        $this->assertStringContainsString(
            'anti-coagulation',
            $comments,
            'NTE interpretive text about anti-coagulation should be preserved in comments'
        );
    }

    #[Test]
    public function testParseSecondPttFixtureWritesDistinctResult(): void
    {
        $hl7 = $this->loadFixture('vitl-ptt-1295775.hl7');
        $matchReq = [];

        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labId,
            'R',
            false
        );

        $this->assertFalse($result->fatal, implode('; ', $result->messages));

        $orderId = $this->fixtureOrderMap['vitl-ptt-1295775.hl7'];

        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report, 'procedure_report should exist for second PTT');

        $resultRow = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result " .
            "WHERE procedure_report_id = ? AND result_code = 'L800371' LIMIT 1",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultRow, 'procedure_result for L800371 should exist');
        $this->assertEquals('32.0', $resultRow['result'], 'Second PTT should have result 32.0, not 33.0');
        $this->assertEquals('L800371', $resultRow['result_code']);

        /** @var string $comments */
        $comments = $resultRow['comments'] ?? '';
        $this->assertStringContainsString(
            'anti-coagulation',
            $comments,
            'NTE interpretive text about anti-coagulation should be preserved'
        );
    }

    #[Test]
    public function testBidirectionalPlainPlacerMatchesOrder(): void
    {
        $orderId = $this->bidirectionalOrderId;
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];

        // Look up patient details for PID segment
        $patient = QueryUtils::querySingleRow(
            "SELECT fname, lname, DOB, sex FROM patient_data WHERE pid = ?",
            [$pid]
        );
        $this->assertIsArray($patient);

        /** @var string $fname */
        $fname = $patient['fname'];
        /** @var string $lname */
        $lname = $patient['lname'];
        /** @var string $rawDob */
        $rawDob = $patient['DOB'];
        $dob = str_replace('-', '', $rawDob);
        $sex = ($patient['sex'] === 'Male') ? 'M' : 'F';
        $timestamp = '20260225120000';

        // Build synthetic HL7 ORU^R01 with plain numeric placer number.
        $hl7 = implode("\r", [
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$timestamp}||ORU^R01|TESTMSG001|P|2.3|||AL",
            "PID|1||{$pid}||{$lname}^{$fname}||{$dob}|{$sex}||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}||CBC^Complete Blood Count|||{$timestamp}|||||||||||||||{$timestamp}||LAB|F||1",
            "OBX|1|NM|WBC^White Blood Cell Count||7.5|K/uL|4.5-11.0|N|||F|||{$timestamp}",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'B-direction parse should not fatal: ' . implode('; ', $result->messages)
        );

        // Verify procedure_report was written for the B-direction order
        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report, 'procedure_report should exist for B-direction order');
        $this->assertEquals('final', $report['report_status']);

        // Verify procedure_result was written with correct values
        $resultRow = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result " .
            "WHERE procedure_report_id = ? AND result_code = 'WBC' LIMIT 1",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultRow, 'procedure_result for WBC should exist');
        $this->assertEquals('7.5', $resultRow['result']);
        $this->assertEquals('K/uL', $resultRow['units']);
        $this->assertEquals('4.5-11.0', $resultRow['range']);
        $this->assertEquals('no', $resultRow['abnormal']);
    }

    // ---------------------------------------------------------------
    // Synthetic HL7 tests (code-path coverage)
    // ---------------------------------------------------------------

    #[Test]
    public function testUnmatchedPatientCreatesSkeletonPatient(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labId, [
            ['code' => 'SKELTEST', 'name' => 'Skeleton Test'],
        ], 'SKEL001');

        $ts = '20260225120000';

        // R-direction HL7 with unique demographics that won't match any existing patient
        $hl7 = $this->buildHl7([
            "MSH|^~\\&|VITLLAB|RRMC|CMSOEMR||{$ts}||ORU^R01|TESTMSGSK|P|2.3|||AL",
            "PID|1||999999||SKELETONLN^SKELETONFN||19991231|M||||||||||999999",
            "OBR|1|SKEL001|SKEL001|SKELTEST^Skeleton Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|NM|SKEL^Skeleton Result||42.0|mg/dL|10-50|N|||F|||{$ts}",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labId,
            'R',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'Skeleton patient parse should not fatal: ' . implode('; ', $result->messages)
        );

        // Verify skeleton patient was created (ucname normalizes to title case)
        $newPatient = QueryUtils::querySingleRow(
            "SELECT * FROM patient_data WHERE fname = ? AND lname = ?",
            ['Skeletonfn', 'Skeletonln']
        );
        $this->assertIsArray($newPatient, 'Skeleton patient should have been created');
        $this->assertEquals('1999-12-31', $newPatient['DOB']);
        $this->assertEquals('Male', $newPatient['sex']);

        // Track for cleanup
        /** @var int $newPid */
        $newPid = $newPatient['pid'];
        $this->patientPids[] = $newPid;

        // Verify result was still written to the order
        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report, 'procedure_report should exist for skeleton patient order');
    }

    #[Test]
    public function testMultiObrMessageCreatesMultipleReports(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labIdB, [
            ['code' => 'WBC', 'name' => 'White Blood Cell'],
            ['code' => 'RBC', 'name' => 'Red Blood Cell'],
        ]);
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];
        $ts = '20260225120000';

        $hl7 = $this->buildHl7([
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGMO|P|2.3|||AL",
            "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}||WBC^White Blood Cell|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|NM|WBC^White Blood Cell Count||7.5|K/uL|4.5-11.0|N|||F|||{$ts}",
            "OBR|2|{$paddedOrderId}||RBC^Red Blood Cell|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|NM|RBC^Red Blood Cell Count||4.8|M/uL|4.7-6.1|N|||F|||{$ts}",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'Multi-OBR parse should not fatal: ' . implode('; ', $result->messages)
        );

        // Verify 2 reports exist
        $reportCount = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS cnt FROM procedure_report WHERE procedure_order_id = ?",
            [$orderId]
        );
        $this->assertIsArray($reportCount);
        /** @var int|string $cnt */
        $cnt = $reportCount['cnt'];
        $this->assertEquals(2, intval($cnt), 'Should have 2 procedure_report rows');

        // Report for seq=1 (WBC)
        $report1 = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? AND procedure_order_seq = 1 LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report1, 'WBC report should exist');
        $result1 = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? AND result_code = 'WBC' LIMIT 1",
            [$report1['procedure_report_id']]
        );
        $this->assertIsArray($result1, 'WBC result should exist');
        $this->assertEquals('7.5', $result1['result']);

        // Report for seq=2 (RBC)
        $report2 = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? AND procedure_order_seq = 2 LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report2, 'RBC report should exist');
        $result2 = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? AND result_code = 'RBC' LIMIT 1",
            [$report2['procedure_report_id']]
        );
        $this->assertIsArray($result2, 'RBC result should exist');
        $this->assertEquals('4.8', $result2['result']);
    }

    #[Test]
    public function testNonFinalReportStatuses(): void
    {
        $statusMap = ['P' => 'prelim', 'C' => 'correct', 'X' => 'error'];
        $ts = '20260225120000';
        $pid = $this->patientPids[1];

        foreach ($statusMap as $hl7Status => $expectedStatus) {
            $orderId = $this->createOrder($pid, $this->labIdB, [
                ['code' => 'ST' . $hl7Status, 'name' => "Status {$hl7Status} Test"],
            ]);
            $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);

            $hl7 = $this->buildHl7([
                "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGST{$hl7Status}|P|2.3|||AL",
                "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
                "ORC|RE|{$paddedOrderId}|||||||||||",
                "OBR|1|{$paddedOrderId}||ST{$hl7Status}^Status {$hl7Status} Test|||{$ts}|||||||||||||||{$ts}||LAB|{$hl7Status}||1",
                "OBX|1|NM|ST{$hl7Status}^Result||1.0|mg/dL|0-5|N|||F|||{$ts}",
            ]);

            $matchReq = [];
            $result = $this->parser->receiveResults(
                $hl7,
                $matchReq,
                $this->labIdB,
                'B',
                false
            );

            $this->assertFalse(
                $result->fatal,
                "Status {$hl7Status} parse should not fatal: " . implode('; ', $result->messages)
            );

            $report = QueryUtils::querySingleRow(
                "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
                [$orderId]
            );
            $this->assertIsArray($report, "Report should exist for status {$hl7Status}");
            $this->assertEquals(
                $expectedStatus,
                $report['report_status'],
                "Status {$hl7Status} should map to {$expectedStatus}"
            );
        }
    }

    #[Test]
    public function testAbnormalFlagMappings(): void
    {
        $flagMap = ['H' => 'high', 'L' => 'low', 'A' => 'yes', 'HH' => 'vhigh', 'LL' => 'vlow'];
        $ts = '20260225120000';
        $pid = $this->patientPids[1];

        foreach ($flagMap as $hl7Flag => $expectedAbnormal) {
            $orderId = $this->createOrder($pid, $this->labIdB, [
                ['code' => 'AB' . $hl7Flag, 'name' => "Abnormal {$hl7Flag} Test"],
            ]);
            $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);

            $hl7 = $this->buildHl7([
                "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGAB{$hl7Flag}|P|2.3|||AL",
                "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
                "ORC|RE|{$paddedOrderId}|||||||||||",
                "OBR|1|{$paddedOrderId}||AB{$hl7Flag}^Abnormal {$hl7Flag} Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
                "OBX|1|NM|AB{$hl7Flag}^Result||5.0|mg/dL|1-10|{$hl7Flag}|||F|||{$ts}",
            ]);

            $matchReq = [];
            $result = $this->parser->receiveResults(
                $hl7,
                $matchReq,
                $this->labIdB,
                'B',
                false
            );

            $this->assertFalse(
                $result->fatal,
                "Flag {$hl7Flag} parse should not fatal: " . implode('; ', $result->messages)
            );

            $report = QueryUtils::querySingleRow(
                "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
                [$orderId]
            );
            $this->assertIsArray($report, "Report should exist for flag {$hl7Flag}");

            $resultRow = QueryUtils::querySingleRow(
                "SELECT * FROM procedure_result WHERE procedure_report_id = ? LIMIT 1",
                [$report['procedure_report_id']]
            );
            $this->assertIsArray($resultRow, "Result should exist for flag {$hl7Flag}");
            $this->assertEquals(
                $expectedAbnormal,
                $resultRow['abnormal'],
                "Flag {$hl7Flag} should map to {$expectedAbnormal}"
            );
        }
    }

    #[Test]
    public function testPerformingOrganizationFromObx(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labIdB, [
            ['code' => 'PERFORG', 'name' => 'Performing Org Test'],
        ]);
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];
        $ts = '20260225120000';

        // OBX extended to 25 fields: positions 15-22 empty, then OBX-23/24/25
        $hl7 = $this->buildHl7([
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGPO|P|2.3|||AL",
            "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}||PERFORG^Performing Org Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|NM|PERFORG^Performing Org Result||7.5|K/uL|4.5-11.0|N|||F|||{$ts}|||||||||Test Lab|123 Main St^^Anytown^CA^90210^USA|12345^Smith^John^M^III^Dr.",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'Performing org parse should not fatal: ' . implode('; ', $result->messages)
        );

        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report);

        $resultRow = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? LIMIT 1",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultRow, 'Result should exist for performing org test');

        /** @var string $facility */
        $facility = $resultRow['facility'] ?? '';
        $this->assertStringContainsString('Test Lab', $facility, 'Facility should contain lab name');
        $this->assertStringContainsString('Anytown', $facility, 'Facility should contain city');
        $this->assertStringContainsString('Smith', $facility, 'Facility should contain director name');
    }

    #[Test]
    public function testSpmSegmentUpdatesSpecimen(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labIdB, [
            ['code' => 'SPMTEST', 'name' => 'SPM Test'],
        ]);
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];
        $ts = '20260225120000';

        $hl7 = $this->buildHl7([
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGSPM|P|2.3|||AL",
            "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}||SPMTEST^SPM Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|NM|SPMTEST^SPM Result||7.5|K/uL|4.5-11.0|N|||F|||{$ts}",
            "SPM|1|||119297000^BLD^SCT^BldSpc^Blood^99USA^^^Blood Specimen",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'SPM parse should not fatal: ' . implode('; ', $result->messages)
        );

        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report, 'Report should exist for SPM test');
        $this->assertEquals('Blood Specimen', $report['specimen_num']);
        /** @var string $reportNotes */
        $reportNotes = $report['report_notes'] ?? '';
        $this->assertStringContainsString('Specimen type', $reportNotes);
    }

    #[Test]
    public function testZpsSegmentUpdatesLabFacility(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labIdB, [
            ['code' => 'ZPSTEST', 'name' => 'ZPS Test'],
        ]);
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];
        $ts = '20260225120000';

        $hl7 = $this->buildHl7([
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGZPS|P|2.3|||AL",
            "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}||ZPSTEST^ZPS Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|NM|ZPSTEST^ZPS Result||7.5|K/uL|4.5-11.0|N|||F|||{$ts}",
            "ZPS|1|LABX001|Test Laboratory|123 Lab St^^Anytown^CA^90210|555-1234||Smith^John^MD||CLIA123",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'ZPS parse should not fatal: ' . implode('; ', $result->messages)
        );

        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report);

        $resultRow = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? LIMIT 1",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultRow, 'Result should exist for ZPS test');

        /** @var string $facility */
        $facility = $resultRow['facility'] ?? '';
        $this->assertStringContainsString('Test Laboratory', $facility, 'Facility should contain lab name');
        $this->assertStringContainsString('Anytown', $facility, 'Facility should contain city');
        $this->assertStringContainsString('Smith John, MD', $facility, 'Facility should contain director');
    }

    #[Test]
    public function testParentChildObrReports(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labIdB, [
            ['code' => 'PANEL1', 'name' => 'Panel Test'],
            ['code' => 'CHILDTEST', 'name' => 'Child Test'],
        ]);
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];
        $ts = '20260225120000';

        // Parent OBR has filler ID FILL001. Child OBR references parent via:
        //   OBR-26 = WBC&White Blood Cell^ (parent result identifier, & = sub-component separator)
        //   OBR-29 = ^FILL001 (parent filler ID)
        $hl7 = $this->buildHl7([
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGPC|P|2.3|||AL",
            "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}|FILL001|PANEL1^Panel Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|NM|WBC^White Blood Cell||7.5|K/uL|4.5-11.0|N|||F|||{$ts}",
            "OBR|2|{$paddedOrderId}|FILL002|CHILDTEST^Child Test|||{$ts}|||||||||||||||{$ts}||LAB|F|WBC&White Blood Cell^|1||^FILL001",
            "OBX|1|NM|CHILD^Child Result||99.0|mg/dL|50-150|N|||F|||{$ts}",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'Parent-child parse should not fatal: ' . implode('; ', $result->messages)
        );

        // Verify 2 reports exist
        $reportCount = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS cnt FROM procedure_report WHERE procedure_order_id = ?",
            [$orderId]
        );
        $this->assertIsArray($reportCount);
        /** @var int|string $cnt */
        $cnt = $reportCount['cnt'];
        $this->assertEquals(2, intval($cnt), 'Should have 2 reports (parent and child)');

        // Verify child report (seq=2) has parent reference in report_notes
        $childReport = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report " .
            "WHERE procedure_order_id = ? AND procedure_order_seq = 2 LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($childReport, 'Child report should exist');

        /** @var string $notes */
        $notes = $childReport['report_notes'] ?? '';
        $this->assertStringContainsString('child of result', $notes);
        $this->assertStringContainsString('WBC', $notes);
        $this->assertStringContainsString('7.5', $notes);

        // Verify child has its own result
        $childResult = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? LIMIT 1",
            [$childReport['procedure_report_id']]
        );
        $this->assertIsArray($childResult, 'Child report should have a result');
    }

    #[Test]
    public function testTextResultMergingCombinesComments(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labIdB, [
            ['code' => 'NOTES', 'name' => 'Lab Notes'],
        ]);
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];
        $ts = '20260225120000';

        // Three TX OBX segments with same result code — should merge into one result
        $hl7 = $this->buildHl7([
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGTX|P|2.3|||AL",
            "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}||NOTES^Lab Notes|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|TX|NOTE^Lab Notes||Line one of text|||||F|||{$ts}",
            "OBX|2|TX|NOTE^Lab Notes||Line two of text|||||F|||{$ts}",
            "OBX|3|TX|NOTE^Lab Notes||Line three of text|||||F|||{$ts}",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'TX merge parse should not fatal: ' . implode('; ', $result->messages)
        );

        $report = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report WHERE procedure_order_id = ? LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report);

        // Should be only 1 result row (merged), not 3
        $resultCount = QueryUtils::querySingleRow(
            "SELECT COUNT(*) AS cnt FROM procedure_result WHERE procedure_report_id = ?",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultCount);
        /** @var int|string $cnt */
        $cnt = $resultCount['cnt'];
        $this->assertEquals(1, intval($cnt), 'Merged TX should produce exactly 1 result row');

        $resultRow = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? LIMIT 1",
            [$report['procedure_report_id']]
        );
        $this->assertIsArray($resultRow);
        $this->assertEquals('NOTE', $resultRow['result_code']);

        /** @var string $comments */
        $comments = $resultRow['comments'] ?? '';
        $this->assertStringContainsString('Line one of text', $comments);
        $this->assertStringContainsString('Line two of text', $comments);
        $this->assertStringContainsString('Line three of text', $comments);
    }

    #[Test]
    public function testObxDataTypesCweAndSn(): void
    {
        $orderId = $this->createOrder($this->patientPids[1], $this->labIdB, [
            ['code' => 'CWETEST', 'name' => 'CWE Test'],
            ['code' => 'SNTEST', 'name' => 'SN Test'],
        ]);
        $paddedOrderId = str_pad((string) $orderId, 4, '0', STR_PAD_LEFT);
        $pid = $this->patientPids[1];
        $ts = '20260225120000';

        $hl7 = $this->buildHl7([
            "MSH|^~\\&|TESTFAC|TESTFAC|OPENEMR||{$ts}||ORU^R01|TESTMSGDT|P|2.3|||AL",
            "PID|1||{$pid}||ZZZHUBTEST^VITL||19840303|M||||||||||{$pid}",
            "ORC|RE|{$paddedOrderId}|||||||||||",
            "OBR|1|{$paddedOrderId}||CWETEST^CWE Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|CWE|CWETEST^CWE Result||12345^Coded Value^LN|||N|||F|||{$ts}",
            "OBR|2|{$paddedOrderId}||SNTEST^SN Test|||{$ts}|||||||||||||||{$ts}||LAB|F||1",
            "OBX|1|SN|SNTEST^SN Result||>^120|||N|||F|||{$ts}",
        ]);

        $matchReq = [];
        $result = $this->parser->receiveResults(
            $hl7,
            $matchReq,
            $this->labIdB,
            'B',
            false
        );

        $this->assertFalse(
            $result->fatal,
            'CWE/SN parse should not fatal: ' . implode('; ', $result->messages)
        );

        // CWE result: rhl7CWE("12345^Coded Value^LN") → "12345 (Coded Value)"
        $report1 = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report " .
            "WHERE procedure_order_id = ? AND procedure_order_seq = 1 LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report1, 'CWE report should exist');

        $cweResult = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? LIMIT 1",
            [$report1['procedure_report_id']]
        );
        $this->assertIsArray($cweResult, 'CWE result should exist');
        $this->assertEquals('12345 (Coded Value)', $cweResult['result']);
        $this->assertEquals('C', $cweResult['result_data_type']);

        // SN result: trim(str_replace("^", " ", ">^120")) → "> 120"
        $report2 = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_report " .
            "WHERE procedure_order_id = ? AND procedure_order_seq = 2 LIMIT 1",
            [$orderId]
        );
        $this->assertIsArray($report2, 'SN report should exist');

        $snResult = QueryUtils::querySingleRow(
            "SELECT * FROM procedure_result WHERE procedure_report_id = ? LIMIT 1",
            [$report2['procedure_report_id']]
        );
        $this->assertIsArray($snResult, 'SN result should exist');
        $this->assertEquals('> 120', $snResult['result']);
        $this->assertEquals('S', $snResult['result_data_type']);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function loadFixture(string $filename): string
    {
        $path = self::FIXTURE_DIR . $filename;
        $this->assertFileExists($path, "HL7 fixture file not found: $path");
        $content = file_get_contents($path);
        $this->assertIsString($content, "Failed to read fixture: $path");
        return $content;
    }

    private function createPatient(string $fname, string $lname, string $dob, string $sex): int
    {
        $pidRow = QueryUtils::querySingleRow(
            "SELECT IFNULL(MAX(pid), 0) + 1 AS next_pid FROM patient_data"
        );
        /** @var int $pid */
        $pid = is_array($pidRow) ? $pidRow['next_pid'] : 1;

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data SET " .
            "`pid` = ?, `fname` = ?, `lname` = ?, `DOB` = ?, `sex` = ?, " .
            "`pubpid` = ?",
            [$pid, $fname, $lname, $dob, $sex, 'test-fixture-' . strtolower($fname)]
        );

        return $pid;
    }

    /**
     * Create a procedure_order with procedure_order_code rows for testing.
     *
     * @param int    $pid       Patient PID
     * @param int    $labId     Lab (procedure_providers) ID
     * @param list<array{code: string, name: string}> $codes  Procedure codes (one per seq)
     * @param string $controlId Optional control_id for R-direction matching
     * @return int   The procedure_order_id
     */
    private function createOrder(int $pid, int $labId, array $codes, string $controlId = ''): int
    {
        $orderId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_order SET " .
            "`provider_id` = 0, " .
            "`patient_id` = ?, " .
            "`lab_id` = ?, " .
            "`control_id` = ?, " .
            "`date_ordered` = '2026-02-25', " .
            "`clinical_hx` = 'test-fixture-synthetic'",
            [$pid, $labId, $controlId]
        );
        $this->orderIds[] = $orderId;

        foreach ($codes as $seq => $code) {
            QueryUtils::sqlInsert(
                "INSERT INTO procedure_order_code SET " .
                "`procedure_order_id` = ?, " .
                "`procedure_order_seq` = ?, " .
                "`procedure_code` = ?, " .
                "`procedure_name` = ?",
                [$orderId, $seq + 1, $code['code'], $code['name']]
            );
        }

        return $orderId;
    }

    /** @param list<string> $segments HL7 segment strings */
    private function buildHl7(array $segments): string
    {
        return implode("\r", $segments);
    }
}
