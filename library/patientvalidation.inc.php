<?php

/* +-----------------------------------------------------------------------------+

* Function to check if Patientvalidation hook is active
*
* Copyright 2016 matrix israel
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see
* http://www.gnu.org/licenses/licenses.html#GPL


 * @package OpenEMR
 * @author  Dror Golan <drorgo@matrix.co.il>
 * @link    https://www.open-emr.org
 * +------------------------------------------------------------------------------+
 *
 */


//Check if new patient is added to hooks
function checkIfPatientValidationHookIsActive()
{
    $module_query = sqlStatement("SELECT * FROM modules WHERE mod_name= 'Patientvalidation' and mod_active=1");

    if (sqlNumRows($module_query)) {
        //if you want to check inactive active hook please uncheck the following comment
        //$s = "<div style='margin-bottom:10px; border:1px solid black;padding: 5px 5px 5px 5px;width:300px;background-color: #79bbff'><center>  " . xl("You are using patient validation module") . "</center></div>";
       // echo $s;
        return true;
    } else {
        return false;
    }
}
