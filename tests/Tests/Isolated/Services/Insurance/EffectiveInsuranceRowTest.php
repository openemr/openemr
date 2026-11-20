<?php

/**
 * The service's join on ids: a policy with its row and company name,
 * whichever way a read returns the ids.
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

    /**
     * The join as a read with bound values returns the ids: ints.
     */
    #[Test]
    public function aPolicyKeepsItsRowAndCompanyNameWhenIdsArriveAsInts(): void
    {
        $rows = self::rows(static fn(int $n): int => $n);
        $companies = [['id' => 101, 'name' => 'Gold Aetna'], ['id' => 102, 'name' => 'Gold BCBS']];

        $joined = EffectiveInsuranceRow::forPolicies(EffectiveInsuranceRule::choose($rows, new DateTimeImmutable('2026-11-04')), $rows, $companies);

        self::assertSame(['P-5 Gold Aetna', 'S-6 Gold BCBS'], self::shown($joined));
    }

    /**
     * The rule accepts digit strings, so the join must too, or every policy it chose would be dropped with no error.
     */
    #[Test]
    public function aPolicyKeepsItsRowAndCompanyNameWhenIdsArriveAsDigitStrings(): void
    {
        $rows = self::rows(static fn(int $n): string => (string)$n);
        $companies = [['id' => '101', 'name' => 'Gold Aetna'], ['id' => '102', 'name' => 'Gold BCBS']];

        $policies = EffectiveInsuranceRule::choose($rows, new DateTimeImmutable('2026-11-04'));
        $joined = EffectiveInsuranceRow::forPolicies($policies, $rows, $companies);

        self::assertCount(2, $policies);
        self::assertSame(['P-5 Gold Aetna', 'S-6 Gold BCBS'], self::shown($joined));
    }
}
