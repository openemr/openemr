<?php

/**
 * Isolated tests for the UDS clinical measure map.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting\Clinical;

use OpenEMR\FQHC\Reporting\Clinical\UdsClinicalMeasure;
use PHPUnit\Framework\TestCase;

final class UdsClinicalMeasureTest extends TestCase
{
    public function testEveryMeasureHasALabelAndACmsId(): void
    {
        foreach (UdsClinicalMeasure::cases() as $measure) {
            self::assertNotSame('', $measure->label());
            self::assertMatchesRegularExpression('/^CMS\d+v\d+$/', $measure->cmsId());
            self::assertSame($measure->value, $measure->cmsId());
        }
    }

    public function testCmsIdsAreUnique(): void
    {
        $ids = array_map(static fn (UdsClinicalMeasure $measure) => $measure->cmsId(), UdsClinicalMeasure::cases());

        self::assertCount(count($ids), array_unique($ids));
    }

    public function testOnlyControllingBloodPressureAndDiabetesGlycemicStatusFeedTable7(): void
    {
        $table7Measures = array_values(array_filter(
            UdsClinicalMeasure::cases(),
            static fn (UdsClinicalMeasure $measure) => $measure->feedsTable7(),
        ));

        self::assertEqualsCanonicalizing(
            [UdsClinicalMeasure::ControllingHighBloodPressure, UdsClinicalMeasure::DiabetesGlycemicStatusAssessment],
            $table7Measures,
        );
    }
}
