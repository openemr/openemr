<?php
// Copyright (C) 2011 Ken Chapple <ken@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
require_once( 'AmcFilterIF.php' );
require_once( dirname(__FILE__)."/../../../../clinical_rules.php" );
require_once( dirname(__FILE__)."/../../../../amc.php" );

abstract class AbstractAmcReport implements RsReportIF
{
    protected $_amcPopulation;

    protected $_resultsArray = array();

    protected $_rowRule;
    protected $_ruleId;
    protected $_beginMeasurement;
    protected $_endMeasurement;

    protected $_manualLabNumber;

    public function __construct( array $rowRule, array $patientIdArray, $dateTarget, $options )
    {
        // require all .php files in the report's sub-folder
        $className = get_class( $this );
        foreach ( glob( dirname(__FILE__)."/../reports/".$className."/*.php" ) as $filename ) {
            require_once( $filename );
        }
        // require common .php files
        foreach ( glob( dirname(__FILE__)."/../reports/common/*.php" ) as $filename ) {
            require_once( $filename );
        }
        // require clinical types
        foreach ( glob( dirname(__FILE__)."/../../../ClinicalTypes/*.php" ) as $filename ) {
            require_once( $filename );
        }

        $this->_amcPopulation = new AmcPopulation( $patientIdArray );
        $this->_rowRule = $rowRule;
        $this->_ruleId = isset( $rowRule['id'] ) ? $rowRule['id'] : '';
        // Parse measurement period, which is stored as array in $dateTarget ('dateBegin' and 'dateTarget').
        $this->_beginMeasurement = $dateTarget['dateBegin'];
        $this->_endMeasurement = $dateTarget['dateTarget'];
        $this->_manualLabNumber = $options['labs_manual'];
    }
    
    public abstract function createNumerator();
    public abstract function createDenominator();
    public abstract function getObjectToCount();
        
    public function getResults() {
        return $this->_resultsArray;
    }

    public function execute()
    {
        $numerator = $this->createNumerator();
        if ( !$numerator instanceof AmcFilterIF ) {
            throw new Exception( "Numerator must be an instance of AmcFilterIF" );
        }
        
        $denominator = $this->createDenominator();
        if ( !$denominator instanceof AmcFilterIF ) {
            throw new Exception( "Denominator must be an instance of AmcFilterIF" );
        }

        $totalPatients = count( $this->_amcPopulation );

        // Figure out object to be counted
        //   (patients, labs, transitions, visits, or prescriptions)
        $object_to_count = $this->getObjectToCount();
        if (empty($object_to_count)) {
            $object_to_count="patients";
        }
        
        $numeratorObjects = 0;
        $denominatorObjects = 0;
        foreach ( $this->_amcPopulation as $patient ) 
        {
            // If begin measurement is empty, then make the begin
            //  measurement the patient dob.
            $tempBeginMeasurement = "";
            if (empty($this->_beginMeasurement)) {
                $tempBeginMeasurement = $patient->dob;
            }
            else {
                $tempBeginMeasurement = $this->_beginMeasurement;
            }

            // Count Denominators
            if ($object_to_count == "patients") {
                // Counting patients
                if ( !$denominator->test( $patient, $tempBeginMeasurement, $this->_endMeasurement ) ) {
                    continue;
                }
                $denominatorObjects++;
            }
            else {
                // Counting objects other than patients
                //   First, collect the pertinent objects
                $objects = $this->collectObjects($patient,$object_to_count,$tempBeginMeasurement,$this->_endMeasurement);

                //   Second, test each object
                $objects_pass=array();
                foreach ($objects as $object) {
                    $patient->object=$object;
                    if ( $denominator->test( $patient, $tempBeginMeasurement, $this->_endMeasurement ) ) {
                        $denominatorObjects++;
                        array_push($objects_pass,$object);
                    }
                }
            }

            // Count Numerators
            if ($object_to_count == "patients") {
                // Counting patients
                if ( !$numerator->test( $patient, $tempBeginMeasurement, $this->_endMeasurement ) ) {
                    continue;
                }
                $numeratorObjects++;
            }
            else {
                // Counting objects other than patients
                //   test each object that passed the above denominator testing
                foreach ($objects_pass as $object) {
                    $patient->object=$object;
                    if ( $numerator->test( $patient, $tempBeginMeasurement, $this->_endMeasurement ) ) {
                        $numeratorObjects++;
                    }
                }
            }

        }

        // Deal with the manually added labs for the electronic labs AMC measure
        if ($object_to_count == "labs") {
          $denominatorObjects = $denominatorObjects + $this->_manualLabNumber;
        }
        
        $percentage = calculate_percentage( $denominatorObjects, 0, $numeratorObjects );
        $result = new AmcResult( $this->_rowRule, $totalPatients, $denominatorObjects, 0, $numeratorObjects, $percentage );
        $this->_resultsArray[]= $result;
    }

    private function collectObjects ($patient,$object_label,$begin,$end) {

        $results = array();
        $sqlBindArray = array();

        switch ($object_label) {
            case "transitions-in":
                $sql = "SELECT amc_misc_data.map_id as `encounter`, amc_misc_data.date_completed as `completed`, form_encounter.date as `date` " .
                        "FROM `amc_misc_data`, `form_encounter` " .
                        "WHERE amc_misc_data.map_id = form_encounter.encounter " .
                        "AND amc_misc_data.map_category = 'form_encounter' " .
                        "AND amc_misc_data.pid = form_encounter.pid = ? " .
                        "AND amc_misc_data.amc_id = 'med_reconc_amc' " .
                        "AND form_encounter.date >= ? AND form_encounter.date <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "transitions-out":
                $sql = "SELECT * " .
                       "FROM `transactions` " .
                       "WHERE `title` = 'Referral' " .
                       "AND `pid` = ? " .
                       "AND `date` >= ? AND `date` <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "encounters":
                $sql = "SELECT * " .
                       "FROM `form_encounter` " .
                       "WHERE `pid` = ? " .
                       "AND `date` >= ? AND `date` <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "prescriptions":
                $sql = "SELECT * " .
                       "FROM `prescriptions` " .
                       "WHERE `patient_id` = ? " .
                       "AND `date_added` >= ? AND `date_added` <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
            case "labs":
                $sql = "SELECT procedure_result.result FROM " .
                       "procedure_order, " .
                       "procedure_report, " .
                       "procedure_result " .
                       "WHERE " .
                       "procedure_order.patient_id = ? AND " .
                       "procedure_order.procedure_order_id = procedure_report.procedure_order_id AND " .
                       "procedure_report.procedure_report_id = procedure_result.procedure_report_id AND " .
                       "procedure_report.date_collected >= ? AND procedure_report.date_collected <= ?";
                array_push($sqlBindArray, $patient->id, $begin, $end);
                break;
        }

        $rez = sqlStatement($sql, $sqlBindArray);
        for($iter=0; $row=sqlFetchArray($rez); $iter++)
            $results[$iter]=$row;

        return $results;
    }
}
