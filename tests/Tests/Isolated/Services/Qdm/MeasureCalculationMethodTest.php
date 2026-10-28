<?php

/**
 * Measure must pass the calculation method the measure bundle declares to the
 * calculator. It used to force EPISODE_OF_CARE on all but a few measures,
 * which changed the results of patient-based measures.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Services\Qdm\Measure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MeasureCalculationMethodTest extends TestCase
{
    #[DataProvider('measureProvider')]
    public function testMeasureKeepsTheBundleCalculationMethod(string $path): void
    {
        $json = file_get_contents($path);
        $this->assertIsString($json);
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('calculation_method', $data);

        $measure = new Measure($data);

        $this->assertSame($data['calculation_method'], $measure->calculation_method);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function measureProvider(): array
    {
        $root = dirname(__DIR__, 5) . '/vendor/openemr/oe-cqm-parsers';
        $cases = [];
        foreach (glob($root . '/*_reporting_period/json_measures/*/CMS*.json') ?: [] as $path) {
            $year = basename(dirname($path, 3));
            $cases[$year . ' ' . basename($path, '.json')] = [$path];
        }
        return $cases;
    }
}
