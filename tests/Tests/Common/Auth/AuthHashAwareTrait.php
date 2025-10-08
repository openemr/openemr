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

namespace OpenEMR\Tests\Common\Auth;

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 */
trait AuthHashAwareTrait
{
    private function assertCorrectUserPassword(string $expectedPassword, int $id, string $username, string $message = ''): void
    {
        $passwordHash = QueryUtils::getSingleScalarResultBy('users_secure', 'password', [
            'id' => $id,
            'username' => $username,
        ]);

        self::assertThat(
            AuthHash::passwordVerify($expectedPassword, $passwordHash),
            self::isTrue(),
            '' !== $message ?: sprintf(
                "Expected %s's password to be '%s', but its hash does not match hash at database",
                $username,
                $expectedPassword
            )
        );
    }
}
