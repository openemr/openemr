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
use OpenEMR\Fixture\FixtureInterface;
use OpenEMR\Fixture\Purger\PurgerInterface;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Fixture\KeysFixture;
use OpenEMR\Setting\Fixture\Purger\KeysPurger;
use OpenEMR\Setting\Manager\EncryptedSettingManager;
use OpenEMR\Tests\Api\ApiTestClient;
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
    private PurgerInterface $purger;

    private FixtureInterface $fixture;

    private ApiTestClient $testClient;

    protected function setUp(): void
    {
        $this->purger = KeysPurger::getInstance();
        $this->purger->purge();

        $this->fixture = KeysFixture::getInstance();
        $this->fixture->load();

        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
    }

    protected function tearDown(): void
    {
        $this->purger->restore();

        // Remove test client created by ApiTestClient
        // $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    #[Test]
    #[DataProvider('getSettingValueDataProvider')]
    public function getSettingValueTest(
        string $encryptedSettingValue,
        string $expectedDecryptedValue
    ): void {
        $driver = $this->createMock(SettingDriverInterface::class);
        $driver->method('getSettingValue')->with('setting_key')->willReturn($encryptedSettingValue);

        $manager = new EncryptedSettingManager(
            $driver,
            $this->createMock(GlobalsService::class),
        );

        $this->assertEquals(
            $expectedDecryptedValue,
            $manager->getSettingValue('setting_key')
        );
    }

    public static function getSettingValueDataProvider(): iterable
    {
        yield 'couchdb_pass' => ['00781nJfg/Dn8tOlwQNw2QtcRZp1UdboMSEqHMuCgz5wFuhKHgeJ5TqqsiMO7JlwZMoSasFoaFAmU0X9lihx8W63YtCHm13POZWkTngZTFl/To=', 'password'];
    }

    /**
     * Note, here we're using key from DB
     * @see keys.json
     */
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

        $manager = new EncryptedSettingManager(
            $driver,
            $this->createMock(GlobalsService::class),
        );

        $manager->setSettingValue('setting_key', $settingValue);
    }

    public static function setSettingValueDataProvider(): iterable
    {
        yield 'Empty' => [''];
        yield ['password'];
    }
}
