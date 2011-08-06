<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( "AmcPatient.php" );
/*	Defines a population of patients
 * 
 */
class AmcPopulation extends RsPopulation
{
    /*
     * initialize the patient population
     */
    public function __construct( array $patientIdArray ) {
        foreach ( $patientIdArray as $patientId ) {
            $this->_patients[]= new AmcPatient( $patientId );
        }
    }

    /*
     * ArrayAccess Interface
     */
    public function offsetSet($offset,$value) {
        if ($value instanceof AmcPatient ) {
            if ( $offset == "" ) {
                $this->_patients[] = $value;
            }else {
                $this->_patients[$offset] = $value;
            }
        } else {
            throw new Exception( "Value must be an instance of AmcPatient" );
        }
    }

}