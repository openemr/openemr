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

namespace OpenEMR\Tests\Setting\Manager;

use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Manager\NumberSettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(NumberSettingManager::class)]
#[CoversMethod(NumberSettingManager::class, 'getSettingValue')]
class NumericSettingManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('getSettingValueDataProvider')]
    public function getSettingValueTest(
        string $settingValue,
        int $expectedNumericValue
    ): void {
        $driver = $this->createMock(SettingDriverInterface::class);
        $driver->method('getSettingValue')->with('setting_key')->willReturn($settingValue);

        $manager = new NumberSettingManager($driver);
        $this->assertEquals(
            $expectedNumericValue,
            $manager->getSettingValue('setting_key')
        );
    }

    public static function getSettingValueDataProvider(): iterable
    {
        yield 'Empty' => ['', 0]; // Are we sure this is what needed?

        yield ['0', 0];
        yield ['1', 1];

        /**
         * @see PHP_INT_MAX
         */
        yield ['9223372036854775807', 9223372036854775807];
    }
}
