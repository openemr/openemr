<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//


class AMC_302j extends AbstractAmcReport
{
    public function getTitle()
    {
        return "AMC_302j";
    }

    public function getObjectToCount()
    {
        return "transitions-in";
    }
 
    public function createDenominator() 
    {
        return new AMC_302j_Denominator();
    }
    
    public function createNumerator()
    {
        return new AMC_302j_Numerator();
    }
}
