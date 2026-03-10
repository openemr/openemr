<?php

namespace OpenEMR\Validators;

use Particle\Validator\Chain;
use Particle\Validator\Validator;

/**
 * Supports AllergyIntolerance Record Validation.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Yash Bothra <yashrajbothra786@gmail.com>
 * @copyright Copyright (c) 2020 Yash Bothra <yashrajbothra786@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class AllergyIntoleranceValidator extends BaseValidator
{
    /**
     * Configures validations for the AllergyIntolerance DB Insert and Update use-case.
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
                $context->required('title')->lengthBetween(2, 255);
                $context->optional('begdate')->datetime('Y-m-d');
                $context->optional('diagnosis')->lengthBetween(2, 255);
                $context->optional('enddate')->datetime('Y-m-d');
                $context->optional('comments');
                $context->required("puuid", "Patient UUID")->callback(
                    fn($value) => $this->validateId("uuid", "patient_data", $value, true)
                );
            }
        );

        // update validations copied from insert
        $this->validator->context(
            self::DATABASE_UPDATE_CONTEXT,
            function (Validator $context): void {
                $context->copyContext(
                    self::DATABASE_INSERT_CONTEXT,
                    /** @param Chain[] $rules */
                    function (array $rules): void {
                        foreach ($rules as $chain) {
                            /** @var Chain $chain */
                            $chain->required(false);
                        }
                    }
                );
                // additional euuid validation
                $context->required("uuid", "Allergy UUID")->callback(fn($value) => $this->validateId("uuid", "lists", $value, true))->uuid();
            }
        );
    }
}
