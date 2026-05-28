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
                // CVX codes are 1-4 digit integers per https://www2.cdc.gov/vaccines/iis/iisstandards/vaccines.asp
                // We don't enumerate the full set here (large + updated frequently); the
                // shape check rejects free text and length 255 abuses but defers value
                // membership to ListService::getCvxList() at read/import time.
                $context->required('cvx_code')->regex('/^[0-9]{1,4}$/');
                $context->required('administered_date')->datetime('Y-m-d');
                $context->optional('completion_status')->lengthBetween(1, 255);
                $context->optional('manufacturer')->lengthBetween(1, 255);
                // lot_number is a vendor-printed alphanumeric (sometimes with hyphens or
                // dots). Reject control characters / HTML so a stored-XSS path cannot be
                // smuggled through this field.
                $context->optional('lot_number')->regex('/^[A-Za-z0-9 .\-_\/]{1,255}$/');
                $context->optional('administered_by_id')->numeric();
                $context->optional('note');
                $context->optional('route')->lengthBetween(1, 255);
                $context->optional('administration_site')->lengthBetween(1, 255);
                // amount_administered is a vaccine dose volume in mL — bound it to a
                // sane range. 50 mL is well past any real-world single-dose.
                $context->optional('amount_administered')->numeric()->between(0, 50);
                $context->optional('amount_administered_unit')->lengthBetween(1, 255);
                $context->optional('expiration_date')
                    ->datetime('Y-m-d')
                    ->callback(static function ($value): bool {
                        // Reject already-expired vaccines on insert. (Updates run through
                        // the copied context with required(false), so historical records
                        // can still be touched without triggering this rule.)
                        if (!is_string($value) || $value === '') {
                            return true;
                        }
                        $expiry = date_create_immutable($value);
                        if ($expiry === false) {
                            return false;
                        }
                        return $expiry >= date_create_immutable('today');
                    });
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
