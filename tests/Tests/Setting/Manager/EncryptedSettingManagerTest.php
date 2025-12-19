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

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Manager\EncryptedSettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('setting')]
#[CoversClass(EncryptedSettingManager::class)]
#[CoversMethod(EncryptedSettingManager::class, 'getSettingValue')]
#[CoversMethod(EncryptedSettingManager::class, 'setSettingValue')]
class EncryptedSettingManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('getSettingValueDataProvider')]
    public function getSettingValueTest(
        string $encryptedSettingValue,
        string $expectedDecryptedValue
    ): void {
        $driver = $this->createMock(SettingDriverInterface::class);
        $driver->method('getSettingValue')->with('setting_key')->willReturn($encryptedSettingValue);

        $manager = new EncryptedSettingManager($driver);
        $this->assertEquals(
            $expectedDecryptedValue,
            $manager->getSettingValue('setting_key')
        );
    }

    public static function getSettingValueDataProvider(): iterable
    {
        yield 'couchdb_pass' => ['007dYmqIb/vWIzddmdgZW5IWTdG2v20dimKcSIkSM+YTIFluLCOJxg8HMElcfmJY64CVgJr+mWs0wWaMMxOsYHe22KZ6vym0wf7Y9Y1EB8t6yM=', 'password'];
    }

    #[Test]
    #[DataProvider('setSettingValueDataProvider')]
    public function setSettingValueTest(string $settingValue): void
    {
        $driver = $this->createMock(SettingDriverInterface::class);
        $driver
            ->expects($this->once())
            ->method('setSettingValue')
            ->with(
                $this->equalTo('setting_key'),
                $this->callback(fn (string $encryptedValue): bool => $settingValue === (new CryptoGen())->decryptStandard($encryptedValue))
            )
        ;

        $manager = new EncryptedSettingManager($driver);
        $manager->setSettingValue('setting_key', $settingValue);
    }

    public static function setSettingValueDataProvider(): iterable
    {
        yield 'Empty' => [''];
        yield ['password'];
    }
}
