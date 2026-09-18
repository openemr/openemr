<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Core;

use OpenEMR\Core\OEEnvBag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('core')]
class OEEnvBagIsolatedTest extends TestCase
{
    public function testConstructFromExplicitParameters(): void
    {
        $bag = new OEEnvBag(['FOO' => 'bar']);

        $this->assertTrue($bag->has('FOO'));
        $this->assertSame('bar', $bag->getString('FOO'));
    }

    public function testGetBooleanWithTruthyValues(): void
    {
        $bag = new OEEnvBag([
            'A' => 'true',
            'B' => '1',
            'C' => 'yes',
            'D' => 'on',
        ]);

        $this->assertTrue($bag->getBoolean('A'));
        $this->assertTrue($bag->getBoolean('B'));
        // ParameterBag::getBoolean uses filter_var FILTER_VALIDATE_BOOLEAN
        $this->assertTrue($bag->getBoolean('C'));
        $this->assertTrue($bag->getBoolean('D'));
    }

    public function testGetBooleanWithFalsyValues(): void
    {
        $bag = new OEEnvBag([
            'A' => 'false',
            'B' => '0',
            'C' => '',
            'D' => 'no',
        ]);

        $this->assertFalse($bag->getBoolean('A'));
        $this->assertFalse($bag->getBoolean('B'));
        $this->assertFalse($bag->getBoolean('C'));
        $this->assertFalse($bag->getBoolean('D'));
    }

    public function testGetBooleanDefaultForMissingKey(): void
    {
        $bag = new OEEnvBag([]);

        $this->assertFalse($bag->getBoolean('MISSING'));
        $this->assertTrue($bag->getBoolean('MISSING', true));
    }

    public function testMergePriority(): void
    {
        // Later sources in array_merge win. Verify by constructing directly
        // with a known merge order.
        $server = ['SHARED' => 'from_server', 'SERVER_ONLY' => 'server'];
        $env = ['SHARED' => 'from_env', 'ENV_ONLY' => 'env'];
        $getenv = ['SHARED' => 'from_getenv', 'GETENV_ONLY' => 'getenv'];

        $bag = new OEEnvBag(array_merge($server, $env, $getenv));

        $this->assertSame('from_getenv', $bag->getString('SHARED'));
        $this->assertSame('server', $bag->getString('SERVER_ONLY'));
        $this->assertSame('env', $bag->getString('ENV_ONLY'));
        $this->assertSame('getenv', $bag->getString('GETENV_ONLY'));
    }

    public function testGetIntAndGetString(): void
    {
        $bag = new OEEnvBag(['PORT' => '8080', 'NAME' => 'openemr']);

        $this->assertSame(8080, $bag->getInt('PORT'));
        $this->assertSame('openemr', $bag->getString('NAME'));
    }

    public function testCreateInstanceMergesEnvSources(): void
    {
        $key = 'OEENVBAG_TEST_' . bin2hex(random_bytes(4));
        try {
            putenv("{$key}=from_getenv");
            $_ENV[$key] = 'from_env';
            $_SERVER[$key] = 'from_server';

            $bag = OEEnvBag::getInstance();

            // getenv() values win over $_ENV and $_SERVER
            $this->assertSame('from_getenv', $bag->getString($key));
        } finally {
            putenv($key); // unset
            unset($_ENV[$key], $_SERVER[$key]);
        }
    }
}
