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

namespace OpenEMR\Tests\Common\Auth;

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Database\Repository\User\UserSecureRepository;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use OpenEMR\Common\Database\Repository\User\UserRepository;

/**
 * @todo Verify password isolated way - by trying to login with that username-password rather than checking database records
 *
 * @phpstan-import-type TUser from UserRepository
 * @mixin TestCase
 */
trait AssertCorrectUserPasswordAwareTrait
{
    protected readonly UserSecureRepository $userSecureRepository;

    /**
     * @param array&TUser $user
     */
    private function assertCorrectUserPassword(string $expectedPassword, array $user, string $message = ''): void
    {
        Assert::keyExists($user, 'id');
        Assert::keyExists($user, 'username');

        $passwordHash = $this->userSecureRepository->getSingleScalarResultBy('password', [
            'id' => $user['id'],
            'username' => $user['username'],
        ]);

        self::assertThat(
            AuthHash::passwordVerify($expectedPassword, $passwordHash),
            self::isTrue(),
            '' !== $message ?: sprintf(
                "Expected %s's password to be '%s', but its hash does not match hash at database",
                $user['username'],
                $expectedPassword,
            )
        );
    }
}
