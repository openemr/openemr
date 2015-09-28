<?php
/**
 *
 * AMC 304i STAGE2 Numerator
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

class AMC_304i_STG2_Numerator implements AmcFilterIF
{
    public function getTitle()
    {
        return "AMC_304i_STG2 Numerator";
    }
    
    public function test( AmcPatient $patient, $beginDate, $endDate ) 
    {
		//The number of transitions of care and referrals in the denominator where a summary of care record was electronically transmitted using CEHRT to a recipient.
		$sumQry =   "SELECT count(*) as cnt FROM amc_misc_data ".
					"WHERE map_category = 'form_encounter' ".
					"AND amc_id IN( 'med_reconc_amc' ) ".
					"AND from_ccda = 1 ".
					"AND map_id = ? ";
		$check = sqlQuery($sumQry, array($patient->object['encounter'])); 
		if ($check['cnt'] > 0){
			return true;
		}else{
			return false;
		}
    }
}
