<?php

/**
 * Isolated tests for PopulationSelectors trait
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services\Qrda\Helpers;

use OpenEMR\Services\Qrda\Helpers\PopulationSelectors;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Concrete class to host the PopulationSelectors trait for testing.
 */
class PopulationSelectorsHost
{
    use PopulationSelectors;

    /** @param list<object> $populations */
    public function __construct(public array $populations)
    {
    }
}

#[CoversClass(PopulationSelectors::class)]
#[Group('isolated')]
class PopulationSelectorsTest extends TestCase
{
    /** @param list<object> $populations */
    private function makeHost(array $populations): PopulationSelectorsHost
    {
        return new PopulationSelectorsHost($populations);
    }

    private function pop(string $type, string $id = '', int $value = 0): object
    {
        return (object) ['type' => $type, 'id' => $id, 'value' => $value];
    }

    public function testNumeratorReturnsTrueWhenPresent(): void
    {
        $host = $this->makeHost([$this->pop('NUMER')]);
        $this->assertTrue($host->numerator());
    }

    public function testNumeratorReturnsFalseWhenAbsent(): void
    {
        $host = $this->makeHost([$this->pop('DENOM')]);
        $this->assertFalse($host->numerator());
    }

    public function testDenominatorReturnsTrueWhenPresent(): void
    {
        $host = $this->makeHost([$this->pop('DENOM')]);
        $this->assertTrue($host->denominator());
    }

    public function testDenominatorReturnsFalseWhenAbsent(): void
    {
        $host = $this->makeHost([]);
        $this->assertFalse($host->denominator());
    }

    public function testDenominatorExceptionsReturnsTrueWhenPresent(): void
    {
        $host = $this->makeHost([$this->pop('DENEXCEP')]);
        $this->assertTrue($host->denominator_exceptions());
    }

    public function testDenominatorExceptionsReturnsFalseWhenAbsent(): void
    {
        $host = $this->makeHost([$this->pop('NUMER')]);
        $this->assertFalse($host->denominator_exceptions());
    }

    public function testDenominatorExclusionsReturnsTrueWhenPresent(): void
    {
        $host = $this->makeHost([$this->pop('DENEX')]);
        $this->assertTrue($host->denominator_exclusions());
    }

    public function testDenominatorExclusionsReturnsFalseWhenAbsent(): void
    {
        $host = $this->makeHost([$this->pop('NUMER')]);
        $this->assertFalse($host->denominator_exclusions());
    }

    public function testPopulationCountReturnsValue(): void
    {
        $host = $this->makeHost([
            $this->pop('NUMER', 'n1', 42),
            $this->pop('DENOM', 'd1', 100),
        ]);

        $this->assertSame(42, $host->population_count('NUMER', 'n1'));
        $this->assertSame(100, $host->population_count('DENOM', 'd1'));
    }

    public function testPopulationCountReturnsZeroWhenNotFound(): void
    {
        $host = $this->makeHost([$this->pop('NUMER', 'n1', 42)]);
        $this->assertSame(0, $host->population_count('NUMER', 'wrong-id'));
    }

    public function testPopulationIdReturnsId(): void
    {
        $host = $this->makeHost([$this->pop('NUMER', 'abc-123')]);
        $this->assertSame('abc-123', $host->population_id('NUMER'));
    }

    public function testPopulationIdReturnsFalseWhenNotFound(): void
    {
        $host = $this->makeHost([]);
        $this->assertFalse($host->population_id('NUMER'));
    }
}
