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

namespace OpenEMR\Common\Auth\Password;

use OpenEMR\Core\Traits\SingletonTrait;

/**
 * This checker should not check password length requirements
 *
 * Password should contain at least each of the following items:
 * - A number
 * - A lowercase letter
 * - An uppercase letter
 * - A special character (not a letter or number)
 *
 * Examples:
 * - aB1@
 */
class PasswordStrengthChecker
{
    use SingletonTrait;

    private const MANDATORY_STRENGTH_REQUIREMENTS = [
        '/[a-z]+/',
        '/[A-Z]+/',
        "/\d+/",
        "/[\W_]+/",
    ];

    public function isPasswordStrongEnough(string $password): bool
    {
        return array_sum(array_map(
            static fn (string $strengthRequirementRegexp): int => (1 === preg_match($strengthRequirementRegexp, $password)) ? 1 : 0,
            self::MANDATORY_STRENGTH_REQUIREMENTS,
        )) >= count(self::MANDATORY_STRENGTH_REQUIREMENTS);
    }
}
