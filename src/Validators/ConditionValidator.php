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

use Particle\Validator\Validator;

/**
 * Supports AllergyIntolerance Record Validation.
 */
class ConditionValidator extends BaseValidator
{
    /**
     * Configures validations for the AllergyIntolerance DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     */
    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        // Insert & update
        $validator
            ->required('title')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->lengthBetween(2, 255)
        ;

        $validator
            ->required('begdate')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->datetime('Y-m-d')
        ;

        $validator
            ->optional('diagnosis')
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('enddate')
            ->datetime('Y-m-d')
        ;

        $validator
            ->optional('puuid', 'Patient UUID')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->callback(fn($value) => $this->validateId('uuid', 'patient_data', $value, true))
        ;

        // Update only
        $validator
            ->optional('uuid', 'Condition UUID')
            ->required(BaseValidator::DATABASE_UPDATE_CONTEXT === $contextName)
            ->callback(fn($value) => $this->validateId('uuid', 'lists', $value, true))
            ->uuid()
        ;
    }
}
