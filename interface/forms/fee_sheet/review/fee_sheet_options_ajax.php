<?php
/**
 * Controller for getting information about fee sheet options
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
require_once("fee_sheet_options_queries.php");
include_once("$srcdir/jsonwrapper/jsonwrapper.php");
if(!acl_check('acct', 'bill'))
{
    header("HTTP/1.0 403 Forbidden");    
    echo "Not authorized for billing";   
    return false;
}
if (isset($_REQUEST['pricelevel']))
{
    $pricelevel=$_REQUEST['pricelevel'];
}
else
{
    $pricelevel='standard';
}

$fso=load_fee_sheet_options($pricelevel);
$retval=array();
$retval['fee_sheet_options']=$fso;
$retval['pricelevel']=$pricelevel;
echo json_encode($retval);
?>
