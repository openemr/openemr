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

use OpenEMR\Common\Database\Repository\Settings\LanguageRepository;
use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\GlobalSettingDriver;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Manager\LanguageSettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('setting')]
#[Group('language')]
#[CoversClass(LanguageSettingManager::class)]
#[CoversMethod(LanguageSettingManager::class, 'isDataTypeSupported')]
#[CoversMethod(LanguageSettingManager::class, 'validateSettingValue')]
class LanguageSettingManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('isDataTypeSupportedDataProvider')]
    public function isDataTypeSupportedTest(
        string $dataType,
        bool $expected,
    ): void {
        $manager = new LanguageSettingManager(
            $this->createMock(LanguageRepository::class),
            $this->createMock(SettingDriverInterface::class),
            $this->createMock(GlobalsService::class),
        );

        $this->assertEquals(
            $expected,
            $manager->isDataTypeSupported($dataType),
        );
    }

    public static function isDataTypeSupportedDataProvider(): iterable
    {
        yield [GlobalSetting::DATA_TYPE_ADDRESS_BOOK, false];
        yield [GlobalSetting::DATA_TYPE_BOOL, false];
        yield [GlobalSetting::DATA_TYPE_CODE_TYPES, false];
        yield [GlobalSetting::DATA_TYPE_COLOR_CODE, false];
        yield [GlobalSetting::DATA_TYPE_CSS, false];
        yield [GlobalSetting::DATA_TYPE_DEFAULT_RANDOM_UUID, false];
        yield [GlobalSetting::DATA_TYPE_DEFAULT_VISIT_CATEGORY, false];
        yield [GlobalSetting::DATA_TYPE_ENCRYPTED, false];
        yield [GlobalSetting::DATA_TYPE_ENCRYPTED_HASH, false];
        yield [GlobalSetting::DATA_TYPE_ENUM, false];
        yield [GlobalSetting::DATA_TYPE_HOUR, false];
        yield [GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION, false];
        yield 'Supported DATA_TYPE_LANGUAGE' => [GlobalSetting::DATA_TYPE_LANGUAGE, true];
        yield [GlobalSetting::DATA_TYPE_MULTI_DASHBOARD_CARDS, false];
        yield [GlobalSetting::DATA_TYPE_MULTI_LANGUAGE_SELECT, false];
        yield [GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR, false];
        yield [GlobalSetting::DATA_TYPE_NUMBER, false];
        yield [GlobalSetting::DATA_TYPE_PASS, false];
        yield [GlobalSetting::DATA_TYPE_TABS_CSS, false];
    }

    #[Test]
    #[DataProvider('validateSettingValueFailedDataProvider')]
    public function validateSettingValueFailedTest(
        string $settingValue,
        string $expectedExceptionMessage,
    ): void {
        $manager = new LanguageSettingManager(
            $this->getRepositoryMock(),
            $this->createMock(SettingDriverInterface::class),
            $this->createMock(GlobalsService::class),
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $manager->validateSettingValue('language_default', $settingValue);
    }

    public static function validateSettingValueFailedDataProvider(): iterable
    {
        yield 'Empty' => ['', 'Setting "language_default" can not accept value "". Expected one of: "English (Standard)", "Swedish", "Spanish (Spain)"'];
        yield 'Not exact value' => ['English', 'Setting "language_default" can not accept value "English". Expected one of: "English (Standard)", "Swedish", "Spanish (Spain)"'];
    }

    #[Test]
    #[DataProvider('normalizeSettingDataProvider')]
    public function normalizeSettingTest(
        string $settingKey,
        string $settingDefaultValue,
        ?string $settingValue,
        array $expectedNormalizedSetting,
    ): void {
        $globalSettingDriver = $this->createMock(GlobalSettingDriver::class);
        $globalSettingDriver->method('getSettingDefaultValue')->with($settingKey)->willReturn($settingDefaultValue);
        $globalSettingDriver->method('getSettingValue')->with($settingKey)->willReturn($settingValue);

        $manager = new LanguageSettingManager(
            $this->getRepositoryMock(),
            $globalSettingDriver,
            GlobalsServiceFactory::getInstance(),
        );

        $this->assertEquals(
            $expectedNormalizedSetting,
            iterator_to_array(
                $manager->normalizeSetting($settingKey)
            )
        );
    }

    public static function normalizeSettingDataProvider(): iterable
    {
        $valueOptions = [[
            'option_value' => 'English (Standard)',
            'option_label' => 'English (Standard)',
        ],[
            'option_value' => 'Swedish',
            'option_label' => 'Swedish',
        ],[
            'option_value' => 'Spanish (Spain)',
            'option_label' => 'Spanish (Spain)',
        ]];

        yield 'Value NOT set' => [
            'language_default',
            'Spanish (Spain)',
            null,
            [
                'setting_key' => 'language_default',
                'setting_name' => 'Default Language',
                'setting_description' => 'Default language if no other is allowed or chosen.',
                'setting_default_value' => 'Spanish (Spain)',
                'setting_is_default_value' => true,
                'setting_value' => null,
                'setting_value_options' => $valueOptions,
            ],
        ];

        yield 'Value SET (default)' => [
            'language_default',
            'Spanish (Spain)',
            'Spanish (Spain)',
            [
                'setting_key' => 'language_default',
                'setting_name' => 'Default Language',
                'setting_description' => 'Default language if no other is allowed or chosen.',
                'setting_default_value' => 'Spanish (Spain)',
                'setting_is_default_value' => true,
                'setting_value' => 'Spanish (Spain)',
                'setting_value_options' => $valueOptions,
            ],
        ];

        yield 'Value SET (non-default)' => [
            'language_default',
            'Spanish (Spain)',
            'Swedish',
            [
                'setting_key' => 'language_default',
                'setting_name' => 'Default Language',
                'setting_description' => 'Default language if no other is allowed or chosen.',
                'setting_default_value' => 'Spanish (Spain)',
                'setting_is_default_value' => false,
                'setting_value' => 'Swedish',
                'setting_value_options' => $valueOptions,
            ],
        ];
    }

    private function getRepositoryMock(): LanguageRepository
    {
        $repository = $this->createMock(LanguageRepository::class);
        $repository->method('findAll')->willReturn([
            ['lang_id' => 1, 'lang_code' => 'en', 'lang_description' => 'English (Standard)'],
            ['lang_id' => 2, 'lang_code' => 'se', 'lang_description' => 'Swedish'],
            ['lang_id' => 3, 'lang_code' => 'es', 'lang_description' => 'Spanish (Spain)'],
        ]);

        return $repository;
    }
}
