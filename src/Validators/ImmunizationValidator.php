<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

/**
 * Supports Immunization Record Validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class ImmunizationValidator extends BaseValidator
{
    /**
     * Configures validations for the Immunization DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     */
    protected function configureValidator(): void
    {
        parent::configureValidator();

        // insert validations
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context): void {
                $context->required('patient_id')->numeric();
                $context->required('cvx_code')->lengthBetween(1, 255);
                $context->required('administered_date')->datetime('Y-m-d');
                $context->optional('completion_status')->lengthBetween(1, 255);
                $context->optional('manufacturer')->lengthBetween(1, 255);
                $context->optional('lot_number')->lengthBetween(1, 255);
                $context->optional('administered_by_id')->numeric();
                $context->optional('note');
                $context->optional('route')->lengthBetween(1, 255);
                $context->optional('administration_site')->lengthBetween(1, 255);
                $context->optional('amount_administered');
                $context->optional('amount_administered_unit')->lengthBetween(1, 255);
                $context->optional('expiration_date')->datetime('Y-m-d');
                $context->optional('refusal_reason')->lengthBetween(1, 255);
                $context->optional('information_source')->lengthBetween(1, 255);
            }
        );

        // update validations copied from insert
        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context): void {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    function ($rules): void {
                        foreach ($rules as $chain) {
                            $chain->required(false);
                        }
                    }
                );
                // additional uuid validation. validateId() returns ProcessingResult
                // on failure, which is truthy and would let invalid UUIDs through —
                // coerce to a strict boolean so the callback rule rejects properly.
                $context->required("uuid", "Immunization UUID")->callback(
                    fn($value): bool => $this->validateId("uuid", "immunizations", $value, true) === true
                )->uuid();
            }
        );
    }
}
