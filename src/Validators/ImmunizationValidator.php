<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;

/**
 * Supports PractitionerRole Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class ImmunizationValidator extends BaseValidator
{
    /**
     * Configures validations for the PractitionerRole DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid are not required.
     */
    protected function configureValidator()
    {
        parent::configureValidator();

        // insert validations
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context): void {
                // Currently no specific validations defined - add as needed
            }
        );

        // update validations - same as insert but not required
        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context): void {
                // Currently no specific validations defined - add as needed
            }
        );
    }
}
