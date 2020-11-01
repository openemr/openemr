<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0421_PopulationCriteria1 implements CqmPopulationCrtiteriaFactory
{
    public function getTitle()
    {
        return "Population Criteria 1";
    }

    public function createInitialPatientPopulation()
    {
        return new NQF_0421_InitialPatientPopulation();
    }

    public function createNumerators()
    {
        return new NQF_0421_Numerator1();
    }

    public function createDenominator()
    {
        return new NQF_0421_Denominator();
    }

    public function createExclusion()
    {
        return new NQF_0421_Exclusion();
    }

    public function createDenominatorException()
    {
        return new ExceptionsNone();
    }
}
