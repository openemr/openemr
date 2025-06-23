<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0038_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{
    public function getTitle()
    {
        return "Population Criteria";
    }

    public function createInitialPatientPopulation()
    {
        return new NQF_0038_InitialPatientPopulation();
    }

    public function createNumerators()
    {
        $numerators = array();
        $numerators[] = new NQF_0038_Numerator1();
        $numerators[] = new NQF_0038_Numerator2();
        $numerators[] = new NQF_0038_Numerator3();
        $numerators[] = new NQF_0038_Numerator4();
        $numerators[] = new NQF_0038_Numerator5();
        $numerators[] = new NQF_0038_Numerator6();
        $numerators[] = new NQF_0038_Numerator7();
        $numerators[] = new NQF_0038_Numerator8();
        $numerators[] = new NQF_0038_Numerator9();
        $numerators[] = new NQF_0038_Numerator10();
        $numerators[] = new NQF_0038_Numerator11();
        $numerators[] = new NQF_0038_Numerator12();
        return $numerators;
    }

    public function createDenominator()
    {
        return new NQF_0038_Denominator();
    }

    public function createExclusion()
    {
        return new ExclusionsNone();
    }

    public function createDenominatorException()
    {
        return new ExceptionsNone();
    }
}
