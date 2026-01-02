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
use OpenEMR\Setting\Manager\BooleanSettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(BooleanSettingManager::class)]
#[CoversMethod(BooleanSettingManager::class, 'getSettingValue')]
class BooleanSettingManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('getSettingValueDataProvider')]
    public function getSettingValueTest(
        string $settingValue,
        bool $expectedBooleanValue
    ): void {
        $driver = $this->createMock(SettingDriverInterface::class);
        $driver->method('getSettingValue')->with('setting_key')->willReturn($settingValue);

        $manager = new BooleanSettingManager($driver);
        $this->assertEquals(
            $expectedBooleanValue,
            $manager->getSettingValue('setting_key')
        );
    }

    public static function getSettingValueDataProvider(): iterable
    {
        yield 'Empty' => ['', false];
        yield ['0', false];

        yield ['1', true];
    }
}
