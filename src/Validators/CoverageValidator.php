<?php

namespace OpenEMR\Validators;

use OpenEMR\Billing\InsurancePolicyTypes;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;

/**
 * Supports Insurance Coverage Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class CoverageValidator extends BaseValidator
{
    protected function getInnerValidator(): Validator
    {
        return new OpenEMRParticleValidator();
    }

    /**
     * Configures validations for the Insurance Coverage DB Insert and Update use-case.
     * The update use-case is comprised of the same fields as the insert use-case.
     * The update use-case differs from the insert use-case in that fields other than uuid & type are not required.
     */
    protected function configureValidator()
    {
        parent::configureValidator();


        // insert validations
        $this->validator->context(
            self::DATABASE_INSERT_CONTEXT,
            function (Validator $context) {
                if (!$context instanceof OpenEMRParticleValidator) {
                    throw new \RuntimeException("CoverageValidator requires an instance of OpenEMRParticleValidator");
                }
                $context->required('pid')->numeric();
                $context->required('type')->inArray(array('primary', 'secondary', 'tertiary'))
                    ->callback(function ($value) {
                        if ($GLOBALS['insurance_only_one']) {
                            if ($value !== 'primary') {
                                throw new InvalidValueException("only primary insurance allowed with insurance_only_one global setting enabled", "INSURANCE_ONLY_ONE::INVALID_INSURANCE_TYPE");
                            }
                        }
                        return true;
                    });
                $context->required('provider')->numeric();
                $context->optional('plan_name')->lengthBetween(2, 255);
                $context->required('policy_number')->lengthBetween(2, 255);
                $context->optional('group_number')->lengthBetween(2, 255);
                $context->required('subscriber_lname')->lengthBetween(2, 255);
                $context->optional('subscriber_mname')->lengthBetween(2, 255);
                $context->required('subscriber_fname')->lengthBetween(2, 255);
                $context->required('subscriber_relationship')->listOption('sub_relation');
                $context->required('subscriber_ss')->lengthBetween(2, 255);
                $context->required('subscriber_DOB')->datetime('Y-m-d');
                $context->required('subscriber_street')->lengthBetween(2, 255);
                $context->required('subscriber_postal_code')->lengthBetween(2, 255);
                $context->required('subscriber_city')->lengthBetween(2, 255);
                $context->required('subscriber_state')->listOption($GLOBALS['state_list']);
                $context->required('subscriber_country')->listOption($GLOBALS['country_list']);
                $context->optional('subscriber_phone')->lengthBetween(2, 255);
                $context->required('subscriber_sex')->listOption('sex');
                $context->required('accept_assignment')->inArray(['TRUE', 'FALSE']);
                // policy type has a Not Applicable(NA) option which is an empty string so we allow empty here
                $context->required('policy_type')->allowEmpty(true)->inArray(InsurancePolicyTypes::POLICY_TYPES);
                $context->optional('subscriber_employer')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_street')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_postal_code')->lengthBetween(2, 255);
                $context->optional('subscriber_employer_state')->listOption($GLOBALS['state_list']);
                $context->optional('subscriber_employer_country')->listOption($GLOBALS['country_list']);
                $context->optional('subscriber_employer_city')->lengthBetween(2, 255);
                $context->optional('copay')->lengthBetween(2, 255);
                $context->required('date')->datetime('Y-m-d')
                    ->callback(function ($value, $values) {
                    // need to check
                        if (!empty($values['pid']) && !empty($values['type']) && !empty($value)) {
                            $sqlCheck = "SELECT COUNT(*) AS cnt FROM insurance_data WHERE pid = ? AND type = ? AND date = ?";
                            $binds = [$values['pid'], $values['type'], $value];
                            if (!empty($values['uuid'])) {
                                $sqlCheck .= " AND uuid != ?";
                                $binds[] = UuidRegistry::uuidToBytes($values['uuid']);
                            }
                            $duplicatePolicyCount = QueryUtils::fetchSingleValue($sqlCheck, 'cnt', $binds);
                            if ($duplicatePolicyCount != null && $duplicatePolicyCount > 0) {
                                throw new InvalidValueException("A policy for this type with the same effective date already exists for this patient", "Record::DUPLICATE_RECORD");
                            }
                        }

                        // can't figure out how to make the current policy check work with a nullable date_end
                        // so we will do it on the date column
                        if (!empty($values['pid']) && !empty($values['type'])) {
                            if (empty($value)) {
                                $sqlCheck = "SELECT COUNT(*) AS cnt FROM insurance_data WHERE pid = ? AND type = ? AND date_end IS NULL";
                                $binds = [$values['pid'], $values['type']];
                                if (!empty($values['uuid'])) {
                                    $sqlCheck .= " AND uuid != ?";
                                    $binds[] = UuidRegistry::uuidToBytes($values['uuid']);
                                }
                                // check to make sure there are no other policies for this type with an empty end date as we can
                                // only have one current policy for a given type
                                $currentPolicyCount = QueryUtils::fetchSingleValue($sqlCheck, 'cnt', $binds);
                                if ($currentPolicyCount != null && $currentPolicyCount > 0) {
                                    throw new InvalidValueException("A current policy (no end date) already exists for this patient and type.", "Record::DUPLICATE_CURRENT_POLICY");
                                }
                            }
                            if (strtotime($values['date']) > strtotime($values['date_end'])) {
                                throw new InvalidValueException("Start date cannot be after end date", "DateTime::INVALID_START_DATE");
                            }
                        }
                        return true;
                    });
                $context->optional('date_end')
                    ->required(function ($values) {
                        if (!empty($values['date'])) {
                            if (!empty($values['pid']) && !empty($values['type'])) {
                                return true;
                            }
                        }
                        return false;
                    }, null, true)
                    ->datetime('Y-m-d')
                    ->callback(function ($value, $values) {


                        return true;
                    });
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
                            if ($key !== 'type') {
                                $chain->required(false);
                            }
                        }
                    }
                );
                // additional uuid validation
                $context->required("uuid", "Coverage UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "insurance_data", $value, true);
                })->uuid();
            }
        );
    }
}
