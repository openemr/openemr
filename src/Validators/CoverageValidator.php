<?php

namespace OpenEMR\Validators;

use OpenEMR\Billing\InsurancePolicyTypes;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\PatientService;
use OpenEMR\Services\Search\CompositeSearchField;
use OpenEMR\Services\Search\SearchModifier;
use OpenEMR\Services\Search\StringSearchField;
use OpenEMR\Services\Search\TokenSearchField;
use OpenEMR\Services\Search\TokenSearchValue;
use Particle\Validator\Exception\InvalidValueException;
use Particle\Validator\Validator;

/**
 * Supports Insurance Coverage Record Validation.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2021 Vishnu Yarmaneni <vardhanvishnu@gmail.com>
 * @copyright Copyright (c) 2024 Care Management Solutions, Inc. <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
class CoverageValidator extends BaseValidator
{
    const DATABASE_SWAP_CONTEXT = "database-swap";


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
        array_push($this->supportedContexts, self::DATABASE_SWAP_CONTEXT);


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
                $context->optional('subscriber_mname')->lengthBetween(1, 255);
                $context->required('subscriber_fname')->lengthBetween(2, 255);
                $context->required('subscriber_relationship')->listOption('sub_relation')
                    ->callback(function ($value, $values) {
                        if (
                            !empty($values['pid'])
                            && !empty($values['subscriber_lname']) && !empty($values['subscriber_fname'])
                            && !empty($values['subscriber_ss'])
                        ) {
                            // check name and previous names to make sure a mistake hasn't been made.
                            $pidSearch = new TokenSearchField('pid', new TokenSearchValue($values['pid']));
                            $prevFirstNameSearch = new StringSearchField('previous_name_first', $values['subscriber_fname'], SearchModifier::EXACT);
                            $prevLastNameSearch = new StringSearchField('previous_name_last', $values['subscriber_lname'], SearchModifier::EXACT);
                            $previousNameSearch = new CompositeSearchField('patient-previous-name', []);
                            $previousNameSearch->setChildren([$pidSearch, $prevFirstNameSearch, $prevLastNameSearch]);

                            $firstNameSearch = new StringSearchField('fname', $values['subscriber_fname'], SearchModifier::EXACT);
                            $lastNameSearch = new StringSearchField('lname', $values['subscriber_lname'], SearchModifier::EXACT);
                            $currentNameSearch = new CompositeSearchField('patient-current-name', []);
                            $currentNameSearch->setChildren([$pidSearch, $firstNameSearch, $lastNameSearch]);

                            $ssnSearch = new StringSearchField('ss', $values['subscriber_ss'], SearchModifier::EXACT);
                            $ssnPidSearch = new CompositeSearchField('patient-ssn', []);
                            $ssnPidSearch->setChildren([$pidSearch, $ssnSearch]);
                            $patientService = new PatientService();
                            $isAnd = false;
                            $results = $patientService->search([$previousNameSearch, $currentNameSearch, $ssnPidSearch], $isAnd);

                            if ($value === 'self') {
                                if (!$results->hasData()) {
                                    throw new InvalidValueException("Subscriber name must match patient name for self relationship", "Record::INVALID_SELF_SUBSCRIBER_NAME");
                                }
                                $patient = $results->getData()[0];
                                // need to check the social security number as well
                                if ($patient['ss'] !== $values['subscriber_ss']) {
                                    throw new InvalidValueException("Subscriber social security number must match patient social security number for self relationship", "Record::INVALID_SELF_SUBSCRIBER_SSN");
                                }
                                // if the first and last name do not match exactly we need to search previous names to see if we have a match here.
                                // we do this because the results return SSN OR name matches so we have to manually check the name matches
                                if ($patient['lname'] != $values['subscriber_lname'] || $patient['fname'] != $values['subscriber_fname']) {
                                    // need to search previous names, if no matches here then throw exception
                                    $previousNames = $patient['previous_names'];
                                    $found = false;
                                    foreach ($previousNames as $previousName) {
                                        // do a strict equality and then we can do multibyte comparison for localizations
                                        // note if we want to handle more comprehensive multibytes
                                        // we need to do some normalizations as per this stackoverflow post: https://stackoverflow.com/a/38855868
                                        if (
                                            mb_is_string_equal_ci($previousName['previous_name_first'], $values['subscriber_fname'])
                                            && mb_is_string_equal_ci($previousName['previous_name_last'], $values['subscriber_lname'])
                                        ) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        throw new InvalidValueException("Subscriber name must match patient name for self relationship", "Record::INVALID_SELF_SUBSCRIBER_NAME");
                                    }
                                }
                            } else {
                                if ($results->hasData()) {
                                    // need to check to see if its because the ssn matched
                                    // or because we have a name match
                                    if ($results->getData()[0]['ss'] === $values['subscriber_ss']) {
                                        throw new InvalidValueException("Subscriber social security number must not match patient social security number for non-self relationship", "Record::INVALID_SUBSCRIBER_SSN");
                                    } else {
                                        // Note: we do not throw an exception on the name match here as there can be a subscriber name ie John Smith who is covered by someone who is NOT themselves also named John Smith
                                        // for example a father and son with the same name.  This is a rare case but it does happen.
                                    }
                                }
                            }
                        }
                        return true;
                    });
                $context->required('subscriber_DOB')->datetime('Y-m-d');
                $context->required('subscriber_street')->lengthBetween(2, 255);
                $context->required('subscriber_postal_code')->lengthBetween(2, 255);
                $context->required('subscriber_city')->lengthBetween(2, 255);
                $context->required('subscriber_state')->listOption($GLOBALS['state_list']);
                $context->optional('subscriber_country')->listOption($GLOBALS['country_list']);
                $context->optional('subscriber_phone')->lengthBetween(2, 255);
                $context->required('subscriber_sex')->listOption('sex');
                $context->required('accept_assignment')->inArray(['TRUE', 'FALSE']);
                // policy type has a Not Applicable(NA) option which is an empty string so we allow empty here
                $context->optional('policy_type')->allowEmpty(true)->inArray(InsurancePolicyTypes::POLICY_TYPES);
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
                        // TODO: @adunsulag need to move all these queries into the InsuranceService...
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
                            if (!empty($values['date_end']) && strtotime($values['date']) > strtotime($values['date_end'])) {
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

        $this->validator->context(
            self::DATABASE_SWAP_CONTEXT,
            function (Validator $context) {
                $context->required("uuid", "Coverage UUID")->callback(function ($value) {
                    return $this->validateId("uuid", "insurance_data", $value, true);
                })->uuid();
                $context->required("pid", "Patient ID")->numeric();
                $context->required("type", "Coverage Type")->inArray(array('primary', 'secondary', 'tertiary'))
                    ->callback(function ($value, $values) {
                        if (empty($values['uuid']) && empty($values['pid'])) {
                            return true; // nothing to do here if we don't have a uuid or pid, so don't run the check and let other failures happen
                        }
                        // if src date is null
                        // if most recent target date is null
                                // this is ok.
                        // if most recent target date is not null
                        // we need to check to make sure target type can receive src date

                        // if src date is not null
                        // if most recent target date is null || target date is the same as src date
                        // this is ok as it means there are no other dates and we can easily swap
                        // if most recent target date is not null
                        // we need to check to make sure target type does NOT have a date that is the same as src date

                        // hit the database to grab the most recent policy for this type
                        // grab the uuid we are checking
                        $uuidBytes = UuidRegistry::uuidToBytes($values['uuid']);
                        $srcData = QueryUtils::fetchRecords("SELECT `date`,`type` FROM insurance_data WHERE uuid = ?", [$uuidBytes]);
                        if (empty($srcData)) {
                            throw new InvalidValueException("Invalid coverage uuid", "Record::INVALID_RECORD_UUID");
                        }
                        $srcDate = $srcData[0]['date'];
                        $targetDate = QueryUtils::fetchSingleValue(
                            "SELECT `date` FROM insurance_data WHERE pid = ? AND type = ? ORDER BY date DESC LIMIT 1",
                            'date',
                            [$values['pid'], $values['type']]
                        );
                        if (empty($srcDate)) {
                            if (!empty($targetDate)) {
                                $srcTypeCanReceiveTarget = QueryUtils::fetchSingleValue(
                                    "SELECT COUNT(*) AS cnt FROM insurance_data WHERE pid = ? AND type = ? AND date IS NOT NULL AND date = ?",
                                    'cnt',
                                    [$values['pid'], $srcData['type'], $targetDate]
                                );
                                if ($srcTypeCanReceiveTarget > 0) {
                                    throw new InvalidValueException("Source coverage type already has a policy with an effective date that conflicts with the target policy effective date", "Record::INVALID_SOURCE_POLICY_DATE");
                                }
                            }
                        } else {
                            if (!(empty($targetDate) || $targetDate == $srcDate)) {
                                $targetTypeCanReceiveSrc = QueryUtils::fetchSingleValue(
                                    "SELECT COUNT(*) AS cnt FROM insurance_data WHERE pid = ? AND type = ? AND date IS NOT NULL AND date = ?",
                                    'cnt',
                                    [$values['pid'], $values['type'], $srcDate]
                                );
                                if ($targetTypeCanReceiveSrc > 0) {
                                    throw new InvalidValueException("Target coverage type already has a policy with an effective date that conflicts with the source policy effective date", "Record::INVALID_TARGET_POLICY_DATE");
                                }
                            }
                        }
                        return true;
                    });
            }
        );
    }
}
