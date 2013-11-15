<?php
namespace ESign;

/**
* Implementation of VerificationIF for hashing a signable object
* 
* Copyright (C) 2013 OEMR 501c3 www.oemr.org
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
*
* @package OpenEMR
* @author  Ken Chapple <ken@mi-squared.com>
* @author  Medical Information Integration, LLC
* @link    http://www.open-emr.org
**/

require_once $GLOBALS['srcdir'].'/ESign/VerificationIF.php';

class Utils_Verification implements VerificationIF
{    
    public function hash( $data )
    {
        $string = "";
        if ( is_array( $data ) ) {
            $string = $this->stringifyArray( $data );
        } else {
            $string = $data;
        }
        $hash = sha1( $string );
        return $hash;
    }    

    protected function stringifyArray( array $arr )
    {
        $string = "";
        foreach ( $arr as $part ) {
            
            if ( is_array( $part ) ) {
                $string .= $this->stringifyArray( $part );
            } else {
                $string .= $part;
            }  
        }
        return $string;
    }
    
    public function verify( $data, $hash )
    {
        $currentHash = $this->hash( $data );
        if ( $currentHash == $hash ) {
            return true;
        }
        
        return false;
    }
}
