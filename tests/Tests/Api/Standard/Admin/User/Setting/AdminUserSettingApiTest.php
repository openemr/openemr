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
use OpenEMR\Setting\Fixture\Purger\UserSettingsPurger;
use OpenEMR\Setting\Fixture\UserSettingsFixture;
use OpenEMR\Tests\Api\ApiTestClient;
use OpenEMR\Tests\Api\Standard\SettingTrait\AssertSettingKeysAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\UserSpecific\UserSpecificBoolSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\UserSpecific\UserSpecificCssSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\UserSpecific\UserSpecificEnumSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\UserSpecific\UserSpecificNumberSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\ByDataType\UserSpecific\UserSpecificTabsCssSettingsAwareTrait;
use OpenEMR\Tests\Api\Standard\SettingTrait\BySection\UserSpecific\UserSpecificLocaleSectionAwareTrait;
use OpenEMR\Tests\Api\Standard\User\MeAwareTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('api')]
#[Group('api-standard')]
#[Group('api-standard-admin')]
#[Group('api-standard-setting')]
#[Group('api-standard-admin-user')]
#[Group('api-standard-admin-user-setting')]
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
    use UserSpecificBoolSettingsAwareTrait;
    use UserSpecificCssSettingsAwareTrait;
    use UserSpecificEnumSettingsAwareTrait;
    use UserSpecificNumberSettingsAwareTrait;
    use UserSpecificTabsCssSettingsAwareTrait;
    use UserSpecificLocaleSectionAwareTrait;
    use MeAwareTrait;

    protected UserSettingsPurger $settingsPurger;

    protected UserSettingsFixture $settingsFixture;

    protected function setUp(): void
    {
        $this->settingsPurger = UserSettingsPurger::getInstance();
        $this->settingsPurger->purge();

        $this->settingsFixture = UserSettingsFixture::getInstance();
        $this->settingsFixture->load();

        $baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $this->testClient = new ApiTestClient($baseUrl, false);
        $this->testClient->setAuthToken(ApiTestClient::OPENEMR_AUTH_ENDPOINT);
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
        yield ['appearance'];
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
            $this->getMyUuid(),
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
     * @see AdminUserSettingRestController::getBySectionSlug
     */
    #[Test]
    #[DataProvider('getBySectionSlugFailedDataProvider')]
    public function getBySectionSlugFailedTest(
        string $sectionSlug,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s',
            $this->getMyUuid(),
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

    /**
     * @see AdminUserSettingRestController::getBySectionSlug
     */
    #[Test]
    #[DataProvider('validSectionsDataProvider')]
    public function getBySectionSlugSucceededTest(
        string $sectionSlug,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s',
            $this->getMyUuid(),
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
     * @see AdminUserSettingRestController::getOneBySettingKey
     */
    #[Test]
    #[DataProvider('getOneBySettingKeyDataProvider')]
    public function getOneBySettingKeyTest(
        string $sectionSlug,
        string $settingKey,
        array $expectedResponseData,
    ): void {
        $response = $this->testClient->request('GET', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s/%s',
            $this->getMyUuid(),
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
        yield from self::getUserSpecificBoolDataProviderChunks();
        yield from self::getUserSpecificCssDataProviderChunks();
        yield from self::getUserSpecificEnumDataProviderChunks();
        yield from self::getUserSpecificNumberDataProviderChunks();
        yield from self::getUserSpecificTabsCssDataProviderChunks();
    }

    /**
     * @see AdminUserSettingRestController::patchBySectionSlug
     */
    #[Test]
    #[DataProvider('patchBySectionSlugFailedDataProvider')]
    public function patchBySectionSlugFailedTest(
        string $sectionSlug,
        string $payload,
        array $expectedResponseData,
    ): void {
        $response = $this->testClient->request('PATCH', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s',
            $this->getMyUuid(),
            $sectionSlug
        ), [], $payload, [
            'Content-Type' => 'application/json',
        ]);
        $this->assertEquals(400, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertEquals($expectedResponseData, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);
    }

    public static function patchBySectionSlugFailedDataProvider(): iterable
    {
        yield [
            'features',
//            '
//                [{
//                    "setting_key": "enable_help",
//                    "setting_value": 4
//                }]
//            ',
            '{"enable_help": 4}',
            [
                'Setting "enable_help" can not accept value 4. Expected one of: 0 (Hide Help Modal), 1 (Show Help Modal), 2 (Disable Help Modal)'
            ],
        ];
    }

    /**
     * @see AdminUserSettingRestController::patchBySectionSlug
     */
    #[Test]
    #[DataProvider('patchBySectionSlugSucceededDataProvider')]
    public function patchBySectionSlugSucceededTest(
        string $sectionSlug,
        string $postBody,
        array $expectedResponseData,
    ): void {
        $response = $this->testClient->request('PATCH', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s',
            $this->getMyUuid(),
            $sectionSlug
        ), [], $postBody, [
            'Content-Type' => 'application/json',
        ]);
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertEquals($expectedResponseData, $json['data']);
    }

    public static function patchBySectionSlugSucceededDataProvider(): iterable
    {
        yield 'Single values under features section' => [
            'features',
//            '
//                [{
//                    "setting_key": "enable_help",
//                    "setting_value": 0
//                }]
//            ',
            '{"enable_help": 0}',
            [
                [
                    'setting_key' => 'enable_help',
                    'setting_name' => 'Enable Help Modal',
                    'setting_description' => 'This will allow the display of help modal on help enabled pages',
                    'setting_default_value' => 1,
                    'setting_is_default_value' => false,
                    'setting_value' => 0,
                    'setting_value_options' => [
                        [ 'option_value' => 0, 'option_label' => 'Hide Help Modal' ],
                        [ 'option_value' => 1, 'option_label' => 'Show Help Modal' ],
                        [ 'option_value' => 2, 'option_label' => 'Disable Help Modal' ]
                    ]
                ],
            ],
        ];

        yield 'Multiple values under features section' => [
            'features',
//            '
//                [{
//                    "setting_key": "enable_help",
//                    "setting_value": 0
//                }, {
//                    "setting_key": "expand_form",
//                    "setting_value": false
//                }]
//            ',
            '{"enable_help": 0, "expand_form": false}',
            [
                [
                    'setting_key' => 'enable_help',
                    'setting_name' => 'Enable Help Modal',
                    'setting_description' => 'This will allow the display of help modal on help enabled pages',
                    'setting_default_value' => 1,
                    'setting_is_default_value' => false,
                    'setting_value' => 0,
                    'setting_value_options' => [
                        [ 'option_value' => 0, 'option_label' => 'Hide Help Modal' ],
                        [ 'option_value' => 1, 'option_label' => 'Show Help Modal' ],
                        [ 'option_value' => 2, 'option_label' => 'Disable Help Modal' ]
                    ]
                ],
                [
                    'setting_key' => 'expand_form',
                    'setting_name' => 'Expand Form',
                    'setting_description' => 'Open all expandable forms in expanded state',
                    'setting_default_value' => true,
                    'setting_is_default_value' => false,
                    'setting_value' => false,
                ]
            ],
        ];
    }

    /**
     * @see AdminUserSettingRestController::putBySectionSlug
     */
    #[Test]
    #[DataProvider('putBySectionSlugDataProvider')]
    public function putBySectionSlugTest(
        string $sectionSlug,
        string $payload,
    ): void {
        $response = $this->testClient->request('PUT', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s',
            $this->getMyUuid(),
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
        yield from self::getUserSpecificLocaleDataProviderChunks();
    }

    /**
     * @see AdminUserSettingRestController::resetBySectionSlug
     */
    #[Test]
    #[DataProvider('validSectionsDataProvider')]
    public function resetBySectionSlugTest(string $sectionSlug): void
    {
        $response = $this->testClient->request('POST', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s/reset',
            $this->getMyUuid(),
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
     * @see AdminUserSettingRestController::resetOneBySettingKey
     *
     * Note: After reset, user settings return to global defaults.
     * This test uses a separate data provider with expected reset (default) values.
     */
    #[Test]
    #[DataProvider('resetOneBySettingKeyDataProvider')]
    public function resetOneBySettingKeyTest(
        string $sectionSlug,
        string $settingKey,
        array $expectedResponseData,
    ): void {
        $response = $this->testClient->request('POST', sprintf(
            '/apis/default/api/admin/user/%s/setting/%s/%s/reset',
            $this->getMyUuid(),
            $sectionSlug,
            $settingKey,
        ));
        $this->assertEquals(200, $response->getStatusCode());

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $this->assertCount(0, $json['validationErrors']);
        $this->assertCount(0, $json['internalErrors']);

        $this->assertEquals($expectedResponseData, $json['data']);
    }

    /**
     * Data provider for reset test.
     *
     * After reset, user settings return to their global default values:
     * - setting_default_value = global default
     * - setting_is_default_value = true
     * - setting_value = global default (or null for some types)
     */
    public static function resetOneBySettingKeyDataProvider(): iterable
    {
        // Boolean setting - after reset returns to global default (false)
        yield [
            'appearance',
            'enable_compact_mode',
            [
                'setting_key' => 'enable_compact_mode',
                'setting_name' => 'Enable Compact Mode',
                'setting_description' => 'Changes the current theme to be more compact.',
                'setting_default_value' => false,
                'setting_is_default_value' => true,
                'setting_value' => false,
            ],
        ];

        // Number setting - after reset returns to global default (0)
        yield [
            'calendar',
            'checkout_roll_off',
            [
                'setting_key' => 'checkout_roll_off',
                'setting_name' => 'Flow Board: display completed checkouts (minutes)',
                'setting_description' => 'Flow Board will only display completed checkouts for this many minutes. Zero is continuous display.',
                'setting_default_value' => 0,
                'setting_is_default_value' => true,
                'setting_value' => 0,
            ],
        ];
    }
}
