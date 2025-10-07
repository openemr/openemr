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

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Common\Auth\Password\PasswordStrengthChecker;
use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\UserValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

#[Group('isolated')]
#[Group('validator')]
#[Group('user-validator')]
#[CoversClass(UserValidator::class)]
#[CoversMethod(UserValidator::class, 'validate')]
#[CoversMethod(UserValidator::class, 'configureValidatorContext')]
class UserValidatorIsolatedTest extends TestCase
{
    #[Test]
    #[DataProvider('validateDataProvider')]
    public function validateValidationTest(
        ?array $validatorArgs,
        string $context,
        array $data,
        array $expectedValidationErrors,
    ): void {
        Assert::oneOf($context, [
            BaseValidator::DATABASE_INSERT_CONTEXT,
            BaseValidator::DATABASE_UPDATE_CONTEXT,
        ]);

        $userValidator = new UserValidator(...array_merge([
            'userRepository' => $this->createMock(UserRepository::class),
            'passwordStrengthChecker' => PasswordStrengthChecker::getInstance(),
            'passwordMinLength' => 9,
            'passwordMaxLength' => 72,
            'strongPassword' => true,
        ], $validatorArgs ?? []));

        $result = $userValidator->validate($data, $context);
        $this->assertEquals($expectedValidationErrors, $result->getValidationMessages());
    }

    public static function validateDataProvider(): iterable
    {
        // Failing on insert
        yield 'Empty payload' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [], [
            'fname' => [
                'Required::NON_EXISTENT_KEY' => 'fname must be provided, but does not exist'
            ],
            'lname' => [
                'Required::NON_EXISTENT_KEY' => 'lname must be provided, but does not exist'
            ],
            'username' => [
                'Required::NON_EXISTENT_KEY' => 'username must be provided, but does not exist'
            ],
        ]];

        yield 'One mandatory field provided' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
        ], [
            'lname' => [
                'Required::NON_EXISTENT_KEY' => 'lname must be provided, but does not exist'
            ],
            'username' => [
                'Required::NON_EXISTENT_KEY' => 'username must be provided, but does not exist'
            ],
        ]];

        yield 'Two mandatory fields provided' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
        ], [
            'username' => [
                'Required::NON_EXISTENT_KEY' => 'username must be provided, but does not exist'
            ],
        ]];

        yield 'Too long fname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => str_pad('', 256, '_'),
            'lname' => 'Correct',
            'username' => 'testuser0',
        ], [
            'fname' => [
                'LengthBetween::TOO_LONG' => 'First Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Too short lname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => str_pad('', 1, '_'),
            'username' => 'testuser0',
        ], [
            'lname' => [
                'LengthBetween::TOO_SHORT' => 'Last Name must be 2 characters or longer',
            ],
        ]];

        yield 'Too long lname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => str_pad('', 256, '_'),
            'username' => 'testuser0',
        ], [
            'lname' => [
                'LengthBetween::TOO_LONG' => 'Last Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Invalid email' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'email' => 'invalid@examplecom',
            'username' => 'testuser0',
        ], [
            'email' => [
                'email' => 'Email invalid@examplecom is not a valid email',
            ],
        ]];

        yield 'Too short username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 2, '_'),
        ], [
            'username' => [
                'LengthBetween::TOO_SHORT' => 'Username must be 3 characters or longer',
            ],
        ]];

        yield 'Too long username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 33, '_'),
        ], [
            'username' => [
                'LengthBetween::TOO_LONG' => 'Username must be 32 characters or shorter',
            ],
        ]];

        yield 'Too short password' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 8, '_'),
        ], [
            'password' => [
                'LengthBetween::TOO_SHORT' => 'Password must be 9 characters or longer',
            ],
        ]];

        yield 'Too long password' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 73, '_'),
        ], [
            'password' => [
                'LengthBetween::TOO_LONG' => 'Password must be 72 characters or shorter',
            ],
        ]];

        yield 'Not strong password passed' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB', 9, '_'), // Valid length, but missing numerics
        ], [
            'password' => [
                'Callback::INVALID_VALUE' => 'Password is invalid',
            ],
        ]];

        // Succeeding on insert
        yield 'Strong password passed' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 9, '_'), // Valid length, passing strength check
        ], [
            // No errors
        ]];

        yield 'Not strong password passed when strong password is not required' => [[
            'strongPassword' => false, // Allow not strong passwords
        ], BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB', 9, '_'), // Valid length, but missing numerics
        ], [
            // No errors
        ]];

        // Failing on update
        // @todo Fix UserValidator to have all other fields optional
//        yield 'Empty payload on update' => [null, BaseValidator::DATABASE_UPDATE_CONTEXT, [], [
//            'uuid' => [
//                'Required::NON_EXISTENT_KEY' => 'uuid must be provided, but does not exist',
//            ],
//        ]];
    }
}
