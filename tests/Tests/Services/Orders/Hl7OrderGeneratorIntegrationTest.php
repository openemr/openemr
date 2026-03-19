<?php

/**
 * Integration tests for DefaultHl7OrderGenerator and UniversalHl7OrderGenerator.
 *
 * Verifies that HL7 ORM^O01 messages are correctly generated from procedure order
 * fixture data. The UniversalHl7OrderGenerator test specifically validates the compound
 * placer order number format ({send_fac_id}-{paddedOrderId}) that is central to #7762.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Josh Baiad <josh@joshbaiad.com>
 * @copyright Copyright (c) 2026 Josh Baiad <josh@joshbaiad.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Services\Orders;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Orders\DefaultHl7OrderGenerator;
use OpenEMR\Common\Orders\Hl7OrderGenerationException;
use OpenEMR\Common\Orders\UniversalHl7OrderGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class Hl7OrderGeneratorIntegrationTest extends TestCase
{
    private const SEND_FAC_ID = 'TESTFAC';

    private int $patientPid = 0;
    private int $encounterId = 0;
    private int $providerId = 0;
    private int $labId = 0;
    private int $orderId = 0;
    private int $procedureTypeId = 0;

    protected function setUp(): void
    {
        /** @var string $fileroot */
        $fileroot = $GLOBALS['fileroot'] ?? '';

        // The procedural generator files use $webserver_root to include code_types.
        // phpcs:ignore Generic.PHP.ForbiddenFunctions -- local var needed by require_once'd file
        $webserver_root = $fileroot;

        require_once($fileroot . '/interface/orders/gen_hl7_order.inc.php');
        require_once($fileroot . '/interface/procedure_tools/gen_universal_hl7/gen_hl7_order.inc.php');

        // Create a test patient (pid is not auto-increment)
        $pidRow = QueryUtils::querySingleRow(
            "SELECT IFNULL(MAX(pid), 0) + 1 AS next_pid FROM patient_data"
        );
        /** @var int $nextPid */
        $nextPid = is_array($pidRow) ? $pidRow['next_pid'] : 1;
        $this->patientPid = $nextPid;
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data SET " .
            "`pid` = ?, `fname` = 'Test', `lname` = 'Hl7gen', `DOB` = '1990-06-15', " .
            "`sex` = 'Male', `street` = '123 Lab St', `city` = 'TestCity', " .
            "`state` = 'VT', `postal_code` = '05401', " .
            "`pubpid` = 'test-fixture-hl7gen'",
            [$this->patientPid]
        );

        // Create a test provider (users record)
        $this->providerId = QueryUtils::sqlInsert(
            "INSERT INTO users SET " .
            "`username` = 'test-fixture-hl7gen', " .
            "`fname` = 'DocFirst', `lname` = 'DocLast', " .
            "`npi` = '1234567890', " .
            "`authorized` = 1, `active` = 1"
        );

        // Create an encounter (encounter is not auto-increment)
        $encRow = QueryUtils::querySingleRow(
            "SELECT IFNULL(MAX(encounter), 0) + 1 AS next_enc FROM form_encounter"
        );
        /** @var int $nextEnc */
        $nextEnc = is_array($encRow) ? $encRow['next_enc'] : 1;
        $this->encounterId = $nextEnc;
        QueryUtils::sqlInsert(
            "INSERT INTO form_encounter SET " .
            "`pid` = ?, `encounter` = ?, " .
            "`date` = '2026-01-15 00:00:00', " .
            "`reason` = 'test-fixture-hl7gen'",
            [$this->patientPid, $this->encounterId]
        );

        // Create the procedure_providers (lab) fixture
        $this->labId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_providers SET " .
            "`name` = 'test-fixture-hl7gen-lab', " .
            "`send_app_id` = 'TESTAPP', " .
            "`send_fac_id` = '" . self::SEND_FAC_ID . "', " .
            "`recv_app_id` = 'LABAPP', " .
            "`recv_fac_id` = 'LABFAC', " .
            "`DorP` = 'D', " .
            "`protocol` = 'DL', " .
            "`direction` = 'B'"
        );

        // Create the procedure_order
        $this->orderId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_order SET " .
            "`provider_id` = ?, " .
            "`patient_id` = ?, " .
            "`encounter_id` = ?, " .
            "`lab_id` = ?, " .
            "`date_ordered` = '2026-01-15', " .
            "`date_collected` = '2026-01-15 10:00:00', " .
            "`order_priority` = 'normal', " .
            "`clinical_hx` = 'test-fixture-hl7gen'",
            [$this->providerId, $this->patientPid, $this->encounterId, $this->labId]
        );

        // Create a procedure_type record (needed by default generator JOIN)
        $this->procedureTypeId = QueryUtils::sqlInsert(
            "INSERT INTO procedure_type SET " .
            "`name` = 'Complete Blood Count', " .
            "`procedure_code` = 'CBC', " .
            "`procedure_type` = 'ord', " .
            "`lab_id` = ?, " .
            "`specimen` = 'Blood'",
            [$this->labId]
        );

        // Create procedure_order_code linked to the order
        QueryUtils::sqlInsert(
            "INSERT INTO procedure_order_code SET " .
            "`procedure_order_id` = ?, " .
            "`procedure_order_seq` = 1, " .
            "`procedure_code` = 'CBC', " .
            "`procedure_name` = 'Complete Blood Count', " .
            "`do_not_send` = 0",
            [$this->orderId]
        );

        // Create the forms record that links the order to the encounter
        QueryUtils::sqlInsert(
            "INSERT INTO forms SET " .
            "`date` = NOW(), " .
            "`encounter` = ?, " .
            "`form_name` = 'Procedure Order', " .
            "`form_id` = ?, " .
            "`pid` = ?, " .
            "`formdir` = 'procedure_order'",
            [$this->encounterId, $this->orderId, $this->patientPid]
        );
    }

    protected function tearDown(): void
    {
        // Clean up in reverse dependency order
        if ($this->orderId !== 0) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM forms WHERE formdir = 'procedure_order' AND form_id = ?",
                [$this->orderId]
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_order_code WHERE procedure_order_id = ?",
                [$this->orderId]
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_order WHERE procedure_order_id = ?",
                [$this->orderId]
            );
        }

        if ($this->procedureTypeId !== 0) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_type WHERE procedure_type_id = ?",
                [$this->procedureTypeId]
            );
        }

        if ($this->labId !== 0) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM procedure_providers WHERE ppid = ?",
                [$this->labId]
            );
        }

        if ($this->encounterId !== 0) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM form_encounter WHERE encounter = ?",
                [$this->encounterId]
            );
        }

        if ($this->providerId !== 0) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM users WHERE id = ?",
                [$this->providerId]
            );
        }

        if ($this->patientPid !== 0) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM patient_data WHERE pid = ?",
                [$this->patientPid]
            );
        }
    }

    // ---------------------------------------------------------------
    // DefaultHl7OrderGenerator tests
    // ---------------------------------------------------------------

    #[Test]
    public function testDefaultGeneratorProducesValidHl7(): void
    {
        $generator = new DefaultHl7OrderGenerator();
        $result = $generator->generate($this->orderId);

        $hl7 = $result->hl7;
        $this->assertStringStartsWith('MSH|', $hl7, 'HL7 message should start with MSH segment');
        $this->assertStringContainsString('ORC|', $hl7, 'HL7 message should contain ORC segment');
        $this->assertStringContainsString('OBR|', $hl7, 'HL7 message should contain OBR segment');
        $this->assertStringContainsString('PID|', $hl7, 'HL7 message should contain PID segment');
    }

    #[Test]
    public function testDefaultGeneratorContainsOrderData(): void
    {
        $generator = new DefaultHl7OrderGenerator();
        $result = $generator->generate($this->orderId);

        $segments = $this->parseSegments($result->hl7);

        // ORC-2 should contain the zero-padded order ID
        $orc = $this->findSegment($segments, 'ORC');
        $this->assertNotNull($orc, 'ORC segment should be present');
        $paddedId = str_pad((string) $this->orderId, 4, '0', STR_PAD_LEFT);
        $this->assertEquals($paddedId, $orc[2], 'ORC-2 placer order number should be zero-padded order ID');

        // OBR-4 should contain the procedure code
        $obr = $this->findSegment($segments, 'OBR');
        $this->assertNotNull($obr, 'OBR segment should be present');
        $this->assertStringStartsWith('CBC', $obr[4], 'OBR-4 should contain the procedure code CBC');
    }

    #[Test]
    public function testDefaultGeneratorContainsPatientDemographics(): void
    {
        $generator = new DefaultHl7OrderGenerator();
        $result = $generator->generate($this->orderId);

        $segments = $this->parseSegments($result->hl7);
        $pid = $this->findSegment($segments, 'PID');
        $this->assertNotNull($pid, 'PID segment should be present');

        // PID-5: patient name (lname^fname)
        $this->assertStringContainsString('Hl7gen', $pid[5], 'PID-5 should contain patient last name');
        $this->assertStringContainsString('Test', $pid[5], 'PID-5 should contain patient first name');

        // PID-7: DOB in YYYYMMDD format
        $this->assertStringContainsString('19900615', $pid[7], 'PID-7 should contain DOB');

        // PID-8: Sex
        $this->assertEquals('M', $pid[8], 'PID-8 should be M for Male');
    }

    // ---------------------------------------------------------------
    // UniversalHl7OrderGenerator tests
    // ---------------------------------------------------------------

    #[Test]
    public function testUniversalGeneratorProducesCompoundPlacerNumber(): void
    {
        $generator = new UniversalHl7OrderGenerator();
        $result = $generator->generate($this->orderId);

        $segments = $this->parseSegments($result->hl7);
        $orc = $this->findSegment($segments, 'ORC');
        $this->assertNotNull($orc, 'ORC segment should be present');

        // Universal generator creates compound placer: {send_fac_id}-{paddedOrderId}
        $paddedId = str_pad((string) $this->orderId, 4, '0', STR_PAD_LEFT);
        $expected = self::SEND_FAC_ID . '-' . $paddedId;
        $this->assertEquals(
            $expected,
            $orc[2],
            'ORC-2 should be compound placer number: send_fac_id-paddedOrderId'
        );
    }

    // ---------------------------------------------------------------
    // Error handling
    // ---------------------------------------------------------------

    #[Test]
    public function testGenerateThrowsForMissingOrder(): void
    {
        $generator = new DefaultHl7OrderGenerator();

        $this->expectException(Hl7OrderGenerationException::class);
        $generator->generate(999999);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /**
     * Parse an HL7 message into an array of segments, each split by '|'.
     *
     * @return list<list<string>>
     */
    private function parseSegments(string $hl7): array
    {
        $lines = preg_split('/[\r\n]+/', $hl7, -1, PREG_SPLIT_NO_EMPTY);
        $segments = [];
        foreach (($lines ?: []) as $line) {
            $segments[] = explode('|', $line);
        }
        return $segments;
    }

    /**
     * Find the first segment with the given type.
     *
     * @param list<list<string>> $segments
     * @return list<string>|null
     */
    private function findSegment(array $segments, string $type): ?array
    {
        foreach ($segments as $seg) {
            if (($seg[0] ?? '') === $type) {
                return $seg;
            }
        }
        return null;
    }
}
