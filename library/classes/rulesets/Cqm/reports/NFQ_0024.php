<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
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
