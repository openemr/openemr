<?php

/**
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

/**
 * Supports Insurance Company Record Validation.
 */
class InsuranceCompanyValidator extends BaseValidator
{
    /**
     * Configures validations for the Insurance Company DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     */
    protected function configureValidatorContext(Validator $validator, string $contextName): void
    {
        // Insert & update
        $validator
            ->optional('name')
            ->required(BaseValidator::DATABASE_INSERT_CONTEXT === $contextName)
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('attn')
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('cms_id')
            ->lengthBetween(2, 15)
        ;

        $validator
            ->optional('alt_cms_id')
            ->lengthBetween(2, 15)
        ;

        $validator
            ->optional('ins_type_code')
            ->numeric()
        ;

        $validator
            ->optional('x12_receiver_id')
            ->lengthBetween(2, 25)
        ;

        $validator
            ->optional('x12_default_partner_id')
            ->numeric()
        ;

        // Update only
        $validator
            ->optional('uuid', 'Insurance UUID')
            ->required(BaseValidator::DATABASE_UPDATE_CONTEXT === $contextName)
            ->callback(fn($value) => $this->validateId('uuid', 'insurance_companies', $value, true))
            ->uuid()
        ;
    }
}
