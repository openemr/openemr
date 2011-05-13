<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
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
