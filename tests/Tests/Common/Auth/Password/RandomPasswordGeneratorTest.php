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
use OpenEMR\Common\Auth\Password\RandomPasswordGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

#[Group('auth')]
#[Group('password')]
#[Group('generator')]
#[CoversClass(RandomPasswordGenerator::class)]
#[CoversMethod(RandomPasswordGenerator::class, 'generatePassword')]
class RandomPasswordGeneratorTest extends TestCase
{
    #[Test]
    #[DataProvider('generatePasswordExceptionDataProvider')]
    public function generatePasswordExceptionTest(
        array $constructorArguments,
        int $length,
        string $expectedExceptionMessage,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $generator = new RandomPasswordGenerator(...$constructorArguments);
        $generator->generatePassword($length);
    }

    public static function generatePasswordExceptionDataProvider(): iterable
    {
        yield 'Min not specified, Max < 4' => [[0, 3], 16, 'Maximum length must be at least 4, got 3'];
        yield 'Max < 4' => [[1, 3], 16, 'Maximum length must be at least 4, got 3'];

        yield 'Length < 4' => [[1, 4], 3, 'Password length must be at least 4, got 3'];
        yield 'Max not specified, Length < 4' => [[1, 0], 3, 'Password length must be at least 4, got 3'];
        yield 'Min and Max not specified, Length < 4' => [[0, 0], 3, 'Password length must be at least 4, got 3'];

        yield 'Min > Max' => [[20, 10], 16, 'Maximum length must be at least 20 (greater than minimal length), got 10'];
    }

    #[Test]
    #[DataProvider('generatePasswordDataProvider')]
    public function generatePasswordTest(
        array $constructorArguments,
        int $length,
        int $expectedPasswordLength,
    ): void {
        $checker = new PasswordStrengthChecker();

        $generator = new RandomPasswordGenerator(...$constructorArguments);
        $password = $generator->generatePassword($length);

        $this->assertSame($expectedPasswordLength, strlen($password));
        $this->assertTrue($checker->isPasswordStrongEnough($password), sprintf(
            'Generated password %s is not strong enough',
            $password,
        ));
    }

    public static function generatePasswordDataProvider(): iterable
    {
        yield 'Defaults' => [[], 16, 16];
        yield 'Length < Real life min' => [[9, 72], 8, 9];
        yield 'Length > Real life max' => [[9, 72], 73, 72];
    }
}
