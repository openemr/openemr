<?php
class NFQ_0013 extends AbstractCqmReport
{   
    public function createPopulationCriteria()
    {
         return new NFQ_0013_PopulationCriteria();    
    }
}
