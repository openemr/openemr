<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

/**
 * Supports Practitioner Record Validation.
 */
class PractitionerValidator extends BaseValidator
{
    /**
     * Configures validations for the Practitioner DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     *
     * @param OpenEMRParticleValidator $validator
     */
    protected function configureValidatorContext(InnerValidator $validator, string $contextName): void
    {
        // Insert & update
        $validator
            ->optional('fname', 'First Name')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('lname', 'Last Name')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('npi', 'NPI')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->numeric()
            ->lengthBetween(10, 15)
        ;

        $validator
            ->optional('facility_id', 'Facility Id')
            ->numeric()
            ->callback(fn($value) => $this->validateId('id', 'facility', $value))
        ;

        $validator
            ->optional('email', 'Email')
            ->email()
        ;

        // Update only
        if (self::DATABASE_UPDATE_CONTEXT !== $contextName) {
            return;
        }

        $validator
            ->optional('uuid', 'Practitioner UUID')
            ->required(BaseValidator::DATABASE_UPDATE_CONTEXT === $contextName)
            ->callback(fn($value) => $this->validateId('uuid', 'users', $value, true))
            ->uuid()
        ;
    }
}
