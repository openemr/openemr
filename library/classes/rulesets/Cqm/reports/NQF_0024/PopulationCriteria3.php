<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0024_PopulationCriteria3 implements CqmPopulationCrtiteriaFactory
{
    public function getTitle(): string
    {
        return "Population Criteria 3";
    }

    public function createInitialPatientPopulation(): CqmFilterIF
    {
        return new NQF_0024_InitialPatientPopulation3();
    }

    public function createNumerators(): array
    {
        $nums = [];
        $nums[] = new NQF_0024_Numerator1();
        $nums[] = new NQF_0024_Numerator2();
        $nums[] = new NQF_0024_Numerator3();
        return $nums;
    }

    public function createDenominator(): CqmFilterIF
    {
        return new NQF_0024_Denominator();
    }

    public function createExclusion(): CqmFilterIF
    {
        return new ExclusionsNone();
    }

    public function createDenominatorException(): CqmFilterIF
    {
        return new ExceptionsNone();
    }
}
