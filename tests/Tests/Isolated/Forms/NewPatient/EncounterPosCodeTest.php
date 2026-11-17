<?php

/**
 * The POS the encounter form preselects.
 *
 * A new encounter preselected nothing, so the form posted the first option,
 * 01 (Pharmacy), and claims went out with POS 01 instead of the facility's.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\NewPatient;

use OpenEMR\Forms\NewPatient\EncounterPosCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EncounterPosCodeTest extends TestCase
{
    /**
     * @return array<string, array{bool, mixed, mixed, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function posProvider(): array
    {
        return [
            'new encounter takes the facility POS' => [false, null, '31', '31'],
            'new encounter, facility POS as int' => [false, null, 11, '11'],
            'new encounter ignores a leftover saved code' => [false, '22', '11', '11'],
            'existing encounter keeps its saved POS' => [true, '22', '11', '22'],
            'existing encounter saved as int' => [true, 22, '11', '22'],
            'existing encounter with no saved POS takes the facility POS' => [true, null, '11', '11'],
            'existing encounter with POS 0 takes the facility POS' => [true, '0', '11', '11'],
            'no facility POS selects nothing' => [false, null, '', null],
            'nothing anywhere selects nothing' => [true, '', null, null],
        ];
    }

    #[DataProvider('posProvider')]
    public function testToSelect(bool $existingEncounter, mixed $saved, mixed $facility, ?string $expected): void
    {
        $this->assertSame($expected, EncounterPosCode::toSelect($existingEncounter, $saved, $facility));
    }
}
