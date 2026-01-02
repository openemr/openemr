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

namespace OpenEMR\Common\Database\Repository\User;

use OpenEMR\Common\Database\DatabaseManager;
use OpenEMR\Common\Database\Repository\IdAwareAbstractRepository;

/**
 * Usage:
 *   $userSecureRepository = UserSecureRepository::getInstance();
 *   $affected = $userRepository->remove($id);
 *   $affected = $userRepository->removeBy(['username' => $username]);
 *
 * @phpstan-type TUserSecure = array{
 *     id: int,
 *     username: string,
 *     password: string,
 *     password_history1: ?string,
 *     password_history2: ?string,
 *     password_history3: ?string,
 *     password_history4: ?string,
 *     last_challenge_response: ?string,
 *     login_work_area: ?string,
 *     last_update_password: string,
 *     last_update: string,
 *     total_login_fail_counter: int,
 *     login_fail_counter: int,
 *     last_login_fail: ?int,
 *     auto_block_emailed: int,
 * }
 *
 * @template-extends IdAwareAbstractRepository<TUserSecure>
 */
class UserSecureRepository extends IdAwareAbstractRepository
{
    protected static function createInstance(): static
    {
        return new self(
            DatabaseManager::getInstance(),
            'users_secure',
        );
    }
}
