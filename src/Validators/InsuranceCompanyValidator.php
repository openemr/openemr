<?php

namespace OpenEMR\Validators;

use Particle\Validator\Chain;
use Particle\Validator\Validator;

/**
 * Supports Insurance Company Record Validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
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
    protected function configureValidator(): void
    {
        parent::configureValidator();

        // insert validations
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context): void {
                $context->required('name')->lengthBetween(2, 255);
                $context->optional('attn')->lengthBetween(2, 255);
                $context->optional('cms_id')->lengthBetween(2, 15);
                $context->optional('alt_cms_id')->lengthBetween(2, 15);
                $context->optional('ins_type_code')->numeric();
                $context->optional('x12_receiver_id')->lengthBetween(2, 25);
                $context->optional('x12_default_partner_id')->numeric();
                // the addresses columns' widths; string() first, since the length rule can't take
                // an array and the address keeps only strings
                $context->optional('line1')->string()->lengthBetween(1, 255);
                $context->optional('line2')->string()->lengthBetween(1, 255);
                $context->optional('city')->string()->lengthBetween(1, 255);
                $context->optional('state')->string()->lengthBetween(1, 35);
                $context->optional('zip')->string()->lengthBetween(1, 10);
                $context->optional('country')->string()->lengthBetween(1, 255);
            }
        );

        // update validations copied from insert
        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context): void {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    /** @param array<string, Chain> $rules */
                    function (array $rules): void {
                        foreach ($rules as $chain) {
                            /** @var Chain $chain */
                            $chain->required(false);
                        }
                    }
                );
                // additional uuid validation
                $context->required("uuid", "Insurance UUID")->callback(fn($value) => $this->validateId("uuid", "insurance_companies", $value, true))->uuid();
            }
        );
    }
}
