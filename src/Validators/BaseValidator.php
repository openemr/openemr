<?php

namespace OpenEMR\Validators;

use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Validators\ProcessingResult;
use Particle\Validator\Validator;
use Ramsey\Uuid\Exception\InvalidUuidStringException;

/**
 * Base class for OpenEMR object validation.
 * Validation processes are implemented using Particle (https://github.com/particle-php/Validator)
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dixon Whitmire <dixonwh@gmail.com>
 * @copyright Copyright (c) 2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2020 Dixon Whitmire <dixonwh@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
abstract class BaseValidator
{
    // supported validation contexts for database operations
    public const DATABASE_INSERT_CONTEXT = "db-insert";
    public const DATABASE_UPDATE_CONTEXT = "db-update";

    protected $validator;

    protected $supportedContexts;

    /**
     * Configures the validator instance with validation requirements and rules.
     * This default implementation sets the validator's supported context to include
     * database inserts and updates.
     */
    protected function configureValidator()
    {
        array_push($this->supportedContexts, self::DATABASE_INSERT_CONTEXT, self::DATABASE_UPDATE_CONTEXT);
    }

    public function __construct()
    {
        $this->validator = new Validator();
        $this->supportedContexts = [];
        $this->configureValidator();
    }

    /**
     * @return true if the requested context is supported by the validator instance.
     */
    private function isValidContext($context)
    {
        return in_array($context, $this->supportedContexts);
    }

    /**
     * Performs a data validation using the configured rules and requirements.
     *
     * Validation results are conveyed by an array with the following keys:
     * - isValid => true|false
     * - messages => array(validationMessage, validationMessage, etc)
     *
     * @param $dataFields -  The fields to validate.
     * @param $context - The validation context to utilize. This is simply a "handle" for the rules.
     * @return $validationResult array
     */
    public function validate($dataFields, $context)
    {
        if (!$this->isValidContext($context)) {
            throw new \RuntimeException("unsupported context: " . $context);
        }

        $validationResult = $this->validator->validate($dataFields, $context);

        $result = new ProcessingResult();
        $result->setValidationMessages($validationResult->getMessages());

        return $result;
    }

    /**
     * Validates that a ID exists in the database.
     *
     * @param $field The identifier field in database
     * @param $table The table in database
     * @param $lookupId The identifier to validateId
     * @param $isUuid true if the lookupId is UUID, otherwise false
     * @return true if the lookupId is a valid existing id, otherwise Validation Message
     */
    public static function validateId($field, $table, $lookupId, $isUuid = false)
    {
        $validationResult = new ProcessingResult();

        // Error Message
        $validationMessages = [
            $field => ["invalid or nonexisting value" => "value " . $lookupId],
        ];
        $validationResult->setValidationMessages($validationMessages);

        // Check if $id is not UUID or a Valid Integer
        if ($isUuid) {
            try {
                $lookupId = UuidRegistry::uuidToBytes($lookupId);
            } catch (InvalidUuidStringException $e) {
                return $validationResult;
            }
        } elseif (!is_int(intval($lookupId))) {
            return $validationResult;
        }

        $result = sqlQuery(
            "SELECT $field FROM $table WHERE $field = ?",
            array($lookupId)
        );
        if (!empty($result[$field])) {
            return true;
        } else {
            return $validationResult;
        }
    }

    /**
     * Validates that a Code from Valueset exists in the database.
     *
     * @param $code The code which needs to be verified
     * @param $table The table in database
     * @param $valueset Name of the particular Valueset
     * @return boolean
     */
    public function validateCode($code, $table, $valueset)
    {
        $sql = "SELECT option_id FROM $table WHERE list_id = ? AND option_id = ?";
        $result = sqlQuery(
            $sql,
            array($valueset, $code)
        );
        return $result['option_id'] ? true : false;
    }
}
