<?php

/**
 * Supports Admin User Record Validation.
 *
 * @link      https://www.open-emr.org
 * @author    Milan Zivkovic <zivkovic.milan@gmail.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 */

declare(strict_types=1);

namespace OpenEMR\Validators\Admin;

use OpenEMR\Validators\BaseValidator;
use Particle\Validator\Validator;

class UserValidator extends BaseValidator
{
    /**
     * Configures validations for the admin user creation use-case.
     */
    protected function configureValidator(): void
    {
        parent::configureValidator();

        /** @phpstan-ignore method.nonObject (BaseValidator::$validator is untyped) */
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context): void {
                $context->required('username')->lengthBetween(2, 255)->regex('/^[a-zA-Z0-9._-]+$/');
                $context->required('password')->lengthBetween(8, 255);
                $context->required('admin_password')->lengthBetween(1, 255);
                $context->required('fname')->lengthBetween(1, 255);
                $context->required('lname')->lengthBetween(1, 255);
                $context->optional('mname')->lengthBetween(0, 255);
                $context->optional('suffix')->lengthBetween(0, 255);
                $context->optional('email')->email();
                $context->optional('authorized')->inArray([0, 1, '0', '1']);
                $context->optional('facility_id')->numeric();
                $context->optional('billing_facility_id')->numeric();
                $context->optional('npi')->lengthBetween(0, 15);
                $context->optional('taxonomy')->lengthBetween(0, 255);
                $context->optional('specialty')->lengthBetween(0, 255);
                $context->optional('calendar')->inArray([0, 1, '0', '1']);
                $context->optional('portal_user')->inArray([0, 1, '0', '1']);
                $context->optional('federaltaxid')->lengthBetween(0, 255);
                $context->optional('state_license_number')->lengthBetween(0, 255);
                $context->optional('federaldrugid')->lengthBetween(0, 255);
                $context->optional('upin')->lengthBetween(0, 255);
                $context->required('access_group')->isArray()->callback(function ($value): bool {
                    if (!is_array($value) || $value === []) {
                        return false;
                    }
                    foreach ($value as $item) {
                        if (!is_string($item) || trim($item) === '') {
                            return false;
                        }
                    }
                    return true;
                });
                $context->optional('groupname')->lengthBetween(1, 255);
            }
        );
    }
}
