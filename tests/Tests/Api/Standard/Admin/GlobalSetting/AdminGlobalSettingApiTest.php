<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Api\Standard\Admin\GlobalSetting;

use OpenEMR\RestControllers\Standard\Admin\GlobalSetting\AdminGlobalSettingRestController;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Api\Standard\SettingTrait\AssertSettingKeysAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\BoolSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\CodeTypeSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\DefaultVisitCategorySettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\EnumSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\LanguageSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\NumberSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\TextSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\BySection\Global\GlobalLocaleSectionAwareTrait;
use OpenEMR\Tests\Fixtures\Purger\Settings\GlobalSettingsPurger;
use OpenEMR\Tests\Fixtures\Settings\GlobalSettingsFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-admin')]
#[Group('api-standard-admin-global-setting')]
#[Group('api-standard-setting')]
#[CoversClass(AdminGlobalSettingRestController::class)]
#[CoversMethod(AdminGlobalSettingRestController::class, 'getAll')]
#[CoversMethod(AdminGlobalSettingRestController::class, 'getBySectionSlug')]
#[CoversMethod(AdminGlobalSettingRestController::class, 'getOneBySettingKey')]
#[CoversMethod(AdminGlobalSettingRestController::class, 'putBySectionSlug')]
#[CoversMethod(AdminGlobalSettingRestController::class, 'resetOneBySettingKey')]
class AdminGlobalSettingApiTest extends TestCase
{
    use AssertSettingKeysAwareTrait;

    use BoolSettingsAwareTrait;
    use CodeTypeSettingsAwareTrait;
    use DefaultVisitCategorySettingsAwareTrait;
    use EnumSettingsAwareTrait;
    use LanguageSettingsAwareTrait;
    use NumberSettingsAwareTrait;
    use TextSettingsAwareTrait;

    use GlobalLocaleSectionAwareTrait;

    protected ApiTestClient $testClient;

    protected GlobalSettingsPurger $settingsPurger;

    protected GlobalSettingsFixture $settingsFixture;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->settingsPurger = GlobalSettingsPurger::getInstance();
        $this->settingsPurger->purge();

        $this->settingsFixture = GlobalSettingsFixture::getInstance();
        $this->settingsFixture->load();
    }

    protected function tearDown(): void
    {
        $this->settingsPurger->restore();

        // Remove test client created by ApiTestClient
        // $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

//    #[Test]
//    public function getAllTest(): void
//    {
//        $response = $this->testClient->request('GET', '/apis/default/api/admin/global-setting');
//        $this->assertEquals(200, $response->getStatusCode());
//
//        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
//        $this->assertCount(0, $json['validationErrors']);
//        $this->assertCount(0, $json['internalErrors']);
//
//        foreach ($json['data'] as $settingData) {
//            $this->assertArrayHasAllSettingKeys($settingData);
//        }
//    }

    #[Test]
    #[DataProvider('getBySectionSlugDataProvider')]
    public function getBySectionSlugTest(
        string $sectionSlug,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/global-setting/%s',
            $sectionSlug,
        ));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        foreach ($json['data'] as $settingData) {
            $this->assertArrayHasAllSettingKeys($settingData);
        }
    }

    /**
     * @see GlobalSettingSection::ALL_SECTIONS
     */
    public static function getBySectionSlugDataProvider(): iterable
    {
        // yield ['appearance']; // Unable to find manager for setting theme_tabs_layout
        yield ['billing'];
        yield ['branding'];
        yield ['cdr'];
        yield ['calendar'];
        // yield ['carecoordination'];
        yield ['connectors'];
        yield ['documents'];
        yield ['e-sign'];
        yield ['encounter-form'];
        yield ['features'];
        yield ['insurance'];
        yield ['locale'];
        yield ['logging'];
        yield ['login-page'];
        // yield ['miscellaneous'];
        yield ['notifications'];
        yield ['pdf'];
        yield ['patient-banner-bar'];
        yield ['portal'];
        yield ['questionnaires'];
        yield ['report'];
        yield ['rx'];
        yield ['security'];
    }

    #[Test]
    #[DataProvider('getOneBySettingKeyDataProvider')]
    public function getOneBySettingKeyTest(
        string $sectionSlug,
        string $settingKey,
        array $expectedResponseData,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/global-setting/%s/%s',
            $sectionSlug,
            $settingKey,
        ));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertEquals($expectedResponseData, $json['data']);
    }

    public static function getOneBySettingKeyDataProvider(): iterable
    {
        yield from self::getBoolDataProviderChunks();
        yield from self::getCodeTypeDataProviderChunks();
        yield from self::getDefaultVisitCategoryDataProviderChunks();
        yield from self::getEnumDataProviderChunks();
        yield from self::getLanguageDataProviderChunks();
        yield from self::getNumberDataProviderChunks();
        yield from self::getTextDataProviderChunks();
    }

    #[Test]
    #[DataProvider('putBySectionSlugDataProvider')]
    public function putBySectionSlugTest(
        string $sectionSlug,
        string $payload,
    ): void {
        $response = $this->testClient->request('PUT', sprintf(
            '/apis/default/api/admin/global-setting/%s',
            $sectionSlug,
        ), [], $payload, [
            'Content-Type' => 'application/json',
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        foreach ($json['data'] as $settingData) {
            $this->assertArrayHasAllSettingKeys($settingData);
        }
    }

    public static function putBySectionSlugDataProvider(): iterable
    {
        yield from self::getGlobalLocaleDataProviderChunks();
    }

    #[Test]
    #[DataProvider('resetOneBySettingKeyDataProvider')]
    public function resetOneBySettingKeyTest(
        string $sectionSlug,
        string $settingKey,
        array $expectedResponseData,
    ): void {
        $response = $this->testClient->request('POST', sprintf(
            '/apis/default/api/admin/global-setting/%s/%s/reset',
            $sectionSlug,
            $settingKey,
        ));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertEquals($expectedResponseData, $json['data']);
    }

    public static function resetOneBySettingKeyDataProvider(): iterable
    {
        yield from self::getBoolDataProviderChunks();
        yield from self::getCodeTypeDataProviderChunks();
        yield from self::getDefaultVisitCategoryDataProviderChunks();
        yield from self::getEnumDataProviderChunks();
        yield from self::getLanguageDataProviderChunks();
        yield from self::getNumberDataProviderChunks();
        yield from self::getTextDataProviderChunks();
    }
}
