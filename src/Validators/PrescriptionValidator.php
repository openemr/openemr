<?php

namespace OpenEMR\Validators;
use Particle\Validator\Validator;

class PrescriptionValidator extends BaseValidator
{
    protected function configureValidator()
    {
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context) {
                $context->required("start_date", "Start Date")->lengthBetween(2, 255);
                $context->required("route", 'Route')->string();
                $context->required("npi", "NPI")->numeric()->lengthBetween(10, 15);
                $context->optional("facility_id", "Facility Id")->numeric()->callback(
                // check if facility exist
                    function ($value) {
                        return $this->validateId("id", "facility", $value);
                    }
                );
                $context->optional("email", "Email")->email();
            }
        );

    }
}
/**
 * active: 1
start_date: 2023-07-30
provider_id: 1
patient_id: 6
rxcui_select: on
drug: qwe
quantity: 12
size: x
unit: 1
dosage: 2
form: 4
route: intradermal
interval: 20
refills: 17
per_refill: 12
note:
substitute: 0
id: 0
process: true
rxnorm_drugcode:
 */
