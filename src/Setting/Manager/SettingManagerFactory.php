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

namespace OpenEMR\Setting\Manager;

use OpenEMR\Common\Database\Repository\Settings\CodeTypeRepository;
use OpenEMR\Common\Database\Repository\Settings\LanguageRepository;
use OpenEMR\Common\Database\Repository\Settings\PostCalendarCategoryRepository;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\SettingDriverInterface;
use OpenEMR\Setting\Repository\ListOptionRepository;

/**
 * Usage:
 *   $settingManager = SettingManagerFactory::createNewWithDriver(
 *       UserSettingDriverFactory::getByUserId($userId)
 *   );
 *   $gaclProtect = $settingManager->getSettingValue('gacl_protect');
 */
class SettingManagerFactory
{
    public static function createNewWithDriver(
        SettingDriverInterface $driver,
        ?GlobalsService $globalsService = null,
    ): CompositeSettingManager {
        $globalsService = $globalsService ?: GlobalsServiceFactory::getInstance();

        return new CompositeSettingManager(
            $globalsService,
            [
                new AddressBookSettingManager($driver, $globalsService),
                new BooleanSettingManager($driver, $globalsService),
                new CodeTypeSettingManager(CodeTypeRepository::getInstance(), $driver, $globalsService),
                new CssSettingManager($driver, $globalsService),
                new DashboardCardsSettingManager($driver, $globalsService),
                new EncryptedHashSettingManager($driver, $globalsService),
                new EncryptedSettingManager($driver, $globalsService),
                new EnumSettingManager($driver, $globalsService),
                new HtmlDisplaySectionSettingManager($driver, $globalsService),
                new LanguageSettingManager(LanguageRepository::getInstance(), $driver, $globalsService),
                new MultiLanguageSettingManager(LanguageRepository::getInstance(), $driver, $globalsService),
                new MultiListSettingManager(ListOptionRepository::getInstance(), $driver, $globalsService),
                new NumberSettingManager($driver, $globalsService),
                new PassSettingManager($driver, $globalsService),
                new RandomUuidSettingManager($driver, $globalsService),
                new SortedListSettingManager($driver, $globalsService),
                new TabsCssSettingManager($driver, $globalsService),
                new VisitCategorySettingManager(PostCalendarCategoryRepository::getInstance(), $driver, $globalsService),

                /**
                 * ScalarSettingManager should be last in the list.
                 *
                 * @see ScalarSettingManager::isDataTypeSupported()
                 */
                new ScalarSettingManager($driver, $globalsService),
            ],
        );
    }
}
