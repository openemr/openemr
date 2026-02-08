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
    public $itemized_test_id;

    /**
     * @param mixed $rule
     * @param mixed $numeratorLabel
     * @param mixed $populationLabel
     * @param mixed $totalPatients
     * @param mixed $denominator
     * @param mixed $denom_exclusion
     * @param mixed $numerator
     * @param mixed $percentage Calculated percentage
     * @param mixed $ipp
     * @param mixed $denom_exception
     */
    public function __construct(public $rule, public $numeratorLabel, public $populationLabel, public $totalPatients, public $denominator, public $denom_exclusion, public $numerator, public $percentage, public $ipp, public $denom_exception)
    {
        // If itemization is turned on, then record the itemized_test_id
        if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
            $this->itemized_test_id = ['itemized_test_id' => $GLOBALS['report_itemized_test_id_iterator']];
        }
    }

    public function format()
    {
        $concatenated_label = '';
        if ($this->numeratorLabel != "Numerator") {
            if ($this->populationLabel != "Population Criteria") {
                $concatenated_label = $this->populationLabel . ", " . $this->numeratorLabel;
            } else {
                $concatenated_label = $this->numeratorLabel;
            }
        } else {
            if ($this->populationLabel != "Population Criteria") {
                $concatenated_label = $this->populationLabel;
            }
        }

        $rowFormat = [
            'is_main' => true, // TO DO: figure out way to do this when multiple groups.
            'population_label' => $this->populationLabel,
            'numerator_label' => $this->numeratorLabel,
            'concatenated_label' => $concatenated_label,
            'total_patients' => $this->totalPatients,
            'excluded' => $this->denom_exclusion,
            'pass_filter' => $this->denominator,
            'pass_target' => $this->numerator,
            'percentage' => $this->percentage,
            'initial_population' => $this->ipp,
            'exception' => $this->denom_exception];
            $rowFormat = array_merge($rowFormat, $this->rule);

        // If itemization is turned on, then record the itemized_test_id
        if ($GLOBALS['report_itemizing_temp_flag_and_id']) {
            $rowFormat = array_merge($rowFormat, $this->itemized_test_id);
        }

        return $rowFormat;
    }
}
