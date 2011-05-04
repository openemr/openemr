<?php
class NFQ_0421 extends AbstractCqmReport
{   
    public function createPopulationCriteria()
    {
         $populationCriteria = array();
         $populationCriteria[] = new NFQ_0421_PopulationCriteria1();
         $populationCriteria[] = new NFQ_0421_PopulationCriteria2();   
         return $populationCriteria;    
    }
}
