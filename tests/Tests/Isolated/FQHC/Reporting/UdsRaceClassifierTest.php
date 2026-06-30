<?php

/**
 * Isolated tests for the OpenEMR race → UDS race category classifier.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\UdsRaceCategory;
use OpenEMR\FQHC\Reporting\UdsRaceClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UdsRaceClassifierTest extends TestCase
{
    #[DataProvider('raceProvider')]
    public function testClassify(?string $optionId, UdsRaceCategory $expected): void
    {
        self::assertSame($expected, (new UdsRaceClassifier())->classify($optionId));
    }

    /**
     * Option ids confirmed against sql/database.sql list_id='race'.
     *
     * @return array<string, array{?string, UdsRaceCategory}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function raceProvider(): array
    {
        return [
            'asian indian subtype' => ['asian_indian', UdsRaceCategory::AsianIndian],
            'chinese subtype' => ['chinese', UdsRaceCategory::Chinese],
            'vietnamese subtype' => ['vietnamese', UdsRaceCategory::Vietnamese],
            'generic asian rolls to other asian' => ['Asian', UdsRaceCategory::OtherAsian],
            'native hawaiian subtype' => ['native_hawaiian', UdsRaceCategory::NativeHawaiian],
            'samoan subtype' => ['samoan', UdsRaceCategory::Samoan],
            'guamanian subtype' => ['guamanian_or_chamorro', UdsRaceCategory::GuamanianOrChamorro],
            'generic nhopi rolls to other pacific islander' => ['native_hawai_or_pac_island', UdsRaceCategory::OtherPacificIslander],
            'black' => ['black_or_afri_amer', UdsRaceCategory::BlackOrAfricanAmerican],
            'american indian / alaska native' => ['amer_ind_or_alaska_native', UdsRaceCategory::AmericanIndianAlaskaNative],
            'white' => ['white', UdsRaceCategory::White],
            'declined is unreported' => ['decline_to_specify', UdsRaceCategory::Unreported],
            'null is unreported' => [null, UdsRaceCategory::Unreported],
            'unrecognized code is unreported' => ['klingon', UdsRaceCategory::Unreported],
        ];
    }
}
