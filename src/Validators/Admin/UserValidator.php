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

        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context): void {
                // 150, not the users.username width of 255: AclExtended::setUserAro() registers the
                // username as a gacl_aro.value, which is varchar(150). OpenEMR runs with
                // sql_mode = '' (DatabaseConnectionFactory), so a longer name is silently
                // truncated there while the users row keeps the full string. The ARO lookup that
                // follows (GaclApi::get_object_id) then matches nothing, every group mapping is
                // dropped, and the endpoint reports 201 for a user with none of the access_group
                // memberships it just validated.
                $context->required('username')->lengthBetween(2, 150)->regex('/^[a-zA-Z0-9._-]+$/');
                $context->required('password')->lengthBetween(8, 255);
                $context->required('admin_password')->lengthBetween(1, 255);
                $context->required('fname')->lengthBetween(1, 255);
                // Bounds the assembled name as well as this field. AclExtended::setUserAro()
                // registers "fname [mname] lname" as gacl_aro.name, and GaclApi::add_object()
                // rejects a name of 255 or more outright (GaclApi.php:2963) by returning false.
                // No ARO is created, the add_group_object() calls that follow then match nothing,
                // and the endpoint reports 201 for a user with none of the access_group
                // memberships it just validated -- the same silent failure the username bound
                // above prevents. Each part is under 255 on its own, so only the combination
                // needs checking.
                $context->required('lname')->lengthBetween(1, 255)->callback(
                    /** @param array<string, mixed> $values */
                    function ($value, array $values): bool {
                        $parts = [];
                        foreach (['fname', 'mname'] as $key) {
                            $raw = $values[$key] ?? null;
                            $part = is_string($raw) ? trim($raw) : '';
                            if ($part !== '') {
                                $parts[] = $part;
                            }
                        }
                        $parts[] = is_string($value) ? trim($value) : '';

                        // strlen(), not mb_strlen(): GaclApi::add_object() bounds bytes.
                        return strlen(implode(' ', $parts)) < 255;
                    }
                );
                $context->optional('mname')->lengthBetween(0, 255);
                $context->optional('suffix')->lengthBetween(0, 255);
                $context->optional('email')->email();
                $context->optional('authorized')->inArray([0, 1, '0', '1']);
                $context->optional('facility_id')->numeric();
                $context->optional('billing_facility_id')->numeric();
                $context->optional('npi')->lengthBetween(0, 15);
                $context->optional('taxonomy')->lengthBetween(0, 30);
                $context->optional('specialty')->lengthBetween(0, 255);
                $context->optional('calendar')->inArray([0, 1, '0', '1']);
                $context->optional('portal_user')->inArray([0, 1, '0', '1']);
                $context->optional('federaltaxid')->lengthBetween(0, 255);
                $context->optional('state_license_number')->lengthBetween(0, 25);
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
