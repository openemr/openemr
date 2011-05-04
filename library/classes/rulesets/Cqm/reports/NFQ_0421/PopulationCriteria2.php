<?php
class NFQ_0421_PopulationCriteria2 implements CqmPopulationCrtiteriaFactory
{
    public function getTitle()
    {
        return "Population Criteria 2";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0421_InitialPatientPopulation2();
    }
    
    public function createNumerators()
    {
        return new NFQ_0421_Numerator2();
    }
    
    public function createDenominator()
    {
        return new NFQ_0421_Denominator();
    }
    
    public function createExclusion()
    {
        return new NFQ_0421_Exclusion();
    }
}