<?php

/**
 * The fee sheet's primary policy (FeeSheet::primaryPolicy()): the COPAY line
 * charges its copay, and billing.payer_id is its company. It is the primary
 * in force on the visit date.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use FeeSheet;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../library/FeeSheet.class.php';

final class FeeSheetPrimaryPolicyTest extends TestCase
{
    private const PID = 99995001;
    private const ENC = 99995011;
    private const VISIT = '2026-06-01 10:30:00';
    private const THIS_YEARS_PLAN = 99995021;
    private const NEXT_YEARS_PLAN = 99995022;

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (uuid, pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, 'JANE', 'TESTPATIENT', '1980-01-01', 'Female', NOW())",
            [(new UuidRegistry(['table_name' => 'patient_data']))->createUuid(), self::PID, (string) self::PID]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO form_encounter (pid, encounter, date, reason, facility_id, provider_id) VALUES (?, ?, ?, 'test', 0, ?)",
            [self::PID, self::ENC, self::VISIT, $this->firstUser()]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * Next year's card, entered in advance, is the latest-starting primary;
     * the visit is billed to the plan that covers it.
     */
    #[Test]
    public function theCopayAndPayerAreThoseOfThePrimaryInForceOnTheVisitDate(): void
    {
        $this->addPolicy('primary', self::THIS_YEARS_PLAN, '2026-01-01', null, '20');
        $this->addPolicy('primary', self::NEXT_YEARS_PLAN, '2027-01-01', null, '35');

        $feeSheet = new FeeSheet(self::PID, self::ENC);

        self::assertSame('20', $feeSheet->primaryPolicy()['copay'] ?? null);
        self::assertEquals(self::THIS_YEARS_PLAN, $feeSheet->payer_id);
    }

    /**
     * A primary that ended before the visit is neither charged nor billed.
     */
    #[Test]
    public function anEndedPrimaryIsNotTheVisitsPrimary(): void
    {
        $this->addPolicy('primary', self::THIS_YEARS_PLAN, '2025-01-01', '2026-03-31', '20');

        $feeSheet = new FeeSheet(self::PID, self::ENC);

        self::assertSame([], $feeSheet->primaryPolicy());
        self::assertNull($feeSheet->payer_id);
    }

    /**
     * A policy ending on the visit day still covers it.
     */
    #[Test]
    public function aPrimaryEndingOnTheVisitDayCoversIt(): void
    {
        $this->addPolicy('primary', self::THIS_YEARS_PLAN, '2025-01-01', '2026-06-01', '20');

        self::assertSame('20', (new FeeSheet(self::PID, self::ENC))->primaryPolicy()['copay'] ?? null);
    }

    /**
     * A secondary on its own is no primary: no copay, no payer.
     */
    #[Test]
    public function aSecondaryOnItsOwnIsNoPrimary(): void
    {
        $this->addPolicy('secondary', self::THIS_YEARS_PLAN, '2025-01-01', null, '20');

        $feeSheet = new FeeSheet(self::PID, self::ENC);

        self::assertSame([], $feeSheet->primaryPolicy());
        self::assertNull($feeSheet->payer_id);
    }

    private function addPolicy(string $type, int $company, ?string $start, ?string $end, string $copay): void
    {
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO insurance_data (uuid, pid, type, provider, date, date_end, copay, plan_name) VALUES (?, ?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, $type, (string) $company, $start, $end, $copay]
        );
    }

    private function firstUser(): int
    {
        $id = QueryUtils::fetchSingleValue('SELECT id FROM users ORDER BY id LIMIT 1', 'id');
        if (!is_numeric($id)) {
            throw new \RuntimeException('No user to give the test encounter a provider');
        }
        return (int) $id;
    }

    private function cleanUp(): void
    {
        foreach (['insurance_data', 'patient_data'] as $table) {
            foreach (QueryUtils::fetchTableColumn("SELECT uuid FROM `$table` WHERE pid = ? AND uuid IS NOT NULL", 'uuid', [self::PID]) as $uuid) {
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?', [$table, $uuid]);
            }
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM form_encounter WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
    }
}
