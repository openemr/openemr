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
use OpenEMR\Setting\Manager\ScalarSettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(ScalarSettingManager::class)]
#[CoversMethod(ScalarSettingManager::class, 'getSettingValue')]
class ScalarSettingManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('getSettingValueDataProvider')]
    public function getSettingValueTest(
        string $settingValue,
        string $expectedScalarValue
    ): void {
        $driver = $this->createMock(SettingDriverInterface::class);
        $driver->method('getSettingValue')->with('setting_key')->willReturn($settingValue);

        $manager = new ScalarSettingManager($driver);
        $this->assertEquals(
            $expectedScalarValue,
            $manager->getSettingValue('setting_key')
        );
    }

    /**
     * Scalar manager returns same values as driver returned (unchanged)
     */
    public static function getSettingValueDataProvider(): iterable
    {
        yield 'Empty' => ['', ''];
        yield 'Numeric' => ['1', '1'];
        yield 'String' => ['string', 'string'];
    }
}
