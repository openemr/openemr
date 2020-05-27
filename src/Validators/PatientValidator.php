<?php

namespace OpenEMR\Validators;

use Particle\Validator\Validator;
use Particle\Validator\Exception\InvalidValueException;
use OpenEMR\Common\Uuid\UuidRegistry;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

/**
 * Supports Patient Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class PatientValidator extends BaseValidator
{
    /**
     * Validates that a patient UUID exists in the database
     */
    public function isExistingUuid($uuid)
    {
        try {
            $uuidLookup = UuidRegistry::uuidToBytes($uuid);
        } catch (InvalidUuidStringException $e) {
            return false;
        }

        $result = sqlQuery(
            'SELECT uuid AS uuid FROM patient_data WHERE uuid = ?',
            array($uuidLookup)
        );

        $existingUuid = $result['uuid'] ?? null;
        return $existingUuid != null;
    }

    /**
     * Configures validations for the Patient DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than pid are not required.
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
                $context->required("sex", 'Gender')->lengthBetween(4, 30);
                $context->required("DOB", 'Date of Birth')->datetime('Y-m-d');
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
                // additional uuid validations
                $context->required("uuid", "uuid")->callback(function ($value) {
                    if (!$this->isExistingUuid($value)) {
                        $message = "UUID " . $value . " does not exist";
                        throw new InvalidValueException($message, $value);
                    }
                    return true;
                })->string();
            }
        );
    }
}
