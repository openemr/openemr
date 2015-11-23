<?php
/**
 *
 * AMC 304b 1 STAGE2 Numerator
 *
 * Copyright (C) 2015 Ensoftek, Inc
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Ensoftek
 * @link    http://www.open-emr.org
 */
// Generate and transmit permissible prescriptions electronically and queried for drug formulary.( AMC-2014:170.314(g)(1)/(2)–8 )

class AMC_304b_1_STG2_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304b_1_STG2 Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
       //The number of prescriptions in the denominator generated, queried for a drug formulary and transmitted electronically
       //
       // Still TODO
       // AMC MU2 TODO :
       // Note OpenEMR official codebase does not support the eTransmit and forumulary items
       //
       if ( ($patient->object['eTransmit'] == 1) && ($patient->object['formulary'] == 'yes') )  {
		   return true;
	   }else{
		   return false;
	   }
    }
}
