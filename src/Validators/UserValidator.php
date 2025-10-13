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

namespace OpenEMR\Validators;

use OpenEMR\Common\Auth\Password\PasswordStrengthChecker;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Utils\ValidationUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

class UserValidator extends BaseValidator
{
    private PasswordStrengthChecker $passwordStrengthChecker;

    public function __construct()
    {
        $this->passwordStrengthChecker = new PasswordStrengthChecker();

        parent::__construct();
    }

    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        if (BaseValidator::DATABASE_UPDATE_CONTEXT === $contextName) {
            $validator
                ->required('uuid', 'uuid')
                ->string()
                ->callback(fn ($value): bool => $this->isUserUuidExists($value) ?: throw new InvalidValueException(
                    sprintf('UUID %s does not exist', $value),
                    $value,
                ))
            ;
        }

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
                $value,
            ))
        ;

        $validator
            ->required('username', 'Username')
            ->string()
            ->lengthBetween(3, 32)
            ->callback(fn ($value): bool => !$this->isUsernameTaken($value) ?: throw new InvalidValueException(
                sprintf('Username %s is taken', $value),
                $value,
            ))
        ;

        $passwordChain = $validator
            ->optional('password', 'Password')
            ->string()
            ->lengthBetween(
                ((int) $GLOBALS['gbl_minimum_password_length']) ?: 9,
                ((int) $GLOBALS['gbl_maximum_password_length']) ?: 72,
            )
        ;

        if ((bool) $GLOBALS['secure_password']) {
            $passwordChain
                ->callback(fn ($password): bool => $this->passwordStrengthChecker->isPasswordStrongEnough($password))
            ;
        }
    }

    public function isEmailTaken(string $email): bool
    {
        return 0 !== QueryUtils::countBy('users', ['email' => $email]);
    }

    public function isUsernameTaken(string $username): bool
    {
        return 0 !== QueryUtils::countBy('users', ['username' => $username]);
    }

    public function isUserUuidExists(string $uuid): bool
    {
        try {
            $uuid = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException $e) {
            return false;
        }

        return 0 !== QueryUtils::countBy('users', ['uuid' => $uuid]);
    }
}
