<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Core;

use ErrorException;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('core')]
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

    /**
     * Keep in sync with OEGlobalsBag::DEPRECATED_KEYS.
     *
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function deprecatedKeysProvider(): array
    {
        $keys = [
            'unit_test_placeholder',
        ];
        // Format to DataProvider
        return array_combine($keys, array_map(fn($k) => [$k], $keys));
    }

    #[DataProvider('deprecatedKeysProvider')]
    public function testGetDeprecatedKeyTriggersWarning(string $key): void
    {
        $bag = new OEGlobalsBag([$key => 'test-value']);
        $this->expectException(ErrorException::class);
        $bag->get($key);
    }

    #[DataProvider('deprecatedKeysProvider')]
    public function testHasDeprecatedKeyTriggersWarning(string $key): void
    {
        $bag = new OEGlobalsBag([$key => 'test-value']);
        $this->expectException(ErrorException::class);
        $bag->has($key);
    }
}
