<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

/**
 * Supports Encounter Record Validation.
 */
class EncounterValidator extends BaseValidator
{
    /**
     * Configures validations for the Encounter DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than pid are not required.
     */
    protected function configureValidatorContext(InnerValidator $validator, string $contextName): void
    {
        // Insert & update
        $validator
            ->requiredForInsert('pc_catid', null, $contextName)
        ;

        $validator
            ->requiredForInsert('class_code', null, $contextName)
            ->callback(fn($value) => $this->validateCode($value, 'list_options', '_ActEncounterCode'))
        ;

        $validator
            ->required('puuid', 'Patient UUID')
            ->callback(fn($value) => $this->validateId('uuid', 'patient_data', $value, true))
            ->uuid()
        ;

        // Update only
        if (self::DATABASE_UPDATE_CONTEXT !== $contextName) {
            return;
        }

        $validator
            ->required('euuid', 'Encounter UUID')
            ->callback(fn($value) => $this->validateId('uuid', 'form_encounter', $value, true))
            ->uuid()
        ;

        $validator
            ->required('user', 'Encounter Author')
            ->callback(fn($value) => $this->validateId('username', 'users', $value))
            ->string()
        ;

        $validator
            ->required('group', 'Encounter Provider Group')
            ->string()
        ;
    }
}
