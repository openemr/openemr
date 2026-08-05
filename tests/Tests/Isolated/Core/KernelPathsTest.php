<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 *
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core;

use OpenEMR\Core\Kernel;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('core')]
class KernelPathsTest extends TestCase
{
    private Kernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new Kernel('/var/www/openemr', '/openemr');
    }

    // ---- Web paths --------------------------------------------------------

    public function testGetWebRoot(): void
    {
        $this->assertSame('/openemr', $this->kernel->getWebRoot());
    }

    public function testGetWebRootEmpty(): void
    {
        $kernel = new Kernel('/var/www/openemr', '');
        $this->assertSame('', $kernel->getWebRoot());
    }

    public function testGetRootDir(): void
    {
        $this->assertSame('/openemr/interface', $this->kernel->getRootDir());
    }

    public function testGetAssetsRelative(): void
    {
        $this->assertSame('/openemr/public/assets', $this->kernel->getAssetsRelative());
    }

    public function testGetThemesRelative(): void
    {
        $this->assertSame('/openemr/public/themes', $this->kernel->getThemesRelative());
    }

    public function testGetImagesRelative(): void
    {
        $this->assertSame('/openemr/public/images', $this->kernel->getImagesRelative());
    }

    // ---- Filesystem paths -------------------------------------------------

    public function testGetProjectDir(): void
    {
        $this->assertSame('/var/www/openemr', $this->kernel->getProjectDir());
    }

    public function testGetSrcDir(): void
    {
        $this->assertSame('/var/www/openemr/library', $this->kernel->getSrcDir());
    }

    public function testGetIncludeRoot(): void
    {
        $this->assertSame('/var/www/openemr/interface', $this->kernel->getIncludeRoot());
    }

    public function testGetVendorDir(): void
    {
        $this->assertSame('/var/www/openemr/vendor', $this->kernel->getVendorDir());
    }

    public function testGetTemplateDir(): void
    {
        $this->assertSame('/var/www/openemr/templates/', $this->kernel->getTemplateDir());
    }

    public function testGetImagesAbsolute(): void
    {
        $this->assertSame('/var/www/openemr/public/images', $this->kernel->getImagesAbsolute());
    }

    public function testGetSitesBase(): void
    {
        $this->assertSame('/var/www/openemr/sites', $this->kernel->getSitesBase());
    }

    // ---- Site-specific paths ----------------------------------------------

    public function testGetSiteDir(): void
    {
        $this->assertSame('/var/www/openemr/sites/default', $this->kernel->getSiteDir('default'));
    }

    public function testGetSiteWebRoot(): void
    {
        $this->assertSame('/openemr/sites/default', $this->kernel->getSiteWebRoot('default'));
    }

    // ---- RuntimeException when paths not provided -------------------------

    public function testGetProjectDirThrowsWhenNull(): void
    {
        $kernel = new Kernel();
        $this->expectException(\RuntimeException::class);
        $kernel->getProjectDir();
    }

    public function testGetWebRootThrowsWhenNull(): void
    {
        $kernel = new Kernel();
        $this->expectException(\RuntimeException::class);
        $kernel->getWebRoot();
    }

    // ---- Backward compat: dispatcher-only construction --------------------

    public function testConstructWithDispatcherOnly(): void
    {
        $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();
        $kernel = new Kernel(dispatcher: $dispatcher);
        $this->assertSame($dispatcher, $kernel->getEventDispatcher());
    }
}
