<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class CqmResult implements RsResultIF
{
    public $rule;
    public $numeratorLabel;
    public $populationLabel;
     
    public $totalPatients; // Total number of patients considered
    public $patientsInPopulation; // Number of patients that pass filter
    public $patientsExcluded; // Number of patients that are excluded
    public $patientsIncluded; // Number of patients that pass target
    public $percentage; // Calculated percentage

    public function __construct( $rowRule, $numeratorLabel, $populationLabel, $totalPatients, $patientsInPopulation, $patientsExcluded, $patientsIncluded, $percentage )
    {
        $this->rule = $rowRule;
        $this->numeratorLabel = $numeratorLabel;
        $this->populationLabel = $populationLabel;
        $this->totalPatients = $totalPatients;
        $this->patientsInPopulation = $patientsInPopulation;
        $this->patientsExcluded = $patientsExcluded;
        $this->patientsIncluded = $patientsIncluded;
        $this->percentage = $percentage;
    }

    public function format()
    {
        $rowFormat = array( 
        	'is_main'=>TRUE, // TO DO: figure out way to do this when multiple groups.
            'population_label' => $this->populationLabel,
            'numerator_label' => $this->numeratorLabel,
            'total_patients' => $this->totalPatients,
            'excluded' => $this->patientsExcluded,
            'pass_filter' => $this->patientsInPopulation,
            'pass_target' => $this->patientsIncluded,
            'percentage' => $this->percentage );
            $rowFormat = array_merge( $rowFormat, $this->rule );
        
        return $rowFormat;
    }
}
