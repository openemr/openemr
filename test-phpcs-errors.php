<?php
// This file contains intentional phpcs violations for testing

class badClassName {
    public $bad_variable_name;
    
    function badFunctionName(){
        $x=1+2;
        if($x==3){
            echo"Hello World";
        }
        return $x;
    }
    
    public function anotherBadFunction( $param1,$param2 )
    {
        $result=$param1+$param2;
        if($result>10)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

$global_var = new badClassName();
$global_var->bad_variable_name = "test";