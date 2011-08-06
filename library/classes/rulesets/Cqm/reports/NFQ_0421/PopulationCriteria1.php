<?php
class NFQ_0421_PopulationCriteria1 implements CqmPopulationCrtiteriaFactory
{
    public function getTitle()
    {
        return "Population Criteria 1";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0421_InitialPatientPopulation();
    }
    
    public function createNumerators()
    {
        return new NFQ_0421_Numerator1();
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