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
     * @param OpenEMRParticleValidator $validator
     */
    protected function configureValidatorContext(InnerValidator $validator, string $contextName): void
    {
        // Insert & update
        $validator
            ->requiredForInsert('fname', 'First Name', $contextName)
            ->lengthBetween(1, 255)
        ;

        $validator
            ->requiredForInsert('lname', 'Last Name', $contextName)
            ->lengthBetween(2, 255)
        ;

        $validator
            ->requiredForInsert('sex', 'Gender', $contextName)
            ->lengthBetween(4, 30)
        ;

        $validator
            ->requiredForInsert('DOB', 'Date of Birth', $contextName)
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
        if (self::DATABASE_UPDATE_CONTEXT !== $contextName) {
            return;
        }

        $validator
            ->required('uuid', 'uuid')
            ->callback(fn ($value) => $this->isExistingUuid($value) ?: throw new InvalidValueException(
                sprintf('UUID %s does not exist', $value),
                $value
            ))
            ->string()
        ;
    }
}
