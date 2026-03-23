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
use OpenEMR\Setting\Driver\GlobalSettingDriver;
use OpenEMR\Setting\Driver\UserSettingDriver;
use OpenEMR\Setting\Manager\SettingManagerFactory;
use OpenEMR\Setting\Service\Global\GlobalSettingSectionService;
use OpenEMR\Setting\Service\Global\GlobalSettingService;
use OpenEMR\Setting\Service\User\UserSpecificSettingSectionService;
use OpenEMR\Setting\Service\User\UserSpecificSettingService;

class SettingServiceFactory
{
    public static function createUserSpecificByUuid(string $uuid): UserSpecificSettingService
    {
        return new UserSpecificSettingService(
            GlobalsServiceFactory::getInstance(),
            UserSpecificSettingSectionService::getInstance(),
            SettingManagerFactory::createNewWithDriver(
                UserSettingDriver::getInstanceByUuid($uuid),
            ),
        );
    }

    public static function createGlobal(): GlobalSettingService
    {
        return new GlobalSettingService(
            GlobalsServiceFactory::getInstance(),
            GlobalSettingSectionService::getInstance(),
            SettingManagerFactory::createNewWithDriver(
                GlobalSettingDriver::getInstance(),
            ),
        );
    }
}
