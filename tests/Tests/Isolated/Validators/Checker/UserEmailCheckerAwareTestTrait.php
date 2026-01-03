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

use OpenEMR\Validators\Checker\UserEmailChecker;
use PHPUnit\Framework\MockObject\MockObject;

trait UserEmailCheckerAwareTestTrait
{
    private const EMAIL_TAKEN = 'taken@example.com';

    /**
     * @return MockObject&UserEmailChecker
     */
    private function getUserEmailCheckerMock(): MockObject
    {
        $checker = $this->createMock(UserEmailChecker::class);
        $checker->method('isEmailTaken')->willReturnCallback(
            fn (string $email): bool => self::EMAIL_TAKEN === $email,
        );

        return $checker;
    }
}
