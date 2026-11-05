<?php

/**
 * The service's joins on ids: a policy with its row and company name, and
 * many patients' rows by pid, whichever way a read returns the ids.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Insurance;

use DateTimeImmutable;
use OpenEMR\Services\Insurance\EffectiveInsuranceRow;
use OpenEMR\Services\Insurance\EffectiveInsuranceRule;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EffectiveInsuranceRowTest extends TestCase
{
    /**
     * @return list<array<mixed>> one patient's two policies, with the ids as $id gives them
     */
    private static function rows(callable $id): array
    {
        return [
            ['id' => $id(5), 'uuid' => null, 'pid' => $id(42), 'type' => 'primary', 'provider' => $id(101), 'date' => '2026-01-01', 'date_end' => null, 'policy_number' => 'P-5'],
            ['id' => $id(6), 'uuid' => null, 'pid' => $id(42), 'type' => 'secondary', 'provider' => $id(102), 'date' => '2026-01-01', 'date_end' => null, 'policy_number' => 'S-6'],
        ];
    }

    /**
     * @param list<EffectiveInsuranceRow> $joined
     * @return list<string>
     */
    private static function shown(array $joined): array
    {
        return array_map(static function (EffectiveInsuranceRow $row): string {
            $number = $row->columns['policy_number'] ?? null;
            return (is_string($number) ? $number : '') . ' ' . $row->companyName;
        }, $joined);
    }

    #[Test]
    public function aPolicyKeepsItsRowAndCompanyNameWhenIdsArriveAsInts(): void
    {
        $rows = self::rows(static fn(int $n): int => $n);
        $companies = [['id' => 101, 'name' => 'Gold Aetna'], ['id' => 102, 'name' => 'Gold BCBS']];

        $joined = EffectiveInsuranceRow::forPolicies(EffectiveInsuranceRule::choose($rows, new DateTimeImmutable('2026-11-04')), $rows, $companies);

        self::assertSame(['P-5 Gold Aetna', 'S-6 Gold BCBS'], self::shown($joined));
    }

    #[Test]
    public function aPolicyKeepsItsRowAndCompanyNameWhenIdsArriveAsDigitStrings(): void
    {
        // The rule accepts digit strings; the join must too, or every policy it chose would be dropped silently.
        $rows = self::rows(static fn(int $n): string => (string)$n);
        $companies = [['id' => '101', 'name' => 'Gold Aetna'], ['id' => '102', 'name' => 'Gold BCBS']];

        $policies = EffectiveInsuranceRule::choose($rows, new DateTimeImmutable('2026-11-04'));
        $joined = EffectiveInsuranceRow::forPolicies($policies, $rows, $companies);

        self::assertCount(2, $policies);
        self::assertSame(['P-5 Gold Aetna', 'S-6 Gold BCBS'], self::shown($joined));
    }

    #[Test]
    public function manyPatientsRowsGroupByPidWhicheverWayThePidArrives(): void
    {
        $rows = [
            ['id' => 1, 'pid' => 42],
            ['id' => 2, 'pid' => '42'],
            ['id' => 3, 'pid' => '043'],
            ['id' => 4, 'pid' => 'not a pid'],
            ['id' => 5, 'pid' => 0],
            ['id' => 6, 'pid' => null],
        ];

        $byPid = EffectiveInsuranceRule::rowsByPid($rows);

        self::assertSame([42, 43], array_keys($byPid));
        self::assertSame([1, 2], array_column($byPid[42], 'id'));
        self::assertSame([3], array_column($byPid[43], 'id'));
    }
}
