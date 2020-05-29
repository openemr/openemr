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
                $context->required('date')->datetime('Y-m-d');
                $context->required('pc_catid');
                $context->required("pid", "pid")->callback(function ($value) {
                    if (!$this->validateId('pid', "patient_data", $value)) {
                        $message = "PID " . $value . " does not exist";
                        throw new InvalidValueException($message, $value);
                    }
                    return true;
                })->integer();
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
                // additional eid validation
                $context->required("encounter", "encounter")->callback(function ($value) {
                    if (!$this->validateId("encounter", "form_encounter", $value)) {
                        $message = "EID " . $value . " does not exist";
                        throw new InvalidValueException($message, $value);
                    }
                    return true;
                })->integer();
                // additional pid validation
                $context->required("pid", "pid")->callback(function ($value) {
                    if (!$this->validateId('pid', "patient_data", $value)) {
                        $message = "PID " . $value . " does not exist";
                        throw new InvalidValueException($message, $value);
                    }
                    return true;
                })->integer();
            }
        );
    }
}
