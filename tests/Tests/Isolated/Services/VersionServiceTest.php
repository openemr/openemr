<?php

/**
 * VersionServiceTest - Unit tests for VersionService.asString()
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Allen <craigrallen@gmail.com>
 * @copyright Copyright (c) 2026 Craig Allen <craigrallen@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\VersionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VersionServiceTest extends TestCase
{
    private OEGlobalsBag $bag;

    protected function setUp(): void
    {
        $this->bag = OEGlobalsBag::getInstance();
    }

    private function setVersionGlobals(
        string $major,
        string $minor,
        string $patch,
        string $tag = '',
        string $realpatch = ''
    ): void {
        $this->bag->set('v_major', $major);
        $this->bag->set('v_minor', $minor);
        $this->bag->set('v_patch', $patch);
        $this->bag->set('v_tag', $tag);
        $this->bag->set('v_realpatch', $realpatch);
    }

    #[Test]
    public function testAsStringReturnsBasicVersion(): void
    {
        $this->setVersionGlobals('8', '0', '1');

        $service = new VersionService();
        $result = $this->service->getSoftwareVersion(false, false);

        $this->assertSame('8.0.1', $result);
    }

    #[Test]
    public function testAsStringIncludesTagWhenPresent(): void
    {
        $this->setVersionGlobals('8', '0', '1', '-rc1');

        $service = new VersionService();
        $result = $this->service->getSoftwareVersion(true, false);

        $this->assertSame('8.0.1-rc1', $result);
    }

    #[Test]
    public function testAsStringOmitsTagWhenFlagFalse(): void
    {
        $this->setVersionGlobals('8', '0', '1', '-rc1');

        $service = new VersionService();
        $result = $this->service->getSoftwareVersion(false, false);

        $this->assertSame('8.0.1', $result);
    }

    #[Test]
    public function testAsStringIncludesRealPatchWhenPresent(): void
    {
        $this->setVersionGlobals('8', '0', '1', '', '1.0.1.1');

        $service = new VersionService();
        $result = $this->service->getSoftwareVersion(false, true);

        $this->assertSame('8.0.1 (1.0.1.1)', $result);
    }

    #[Test]
    public function testAsStringOmitsRealPatchWhenFlagFalse(): void
    {
        $this->setVersionGlobals('8', '0', '1', '', '1.0.1.1');

        $service = new VersionService();
        $result = $this->service->getSoftwareVersion(false, false);

        $this->assertSame('8.0.1', $result);
    }

    #[Test]
    public function testAsStringOmitsRealPatchWhenEmpty(): void
    {
        $this->setVersionGlobals('8', '0', '1', '', '');

        $service = new VersionService();
        $result = $this->service->getSoftwareVersion(false, true);

        $this->assertSame('8.0.1', $result);
    }

    #[Test]
    public function testAsStringWithAllComponentsDefault(): void
    {
        $this->setVersionGlobals('8', '0', '1', '-dev', '1.0.1.2');

        $service = new VersionService();
        $result = $this->service->getSoftwareVersion();

        $this->assertSame('8.0.1-dev (1.0.1.2)', $result);
    }

    #[Test]
    public function testAsStringDelegatesToGetSoftwareVersion(): void
    {
        $this->setVersionGlobals('8', '0', '1', '-rc1', '1.0.1.1');

        $service = new VersionService();
        // asString() is deprecated but must continue to delegate correctly
        $this->assertSame($service->getSoftwareVersion(true, true), $service->asString(true, true));
        $this->assertSame($service->getSoftwareVersion(false, false), $service->asString(false, false));
    }
}

