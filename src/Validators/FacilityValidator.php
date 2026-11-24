<?php

namespace OpenEMR\Validators;

use Particle\Validator\Chain;
use Particle\Validator\Validator;

/**
 * Supports Facility Record Validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class FacilityValidator extends BaseValidator
{
    public const FORM_SAVE_CONTEXT = "form-save";

    /**
     * Configures validations for the Facility DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     */
    protected function configureValidator(): void
    {
        parent::configureValidator();
        array_push($this->supportedContexts, self::FORM_SAVE_CONTEXT);

        // insert validations
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context): void {
                $context->required('name')->callback(static function ($value): bool {
                    if (!is_scalar($value)) {
                        return false;
                    }
                    $length = mb_strlen((string) $value);
                    return $length >= 2 && $length <= 255;
                });
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
                $context->optional('pos_code')->integer()->between(-128, 127);
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
                // additional euuid validation
                $context->required("uuid", "Facility UUID")->callback(fn($value) => $this->validateId("uuid", "facility", $value, true))->uuid();
            }
        );

        // form-save validations (facilities.php POST save path). Built explicitly;
        // not derived from DATABASE_INSERT_CONTEXT, which requires facility_npi,
        // applies numeric() to domain_identifier, and enforces min lengths we
        // do not want for the interactive form.
        $this->validator->context(
            self::FORM_SAVE_CONTEXT,
            function (Validator $context): void {
                $context->required('name')->callback(static function ($value): bool {
                    if (!is_scalar($value)) {
                        return false;
                    }
                    $length = mb_strlen((string) $value);
                    return $length >= 1 && $length <= 255;
                });
                $context->optional('phone')->lengthBetween(1, 30);
                $context->optional('fax')->lengthBetween(1, 30);
                $context->optional('street')->lengthBetween(1, 255);
                $context->optional('city')->lengthBetween(1, 255);
                $context->optional('state')->lengthBetween(1, 50);
                $context->optional('postal_code')->lengthBetween(1, 11);
                $context->optional('country_code')->lengthBetween(1, 30);
                $context->optional('federal_ein')->lengthBetween(1, 15);
                $context->optional('website')->lengthBetween(1, 255);
                $context->optional('email')->lengthBetween(1, 255);
                $context->optional('color')->lengthBetween(1, 7);
                $context->optional('service_location')->numeric()->between(0, 1);
                $context->optional('billing_location')->numeric()->between(0, 1);
                $context->optional('accepts_assignment')->numeric()->between(0, 1);
                $context->optional('pos_code')->integer()->between(-128, 127);
                $context->optional('domain_identifier')->regex('/^[A-Za-z0-9]{1,60}$/');
                $context->optional('attn')->lengthBetween(1, 65);
                $context->optional('tax_id_type')->inArray(['EI', 'SY']);
                $context->optional('primary_business_entity')->numeric()->between(0, 1);
                $context->optional('facility_npi')->numeric()->lengthBetween(10, 15);
                $context->optional('facility_taxonomy')->lengthBetween(1, 15);
                $context->optional('facility_code')->lengthBetween(1, 31);
                $context->optional('mail_street')->lengthBetween(1, 30);
                $context->optional('mail_street2')->lengthBetween(1, 30);
                $context->optional('mail_city')->lengthBetween(1, 50);
                $context->optional('mail_state')->lengthBetween(1, 3);
                $context->optional('mail_zip')->lengthBetween(1, 10);
                $context->optional('oid')->lengthBetween(1, 255);
                $context->optional('iban')->lengthBetween(1, 50);
                // info has no length constraint; kept optional with no rule.
                $context->optional('info');
                $context->optional('inactive')->numeric()->between(0, 1);
                $context->optional('organization_type')->lengthBetween(1, 50);
                $context->optional('mammography_cert_number')->lengthBetween(1, 30);
            }
        );
    }
}
