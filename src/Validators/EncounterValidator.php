<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;
use Particle\Validator\Exception\InvalidValueException;

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
    protected function configureValidator()
    {
        parent::configureValidator();

        // insert validations
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context) {
                $context->required('pc_catid');
                $context->required('class_code')->callback(
                    function ($value) {
                        return $this->validateCode($value, "list_options", "_ActEncounterCode");
                    }
                );
                $context->required("puuid", "Patient UUID")->callback(
                    function ($value) {
                        return $this->validateId('uuid', "patient_data", $value, true);
                    }
                )->uuid();
            }
        );

        // update validations copied from insert
        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context) {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    function ($rules) {
                        foreach ($rules as $key => $chain) {
                            $chain->required(false);
                        }
                    }
                );
                // additional euuid validation
                $context->required("euuid", "Encounter UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "form_encounter", $value, true);
                })->uuid();
                // additional puuid validation
                $context->required("puuid", "Patient UUID")->callback(function ($value) {
                    return $this->validateId('uuid', "patient_data", $value, true);
                })->uuid();
                $context->required("user", "Encounter Author")->callback(function ($value) {
                    return $this->validateId('username', "users", $value);
                })->string();
                $context->required("group", "Encounter Provider Group")->string();
                $context->optional('class_code')->callback(
                    function ($value) {
                        return $this->validateCode($value, "list_options", "_ActEncounterCode");
                    }
                );
            }
        );
    }
}
