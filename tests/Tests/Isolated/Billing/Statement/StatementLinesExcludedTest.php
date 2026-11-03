<?php

/**
 * Isolated tests for what a statement keeps about the encounters a module left off it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementLines;
use PHPUnit\Framework\TestCase;

final class StatementLinesExcludedTest extends TestCase
{
    public function testAStatementWithNothingLeftOffIsUntouched(): void
    {
        $stmt = ['pid' => '7', 'lines' => [['dos' => '2026-08-01']], 'amount' => '100.00'];

        $this->assertSame([], StatementLines::excluded($stmt));
    }

    public function testTheEncountersLeftOffAreKeptWithTheirIds(): void
    {
        $stmt = ['pid' => '7', 'lines' => [['dos' => '2026-08-01']], 'excluded' => self::kept(7, [32])];

        $this->assertSame([32], array_column(StatementLines::excluded($stmt), 'id'));
        $this->assertSame([2], array_column(StatementLines::excluded($stmt), 'encounter'));
    }

    public function testAMalformedEntryIsNotReadAsLeftOff(): void
    {
        // A string id (not as StatementLines keeps it) and an entry that isn't an array.
        $kept = ['pid' => 7, 'encounters' => [
            ['encounter' => 1, 'id' => 31, 'dos' => '2026-07-01', 'lines' => [], 'amount' => '60.00'],
            ['encounter' => 2, 'id' => '32', 'dos' => '2026-07-01', 'lines' => [], 'amount' => '60.00'],
            'not an entry',
        ]];
        $stmt = ['pid' => 7, 'lines' => [], 'excluded' => $kept];

        $this->assertSame([31], array_column(StatementLines::excluded($stmt), 'id'));
    }

    public function testAnotherPatientsExclusionsDoNotCarryOver(): void
    {
        // The statement run reuses one array: what was left off patient 7's statement
        // says nothing about patient 8's.
        $stmt = ['pid' => '8', 'lines' => [], 'excluded' => self::kept(7, [31])];

        $this->assertSame([], StatementLines::excluded($stmt));
    }

    /**
     * @param list<int> $ids
     * @return array{pid: int, encounters: list<array<string, mixed>>}
     */
    private static function kept(int $pid, array $ids): array
    {
        return [
            'pid' => $pid,
            'encounters' => array_map(
                static fn (int $id): array => ['encounter' => $id - 30, 'id' => $id, 'dos' => '2026-07-01', 'lines' => [], 'amount' => '60.00'],
                $ids
            ),
        ];
    }
}
