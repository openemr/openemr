<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

use OpenEMR\Common\Utils\ValidationUtils;
use Particle\Validator\Validator;
use Particle\Validator\Exception\InvalidValueException;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

/**
 * Supports Patient Record Validation.
 */
class PatientValidator extends BaseValidator
{
    /**
     * Validates that a patient UUID exists in the database
     */
    public function isExistingUuid(string $uuid): bool
    {
        try {
            $uuidLookup = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException) {
            return false;
        }

        $result = sqlQuery(
            'SELECT uuid AS uuid FROM patient_data WHERE uuid = ?',
            [$uuidLookup]
        );

        $existingUuid = $result['uuid'] ?? null;
        return $existingUuid != null;
    }

    /**
     * Configures validations for the Patient DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than pid are not required.
     */
    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        // Insert & update
        $validator
            ->optional('fname', 'First Name')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->lengthBetween(1, 255)
        ;

        $validator
            ->optional('lname', 'Last Name')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('sex', 'Gender')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->lengthBetween(4, 30)
        ;

        $validator
            ->optional('DOB', 'Date of Birth')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->datetime('Y-m-d')
        ;

        // callback functions are not called for optional parameters unless allowEmpty is false
        $validator
            ->optional('email', 'Email')
            ->required(
                fn($values): bool => BaseValidator::DATABASE_INSERT_CONTEXT === $contextName
                    && (
                        array_key_exists('email', $values)
                        && $values['email'] !== ''
                        && $values['email'] !== null
                    )
            )
            // Validator->email() does not cover unicode characters in the local part so we use
            // the OpenEMR email validator for this.
            ->callback(fn ($value) => ValidationUtils::isValidEmail($value) ?: throw new InvalidValueException(
                sprintf('Email %s is not a valid email', $value),
                'email'
            ))
        ;

        // Update only
        $validator
            ->optional('uuid', 'uuid')
            ->required(BaseValidator::DATABASE_UPDATE_CONTEXT === $contextName)
            ->callback(fn ($value) => $this->isExistingUuid($value) ?: throw new InvalidValueException(
                sprintf('UUID %s does not exist', $value),
                $value
            ))
            ->string()
        ;
    }
}
