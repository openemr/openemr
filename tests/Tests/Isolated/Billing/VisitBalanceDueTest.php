<?php

/**
 * Isolated tests for VisitBalanceDue: whether checkout and the portal payment
 * page treat a visit's balance as the patient's.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing;

use OpenEMR\Billing\VisitBalanceDue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class VisitBalanceDueTest extends TestCase
{
    /**
     * @return array<string, array{mixed, mixed, bool}>
     * @codeCoverageIgnore
     */
    public static function visits(): array
    {
        return [
            'no insurance on file' => [0, 0, true],
            'primary only, not closed' => [1, 0, false],
            'primary only, closed' => [1, 1, true],
            'primary and secondary, primary closed' => [2, 1, false],
            'primary and secondary, both closed' => [2, 2, true],
            'three levels, two closed' => [3, 2, false],
            'three levels, all closed' => [3, 3, true],
            'paid as cash' => [2, VisitBalanceDue::CASH_VISIT, true],
            // A gap in the levels on file: one level, a secondary, which closes as level 2.
            'sole secondary closed as level 2' => [1, 2, true],
            'sole secondary closed as level 1' => [1, 1, true],
            // Primary and tertiary on file; the tertiary closes as level 3.
            'primary and tertiary, tertiary closed' => [2, 3, true],
            // Values as the queries return them: COUNT() and an INT column as strings.
            'strings, not closed' => ['2', '1', false],
            'strings, closed' => ['2', '2', true],
            'string count, int level past it' => ['1', 2, true],
            // A deleted payment can step last_level_closed below zero.
            'below zero, insurance on file' => [1, -1, false],
            'below zero, no insurance' => [0, -1, true],
            'no level recorded, insurance on file' => [1, null, false],
            'no level recorded, no insurance' => [0, null, true],
        ];
    }

    #[DataProvider('visits')]
    public function testFromPatient(mixed $levelsOnFile, mixed $lastLevelClosed, bool $expected): void
    {
        $this->assertSame($expected, VisitBalanceDue::fromPatient($levelsOnFile, $lastLevelClosed));
    }
}
