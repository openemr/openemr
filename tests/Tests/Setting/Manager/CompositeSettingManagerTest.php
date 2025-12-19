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
use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Manager\BooleanSettingManager;
use OpenEMR\Setting\Manager\CodeTypeSettingManager;
use OpenEMR\Setting\Manager\CompositeSettingManager;
use OpenEMR\Setting\Manager\EncryptedHashSettingManager;
use OpenEMR\Setting\Manager\EncryptedSettingManager;
use OpenEMR\Setting\Manager\EnumSettingManager;
use OpenEMR\Setting\Manager\LanguageSettingManager;
use OpenEMR\Setting\Manager\MultiLanguageSettingManager;
use OpenEMR\Setting\Manager\NumberSettingManager;
use OpenEMR\Setting\Manager\ScalarSettingManager;
use OpenEMR\Setting\Manager\SettingManagerFactory;
use OpenEMR\Setting\Manager\VisitCategorySettingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

// @todo Move initialization to classes
require_once(__DIR__ . "/../../../../library/globals.inc.php"); // As we need section names

#[Group('setting')]
#[CoversClass(CompositeSettingManager::class)]
#[CoversMethod(CompositeSettingManager::class, 'getManagerBySettingKey')]
class CompositeSettingManagerTest extends TestCase
{
    #[Test]
    #[DataProvider('getManagerBySettingKeyDataProvider')]
    public function getManagerBySettingKeyTest(
        string $settingKey,
        string $expectedManagerFCQN
    ): void {
        $settingDriver = $this->createMock(SettingDriverInterface::class);
        $settingManager = SettingManagerFactory::createNewWithDriver($settingDriver);

        $class = new \ReflectionClass($settingManager);
        $method = $class->getMethod('getManagerBySettingKey');

        $this->assertInstanceOf(
            $expectedManagerFCQN,
            $method->invoke($settingManager, $settingKey),
        );
    }

    public static function getManagerBySettingKeyDataProvider(): iterable
    {
        // yield GlobalSetting::DATA_TYPE_ADDRESS_BOOK => [];
        // yield GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION => [];
        // yield GlobalSetting::DATA_TYPE_PASS => [];

        yield GlobalSetting::DATA_TYPE_ENUM => ['logo_position', EnumSettingManager::class];
        yield GlobalSetting::DATA_TYPE_BOOL => ['drive_encryption', BooleanSettingManager::class];
        yield GlobalSetting::DATA_TYPE_CODE_TYPES => ['default_search_code_type', CodeTypeSettingManager::class];
        yield GlobalSetting::DATA_TYPE_COLOR_CODE => ['appt_display_sets_color_1', ScalarSettingManager::class];
        // yield GlobalSetting::DATA_TYPE_CSS => ['css_header', ...];
        // yield GlobalSetting::DATA_TYPE_DEFAULT_RANDOM_UUID => ['unique_installation_id', ...];
        yield GlobalSetting::DATA_TYPE_DEFAULT_VISIT_CATEGORY => ['default_visit_category', VisitCategorySettingManager::class];
        yield GlobalSetting::DATA_TYPE_ENCRYPTED => ['gateway_api_key', EncryptedSettingManager::class];
        yield GlobalSetting::DATA_TYPE_ENCRYPTED_HASH => ['sphere_credit_void_confirm_pin', EncryptedHashSettingManager::class];
        yield GlobalSetting::DATA_TYPE_HOUR => ['schedule_start', ScalarSettingManager::class];
        yield GlobalSetting::DATA_TYPE_LANGUAGE => ['language_default', LanguageSettingManager::class];
        // yield GlobalSetting::DATA_TYPE_MULTI_DASHBOARD_CARDS => ['hide_dashboard_cards', ...];
        yield GlobalSetting::DATA_TYPE_MULTI_LANGUAGE_SELECT => ['language_menu_other', MultiLanguageSettingManager::class];
        // yield GlobalSetting::DATA_TYPE_MULTI_SORTED_LIST_SELECTOR => ['ccda_ccd_section_sort_order', ...];
        yield GlobalSetting::DATA_TYPE_NUMBER => ['age_display_limit', NumberSettingManager::class];
        // yield GlobalSetting::DATA_TYPE_TABS_CSS => ['theme_tabs_layout', ...];
        yield GlobalSetting::DATA_TYPE_TEXT => ['openemr_name', ScalarSettingManager::class];
    }
}
