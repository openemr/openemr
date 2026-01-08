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
    public function testGlobalsBagInit(): void
    {
        $key = 'dummy-key';
        $value = 'dummy-value';
        $values = [$key => $value];

        $bag = new OEGlobalsBag($values);
        $this->assertTrue($bag->has($key));
        $this->assertSame($value, $bag->get($key));

        $this->assertArrayNotHasKey($key, $GLOBALS);
    }

    public function testGlobalsBagPushesIntoGlobalsOnSet(): void
    {
        $key = 'dummy-key';
        $value = 'dummy-value';

        $globalsBag = new OEGlobalsBag([]);
        $this->assertFalse($globalsBag->has($key));
        $this->assertArrayNotHasKey($key, $GLOBALS);

        $globalsBag->set($key, $value);
        $this->assertTrue($globalsBag->has($key));
        $this->assertSame($value, $globalsBag->get($key));

        $this->assertArrayHasKey($key, $GLOBALS);
        $this->assertSame($value, $GLOBALS[$key]);
    }
}
