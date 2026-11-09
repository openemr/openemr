<?php

/**
 * Isolated tests for LabDocumentQuery
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Apostolos Stergiakis
 * @copyright Copyright (c) 2026 Apostolos Stergiakis
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\LabDocumentQuery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LabDocumentQueryTest extends TestCase
{
    /**
     * @return array<string, array{?string, ?string, list<string>, list<string|int>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function dateRangeProvider(): array
    {
        return [
            'both dates' => [
                '2026-10-01', '2026-10-31',
                ['d.deleted = 0', 'DATE(d.date) >= ?', 'DATE(d.date) <= ?'],
                [7, '2026-10-01', '2026-10-31'],
            ],
            'from only' => ['2026-10-01', null, ['d.deleted = 0', 'DATE(d.date) >= ?'], [7, '2026-10-01']],
            'to only' => [null, '2026-10-31', ['d.deleted = 0', 'DATE(d.date) <= ?'], [7, '2026-10-31']],
            'neither' => [null, null, ['d.deleted = 0'], [7]],
        ];
    }

    /**
     * @param list<string> $expectedConditions
     * @param list<string|int> $expectedBinds
     */
    #[DataProvider('dateRangeProvider')]
    public function testBuildsValidWhereClauseForEveryDateCombination(
        ?string $from,
        ?string $to,
        array $expectedConditions,
        array $expectedBinds,
    ): void {
        $query = (new LabDocumentQuery())->build(7, $from, $to);

        $this->assertSame($expectedBinds, $query['binds']);
        $this->assertCount(substr_count($query['sql'], '?'), $query['binds'], 'every placeholder has a bound value');
        $this->assertDoesNotMatchRegularExpression('/WHERE\s+(AND\b|GROUP\b)/', $query['sql']);
        foreach ($expectedConditions as $condition) {
            $this->assertStringContainsString($condition, $query['sql']);
        }
        $this->assertStringContainsString('WHERE d.deleted = 0', $query['sql'], 'deleted documents are never listed');
    }
}
