<?php
class CqmReportFactory extends RsReportFactoryAbstract
{
    public function __construct()
    {
        foreach ( glob( dirname(__FILE__)."/library/*.php" ) as $filename ) {
            require_once( $filename );
        }

        foreach ( glob( dirname(__FILE__)."/reports/*.php" ) as $filename ) {
            require_once( $filename );
        }
    }
    
    public function createReport( $className, $rowRule, $patientData, $dateTarget ) 
    {
        $reportObject = null;
        if ( class_exists( $className ) ) {
            $reportObject = new $className( $rowRule, $patientData, $dateTarget );
        } else {
            $reportObject = new NFQ_Unimplemented();
        }
        
        return $reportObject;
    }
}
