<?php
class NFQ_0038_PopulationCriteria implements CqmPopulationCrtiteriaFactory
{    
    public function getTitle()
    {
        return "Population Criteria";
    }
    
    public function createInitialPatientPopulation()
    {
        return new NFQ_0038_InitialPatientPopulation();
    }
    
    public function createNumerators()
    {
        $numerators = array();
        $numerators[]= new NFQ_0038_Numerator1();
        $numerators[]= new NFQ_0038_Numerator2();
        $numerators[]= new NFQ_0038_Numerator3();
        $numerators[]= new NFQ_0038_Numerator4();
        $numerators[]= new NFQ_0038_Numerator5();
        $numerators[]= new NFQ_0038_Numerator6();
        $numerators[]= new NFQ_0038_Numerator7();
        $numerators[]= new NFQ_0038_Numerator8();
        $numerators[]= new NFQ_0038_Numerator9();
        $numerators[]= new NFQ_0038_Numerator10();
        $numerators[]= new NFQ_0038_Numerator11();
        $numerators[]= new NFQ_0038_Numerator12();
        return $numerators;
    }
    
    public function createDenominator()
    {
        return new NFQ_0038_Denominator();
    }
    
    public function createExclusion()
    {
        return new ExclusionsNone();
    }
}
