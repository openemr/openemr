<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0038_Numerator12 implements CqmFilterIF 
{
    public function getTitle() {
        return "Numerator 12";
    }
    
    public function test( CqmPatient $patient, $beginDate, $endDate )
    {
        if ( Immunizations::checkDtap( $patient, $beginDate, $endDate ) &&
            Immunizations::checkIpv( $patient, $beginDate, $endDate ) && 
            ( Immunizations::checkMmr( $patient, $beginDate, $endDate ) &&
               !Helper::checkAllergy( Allergy::POLYMYXIN, $patient, $patient->dob, $endDate ) ) &&
            Immunizations::checkVzv( $patient, $beginDate, $endDate ) &&
            Immunizations::checkHepB( $patient, $beginDate, $endDate ) &&
            Immunizations::checkPheumococcal( $patient, $beginDate, $endDate ) ) {
            return true;
        } 
        
        return false;
    }
}
