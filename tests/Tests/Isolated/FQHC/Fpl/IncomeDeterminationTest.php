<?php

/**
 * Isolated tests for the income determination value object.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Fpl;

use DomainException;
use OpenEMR\FQHC\Fpl\IncomeDetermination;
use PHPUnit\Framework\TestCase;

final class IncomeDeterminationTest extends TestCase
{
    public function testRejectsHouseholdSizeBelowOne(): void
    {
        $this->expectException(DomainException::class);
        new IncomeDetermination(0, 10000.0);
    }

    public function testRejectsNegativeIncome(): void
    {
        $this->expectException(DomainException::class);
        new IncomeDetermination(2, -1.0);
    }

    public function testDeterminableWithBothValues(): void
    {
        self::assertTrue((new IncomeDetermination(2, 10000.0))->isDeterminable());
    }

    public function testNotDeterminableWhenDeclined(): void
    {
        self::assertFalse((new IncomeDetermination(2, 10000.0, unknown: true))->isDeterminable());
    }

    public function testNotDeterminableWhenMissingAValue(): void
    {
        self::assertFalse((new IncomeDetermination(null, 10000.0))->isDeterminable());
        self::assertFalse((new IncomeDetermination(2, null))->isDeterminable());
    }
}
