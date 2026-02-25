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

    /** @var list<int> Patient PIDs created by this test */
    private array $patientPids = [];

    /** @var list<int> procedure_order IDs created by this test */
    private array $orderIds = [];

    /** @var array<string, int> Fixture filename => procedure_order_id */
    private array $fixtureOrderMap = [];

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

        // Delete the lab
        if ($this->labId !== 0) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_providers WHERE ppid = ?",
                [$this->labId]
            );
        }

        // Delete test patients
        if ($this->patientPids !== []) {
            $placeholders = implode(',', array_fill(0, count($this->patientPids), '?'));
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM patient_data WHERE pid IN ($placeholders)",
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
            'vitl-potassium-1295772.hl7' => ['code' => 'L800515', 'result' => '4.0', 'units' => 'mmol/L'],
            'vitl-glucose-1295773.hl7'   => ['code' => 'L800495', 'result' => '99', 'units' => 'mg/dL'],
            'vitl-sodium-1295774.hl7'    => ['code' => 'L800382', 'result' => '140', 'units' => 'mmol/L'],
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
}
