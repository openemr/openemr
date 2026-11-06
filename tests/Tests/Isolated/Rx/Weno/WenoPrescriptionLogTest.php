<?php

/**
 * The WENO prescription log import keeps the substitution column's meaning.
 *
 * The import used `$line[14] = 'Allowed' ? 1 : 0`, an assignment, so every imported
 * prescription was stored as "substitution allowed". substituteValue() maps the column to
 * the prescriptions.substitute codes (1 allowed, 2 not allowed, 0 not stated).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    James DuRant <jdurant@ad.unc.edu>
 * @copyright Copyright (c) 2026 James DuRant <jdurant@ad.unc.edu>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Weno;

use OpenEMR\Rx\Weno\WenoPrescriptionLog;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('weno')]
class WenoPrescriptionLogTest extends TestCase
{
    /**
     * @return array<string, array{mixed, int}>
     */
    public static function columns(): array
    {
        return [
            'allowed' => ['Allowed', 1],
            'allowed, other case and spacing' => [' allowed ', 1],
            'not allowed' => ['Not Allowed', 2],
            'dispense as written' => ['DAW', 2],
            'empty' => ['', 0],
            'missing column' => [null, 0],
            'not a scalar' => [['Allowed'], 0],
        ];
    }

    #[Test]
    #[DataProvider('columns')]
    public function substituteFollowsTheLogColumn(mixed $column, int $expected): void
    {
        $this->assertSame($expected, WenoPrescriptionLog::substituteValue($column));
    }
}
