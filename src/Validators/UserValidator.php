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

namespace OpenEMR\Validators;

use OpenEMR\Common\Auth\Password\PasswordStrengthChecker;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\Traits\SingletonTrait;
use OpenEMR\Validators\Checker\EmailChecker;
use OpenEMR\Validators\Checker\UserEmailChecker;
use OpenEMR\Validators\Checker\UserUsernameChecker;
use OpenEMR\Validators\Checker\UserUuidChecker;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;
use Webmozart\Assert\Assert;

class UserValidator extends BaseValidator
{
    use SingletonTrait;

    public const UPDATABLE_FIELDS = [
        'username',
        'email',
        'password',

        'title',
        'fname',
        'lname',
        'mname',
        'federaltaxid',
        'federaldrugid',
        'upin',
        'facility_id',
        'facility',
        'npi',
        'specialty',
        'billname',
        'url',
        'assistant',
        'organization',
        'valedictory',
        'street',
        'streetb',
        'city',
        'state',
        'zip',
        'phone',
        'fax',
        'phonew1',
        'phonecell',
        'notes',
        'state_license_number',
    ];

    protected static function createInstance(): static
    {
        $globals = OEGlobalsBag::getInstance();

        return new UserValidator(
            EmailChecker::getInstance(),
            UserEmailChecker::getInstance(),
            UserUsernameChecker::getInstance(),
            UserUuidChecker::getInstance(),
            PasswordStrengthChecker::getInstance(),
            $globals->getInt('gbl_minimum_password_length', 9),
            $globals->getInt('gbl_maximum_password_length', 72),
            $globals->getBoolean('secure_password', true),
        );
    }

    public function __construct(
        private readonly EmailChecker $emailChecker,
        private readonly UserEmailChecker $userEmailChecker,
        private readonly UserUsernameChecker $userUsernameChecker,
        private readonly UserUuidChecker $userUuidChecker,
        private readonly PasswordStrengthChecker $passwordStrengthChecker,
        private readonly int $passwordMinLength,
        private readonly int $passwordMaxLength,
        private readonly bool $strongPasswordRequired,
    ) {
        parent::__construct();
    }

    public function assertNoExtraFields(array $data, string $context): void
    {
        if (self::DATABASE_UPDATE_CONTEXT === $context) {
            $keys = array_values(array_filter(
                array_keys($data),
                fn ($key) => 'uuid' !== $key,
            ));
            $unknownFields = array_diff($keys, self::UPDATABLE_FIELDS);

            Assert::isEmpty($unknownFields, sprintf(
                'Unknown allowed fields: %s. Valid ones: %s.',
                implode(', ', $unknownFields),
                implode(', ', self::UPDATABLE_FIELDS),
            ));
        }
    }

    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        $validator
            ->required('fname', 'First Name')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->string()
            ->lengthBetween(1, 255)
        ;

        $validator
            ->required('lname', 'Last Name')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->string()
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('email', 'Email')
            ->string()
            ->callback(fn ($value): bool => $this->emailChecker->isValidEmail($value) ?: throw new InvalidValueException(
                sprintf('Email %s is not a valid email', $value),
                'Email::INVALID',
            ))
            ->callback(fn ($value): bool => !$this->userEmailChecker->isEmailTaken($value) ?: throw new InvalidValueException(
                sprintf('Email %s is taken', $value),
                'Email::TAKEN',
            ))
        ;

        $validator
            ->required('username', 'Username')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->string()
            ->lengthBetween(3, 32)
            ->callback(fn ($value): bool => !$this->userUsernameChecker->isUsernameTaken($value) ?: throw new InvalidValueException(
                sprintf('Username %s is taken', $value),
                'Username::TAKEN',
            ))
        ;

        $passwordChain = $validator
            ->optional('password', 'Password')
            ->string()
            ->lengthBetween(
                $this->passwordMinLength,
                $this->passwordMaxLength,
            )
        ;

        if ($this->strongPasswordRequired) {
            $passwordChain
                ->callback(fn ($password): bool => $this->passwordStrengthChecker->isPasswordStrongEnough($password) ?: throw new InvalidValueException(
                    'Provided password is too weak',
                    'Password::TOO_WEAK',
                ))
            ;
        }

        if (BaseValidator::DATABASE_UPDATE_CONTEXT !== $contextName) {
            return;
        }

        $validator
            ->required('uuid', 'uuid')
            ->uuid()
            ->callback(fn ($value): bool => $this->userUuidChecker->isUserUuidExists($value) ?: throw new InvalidValueException(
                sprintf('UUID %s does not exist', $value),
                'Uuid::NON_EXISTENT_UUID',
            ))
        ;
    }
}
