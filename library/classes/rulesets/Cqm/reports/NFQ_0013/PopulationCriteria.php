<?php
class NFQ_0013_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{
    public function getTitle()
    {
        return "Population Criteria";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0013_InitialPatientPopulation();
    }
    
    public function createNumerators()
    {
        return new NFQ_0013_Numerator();
    }
    
    public function createDenominator()
    {
        return new DenominatorAllPatients();
    }
    
    public function createExclusion()
    {
        return new ExclusionsNone();
    }
}