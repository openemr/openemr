<?php
require_once( 'AbstractAmcReport.php' );

class AMC_Unimplemented extends AbstractAmcReport implements RsUnimplementedIF
{   
    public function __construct()
    {
        parent::__construct( array(), array(), null );
    }
    
    public function createDenominator() 
    {
        return null;
    }
    
    public function createNumerator()
    {
        return null;
    }
}
