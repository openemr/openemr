<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators\Checker;

use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Core\Traits\SingletonTrait;

class UserUsernameChecker
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        return new self(
            UserRepository::getInstance(),
        );
    }

    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function isUsernameTaken(string $username): bool
    {
        return 0 !== $this->userRepository->countBy(['username' => $username]);
    }
}
