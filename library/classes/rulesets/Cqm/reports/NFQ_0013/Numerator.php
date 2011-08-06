<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
class NFQ_0013_Numerator implements CqmFilterIF
{
    public function getTitle()
    {
        return "Numerator";
    }

    public function test( CqmPatient $patient, $beginDate, $endDate )
    {
        // See if BP has been done within the measurement period (on a day of a specified encounter)
        $query = "SELECT form_vitals.bps, form_vitals.bpd " .
                 "FROM `form_vitals` " .
                 "LEFT JOIN `form_encounter` " .
                 "ON ( DATE(form_vitals.date) = DATE(form_encounter.date)) " .
                 "LEFT JOIN `enc_category_map` " .
                 "ON (enc_category_map.main_cat_id = form_encounter.pc_catid) " .
                 "WHERE form_vitals.pid = ?" .
                 "AND form_vitals.bps IS NOT NULL " .
                 "AND form_vitals.bpd IS NOT NULL " .
                 "AND form_vitals.date >= ? " .
                 "AND form_vitals.date <= ? " .
                 "AND ( enc_category_map.rule_enc_id = 'enc_outpatient' OR enc_category_map.rule_enc_id = 'enc_nurs_fac' )";
        $res = sqlStatement( $query, array( $patient->id, $beginDate, $endDate ) );
        $number = sqlNumRows( $res );
        if ( $number > 0 ) {
            return true;
        }
        
        return false;
    }
}
