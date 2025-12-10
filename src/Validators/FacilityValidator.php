<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Validators;

/**
 * Supports Facility Record Validation.
 */
class FacilityValidator extends BaseValidator
{
    /**
     * Configures validations for the Facility DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     */
    protected function configureValidatorContext(InnerValidator $validator, string $contextName): void
    {
        // Insert & update
        $validator
            ->requiredForInsert('name', null, $contextName)
            ->lengthBetween(2, 255)
        ;

        $validator
            ->requiredForInsert('facility_npi', null, $contextName)
            ->numeric()
            ->lengthBetween(10, 15)
        ;

        $validator
            ->optional('domain_identifier')
            ->numeric()
        ;

        $validator
            ->optional('phone')
            ->lengthBetween(3, 30)
        ;

        $validator
            ->optional('city')
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('state')
            ->lengthBetween(2, 50)
        ;

        $validator
            ->optional('street')
            ->lengthBetween(2, 255)
        ;

        $validator
            ->optional('postal_code')
            ->lengthBetween(2, 11)
        ;

        $validator
            ->optional('email')
            ->email()
        ;

        $validator
            ->optional('fax')
            ->lengthBetween(3, 30)
        ;

        $validator
            ->optional('country_code')
            ->lengthBetween(2, 30)
        ;

        $validator
            ->optional('federal_ein')
            ->lengthBetween(2, 15)
        ;

        $validator
            ->optional('website')
            ->url()
        ;

        $validator
            ->optional('color')
            ->lengthBetween(4, 7)
        ;

        $validator
            ->optional('service_location')
            ->numeric()
        ;

        $validator
            ->optional('billing_location')
            ->numeric()
        ;

        $validator
            ->optional('accepts_assignment')
            ->numeric()
        ;

        $validator
            ->optional('pos_code')
            ->numeric()
        ;

        $validator
            ->optional('domain_identifier')
            ->lengthBetween(2, 60)
        ;

        $validator
            ->optional('attn')
            ->lengthBetween(2, 65)
        ;

        $validator
            ->optional('tax_id_type')
            ->lengthBetween(2, 31)
        ;

        $validator
            ->optional('primary_business_entity')
            ->numeric()
        ;

        $validator
            ->optional('facility_code')
            ->lengthBetween(2, 31)
        ;

        $validator
            ->optional('facility_taxonomy')
            ->lengthBetween(2, 15)
        ;

        $validator
            ->optional('iban')
            ->lengthBetween(2, 34)
        ;

        // Update only
        if (self::DATABASE_UPDATE_CONTEXT !== $contextName) {
            return;
        }

        $validator
            ->required('uuid', 'Facility UUID')
            ->callback(fn($value) => $this->validateId('uuid', 'facility', $value, true))
            ->uuid()
        ;
    }
}
