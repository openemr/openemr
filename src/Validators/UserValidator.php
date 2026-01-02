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

namespace OpenEMR\Validators;

use OpenEMR\Common\Auth\Password\PasswordStrengthChecker;
use OpenEMR\Common\Database\Repository\User\UserRepository;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\Traits\SingletonTrait;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;

class UserValidator extends BaseValidator
{
    use SingletonTrait;

    protected static function createInstance(): static
    {
        $globals = OEGlobalsBag::getInstance();

        return new UserValidator(
            UserRepository::getInstance(),
            PasswordStrengthChecker::getInstance(),
            $globals->getInt('gbl_minimum_password_length', 9),
            $globals->getInt('gbl_maximum_password_length', 72),
            $globals->getBoolean('secure_password', true),
        );
    }

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PasswordStrengthChecker $passwordStrengthChecker,
        private readonly int $passwordMinLength,
        private readonly int $passwordMaxLength,
        private readonly bool $strongPassword,
    ) {
        parent::__construct();
    }

    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        $validator
            ->required('fname', 'First Name')
            ->string()
            ->lengthBetween(1, 255)
        ;

        $validator
            ->required('lname', 'Last Name')
            ->string()
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('email', 'Email')
            ->string()
            ->required(static fn ($values): bool => !in_array($values['email'] ?? '', ['', null], true))
            ->callback(fn ($value): bool => ValidationUtils::isValidEmail($value) ?: throw new InvalidValueException(
                sprintf('Email %s is not a valid email', $value),
                'email',
            ))
            ->callback(fn ($value): bool => !$this->isEmailTaken($value) ?: throw new InvalidValueException(
                sprintf('Email %s is taken', $value),
                'email',
            ))
        ;

        $validator
            ->required('username', 'Username')
            ->string()
            ->lengthBetween(3, 32)
            ->callback(fn ($value): bool => !$this->isUsernameTaken($value) ?: throw new InvalidValueException(
                sprintf('Username %s is taken', $value),
                'username',
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

        if ($this->strongPassword) {
            $passwordChain
                ->callback(fn ($password): bool => $this->passwordStrengthChecker->isPasswordStrongEnough($password))
            ;
        }

        if (BaseValidator::DATABASE_UPDATE_CONTEXT !== $contextName) {
            return;
        }

        $validator
            ->required('uuid', 'uuid')
            ->string()
            ->callback(fn ($value): bool => $this->isUserUuidExists($value) ?: throw new InvalidValueException(
                sprintf('UUID %s does not exist', $value),
                $value,
            ))
        ;
    }

    public function isEmailTaken(string $email): bool
    {
        return 0 !== $this->userRepository->countBy(['email' => $email]);
    }

    public function isUsernameTaken(string $username): bool
    {
        return 0 !== $this->userRepository->countBy(['username' => $username]);
    }

    public function isUserIdExists(string $id): bool
    {
        return 0 !== $this->userRepository->countBy(['id' => $id]);
    }

    public function isUserUuidExists(string $uuid): bool
    {
        return 0 !== $this->userRepository->countByUuid($uuid);
    }
}
