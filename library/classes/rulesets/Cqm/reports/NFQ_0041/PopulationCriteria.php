<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0041_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{    
    public function getTitle()
    {
        return "Population Criteria";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0041_InitialPatientPopulation();
    }
    
    public function createNumerators()
    {
        return new NFQ_0041_Numerator();
    }
    
    public function createDenominator()
    {
        return new NFQ_0041_Denominator();
    }
    
    public function createExclusion()
    {
        return new NFQ_0041_Exclusions();
    }
}
