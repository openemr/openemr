<?php

/**
 * InsuranceLevel: the level of a patient's insurance policy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Insurance;

use OpenEMR\Services\Insurance\InsuranceLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class InsuranceLevelTest extends TestCase
{
    /**
     * Each level's number is the one HL7 IN1-22 sends, in the order the levels are declared.
     */
    #[Test]
    public function eachLevelHasItsNumber(): void
    {
        self::assertSame(1, InsuranceLevel::Primary->number());
        self::assertSame(2, InsuranceLevel::Secondary->number());
        self::assertSame(3, InsuranceLevel::Tertiary->number());
        self::assertSame(
            [1, 2, 3],
            array_map(static fn(InsuranceLevel $level): int => $level->number(), InsuranceLevel::cases())
        );
    }
}
