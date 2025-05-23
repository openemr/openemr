<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;
use Particle\Validator\Exception\InvalidValueException;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

/**
 * Supports Facility Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FacilityValidator extends BaseValidator
{
    /**
     * Configures validations for the Facility DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     */
    protected function configureValidator()
    {
        parent::configureValidator();

        // insert validations
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context) {
                $context->required('name')->lengthBetween(2, 255);
                $context->required('facility_npi')->numeric()->lengthBetween(10, 15);
                $context->optional('domain_identifier')->numeric();
                $context->optional('phone')->lengthBetween(3, 30);
                $context->optional('city')->lengthBetween(2, 255);
                $context->optional('state')->lengthBetween(2, 50);
                $context->optional('street')->lengthBetween(2, 255);
                $context->optional('postal_code')->lengthBetween(2, 11);
                $context->optional('email')->email();
                $context->optional('fax')->lengthBetween(3, 30);
                $context->optional('country_code')->lengthBetween(2, 30);
                $context->optional('federal_ein')->lengthBetween(2, 15);
                $context->optional('website')->url();
                $context->optional('color')->lengthBetween(4, 7);
                $context->optional('service_location')->numeric();
                $context->optional('billing_location')->numeric();
                $context->optional('accepts_assignment')->numeric();
                $context->optional('pos_code')->numeric();
                $context->optional('domain_identifier')->lengthBetween(2, 60);
                $context->optional('attn')->lengthBetween(2, 65);
                $context->optional('tax_id_type')->lengthBetween(2, 31);
                $context->optional('primary_business_entity')->numeric();
                $context->optional('facility_code')->lengthBetween(2, 31);
                $context->optional('facility_taxonomy')->lengthBetween(2, 15);
                $context->optional('iban')->lengthBetween(2, 34);
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
                $context->required("uuid", "Facility UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "facility", $value, true);
                })->uuid();
            }
        );
    }
}
