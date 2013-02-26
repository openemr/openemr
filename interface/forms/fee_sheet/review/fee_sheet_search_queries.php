<?php
/**
 * Functions to help search for codes on the fee sheet
 * 
 * Copyright (C) 2013 Kevin Yeh <kevin.y@integralemr.com> and OEMR <www.oemr.org>
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
 * @author  Kevin Yeh <kevin.y@integralemr.com>
 * @link    http://www.open-emr.org
 */
require_once("$srcdir/../custom/code_types.inc.php");

/**
 * 
 * wrapper for sequential code set search
 * 
 * @param type $search_type_id      The integer ID used for code_type in codes (e.g. 2 for ICD9)
 * @param type $search_type         A string representing the code type to be searched on (e.g. ICD9, DSMIV)
 * @param type $search_query        The text to search on.
 * @return array
 */
function diagnosis_search($search_type_id,$search_type,$search_query)
{
    $retval=array();
    $search=main_code_set_search($search_type,$search_query,20);
    while($code=sqlFetchArray($search))
    {
        array_push($retval,new code_info($code['code'],$search_type,$code['code_text']));
    }
    return $retval;            
}
?>
