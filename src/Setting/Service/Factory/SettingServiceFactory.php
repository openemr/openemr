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

namespace OpenEMR\Setting\Service\Factory;

use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\Factory\SettingDriverFactory;
use OpenEMR\Setting\Manager\SettingManagerFactory;
use OpenEMR\Setting\Service\Global\GlobalSettingSectionService;
use OpenEMR\Setting\Service\Global\GlobalSettingService;
use OpenEMR\Setting\Service\User\UserSpecificSettingService;

class SettingServiceFactory
{
    public static function createGlobal(): GlobalSettingService
    {
        return new GlobalSettingService(
            GlobalsServiceFactory::getInstance(),
            GlobalSettingSectionService::getInstance(),
            SettingManagerFactory::createNewWithDriver(
                SettingDriverFactory::createGlobal(),
            ),
        );
    }

    public static function createUserSpecificByUserId(string $userId): UserSpecificSettingService
    {
        return new UserSpecificSettingService(
            GlobalsServiceFactory::getInstance(),
            GlobalSettingSectionService::getInstance(),
            SettingManagerFactory::createNewWithDriver(
                SettingDriverFactory::createForUserById((int) $userId),
            ),
        );
    }
}
