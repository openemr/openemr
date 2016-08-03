<?php

/**
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
 * http://www.gnu.org/licenses/licenses.html#GPL .
 *
 * @package OpenEMR
 * @license http://www.gnu.org/licenses/licenses.html#GPL GNU GPL V3+
 * @author  Dror Golan <drorgo@matrix.co.il>
 * @link    http://www.open-emr.org
 */


/**get all the validation on the page
 * @param $title
 * @return array
 */
function collectValidationPageRules($title,$active=true){

    if($active){
        $sql = sqlStatement("SELECT * " .
            "FROM `list_options` WHERE list_id=? AND activity=?  AND title=?",array('page_validation',1,$title));
    }
    else {
        $sql = sqlStatement("SELECT * " .
            "FROM `list_options` WHERE list_id=? AND title=?", array('page_validation', $title));
    }

    $dataArray=array();
    while($row = sqlFetchArray($sql) ) {
        $formPageNameArray = explode('#', $row['option_id']);
        $dataArray[$formPageNameArray[1]]=array('page_name' => $formPageNameArray[0] . ".php",'rules' => $row['notes']);
    }
    return $dataArray;

}

