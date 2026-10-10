<?php

/**
 * CqmCalculator reports a measure the engine cannot calculate as a
 * MeasureCalculationException naming the measure, so QRDA exports can tell
 * the user which measure to leave out instead of failing with the engine's
 * internal error.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Qdm;

use OpenEMR\Cqm\Qdm\Patient;
use OpenEMR\Services\Qdm\CqmCalculator;
use OpenEMR\Services\Qdm\Measure;
use OpenEMR\Services\Qdm\MeasureCalculationException;
use OpenEMR\Services\Qdm\MeasureService;
use PHPUnit\Framework\TestCase;

class CqmCalculatorTest extends TestCase
{
    private const MEASURES = __DIR__ . '/../../../../../vendor/openemr/oe-cqm-parsers/2025_reporting_period/json_measures/';

    public function testAMeasureTheEngineCannotRunNamesTheMeasure(): void
    {
        // CMS871v4 calls a function with arguments no overload accepts; cqm-execution fails on it too
        try {
            (new CqmCalculator())->calculateMeasure([self::patient()], self::measure('CMS871v4'), '2025-01-01 00:00:00');
            $this->fail('CMS871v4 calculated although the engine cannot run it');
        } catch (MeasureCalculationException $e) {
            $this->assertSame('CMS871v4', $e->measureId);
            $this->assertSame('Measure CMS871v4 cannot be calculated by the eCQM calculation engine', $e->getMessage());
            $this->assertInstanceOf(\UnexpectedValueException::class, $e->getPrevious());
        }
    }

    public function testASupportedMeasureCalculates(): void
    {
        $results = (new CqmCalculator())->calculateMeasure([self::patient()], self::measure('CMS122v13'), '2025-01-01 00:00:00');

        $this->assertCount(1, $results);
        $populationSets = reset($results);
        $this->assertIsArray($populationSets);
        $this->assertSame(0, $populationSets['PopulationSet_1']['IPP'] ?? null);
    }

    private static function measure(string $name): Measure
    {
        $path = self::MEASURES . $name;
        $measure = new Measure(MeasureService::fetchMeasureJson($path));
        $measure->measure_path = $path;
        return $measure;
    }

    private static function patient(): Patient
    {
        return new Patient(['birthDatetime' => '1950-01-01 00:00:00']);
    }
}
