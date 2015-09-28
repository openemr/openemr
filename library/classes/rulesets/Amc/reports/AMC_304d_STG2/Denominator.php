<?php
/**
 *
 * AMC 304d STAGE2 Denominator
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

class AMC_304d_STG2_Denominator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304d_STG2 Denominator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
		//MEASURE STAGE 2: Number of unique patients who have had two or more office visits with the EP in the 24 months prior to the beginning of the EHR reporting period
		$twoEncounter = array( Encounter::OPTION_ENCOUNTER_COUNT => 2 );
		if (  Helper::check( ClinicalType::ENCOUNTER, Encounter::ENC_OFF_VIS, $patient, $beginDate, $endDate, $twoEncounter ) ){
			return true;
		}
		return false;
    }
}
