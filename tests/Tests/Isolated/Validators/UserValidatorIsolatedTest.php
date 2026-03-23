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

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Validators;

use OpenEMR\Common\Auth\Password\PasswordStrengthChecker;
use OpenEMR\Tests\Common\AssertValidNamedArgumentsTrait;
use OpenEMR\Tests\Isolated\Validators\Checker\UserEmailCheckerAwareTestTrait;
use OpenEMR\Tests\Isolated\Validators\Checker\UserUsernameCheckerAwareTestTrait;
use OpenEMR\Tests\Isolated\Validators\Checker\UserUuidCheckerAwareTestTrait;
use OpenEMR\Validators\BaseValidator;
use OpenEMR\Validators\Checker\EmailChecker;
use OpenEMR\Validators\Checker\UserEmailChecker;
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
    use AssertValidNamedArgumentsTrait;
    use UserEmailCheckerAwareTestTrait;
    use UserUsernameCheckerAwareTestTrait;
    use UserUuidCheckerAwareTestTrait;

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

        self::assertValidNamedArguments($validatorArgs, UserValidator::class);

        $userEmailChecker = $this->createMock(UserEmailChecker::class);
        $userEmailChecker->method('isEmailTaken')->willReturnCallback(
            fn (string $email): bool => 'taken@example.com' !== $email,
        );

        $userValidator = new UserValidator(...array_merge([
            'emailChecker' => EmailChecker::getInstance(),
            'userEmailChecker' => $this->getUserEmailCheckerMock(),
            'userUsernameChecker' => $this->getUserUsernameCheckerMock(),
            'userUuidChecker' => $this->getUserUuidCheckerMock(),
            'passwordStrengthChecker' => PasswordStrengthChecker::getInstance(),
            'passwordMinLength' => 9,
            'passwordMaxLength' => 72,
            'strongPasswordRequired' => true,
        ], $validatorArgs ?? []));

        $result = $userValidator->validate($data, $context);
        $this->assertEquals($expectedValidationErrors, $result->getValidationMessages());
    }

    public static function validateDataProvider(): iterable
    {
        yield 'Insert - Fail when empty payload' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [], [
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

        yield 'Insert - Fail when only one mandatory field provided' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
        ], [
            'lname' => [
                'Required::NON_EXISTENT_KEY' => 'lname must be provided, but does not exist'
            ],
            'username' => [
                'Required::NON_EXISTENT_KEY' => 'username must be provided, but does not exist'
            ],
        ]];

        yield 'Insert - Fail when only two mandatory fields provided' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
        ], [
            'username' => [
                'Required::NON_EXISTENT_KEY' => 'username must be provided, but does not exist'
            ],
        ]];

        // Fname
        yield 'Insert - Fail - Empty fname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => '',
            'lname' => 'Correct',
            'username' => 'testuser0',
        ], [
            'fname' => [
                'NotEmpty::EMPTY_VALUE' => 'First Name must not be empty',
            ],
        ]];

        yield 'Insert - Success - Minimal length fname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'A', // Minimal length - 1
            'lname' => 'Correct',
            'username' => 'testuser0',
        ], []];

        yield 'Insert - Fail - Too long fname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => str_pad('', 256, '_'),
            'lname' => 'Correct',
            'username' => 'testuser0',
        ], [
            'fname' => [
                'LengthBetween::TOO_LONG' => 'First Name must be 255 characters or shorter',
            ],
        ]];

        yield 'Insert - Success - Max length fname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => str_pad('', 255, '_'), // Max length - 255
            'lname' => 'Correct',
            'username' => 'testuser0',
        ], []];


        // Lname
        yield 'Insert - Fail - Empty lname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => '',
            'username' => 'testuser0',
        ], [
            'lname' => [
                'NotEmpty::EMPTY_VALUE' => 'Last Name must not be empty',
            ],
        ]];

        yield 'Insert - Fail - Too short lname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => str_pad('', 1, '_'),
            'username' => 'testuser0',
        ], [
            'lname' => [
                'LengthBetween::TOO_SHORT' => 'Last Name must be 2 characters or longer',
            ],
        ]];

        yield 'Insert - Fail - Too long lname' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => str_pad('', 256, '_'),
            'username' => 'testuser0',
        ], [
            'lname' => [
                'LengthBetween::TOO_LONG' => 'Last Name must be 255 characters or shorter',
            ],
        ]];

        // Email
        yield 'Insert - Fail - Invalid email' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'email' => 'invalid@examplecom',
            'username' => 'testuser0',
        ], [
            'email' => [
                'Email::INVALID' => 'Email invalid@examplecom is not a valid email',
            ],
        ]];

        yield 'Insert - Fail - Taken email' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'email' => self::EMAIL_TAKEN,
            'username' => 'testuser0',
        ], [
            'email' => [
                'Email::TAKEN' => 'Email taken@example.com is taken',
            ],
        ]];

        // Username
        yield 'Insert - Fail - Empty username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => '',
        ], [
            'username' => [
                'NotEmpty::EMPTY_VALUE' => 'Username must not be empty',
            ],
        ]];

        yield 'Insert - Fail - Too short username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 2, '_'),
        ], [
            'username' => [
                'LengthBetween::TOO_SHORT' => 'Username must be 3 characters or longer',
            ],
        ]];

        yield 'Insert - Success - Min length username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 3, '_'), // Min length - 3
        ], []];

        yield 'Insert - Fail - Too long username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 33, '_'),
        ], [
            'username' => [
                'LengthBetween::TOO_LONG' => 'Username must be 32 characters or shorter',
            ],
        ]];

        yield 'Insert - Success - Max length username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => str_pad('', 32, '_'), // Max length - 32
        ], []];

        yield 'Insert - Fail - Taken username' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => self::USERNAME_TAKEN,
        ], [
            'username' => [
                'Username::TAKEN' => 'Username taken-username is taken',
            ],
        ]];

        // Password
        yield 'Insert - Fail - Weak and too short password' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => 'weak',
        ], [
            'password' => [
                'LengthBetween::TOO_SHORT' => 'Password must be 9 characters or longer',
                'Password::TOO_WEAK' => 'Provided password is too weak',
            ],
        ]];

        yield 'Insert - Fail - Enough length but weak password' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB', 9, '_'), // Valid length, but weak (missing numerics)
        ], [
            'password' => [
                'Password::TOO_WEAK' => 'Provided password is too weak',
            ],
        ]];

        yield 'Insert - Fail - Strong but too short password' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 8, '_'),
        ], [
            'password' => [
                'LengthBetween::TOO_SHORT' => 'Password must be 9 characters or longer',
            ],
        ]];

        yield 'Insert - Fail - Too long password' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 73, '_'),
        ], [
            'password' => [
                'LengthBetween::TOO_LONG' => 'Password must be 72 characters or shorter',
            ],
        ]];

        yield 'Insert - Success - Strong password passed' => [null, BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 9, '_'), // Valid length, passing strength check
        ], [
            // No errors
        ]];

        yield 'Insert - Success - Not strong password passed when strong password is not required' => [[
            'strongPasswordRequired' => false, // Allow not strong passwords
        ], BaseValidator::DATABASE_INSERT_CONTEXT, [
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB', 9, '_'), // Valid length, but missing numerics
        ], [
            // No errors
        ]];

        // Update
        yield 'Update - Fail - Empty payload' => [null, BaseValidator::DATABASE_UPDATE_CONTEXT, [], [
            'uuid' => [
                'Required::NON_EXISTENT_KEY' => 'uuid must be provided, but does not exist',
            ],
        ]];

        yield 'Update - Fail - Minimal payload - Invalid uuid' => [null, BaseValidator::DATABASE_UPDATE_CONTEXT, [
            'uuid' => 'invalid-uuid',
        ], [
            'uuid' => [
                'Uuid::INVALID_UUID' => 'uuid must be a valid UUID (valid format)',
                'Uuid::NON_EXISTENT_UUID' => 'UUID invalid-uuid does not exist',
            ]
        ]];

        yield 'Update - Fail - Minimal payload - Valid but not existing uuid' => [null, BaseValidator::DATABASE_UPDATE_CONTEXT, [
            'uuid' => '550e8400-e29b-41d4-a716-446655440001',
        ], [
            'uuid' => [
                'Uuid::NON_EXISTENT_UUID' => 'UUID 550e8400-e29b-41d4-a716-446655440001 does not exist'
            ]
        ]];

        yield 'Update - Success - Minimal payload - Valid uuid' => [null, BaseValidator::DATABASE_UPDATE_CONTEXT, [
            'uuid' => self::UUID_EXISTING,
        ], []];


        yield 'Update - Success - Typical payload' => [null, BaseValidator::DATABASE_UPDATE_CONTEXT, [
            'uuid' => self::UUID_EXISTING,
            'fname' => 'Correct',
            'lname' => 'Correct',
            'username' => 'testuser0',
            'password' => str_pad('aB1@', 9, '_'), // Valid length, passing strength check
        ], []];
    }
}
