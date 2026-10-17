<?php

/**
 * SearchFieldComparableValue Isolated Test
 *
 * __toString() used `||` instead of `??`, which turned the value into "1",
 * and an operator precedence slip dropped the closing parenthesis.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Search;

use OpenEMR\Services\Search\SearchComparator;
use OpenEMR\Services\Search\SearchFieldComparableValue;
use PHPUnit\Framework\TestCase;

class SearchFieldComparableValueTest extends TestCase
{
    public function testToStringShowsTheValueAndComparator(): void
    {
        $value = new SearchFieldComparableValue('2020-01-01', SearchComparator::GREATER_THAN);

        $this->assertSame('(value=2020-01-01,comparator=gt)', (string) $value);
    }

    public function testToStringWithNullValue(): void
    {
        $value = new SearchFieldComparableValue(null);

        $this->assertSame('(value=,comparator=eq)', (string) $value);
    }

    public function testToStringUsesTheClassNameForObjectsWithoutToString(): void
    {
        $value = new SearchFieldComparableValue(new \DateTimeImmutable('2020-01-01'));

        $this->assertSame('(value=DateTimeImmutable,comparator=eq)', (string) $value);
    }
}
