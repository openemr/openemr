<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class Range
{
    const NEG_INF = -1E100000000;
    const POS_INF = INF;
    
    public $lowerBound;
    public $upperBound;
    
    public function __construct( $lowerBound, $upperBound )
    {
        $this->lowerBound = $lowerBound;
        $this->upperBound = $upperBound;
    }

    public function test( $val )
    {
        if ( $val > $this->lowerBound && 
            $val < $this->upperBound ) {
            return true;        
        }
        
        return false;
    }
}
