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

namespace OpenEMR\Setting\Validator\Factory;

use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\Factory\SettingDriverFactory;
use OpenEMR\Setting\Driver\GlobalSettingDriver;
use OpenEMR\Setting\Manager\SettingManagerFactory;
use OpenEMR\Setting\Service\Global\GlobalSettingSectionService;
use OpenEMR\Setting\Service\User\UserSpecificSettingSectionService;
use OpenEMR\Setting\Validator\GlobalSettingSectionValidator;
use OpenEMR\Setting\Validator\UserSpecificSettingSectionValidator;

class SettingSectionValidatorFactory
{
    public static function createUserSpecific(int $userId, string $sectionSlug): UserSpecificSettingSectionValidator
    {
        $globalsService = GlobalsServiceFactory::getInstance();

        return new UserSpecificSettingSectionValidator(
            $sectionSlug,
            UserSpecificSettingSectionService::getInstance(),
            SettingManagerFactory::createNewWithDriver(
                SettingDriverFactory::createForUserById($userId),
            ),
            $globalsService,
        );
    }

    public static function createGlobal(string $sectionSlug): GlobalSettingSectionValidator
    {
        $globalsService = GlobalsServiceFactory::getInstance();

        return new GlobalSettingSectionValidator(
            $sectionSlug,
            GlobalSettingSectionService::getInstance(),
            SettingManagerFactory::createNewWithDriver(
                new GlobalSettingDriver($globalsService)
            ),
            $globalsService,
        );
    }
}
