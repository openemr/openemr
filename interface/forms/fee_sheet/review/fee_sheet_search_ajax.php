<?php
/**
 * Controller for AJAX requests to search for codes from the fee sheet
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
$fake_register_globals=false;
$sanitize_all_escapes=true;
require_once("../../../globals.php");
require_once("fee_sheet_classes.php");
require_once("fee_sheet_search_queries.php");
include_once("$srcdir/jsonwrapper/jsonwrapper.php");

if(!acl_check('acct', 'bill'))
{
    header("HTTP/1.0 403 Forbidden");    
    echo "Not authorized for billing";   
    return false;
}

if(isset($_REQUEST['search_query']))
{
    $search_query=$_REQUEST['search_query'];
}
else
{
    header("HTTP/1.0 403 Forbidden");    
    echo "No search parameter specified";   
    return false;
}
if(isset($_REQUEST['search_type']))
{
    $search_type=$_REQUEST['search_type'];
}
else 
{
    $search_type='ICD9';
}
if(isset($_REQUEST['search_type_id']))
{
    $search_type_id=$_REQUEST['search_type_id'];
}
else
{
    $search_type_id=2;
}
$retval['codes']=diagnosis_search($search_type_id,$search_type,$search_query);

echo json_encode($retval);
?>
