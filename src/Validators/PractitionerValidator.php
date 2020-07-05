<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;
use Particle\Validator\Exception\InvalidValueException;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

/**
 * Supports Practitioner Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PractitionerValidator extends BaseValidator
{
    /**
     * Configures validations for the Practitioner DB Insert and Update use-case.
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
                $context->required("fname", "First Name")->lengthBetween(2, 255);
                $context->required("lname", 'Last Name')->lengthBetween(2, 255);
                $context->required("npi", "NPI")->numeric()->lengthBetween(10, 15);
                $context->optional("facility_id", "Facility Id")->numeric()->callback(
                    // check if facility exist
                    function ($value) {
                        return $this->validateId("id", "facility", $value);
                    }
                );
                $context->optional("email", "Email")->email();
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
                $context->required("uuid", "Practitioner UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "users", $value, true);
                })->uuid();
            }
        );
    }
}
