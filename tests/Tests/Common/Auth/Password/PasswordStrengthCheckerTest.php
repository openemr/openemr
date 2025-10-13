<?php

declare(strict_types=1);

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Common\Auth\Password;

use OpenEMR\Common\Auth\Password\PasswordStrengthChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('auth')]
#[Group('password')]
#[Group('checker')]
#[CoversClass(PasswordStrengthChecker::class)]
#[CoversMethod(PasswordStrengthChecker::class, 'isPasswordStrongEnough')]
class PasswordStrengthCheckerTest extends TestCase
{
    #[Test]
    #[DataProvider('isPasswordStrongEnoughDataProvider')]
    public function isPasswordStrongEnoughTest(
        string $password,
        bool $expectedIsPasswordStrongEnough,
    ): void {
        $checker = new PasswordStrengthChecker();
        $this->assertEquals(
            $expectedIsPasswordStrongEnough,
            $checker->isPasswordStrongEnough($password),
        );
    }

    public static function isPasswordStrongEnoughDataProvider(): iterable
    {
        yield 'Empty' => ['', false];
        yield 'Small length' => ['aB1', false];
        yield 'Good length, but not meet rules' => ['aB1a', false];
        yield 'Valid (minimal)' => ['aB1@', true];
    }
}
