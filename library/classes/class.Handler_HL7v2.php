<?php

    // $Id$
    // $Author$

class Handler_HL7v2
{

    var $parser;

    function __construct($parser)
    {
        $this->parser = &$parser;
    }

    function Type()
    {
        return false;
    }

    //----- Internal methods

    function _StripToNumeric($string)
    {
        $target = '';
        for ($pos = 0; $pos < strlen($string); $pos++) {
            switch (substr($string, $pos, 1)) {
                case '0':
                case '1':
                case '2':
                case '3':
                case '4':
                case '5':
                case '6':
                case '7':
                case '8':
                case '9':
                                                                $target .= substr($string, $pos, 1);
                    break;
                default: // do nothing
                    break;
            }
        }

        return $target;
    } // end method _StripToNumeric
} // end class Handler_HL7v2
