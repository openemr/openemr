<?php
require_once( 'AbstractCqmReport.php' );

class NFQ_Unimplemented extends AbstractCqmReport implements RsUnimplementedIF
{   
    public function __construct() {
        parent::__construct( array(), array(), null );    
    }
    
    public function createPopulationCriteria()
    {
         return null;    
    }
}