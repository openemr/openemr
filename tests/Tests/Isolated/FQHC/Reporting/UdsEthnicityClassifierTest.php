<?php

/**
 * Isolated tests for the OpenEMR ethnicity → UDS ethnicity category classifier.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Claude Code
 * @copyright Copyright (c) 2026 OpenEMR FQHC project
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\FQHC\Reporting;

use OpenEMR\FQHC\Reporting\UdsEthnicityCategory;
use OpenEMR\FQHC\Reporting\UdsEthnicityClassifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UdsEthnicityClassifierTest extends TestCase
{
    #[DataProvider('ethnicityProvider')]
    public function testClassify(?string $optionId, UdsEthnicityCategory $expected): void
    {
        self::assertSame($expected, (new UdsEthnicityClassifier())->classify($optionId));
    }

    /**
     * @return array<string, array{?string, UdsEthnicityCategory}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ethnicityProvider(): array
    {
        return [
            'hispanic maps to combined column' => ['hisp_or_latin', UdsEthnicityCategory::Combined],
            'not hispanic' => ['not_hisp_or_latin', UdsEthnicityCategory::NotHispanic],
            'declined is unreported' => ['decline_to_specify', UdsEthnicityCategory::Unreported],
            'null is unreported' => [null, UdsEthnicityCategory::Unreported],
            'unrecognized is unreported' => ['martian', UdsEthnicityCategory::Unreported],
        ];
    }
}
