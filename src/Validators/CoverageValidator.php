<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

/**
 * Supports Insurance Coverage Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class CoverageValidator extends BaseValidator
{
    /**
     * Configures validations for the Insurance Coverage DB Insert and Update use-case.
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
                $context->required('pid')->numeric();
                $context->required('type')->inArray(array('primary', 'secondary', 'tertiary'));
                $context->required('provider')->numeric();
                $context->required('plan_name')->lengthBetween(2, 255);
                $context->required('policy_number')->lengthBetween(2, 255);
                $context->required('group_number')->lengthBetween(2, 255);
                $context->required('subscriber_lname')->lengthBetween(2, 255);
                $context->optional('subscriber_mname')->lengthBetween(2, 255);
                $context->required('subscriber_fname')->lengthBetween(2, 255);
                $context->required('subscriber_relationship')->lengthBetween(2, 255);
                $context->required('subscriber_ss')->lengthBetween(2, 255);
                $context->required('subscriber_DOB')->datetime('Y-m-d');
                $context->required('subscriber_street')->lengthBetween(2, 255);
                $context->required('subscriber_postal_code')->lengthBetween(2, 255);
                $context->required('subscriber_city')->lengthBetween(2, 255);
                $context->required('subscriber_state')->lengthBetween(2, 255);
                $context->required('subscriber_country')->lengthBetween(2, 255);
                $context->required('subscriber_phone')->lengthBetween(2, 255);
                $context->required('subscriber_sex')->lengthBetween(1, 25);
                $context->required('accept_assignment')->lengthBetween(1, 5);
                $context->required('policy_type')->lengthBetween(1, 25);
                $context->optional('subscriber_employer')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_street')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_postal_code')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_state')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_country')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_city')->lengthBetween(2, 255);
                $context->optional('copay')->lengthBetween(2, 255);
                $context->optional('date')->datetime('Y-m-d');
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
                // additional uuid validation
                $context->required("uuid", "Coverage UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "insurance_data", $value, true);
                })->uuid();
            }
        );
    }
}
