<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0059_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{
    public function getTitle(): string
    {
        return "Population Criteria";
    }

    public function createInitialPatientPopulation(): CqmFilterIF
    {
        return new NQF_0059_InitialPatientPopulation();
    }

    public function createNumerators(): CqmFilterIF
    {
        return new NQF_0059_Numerator();
    }

    public function createDenominator(): CqmFilterIF
    {
        return new NQF_0059_Denominator();
    }

    public function createExclusion(): CqmFilterIF
    {
        return new DiabetesExclusions();
    }

    public function createDenominatorException(): CqmFilterIF
    {
        return new ExceptionsNone();
    }
}
