<?php

/**
 * Database-backed coverage of CLM05 and its following elements in an 837I batch.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\BillingProcessor\BillingClaim;
use OpenEMR\Billing\BillingProcessor\BillingClaimBatch;
use OpenEMR\Billing\BillingProcessor\BillingLogger;
use OpenEMR\Billing\BillingProcessor\Tasks\GeneratorUB04X12;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class GeneratorUB04X12Test extends TestCase
{
    private const PID = 99990072;
    private const ENC = 99990172;
    private const PAYER = 99990272;
    private const FACILITY = 99990372;
    private const PARTNER = 99990472;

    protected function setUp(): void
    {
        // ub04_dispose.php includes ../globals.php relative to the billing directory.
        $cwd = getcwd();
        if ($cwd === false) {
            throw new \RuntimeException('Cannot determine the working directory');
        }
        chdir(dirname(__DIR__, 4) . '/interface/billing');
        try {
            class_exists(GeneratorUB04X12::class);
        } finally {
            chdir($cwd);
        }

        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO facility (id, name, street, city, state, postal_code, phone, attn, federal_ein, facility_npi, accepts_assignment) " .
            "VALUES (?, 'TEST CLINIC', '1 MAIN ST', 'TEST CITY', 'CA', '90210', '2025550100', 'BILLING', '123456789', '1234567893', 1)",
            [self::FACILITY]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_companies (id, name, cms_id, ins_type_code) VALUES (?, 'TEST PAYER', 'TP001', 2)",
            [self::PAYER]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, 'JANE', 'TESTPATIENT', '1950-01-01', 'Female', NOW())",
            [self::PID, (string) self::PID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (pid, type, provider, policy_number, date, subscriber_relationship) " .
            "VALUES (?, 'primary', ?, 'TEST123', '2020-01-01', 'self')",
            [self::PID, self::PAYER]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, date, reason, facility_id, billing_facility, provider_id) " .
            "VALUES (?, ?, '2026-09-01', 'test', ?, ?, 1)",
            [self::PID, self::ENC, self::FACILITY, self::FACILITY]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, encounter, provider_id, user, groupname, authorized, " .
            "activity, billed, units, fee, code_text, payer_id) " .
            "VALUES (NOW(), 'CPT4', '99213', ?, ?, 1, 1, 'Default', 1, 1, 0, 1, 100.00, 'Office visit', ?)",
            [self::PID, self::ENC, self::PAYER]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO x12_partners (id, name, x12_sender_id, x12_receiver_id) VALUES (?, 'TEST PARTNER', 'SENDER', 'RECEIVER')",
            [self::PARTNER]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    #[DataProvider('typeOfBillProvider')]
    public function testGeneratedClaimElements(?string $typeOfBill, string $expectedClm05, bool $expectError): void
    {
        // Use the saved-claim path so a missing field 7 is not replaced by the default 831.
        $ub04 = [13 => '090126', 14 => '090126', 25 => '090126', 99 => '0771', 102 => '20260901'];
        if ($typeOfBill !== null) {
            $ub04[7] = $typeOfBill;
        }
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO claims (patient_id, encounter_id, version, status, submitted_claim) VALUES (?, ?, 1, 1, ?)',
            [self::PID, self::ENC, json_encode($ub04, JSON_THROW_ON_ERROR)]
        );

        $claim = new BillingClaim(
            self::PID . '-' . self::ENC,
            ['partner' => self::PARTNER, 'payer' => 'P' . self::PAYER],
            'validate'
        );
        $batch = new BillingClaimBatch('.txt', ['claims' => [$claim]]);
        $logger = $this->createMock(BillingLogger::class);
        $logger->expects($this->once())->method('appendToLog')->with($this->callback(
            static fn (string $log): bool => str_contains($log, '*** Error: Missing or invalid Type of Bill (UB-04 box 4)!') === $expectError
        ));
        $generator = new GeneratorUB04X12('validate');
        $generator->setLogger($logger);
        // Avoid setup()'s request dispatch; retain the real validateOnly/updateBatch path.
        (new ReflectionProperty(GeneratorUB04X12::class, 'batch'))->setValue($generator, $batch);
        $generator->validateOnly($claim);

        $claimSegments = array_values(array_filter(
            explode('~', $batch->getBatContent()),
            static fn (string $segment): bool => str_starts_with($segment, 'CLM*')
        ));
        $this->assertCount(1, $claimSegments);
        $this->assertSame(
            ['CLM', self::PID . '-' . self::ENC, '100.00', '', '', $expectedClm05, '', 'A', 'Y', 'Y'],
            explode('*', $claimSegments[0])
        );
        $this->assertSame([$claim], $batch->getClaims());
    }

    /**
     * @return array<string, array{?string, string, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function typeOfBillProvider(): array
    {
        return [
            'FQHC' => ['0771', '77:A:1', false],
            'invalid field 7' => ['83', '', true],
            'missing field 7' => [null, '', true],
            'zero facility type' => ['0001', '', true],
        ];
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException('DELETE FROM claims WHERE patient_id = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM billing WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_companies WHERE id = ?', [self::PAYER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM facility WHERE id = ?', [self::FACILITY]);
        QueryUtils::sqlStatementThrowException('DELETE FROM x12_partners WHERE id = ?', [self::PARTNER]);
    }
}
