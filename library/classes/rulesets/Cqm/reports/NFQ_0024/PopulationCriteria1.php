<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0024_PopulationCriteria1 implements CqmPopulationCrtiteriaFactory
{    
    public function getTitle()
    {
        return "Population Criteria 1";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0024_InitialPatientPopulation1();
    }
    
    public function createNumerators()
    {
        $nums = array();
        $nums[]= new NFQ_0024_Numerator1();
        $nums[]= new NFQ_0024_Numerator2();
        $nums[]= new NFQ_0024_Numerator3();
        return $nums;
    }
    
    public function createDenominator()
    {
        return new NFQ_0024_Denominator();
    }
    
    public function createExclusion()
    {
        return new ExclusionsNone();
    }
}
