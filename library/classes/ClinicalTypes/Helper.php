<?php
require_once( 'ClinicalType.php' );

class Helper
{
    public static function check( $type, $subType, RsPatient $patient, $beginDate = null, $endDate = null, $options = null )
    {
        $typeObj = new $type( $subType );
        if ( $typeObj instanceof ClinicalType ) {
            return $typeObj->doPatientCheck( $patient, $beginDate, $endDate, $options );
        } else {
            throw new Exception( "Type must be a subclass of AbstractClinicalType" );
        }
    }
    
    public static function fetchEncounterDates( $encounterType, RsPatient $patient, $beginDate = null, $endDate = null )
    {
        $encounter = new Encounter( $encounterType );
        return $encounter->fetchDates( $patient, $beginDate, $endDate );
    }
}
