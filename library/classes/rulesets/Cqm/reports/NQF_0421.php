<?php

// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NQF_0421 extends AbstractCqmReport
{
    public function createPopulationCriteria()
    {
         $populationCriteria = array();
         $populationCriteria[] = new NQF_0421_PopulationCriteria1();
         $populationCriteria[] = new NQF_0421_PopulationCriteria2();
         return $populationCriteria;
    }
}
