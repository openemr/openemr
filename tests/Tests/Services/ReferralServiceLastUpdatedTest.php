<?php

/**
 * Which _lastUpdated searches narrow the referrals before they're grouped: ReferralService (#15137).
 * It isn't an isolated test because loading BaseService queries the database.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use OpenEMR\Services\ReferralService;
use OpenEMR\Services\Search\DateSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReferralServiceLastUpdatedTest extends TestCase
{
    /**
     * @return array<string, array{list<string>, bool, int}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function lowerBounds(): array
    {
        // the search's values, whether all must hold => how many lower bounds narrow it
        return [
            'gt' => [['gt2026-01-01'], false, 1],
            'ge' => [['ge2026-01-01'], false, 1],
            'sa' => [['sa2026-01-01'], false, 1],
            'a range, by its lower bound' => [['ge2026-01-01', 'lt2026-06-01'], true, 1],
            'two lower bounds' => [['gt2026-01-01', 'gt2026-03-01'], true, 2],
        ];
    }

    /**
     * @param list<string> $values
     */
    #[DataProvider('lowerBounds')]
    public function testALowerBoundNarrowsByEitherDate(array $values, bool $isAnd, int $bounds): void
    {
        $fragment = ReferralService::lastUpdatedAtSource(new DateSearchField('last_modified', $values, DateSearchField::DATE_TYPE_DATETIME, $isAnd));

        $this->assertNotNull($fragment);
        $this->assertStringContainsString('t.date', $fragment->getFragment());
        $this->assertStringContainsString('task.last_modified', $fragment->getFragment());
        $this->assertCount(2 * $bounds, $fragment->getBoundValues(), 'each bound on each date');
    }

    /**
     * @return array<string, array{list<string>, bool}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function otherSearches(): array
    {
        return [
            'an upper bound' => [['lt2026-01-01'], false],
            'an exact date' => [['eq2026-01-01'], false],
            'alternatives' => [['gt2026-01-01', 'lt2020-01-01'], false],
        ];
    }

    /**
     * @param list<string> $values
     */
    #[DataProvider('otherSearches')]
    public function testAnythingElseLeavesItToTheGroupedFilter(array $values, bool $isAnd): void
    {
        $this->assertNull(ReferralService::lastUpdatedAtSource(new DateSearchField('last_modified', $values, DateSearchField::DATE_TYPE_DATETIME, $isAnd)));
    }

    public function testAnotherFieldDoesNotNarrow(): void
    {
        $this->assertNull(ReferralService::lastUpdatedAtSource(new DateSearchField('authored', ['gt2026-01-01'])));
        $this->assertNull(ReferralService::lastUpdatedAtSource(new TokenSearchField('last_modified', ['x'])));
    }
}
