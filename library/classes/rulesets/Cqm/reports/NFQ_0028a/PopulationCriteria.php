<?php
class NFQ_0028a_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{    
    public function getTitle()
    {
        return "Population Criteria";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0028a_InitialPatientPopulation();
    }
    
    public function createNumerators()
    {
        return new NFQ_0028a_Numerator();
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
