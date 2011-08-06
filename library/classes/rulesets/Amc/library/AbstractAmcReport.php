<?php
require_once( 'AmcFilterIF.php' );

abstract class AbstractAmcReport implements RsReportIF
{
    protected $_amcPopulation;

    protected $_resultsArray = array();

    protected $_rowRule;
    protected $_ruleId;
    protected $_beginMeasurement;
    protected $_endMeasurement;

    public function __construct( array $rowRule, array $patientIdArray, $dateTarget )
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
        // Calculate measurement period
        $tempDateArray = explode( "-",$dateTarget );
        $tempYear = $tempDateArray[0];
        $this->_beginMeasurement = $tempDateArray[0] . "-01-01 00:00:00";
        $this->_endMeasurement = $tempDateArray[0] . "-12-31 23:59:59";
    }
    
    public abstract function createNumerator();
    public abstract function createDenominator();
        
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
        $numeratorPatients = 0;
        $denominatorPatients = 0;
        foreach ( $this->_amcPopulation as $patient ) 
        {
            if ( !$denominator->test( $patient, $this->_beginMeasurement, $this->_endMeasurement ) ) {
                continue;
            }
            
            $denominatorPatients++;
            
            if ( !$numerator->test( $patient, $this->_beginMeasurement, $this->_endMeasurement ) ) {
                continue;
            }
            
            $numeratorPatients++;
        }
        
        // TODO calculate results
        $percentage = calculate_percentage( $denominatorPatients, 0, $numeratorPatients );
        $result = new AmcResult( $this->_rowRule, $totalPatients, $denominatorPatients, 0, $numeratorPatients, $percentage );
        $this->_resultsArray[]= $result;
    }
}
