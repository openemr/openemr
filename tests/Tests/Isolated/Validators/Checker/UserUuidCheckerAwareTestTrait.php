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

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators\Checker;

use OpenEMR\Validators\Checker\UserUsernameChecker;
use OpenEMR\Validators\Checker\UserUuidChecker;
use PHPUnit\Framework\MockObject\MockObject;

trait UserUuidCheckerAwareTestTrait
{
    private const UUID_EXISTING = '550e8400-e29b-41d4-a716-446655440000';

    /**
     * @return MockObject&UserUsernameChecker
     */
    private function getUserUuidCheckerMock(): MockObject
    {
        $checker = $this->createMock(UserUuidChecker::class);
        $checker->method('isUserUuidExists')->willReturnCallback(
            fn (string $uuid): bool => self::UUID_EXISTING === $uuid,
        );

        return $checker;
    }
}
