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

namespace OpenEMR\Tests\Api\Standard\Admin\User\Setting;

use OpenEMR\RestControllers\Standard\Admin\User\Setting\AdminUserSettingRestController;
use OpenEMR\Services\Globals\GlobalSettingSection;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Api\Standard\SettingTrait\AssertSettingKeysAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\BoolSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\CodeTypeSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\EnumSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\LanguageSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\Global\NumberSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\BySection\UserSpecific\UserSpecificLocaleSectionAwareTrait;
use OpenEMR\Tests\Fixtures\Purger\Settings\UserSettingsPurger;
use OpenEMR\Tests\Fixtures\Settings\UserSettingsFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-admin')]
#[Group('api-standard-admin-user-setting')]
#[Group('api-standard-user-setting')]
#[Group('api-standard-setting')]
#[CoversClass(AdminUserSettingRestController::class)]
#[CoversMethod(AdminUserSettingRestController::class, 'getAll')]
#[CoversMethod(AdminUserSettingRestController::class, 'getBySectionSlug')]
#[CoversMethod(AdminUserSettingRestController::class, 'getOneBySettingKey')]
#[CoversMethod(AdminUserSettingRestController::class, 'putBySectionSlug')]
#[CoversMethod(AdminUserSettingRestController::class, 'resetBySectionSlug')]
#[CoversMethod(AdminUserSettingRestController::class, 'resetOneBySettingKey')]
class AdminUserSettingApiTest extends TestCase
{
    use AssertSettingKeysAwareTrait;

    use BoolSettingsAwareTrait;
    use CodeTypeSettingsAwareTrait;
    use EnumSettingsAwareTrait;
    use LanguageSettingsAwareTrait;
    use NumberSettingsAwareTrait;

    use UserSpecificLocaleSectionAwareTrait;

    private const ADMIN_USER_ID = 1;

    protected ApiTestClient $testClient;

    protected UserSettingsPurger $settingsPurger;

    protected UserSettingsFixture $settingsFixture;

    protected function setUp(): void
    {
        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);

        $this->settingsPurger = UserSettingsPurger::getInstance();
        $this->settingsPurger->purge();

