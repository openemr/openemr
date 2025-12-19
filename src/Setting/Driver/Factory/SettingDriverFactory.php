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

namespace OpenEMR\Setting\Driver\Factory;

use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Services\Globals\GlobalsService;
use OpenEMR\Services\Globals\GlobalsServiceFactory;
use OpenEMR\Setting\Driver\GlobalSettingDriver;
use OpenEMR\Setting\Driver\UserSettingDriver;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Usage:
 *   $settingManager = SettingManagerFactory::createNewWithDriver(
 *       SettingDriverFactory::createForAuthorizedUser()
 *   );
 *   $gaclProtect = $settingManager->getSettingValue('gacl_protect');
 *
 * @phpstan-import-type TUser from UserRepository
 */
class SettingDriverFactory
{
    public static function createGlobal(): GlobalSettingDriver
    {
        return new GlobalSettingDriver(
            GlobalsServiceFactory::getInstance(),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function createForUserById(int $userId): UserSettingDriver
    {
        Assert::notEq(0, $userId);

        return new UserSettingDriver($userId);
    }

    /**
     * @phpstan-param TUser $user
     */
    public static function createForUser(array $user): UserSettingDriver
    {
        return self::createForUserById($user['id']);
    }

    /**
     * @phpstan-param TUser $user
     * @throws InvalidArgumentException
     */
    public static function createForAuthorizedUser(): UserSettingDriver
    {
        Assert::notNull($_SESSION['authUserID'] ?? null, 'User should be logged in before calling createForCurrentUser');

        return self::createForUserById($_SESSION['authUserID']);
    }
}
