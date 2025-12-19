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

use OpenEMR\Services\Globals\GlobalSetting;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\GlobalSettingDriver;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Manager\EnumSettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('setting')]
#[CoversClass(EnumSettingManager::class)]
#[CoversMethod(EnumSettingManager::class, 'isDataTypeSupported')]
#[CoversMethod(EnumSettingManager::class, 'validateSettingValue')]
#[CoversMethod(EnumSettingManager::class, 'normalizeSetting')]
class EnumSettingManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('isDataTypeSupportedDataProvider')]
    public function isDataTypeSupportedTest(
        string $dataType,
        bool $expected,
    ): void {
        $manager = new EnumSettingManager(
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
        yield 'Supported DATA_TYPE_ENUM' => [GlobalSetting::DATA_TYPE_ENUM, true];
        yield [GlobalSetting::DATA_TYPE_HOUR, false];
        yield [GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION, false];
        yield [GlobalSetting::DATA_TYPE_LANGUAGE, false];
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->getSettingManager()->validateSettingValue('enable_help', $settingValue);
    }

    public static function validateSettingValueFailedDataProvider(): iterable
    {
        yield 'Empty' => ['', 'Setting "enable_help" can not accept value "". Expected one of: 0 (Hide Help Modal), 1 (Show Help Modal), 2 (Disable Help Modal)'];
        yield 'Random string' => ['Random string', 'Setting "enable_help" can not accept value "Random string". Expected one of: 0 (Hide Help Modal), 1 (Show Help Modal), 2 (Disable Help Modal)'];
    }

    #[Test]
    #[DataProvider('normalizeSettingDataProvider')]
    public function normalizeSettingTest(
        string $settingKey,
        array $expectedNormalizedSetting,
    ): void {
        $this->assertEquals(
            $expectedNormalizedSetting,
            iterator_to_array(
                $this->getSettingManager()->normalizeSetting($settingKey)
            )
        );
    }

    public static function normalizeSettingDataProvider(): iterable
    {
        yield [
            'enable_help',
            [
                'setting_key' => 'enable_help',
                'setting_name' => 'Enable Help Modal',
                'setting_description' => 'This will allow the display of help modal on help enabled pages',
                'setting_default_value' => '1',
                'setting_is_default_value' => true,
                'setting_value' => '1',
                'setting_value_options' => [[
                    'option_value' => '0',
                    'option_label' => 'Hide Help Modal'
                ],[
                    'option_value' => '1',
                    'option_label' => 'Show Help Modal',
                ],[
                    'option_value' => '2',
                    'option_label' => 'Disable Help Modal',
                ]],
            ],
        ];
    }

    private function getSettingManager(): EnumSettingManager
    {
        $globalService = GlobalsServiceFactory::getInstance();

        return new EnumSettingManager(
            new GlobalSettingDriver($globalService),
            $globalService,
        );
    }
}
