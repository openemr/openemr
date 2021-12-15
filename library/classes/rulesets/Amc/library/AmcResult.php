<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class AmcResult implements RsResultIF
{
    public $rule;
//    public $numeratorLabel;
//    public $populationLabel;

    public $totalPatients; // Total number of patients considered
    public $patientsInPopulation; // Number of patients that pass filter
    public $patientsExcluded; // Number of patients that are excluded
    public $patientsIncluded; // Number of patients that pass target
    public $percentage; // Calculated percentage
    public $itemized_test_id;

    public function __construct($rowRule, $totalPatients, $patientsInPopulation, $patientsExcluded, $patientsIncluded, $percentage)
    {
        $this->rule = $rowRule;
//        $this->numeratorLabel = $numeratorLabel;
//        $this->populationLabel = $populationLabel;
        $this->totalPatients = $totalPatients;
        $this->patientsInPopulation = $patientsInPopulation;
        $this->patientsExcluded = $patientsExcluded;
        $this->patientsIncluded = $patientsIncluded;
        $this->percentage = $percentage;

        // If itemization is turned on, then record the itemized_test_id
        if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
            $this->itemized_test_id = array('itemized_test_id' => $GLOBALS['report_itemized_test_id_iterator']);
        }
    }

    public function format()
    {
        $rowFormat = array(
            'is_main' => true, // TO DO: figure out way to do this when multiple groups.
//            'population_label' => $this->populationLabel,
//            'numerator_label' => $this->numeratorLabel,
            'total_patients' => $this->totalPatients,
            'excluded' => $this->patientsExcluded,
            'pass_filter' => $this->patientsInPopulation,
            'pass_target' => $this->patientsIncluded,
            'percentage' => $this->percentage );
            $rowFormat = array_merge($rowFormat, $this->rule);

        // If itemization is turned on, then record the itemized_test_id
        if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
            $rowFormat = array_merge($rowFormat, $this->itemized_test_id);
        }

        return $rowFormat;
    }
}
