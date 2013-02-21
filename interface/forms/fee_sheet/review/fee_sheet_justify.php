<?php
/**
 * Controller for fee sheet justification AJAX requests
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

require_once("fee_sheet_queries.php");
include_once("$srcdir/jsonwrapper/jsonwrapper.php");
if(!acl_check('acct', 'bill'))
{
    header("HTTP/1.0 403 Forbidden");    
    echo "Not authorized for billing";   
    return false;
}


if(isset($_REQUEST['pid']))
{
    $req_pid=$_REQUEST['pid'];
}

if(isset($_REQUEST['encounter']))
{
    $req_encounter=$_REQUEST['encounter'];
}
if(isset($_REQUEST['task']))
{
    $task=$_REQUEST['task'];
}
if(isset($_REQUEST['billing_id']))
{
    $billing_id=$_REQUEST['billing_id'];
}
if($task=='retrieve')
{
    $retval=array();
    $patient=issue_diagnoses($req_pid,$req_encounter);      
    $common=common_diagnoses();
    $retval['patient']=$patient;
    $retval['common']=$common;
    $fee_sheet_diags=array();
    $fee_sheet_procs=array();
    fee_sheet_items($req_pid,$req_encounter,$fee_sheet_diags,$fee_sheet_procs);
    $retval['current']=$fee_sheet_diags;
    echo json_encode($retval);
    return;
}
if($task=='update')
{
    $skip_issues=false;
    if(isset($_REQUEST['skip_issues']))
    {
        $skip_issues=$_REQUEST['skip_issues']=='true';
    }
    $diags=array();
    if(isset($_REQUEST['diags']))
    {
        $json_diags=json_decode($_REQUEST['diags']);
    }
    foreach($json_diags as $diag)
    {
        $new_diag=new code_info($diag->{'code'},$diag->{'code_type'},$diag->{'description'});
        if(isset($diag->{'prob_id'}))
        {
            $new_diag->db_id=$diag->{'prob_id'};
        }
        else
        {
            $new_diag->db_id=null;
            $new_diag->create_problem=$diag->{'create_problem'};
        }
        $diags[]=$new_diag;
    }
    $database->StartTrans();
    create_diags($req_pid,$req_encounter,$diags);
    if(!$skip_issues)
    {
        update_issues($req_pid,$req_encounter,$diags);          
    }
    update_justify($req_pid,$req_encounter,$diags,$billing_id);
    $database->CompleteTrans();
}

?>
