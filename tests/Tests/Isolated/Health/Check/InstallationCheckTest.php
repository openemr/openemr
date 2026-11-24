<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Eric Stern <erics@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR <https://opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Health\Check;

use OpenEMR\BC\SiteInstallState;
use OpenEMR\Health\Check\InstallationCheck;
use PHPUnit\Framework\TestCase;

class InstallationCheckTest extends TestCase
{
    public function testInstalledSiteIsHealthy(): void
    {
        $result = (new InstallationCheck(new SiteInstallState(true)))->check();

        self::assertSame(InstallationCheck::NAME, $result->name, 'readyz reports this check under its name');
        self::assertTrue($result->healthy, 'an installed site should pass');
    }

    public function testUninstalledSiteIsUnhealthy(): void
    {
        $result = (new InstallationCheck(new SiteInstallState(false)))->check();

        self::assertFalse($result->healthy, 'a site needing setup should fail');
        self::assertSame('OpenEMR setup required', $result->message, 'the failure should say what is needed');
    }
}
