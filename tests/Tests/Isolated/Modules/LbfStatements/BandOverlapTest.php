<?php

/**
 * BandOverlap tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    $moduleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-lbf-statements/src/';
    if (!is_dir($moduleSrc)) {
        throw new RuntimeException('LBF statements module source not found at ' . $moduleSrc);
    }
    spl_autoload_register(static function (string $class) use ($moduleSrc): void {
        $prefix = 'OpenEMR\\Modules\\LbfStatements\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $moduleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\LbfStatements {

    use OpenEMR\Modules\LbfStatements\BandOverlap;
    use PHPUnit\Framework\TestCase;

    final class BandOverlapTest extends TestCase
    {
        /**
         * Inclusive bands that share an endpoint overlap.
         */
        public function testInclusiveTouchOverlaps(): void
        {
            $this->assertTrue(BandOverlap::rangesOverlap(
                ['min_value' => 1, 'max_value' => 2, 'min_inclusive' => 1, 'max_inclusive' => 1],
                ['min_value' => 2, 'max_value' => 3, 'min_inclusive' => 1, 'max_inclusive' => 1]
            ));
        }

        /**
         * Exclusive bands that only touch do not overlap.
         */
        public function testExclusiveTouchDoesNotOverlap(): void
        {
            $this->assertFalse(BandOverlap::rangesOverlap(
                ['min_value' => 1, 'max_value' => 2, 'min_inclusive' => 1, 'max_inclusive' => 0],
                ['min_value' => 2, 'max_value' => 3, 'min_inclusive' => 1, 'max_inclusive' => 1]
            ));
        }

        /**
         * Bands with a gap between them do not overlap.
         */
        public function testGapDoesNotOverlap(): void
        {
            $this->assertFalse(BandOverlap::rangesOverlap(
                ['min_value' => 1.9, 'max_value' => 4.0, 'min_inclusive' => 1, 'max_inclusive' => 1],
                ['min_value' => 4.1, 'max_value' => 4.5, 'min_inclusive' => 1, 'max_inclusive' => 1]
            ));
        }

        /**
         * An open-ended band overlaps a finite neighbor.
         */
        public function testOpenEndedOverlaps(): void
        {
            $this->assertTrue(BandOverlap::rangesOverlap(
                ['min_value' => 50, 'max_value' => null, 'min_inclusive' => 1, 'max_inclusive' => 1],
                ['min_value' => 40, 'max_value' => 50, 'min_inclusive' => 0, 'max_inclusive' => 1]
            ));
        }

        /**
         * An exclusive open upper bound does not overlap at the edge.
         */
        public function testOpenUpperExclusiveDoesNotOverlapAtBoundary(): void
        {
            $normal = ['min_value' => null, 'max_value' => 40, 'min_inclusive' => 1, 'max_inclusive' => 0];
            $moderate = ['min_value' => 40, 'max_value' => 70, 'min_inclusive' => 1, 'max_inclusive' => 1];
            $severe = ['min_value' => 70, 'max_value' => null, 'min_inclusive' => 0, 'max_inclusive' => 1];
            $this->assertFalse(BandOverlap::rangesOverlap($normal, $moderate));
            $this->assertFalse(BandOverlap::rangesOverlap($moderate, $severe));
            $this->assertFalse(BandOverlap::rangesOverlap($normal, $severe));
        }

        /**
         * Detect a minimum that is greater than the maximum.
         */
        public function testInvertedBounds(): void
        {
            $this->assertTrue(BandOverlap::invertedBounds(['min_value' => 10, 'max_value' => 1]));
            $this->assertFalse(BandOverlap::invertedBounds(['min_value' => 1, 'max_value' => 10]));
            $this->assertFalse(BandOverlap::invertedBounds(['min_value' => 5, 'max_value' => null]));
            $this->assertFalse(BandOverlap::invertedBounds([
                'min_value' => 5,
                'max_value' => 5,
                'min_inclusive' => 1,
                'max_inclusive' => 1,
            ]));
            $this->assertTrue(BandOverlap::invertedBounds([
                'min_value' => 5,
                'max_value' => 5,
                'min_inclusive' => 0,
                'max_inclusive' => 1,
            ]));
        }
    }
}
