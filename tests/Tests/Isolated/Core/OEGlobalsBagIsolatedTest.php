<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Core;

use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('core')]
#[CoversClass(OEGlobalsBag::class)]
#[CoversMethod(OEGlobalsBag::class, 'set')]
class OEGlobalsBagIsolatedTest extends TestCase
{
    #[Test]
    public function compatabilityModeOffTest(): void
    {
        $globalsBag = new OEGlobalsBag([], false);
        $globalsBag->set('dummy-key', 'dummy-value');

        $this->assertArrayNotHasKey('dummy-key', $GLOBALS);
    }

    #[Test]
    public function compatabilityModeOnTest(): void
    {
        $globalsBag = new OEGlobalsBag([], true);
        $globalsBag->set('dummy-key', 'dummy-value');

        $this->assertArrayHasKey('dummy-key', $GLOBALS);
        $this->assertEquals('dummy-value', $GLOBALS['dummy-key']);
    }
}
