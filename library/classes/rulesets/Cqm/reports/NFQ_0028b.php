<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0028b extends AbstractCqmReport
{   
    public function createPopulationCriteria()
    {
         return new NFQ_0028b_PopulationCriteria();    
    }
}
