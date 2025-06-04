<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

/**
 * Supports Insurance Company Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class InsuranceCompanyValidator extends BaseValidator
{
    /**
     * Configures validations for the Insurance Company DB Insert and Update use-case.
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
                $context->optional('attn')->lengthBetween(2, 255);
                $context->optional('cms_id')->lengthBetween(2, 15);
                $context->optional('alt_cms_id')->lengthBetween(2, 15);
                $context->optional('ins_type_code')->numeric();
                $context->optional('x12_receiver_id')->lengthBetween(2, 25);
                $context->optional('x12_default_partner_id')->numeric();
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
                $context->required("uuid", "Insurance UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "insurance_companies", $value, true);
                })->uuid();
            }
        );
    }
}
