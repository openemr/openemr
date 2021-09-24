<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once(dirname(__FILE__) . "/../../../../clinical_rules.php");

abstract class AbstractCqmReport implements RsReportIF
{
    protected $_cqmPopulation;

    protected $_resultsArray = array();

    protected $_rowRule;
    protected $_ruleId;
    protected $_beginMeasurement;
    protected $_endMeasurement;

    public function __construct(array $rowRule, array $patientIdArray, $dateTarget)
    {
        // require all .php files in the report's sub-folder
        $className = get_class($this);
        foreach (glob(dirname(__FILE__) . "/../reports/" . $className . "/*.php") as $filename) {
            require_once($filename);
        }

        // require common .php files
        foreach (glob(dirname(__FILE__) . "/../reports/common/*.php") as $filename) {
            require_once($filename);
        }

        // require clinical types
        foreach (glob(dirname(__FILE__) . "/../../../ClinicalTypes/*.php") as $filename) {
            require_once($filename);
        }

        $this->_cqmPopulation = new CqmPopulation($patientIdArray);
        $this->_rowRule = $rowRule;
        $this->_ruleId = isset($rowRule['id']) ? $rowRule['id'] : '';
        // Calculate measurement period
        $tempDateArray = explode("-", ($dateTarget ?? ''));
        $tempYear = $tempDateArray[0];
        $this->_beginMeasurement = $tempDateArray[0] . "-01-01 00:00:00";
        $this->_endMeasurement = $tempDateArray[0] . "-12-31 23:59:59";
    }

    abstract public function createPopulationCriteria();

    public function getBeginMeasurement()
    {
        return $this->_beginMeasurement;
    }

    public function getEndMeasurement()
    {
        return $this->_endMeasurement;
    }

    public function getResults()
    {
        return $this->_resultsArray;
    }

    public function execute()
    {
        $populationCriterias = $this->createPopulationCriteria();
        if (!is_array($populationCriterias)) {
            $tmpPopulationCriterias = array();
            $tmpPopulationCriterias[] = $populationCriterias;
            $populationCriterias = $tmpPopulationCriterias;
        }

        foreach ($populationCriterias as $populationCriteria) {
            // If itemization is turned on, then iterate the rule id iterator
            if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                $GLOBALS['report_itemized_test_id_iterator']++;
            }

            if ($populationCriteria instanceof CqmPopulationCrtiteriaFactory) {
                $initialPatientPopulationFilter = $populationCriteria->createInitialPatientPopulation();
                if (!$initialPatientPopulationFilter instanceof CqmFilterIF) {
                    throw new Exception("InitialPatientPopulation must be an instance of CqmFilterIF");
                }

                $denominator = $populationCriteria->createDenominator();
                if (!$denominator instanceof CqmFilterIF) {
                    throw new Exception("Denominator must be an instance of CqmFilterIF");
                }

                $numerators = $populationCriteria->createNumerators();
                if (!is_array($numerators)) {
                    $tmpNumerators = array();
                    $tmpNumerators[] = $numerators;
                    $numerators = $tmpNumerators;
                }

                $exclusion = $populationCriteria->createExclusion();
                if (!$exclusion instanceof CqmFilterIF) {
                    throw new Exception("Exclusion must be an instance of CqmFilterIF");
                }

                //Denominator Exception added
                $denomExept = false;
                if (method_exists($populationCriteria, 'createDenominatorException')) {
                    $denomExept = true;
                }

                $totalPatients = count($this->_cqmPopulation);
                $initialPatientPopulation = 0;
                $denominatorPatientPopulation = 0;
                $exclusionsPatientPopulation = 0;
                $exceptionsPatientPopulation = 0; // this is a bridge to no where variable (calculated but not used below). Will keep for now, though.
                $patExclArr = array();
                $patExceptArr = array();
                $numeratorPatientPopulations = $this->initNumeratorPopulations($numerators);
                foreach ($this->_cqmPopulation as $patient) {
                    if (!$initialPatientPopulationFilter->test($patient, $this->_beginMeasurement, $this->_endMeasurement)) {
                        continue;
                    }

                    $initialPatientPopulation++;

                    // If itemization is turned on, then record the "Initial Patient population" item
                    if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                        insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 3, $patient->id);
                    }

                    if (!$denominator->test($patient, $this->_beginMeasurement, $this->_endMeasurement)) {
                        continue;
                    }

                    $denominatorPatientPopulation++;

                    if ($exclusion->test($patient, $this->_beginMeasurement, $this->_endMeasurement)) {
                        $exclusionsPatientPopulation++;
                        $patExclArr[] = $patient->id;
                    }

                    //Denominator Exception added
                    if ($denomExept) {
                        $denom_exception = $populationCriteria->createDenominatorException();
                        if ($denom_exception->test($patient, $this->_beginMeasurement, $this->_endMeasurement)) {
                            $exceptionsPatientPopulation++; // this is a bridge to no where variable (not used below). Will keep for now, though.
                            $patExceptArr[] = $patient->id;
                        }
                    }

                    foreach ($numerators as $numerator) {
                        $this->testNumerator($patient, $numerator, $numeratorPatientPopulations);
                    }
                }

                // tally results, run exclusion on each numerator
                $pass_filt = $denominatorPatientPopulation;
                $exclude_filt = $exclusionsPatientPopulation;
                foreach ($numeratorPatientPopulations as $title => $pass_targ) {
                    if (count($patExclArr) > 0) {
                        foreach ($patExclArr as $patVal) {
                            // If itemization is turned on, then record the "excluded" item
                            if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                                insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 2, $patVal, $title);
                            }
                        }
                    }

                    if (count($patExceptArr) > 0) {
                        foreach ($patExceptArr as $patVal) {
                            // If itemization is turned on, then record the "exception" item
                            if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                                insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 4, $patVal, $title);
                            }
                        }
                    }

                    $percentage = calculate_percentage($pass_filt, $exclude_filt, $pass_targ);
                    $this->_resultsArray[] = new CqmResult(
                        $this->_rowRule,
                        $title,
                        $populationCriteria->getTitle(),
                        $totalPatients,
                        $pass_filt,
                        $exclude_filt,
                        $pass_targ,
                        $percentage,
                        $initialPatientPopulation,
                        $exceptionsPatientPopulation
                    );
                }
            }
        }

        return $this->_resultsArray;
    }

    private function initNumeratorPopulations(array $numerators)
    {
        $numeratorPatientPopulations = array();
        foreach ($numerators as $numerator) {
            $numeratorPatientPopulations[$numerator->getTitle()] = 0;
        }

        return $numeratorPatientPopulations;
    }

    private function testNumerator($patient, $numerator, &$numeratorPatientPopulations)
    {
        if ($numerator instanceof CqmFilterIF) {
            if ($numerator->test($patient, $this->_beginMeasurement, $this->_endMeasurement)) {
                $numeratorPatientPopulations[$numerator->getTitle()]++;

                // If itemization is turned on, then record the "passed" item
                if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                    insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 1, $patient->id, $numerator->getTitle());
                }
            } else {
                // If itemization is turned on, then record the "failed" item
                if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
                    insertItemReportTracker($GLOBALS['report_itemizing_temp_flag_and_id'], $GLOBALS['report_itemized_test_id_iterator'], 0, $patient->id, $numerator->getTitle());
                }
            }
        } else {
            throw new Exception("Numerator must be an instance of CqmFilterIF");
        }
    }
}
