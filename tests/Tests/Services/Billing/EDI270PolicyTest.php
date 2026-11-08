<?php

/**
 * The primary policy a 270 asks about: the one in force on the day its
 * DTP*291 sends (EDI270::primaryPolicyIdOn()). The real-time check from the
 * insurance card (EDI270::realTimeRows()) asks about the primary in force
 * today, else the latest-starting one, so the payer is still asked.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use DateTimeImmutable;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Billing\EDI270;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\InsuranceService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EDI270PolicyTest extends TestCase
{
    private const PID = 99995201;
    private const PLAN = 99995211;
    private const NEXT_PLAN = 99995212;

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (uuid, pid, pubpid, fname, lname, DOB, sex, date) VALUES (?, ?, ?, 'JANE', 'TESTPATIENT', '1980-01-01', 'Female', NOW())",
            [(new UuidRegistry(['table_name' => 'patient_data']))->createUuid(), self::PID, (string) self::PID]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    /**
     * Next year's card, entered in advance, is the latest-starting primary;
     * the real-time check asks about the plan in force today.
     */
    #[Test]
    public function theRealTimeCheckAsksAboutThePrimaryInForceToday(): void
    {
        $this->addPolicy('primary', self::PLAN, 'CURRENT', -1000, null);
        $this->addPolicy('primary', self::NEXT_PLAN, 'NEXT-YEAR', 60, null);

        $rows = $this->realTimeRows();

        self::assertCount(1, $rows);
        self::assertSame('CURRENT', $rows[0]['policy_number'] ?? null);
        self::assertEquals(self::PLAN, $rows[0]['payer_id'] ?? null);
    }

    /**
     * With no primary in force today, the real-time check still asks the
     * payer about the latest-starting one, which usually answers "inactive".
     */
    #[Test]
    public function withNoPrimaryInForceTodayTheRealTimeCheckAsksAboutTheLatestOne(): void
    {
        $this->addPolicy('primary', self::PLAN, 'OLDER', -1000, -500);
        $this->addPolicy('primary', self::NEXT_PLAN, 'ENDED', -400, -100);

        self::assertSame('ENDED', $this->realTimeRows()[0]['policy_number'] ?? null);
    }

    /**
     * The batch asks about the primary in force on the appointment's day: a
     * plan ending before it is not asked about, and the one that starts in
     * time is.
     */
    #[Test]
    public function theBatchAsksAboutThePrimaryInForceOnItsDay(): void
    {
        $ending = $this->addPolicy('primary', self::PLAN, 'ENDING', -1000, 30);
        $next = $this->addPolicy('primary', self::NEXT_PLAN, 'NEXT', 31, null);
        $insurance = new InsuranceService();

        self::assertSame($ending, EDI270::primaryPolicyIdOn($insurance, self::PID, $this->day(10)));
        self::assertSame($next, EDI270::primaryPolicyIdOn($insurance, self::PID, $this->day(45)));
    }

    /**
     * A secondary alone is no primary, and nothing is asked about.
     */
    #[Test]
    public function aSecondaryAloneIsNoPrimary(): void
    {
        $this->addPolicy('secondary', self::PLAN, 'SECONDARY', -1000, null);

        self::assertNull(EDI270::primaryPolicyIdOn(new InsuranceService(), self::PID, $this->day(0)));
        self::assertNull($this->realTimeRows()[0]['policy_number'] ?? null);
    }

    /**
     * @return list<array<mixed>>
     */
    private function realTimeRows(): array
    {
        return EDI270::realTimeRows(self::PID, new InsuranceService(), ServiceContainer::getClock());
    }

    private function day(int $days): DateTimeImmutable
    {
        return new DateTimeImmutable((new DateTimeImmutable())->modify("$days days")->format('Y-m-d'));
    }

    /**
     * Dates are days from today, so the policy in force doesn't depend on the day the test runs.
     *
     * @return int the policy's id
     */
    private function addPolicy(string $type, int $company, string $number, ?int $startDays, ?int $endDays): int
    {
        $day = static fn(?int $days): ?string => $days === null ? null : (new DateTimeImmutable())->modify("$days days")->format('Y-m-d');
        return QueryUtils::sqlInsert(
            "INSERT INTO insurance_data (uuid, pid, type, provider, policy_number, date, date_end, plan_name) VALUES (?, ?, ?, ?, ?, ?, ?, 'test-fixture')",
            [(new UuidRegistry(['table_name' => 'insurance_data']))->createUuid(), self::PID, $type, (string) $company, $number, $day($startDays), $day($endDays)]
        );
    }

    private function cleanUp(): void
    {
        foreach (['insurance_data', 'patient_data'] as $table) {
            foreach (QueryUtils::fetchTableColumn("SELECT uuid FROM `$table` WHERE pid = ? AND uuid IS NOT NULL", 'uuid', [self::PID]) as $uuid) {
                QueryUtils::sqlStatementThrowException('DELETE FROM uuid_registry WHERE table_name = ? AND uuid = ?', [$table, $uuid]);
            }
        }
        QueryUtils::sqlStatementThrowException('DELETE FROM insurance_data WHERE pid = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM patient_data WHERE pid = ?', [self::PID]);
    }
}
