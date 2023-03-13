<?php 
namespace OpenEMR\Modules\ClaimRevConnector;


class ValueMapping
{
    public static function MapPayerResponsibility($payerResponsibility)
    {        
        if(strtolower($payerResponsibility) == "primary")
        {
            return "p";
        }
        else if(strtolower($payerResponsibility) == "secondary")
        {
            return "s";
        }
        else if(strtolower($payerResponsibility) == "tertiary")
        {
            return"t";
        }
        else
        {
            return substr($payerResponsibility,0,1);
        }
    }
}