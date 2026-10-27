<?php

/**
 * Isolated tests for the eye exam's base drawing lookup: a site's own
 * drawing when it has one, the form's stock drawing otherwise.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Forms\EyeMag\BaseDrawing;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BaseDrawingTest extends TestCase
{
    private string $siteDir;

    protected function setUp(): void
    {
        $this->siteDir = sys_get_temp_dir() . '/eye-base-drawing-' . bin2hex(random_bytes(4));
        mkdir($this->siteDir . BaseDrawing::SITE_DIRECTORY, 0777, true);
        touch($this->siteDir . BaseDrawing::SITE_DIRECTORY . 'OU_RETINA_BASE.jpg');
    }

    protected function tearDown(): void
    {
        unlink($this->siteDir . BaseDrawing::SITE_DIRECTORY . 'OU_RETINA_BASE.jpg');
        rmdir($this->siteDir . BaseDrawing::SITE_DIRECTORY);
        rmdir($this->siteDir . '/images');
        rmdir($this->siteDir);
    }

    private function drawing(): BaseDrawing
    {
        return new BaseDrawing($this->siteDir, '/openemr/sites/eyes', '/openemr');
    }

    #[Test]
    public function aSiteDrawingIsUsedWhenTheSiteHasOne(): void
    {
        $this->assertSame(
            '/openemr/sites/eyes/images/eye_mag/OU_RETINA_BASE.jpg',
            $this->drawing()->url('OU', 'RETINA')
        );
    }

    #[Test]
    public function otherZonesKeepTheStockDrawing(): void
    {
        $this->assertSame(
            '/openemr/interface/forms/eye_mag/images/OU_NEURO_BASE.jpg',
            $this->drawing()->url('OU', 'NEURO')
        );
    }

    #[Test]
    public function aSiteWithoutDrawingsKeepsTheStockOnes(): void
    {
        $drawing = new BaseDrawing('', '/openemr/sites/default', '/openemr');
        $this->assertSame(
            '/openemr/interface/forms/eye_mag/images/OU_RETINA_BASE.jpg',
            $drawing->url('OU', 'RETINA')
        );
    }

    #[Test]
    public function onlyPlainZoneNamesAreLookedUpOnDisk(): void
    {
        $this->assertSame(
            '/openemr/interface/forms/eye_mag/images/OU_../RETINA_BASE.jpg',
            $this->drawing()->url('OU', '../RETINA')
        );
    }
}
