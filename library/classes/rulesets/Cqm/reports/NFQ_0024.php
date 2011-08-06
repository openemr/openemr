<?php
class NFQ_0024 extends AbstractCqmReport
{   
    public function createPopulationCriteria()
    {
         $populationCriteria = array();
         $populationCriteria[]= new NFQ_0024_PopulationCriteria1();
         $populationCriteria[]= new NFQ_0024_PopulationCriteria2();   
         $populationCriteria[]= new NFQ_0024_PopulationCriteria3();
         return $populationCriteria;    
    }
}
