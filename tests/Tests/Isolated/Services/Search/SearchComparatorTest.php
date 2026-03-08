<?php

/**
 * SearchComparator Isolated Test
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\SearchComparator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SearchComparatorTest extends TestCase
{
    /**
     * @return iterable<string, array{string}>
     * @codeCoverageIgnore
     */
    public static function validComparatorProvider(): iterable
    {
        yield 'equals' => [SearchComparator::EQUALS];
        yield 'not equals' => [SearchComparator::NOT_EQUALS];
        yield 'greater than' => [SearchComparator::GREATER_THAN];
        yield 'less than' => [SearchComparator::LESS_THAN];
        yield 'greater than or equal' => [SearchComparator::GREATER_THAN_OR_EQUAL_TO];
        yield 'less than or equal' => [SearchComparator::LESS_THAN_OR_EQUAL_TO];
        yield 'starts after' => [SearchComparator::STARTS_AFTER];
        yield 'ends before' => [SearchComparator::ENDS_BEFORE];
    }

    /**
     * @return iterable<string, array{string}>
     * @codeCoverageIgnore
     */
    public static function invalidComparatorProvider(): iterable
    {
        yield 'empty string' => [''];
        yield 'random string' => ['foo'];
        yield 'uppercase EQ' => ['EQ'];
        yield 'approximately' => [SearchComparator::APROXIMATELY_SAME];
    }

    #[DataProvider('validComparatorProvider')]
    public function testIsValidComparatorWithValidValues(string $comparator): void
    {
        $this->assertTrue(SearchComparator::isValidComparator($comparator));
    }

    #[DataProvider('invalidComparatorProvider')]
    public function testIsValidComparatorWithInvalidValues(string $comparator): void
    {
        $this->assertFalse(SearchComparator::isValidComparator($comparator));
    }

    public function testAllComparatorsConstantMatchesValidComparators(): void
    {
        foreach (SearchComparator::ALL_COMPARATORS as $comparator) {
            $this->assertTrue(
                SearchComparator::isValidComparator($comparator),
                "ALL_COMPARATORS entry '{$comparator}' should be valid"
            );
        }
    }

    public function testApproximatelyIsNotInAllComparators(): void
    {
        // "ap" (approximately) is intentionally excluded from ALL_COMPARATORS
        $this->assertFalse(SearchComparator::isValidComparator(SearchComparator::APROXIMATELY_SAME));
    }
}
