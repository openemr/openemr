<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'ClinicalType.php' );

class LabResult extends ClinicalType
{   
    const OPTION_RANGE = 'range';
    
    const HB1AC_TEST = 'lab_hb1ac_test';
    const LDL_TEST = 'lab_ldl_test';
    
    public function getListId() 
    {
        return 'Clinical_Rules_Lab_Res_Types';
    }
    
    public function doPatientCheck( RsPatient $patient, $beginDate = null, $endDate = null, $options = null )
    {
        $data = Codes::lookup( $this->getOptionId() );
        $type = $this->getListType();
        
        $range = new Range( Range::NEG_INF, Range::POS_INF );
        if ( isset( $options[self::OPTION_RANGE] ) &&
            is_a( $options[self::OPTION_RANGE], 'Range' ) ) {
            $range = $options[self::OPTION_RANGE];
        }
        
        foreach( $data as $codeType => $codes ) {
            foreach ( $codes as $code ) {            
                // search through vitals to find the most recent lab result in the date range
                // if the result value is within range using Range->test(val), return true
                $sql = "SELECT procedure_result.result, procedure_result.date " .
                    "FROM `procedure_type`, " .
                    "`procedure_order`, " .
                    "`procedure_report`, " .
                    "`procedure_result` " .
                    "WHERE procedure_type.procedure_type_id = procedure_order.procedure_type_id " .
                    "AND procedure_order.procedure_order_id = procedure_report.procedure_order_id " .
                    "AND procedure_report.procedure_report_id = procedure_result.procedure_report_id " .
                    "AND procedure_type.standard_code = ? " .
                    "AND procedure_result.date >= ?  " .
                	"AND procedure_result.date < ?  " .
                    "AND procedure_order.patient_id = ? ";
                if ( $range->lowerBound != Range::NEG_INF ) {
                    $sql .= "AND procedure_result.result >= ? ";
                } 
                if ( $range->upperBound != Range::POS_INF ) {
                    $sql .= "AND procedure_result.result < ? ";
                } 
                
                // TODO should this be ': or '::'
                $bindings = array( $codeType.':'.$code, $beginDate, $endDate, $patient->id );
                if ( $range->lowerBound != Range::NEG_INF ) {
                    $bindings []= $range->lowerBound;
                } 
                if ( $range->upperBound != Range::POS_INF ) {
                    $bindings []= $range->upperBound;
                }
                $result = sqlStatement( $sql, $bindings ); 
                
                $number = sqlNumRows($result);
                if ( $number > 0 ) {
                    return true;
                }
            }
        }

        return false;
    }
}