        $this->settingsFixture = UserSettingsFixture::getInstance();
        $this->settingsFixture->load();
    }

    protected function tearDown(): void
    {
        $this->settingsPurger->restore();

        // Remove test client created by ApiTestClient
        // $this->testClient->cleanupRevokeAuth();
        $this->testClient->cleanupClient();
    }

    /**
     * @see GlobalSettingSection::USER_SPECIFIC_SECTIONS
     */
    public static function validSectionsDataProvider(): iterable
    {
        // yield ['appearance']; // @todo Fix 400
        yield ['billing'];
        yield ['calendar'];
        yield ['carecoordination'];
        yield ['cdr'];
        yield ['connectors'];
        yield ['features'];
        yield ['locale'];
        yield ['questionnaires'];
        yield ['report'];
    }

    #[Test]
    public function getAllTest(): void
    {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s/setting',
            self::ADMIN_USER_ID,
        ));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        foreach ($json['data'] as $settingData) {
            $this->assertArrayHasAllSettingKeys($settingData);
        }
    }

    #[Test]
    #[DataProvider('getBySectionSlugFailedDataProvider')]
    public function getBySectionSlugFailedTest(
        string $sectionSlug,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s',
            self::ADMIN_USER_ID,
            $sectionSlug,
        ));
        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertEquals([
            sprintf(
                'Section "%s" does not exist. Possible ones: "appearance", "billing", "calendar", "carecoordination", "cdr", "connectors", "features", "locale", "questionnaires", "report".',
                $sectionSlug,
            )
        ], $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
    }

    /**
     * Non-user-specific section slugs
     *
     * @see GlobalSettingSection::USER_SPECIFIC_SECTIONS
     */
    public static function getBySectionSlugFailedDataProvider(): iterable
    {
        yield ['branding'];
        // yield ['carecoordination']; // @todo Fix HTTP 500 here!
        yield ['documents'];
        yield ['e-sign'];
        yield ['encounter-form'];
        yield ['insurance'];
        yield ['logging'];
        yield ['login-page'];
        yield ['miscellaneous'];
        yield ['notifications'];
        yield ['pdf'];
        yield ['patient-banner-bar'];
        yield ['portal'];
        yield ['rx'];
        yield ['security'];
    }

    #[Test]
    #[DataProvider('validSectionsDataProvider')]
    public function getBySectionSlugSucceededTest(
        string $sectionSlug,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s',
            self::ADMIN_USER_ID,
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

    #[Test]
    #[DataProvider('getOneBySettingKeyDataProvider')]
    public function getOneBySettingKeyTest(
        string $sectionSlug,
        string $settingKey,
        array $expectedResponseData,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s/%s',
            self::ADMIN_USER_ID,
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
        yield from self::getEnumDataProviderChunks();
        yield from self::getLanguageDataProviderChunks();
        yield from self::getNumberDataProviderChunks();
        // yield from self::getTextDataProviderChunks();
    }

//    #[Test]
//    #[DataProvider('patchBySectionSlugFailedDataProvider')]
//    public function patchBySectionSlugFailedTest(
//        string $sectionSlug,
//        string $payload,
//        array $expectedResponseData,
//    ): void {
//        $response = $this->testClient->request('POST', sprintf(
//            '/apis/default/api/admin/user/%s/setting/%s',
//            self::ADMIN_USER_ID,
//            $sectionSlug
//        ), [], $payload, [
//            'Content-Type' => 'application/json',
//        ]);
//        $this->assertEquals(400, $response->getStatusCode());
//
//        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
//        $this->assertEquals($expectedResponseData, $json['validationErrors']);
//        $this->assertCount(0, $json['internalErrors']);
//    }
//
//    public static function patchBySectionSlugFailedDataProvider(): iterable
//    {
//        yield [
//            'features',
//            '
//                [{
//                    "setting_key": "enable_help",
//                    "setting_value": 4
//                }]
//            ',
//            [
//                'Setting "enable_help" can not accept value 4. Expected one of: 0 (Hide Help Modal), 1 (Show Help Modal), 2 (Disable Help Modal)'
//            ],
//        ];
//    }
//
//    #[Test]
//    #[DataProvider('patchBySectionSlugSucceededDataProvider')]
//    public function patchBySectionSlugSucceededTest(
//        string $sectionSlug,
//        string $postBody,
//        array $expectedResponseData,
//    ): void {
//        $response = $this->testClient->request('POST', sprintf(
//            '/apis/default/api/admin/user/%s/setting/%s',
//            self::ADMIN_USER_ID,
//            $sectionSlug
//        ), [], $postBody, [
//            'Content-Type' => 'application/json',
//        ]);
//        $this->assertEquals(200, $response->getStatusCode());
//
//        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
//        $this->assertCount(0, $json['validationErrors']);
//        $this->assertCount(0, $json['internalErrors']);
//
//        $this->assertEquals($expectedResponseData, $json['data']);
//    }
//
//    public static function patchBySectionSlugSucceededDataProvider(): iterable
//    {
//        yield 'Single values under features section' => [
//            'features',
//            '
//                [{
//                    "setting_key": "enable_help",
//                    "setting_value": 1
//                }]
//            ',
//            [
//                [
//                    'setting_key' => 'enable_help',
//                    'setting_name' => 'Enable Help Modal',
//                    'setting_description' => 'This will allow the display of help modal on help enabled pages',
//                    'setting_default_value' => 2,
//                    'setting_is_default_value' => false,
//                    'setting_value' => 1,
//                    'setting_value_options' => [
//                        [ 'option_value' => 0, 'option_label' => 'Hide Help Modal' ],
//                        [ 'option_value' => 1, 'option_label' => 'Show Help Modal' ],
//                        [ 'option_value' => 2, 'option_label' => 'Disable Help Modal' ]
//                    ]
//                ],
//            ],
//        ];
//
//        yield 'Multiple values under features section' => [
//            'features',
//            '
//                [{
//                    "setting_key": "enable_help",
//                    "setting_value": 2
//                }, {
//                    "setting_key": "expand_form",
//                    "setting_value": false
//                }]
//            ',
//            [
//                [
//                    'setting_key' => 'enable_help',
//                    'setting_name' => 'Enable Help Modal',
//                    'setting_description' => 'This will allow the display of help modal on help enabled pages',
//                    'setting_default_value' => 2,
//                    'setting_is_default_value' => true,
//                    'setting_value' => 2,
//                    'setting_value_options' => [
//                        [ 'option_value' => 0, 'option_label' => 'Hide Help Modal' ],
//                        [ 'option_value' => 1, 'option_label' => 'Show Help Modal' ],
//                        [ 'option_value' => 2, 'option_label' => 'Disable Help Modal' ]
//                    ]
//                ],
//                [
//                    'setting_key' => 'expand_form',
//                    'setting_name' => 'Expand Form',
//                    'setting_description' => 'Open all expandable forms in expanded state',
//                    'setting_default_value' => true,
//                    'setting_is_default_value' => false,
//                    'setting_value' => false
//                ]
//            ],
//        ];
//    }
//
//    #[Test]
//    #[DataProvider('putBySectionSlugDataProvider')]
//    public function putBySectionSlugTest(
//        string $sectionSlug,
//        string $payload,
//    ): void {
//        $response = $this->testClient->request('PUT', sprintf(
//            '/apis/default/api/admin/user/%s/setting/%s',
//            self::ADMIN_USER_ID,
//            $sectionSlug,
//        ), [], $payload, [
//            'Content-Type' => 'application/json',
//        ]);
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
//
//    public static function putBySectionSlugDataProvider(): iterable
//    {
//        yield from self::getUserSpecificLocaleDataProviderChunks();
//    }
//
//    #[Test]
//    #[DataProvider('validSectionsDataProvider')]
//    public function resetBySectionSlugTest(string $sectionSlug): void
//    {
//        $response = $this->testClient->request('POST', sprintf(
//            '/apis/default/api/admin/user/%s/setting/%s/reset',
//            self::ADMIN_USER_ID,
//            $sectionSlug,
//        ));
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
//
//    #[Test]
//    #[DataProvider('resetOneBySettingKeyDataProvider')]
//    public function resetOneBySettingKeyTest(
//        string $sectionSlug,
//        string $settingKey,
//        array $expectedResponseData,
//    ): void {
//        $response = $this->testClient->request('POST', sprintf(
//            '/apis/default/api/admin/user/%s/setting/%s/%s/reset',
//            self::ADMIN_USER_ID,
//            $sectionSlug,
//            $settingKey,
//        ));
//        $this->assertEquals(200, $response->getStatusCode());
//
//        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
//        $this->assertCount(0, $json['validationErrors']);
//        $this->assertCount(0, $json['internalErrors']);
//
//        $this->assertEquals($expectedResponseData, $json['data']);
//    }
//
//    /**
//     * Note, there are no User-specific TEXT settings
//     */
//    public static function resetOneBySettingKeyDataProvider(): iterable
//    {
//        yield from self::getBoolDataProviderChunks();
//        yield from self::getCodeTypeDataProviderChunks();
//        yield from self::getEnumDataProviderChunks();
//        yield from self::getLanguageDataProviderChunks();
//        yield from self::getNumberDataProviderChunks();
//    }
}
