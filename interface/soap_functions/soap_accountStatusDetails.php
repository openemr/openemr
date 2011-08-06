<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2011 ZMG LLC <sam@zhservices.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Eldho Chacko <eldho@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//
// +------------------------------------------------------------------------------+
//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
require_once('../globals.php');
require_once('../eRx_xml.php');
require_once('../../library/xmltoarray_parser_htmlfix.php');
set_time_limit(0);
if(!$patientid)
$patientid=$pid;
if($_REQUEST['patient'])
$patientid=$_REQUEST['patient'];
$cred=getErxCredentials();
$path = getErxSoapPath();
$path = explode(';',$path);
$client = new SoapClient($path[1]);
$xml1_0['credentials']['PartnerName']=$cred['0'];
$xml1_0['credentials']['Name']=$cred['1'];
$xml1_0['credentials']['Password']=$cred['2'];

$erxSiteID=sqlQuery("SELECT federal_ein FROM facility WHERE primary_business_entity='1'");
$account=sqlQuery("SELECT * FROM users AS u LEFT JOIN facility AS f ON f.id=u.facility_id WHERE u.username=?",array($_SESSION['authUser']));
$xml1_0['accountRequest']['AccountId']='1';
$xml1_0['accountRequest']['SiteId']=$erxSiteID['federal_ein'];

$location=sqlQuery("SELECT f.id AS id,u.id AS useID FROM users AS u LEFT JOIN facility AS f ON f.id=u.facility_id WHERE u.username=?",array($_SESSION['authUser']));
$xml1_0['locationId']=$patientid;
$xml1_0['userType']='P';
$user_details = sqlQuery("SELECT * FROM users WHERE id = ?",array($_SESSION['authUserID']));
$xml1_0['userId']=$user_details['npi'];

$xml = $client->GetAccountStatus($xml1_0);

html_header_show();?>
<head>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class='body_top'>
    <table class='text' align=center width='90%' height='80%' style='padding-top:6%'>
        <tr>
            <th colspan=2><?php echo htmlspecialchars( xl('eRx Account Status'), ENT_NOQUOTES); ?></th>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars( xl('Pending Rx Count'), ENT_NOQUOTES); ?></td>
            <td><?php echo $xml->GetAccountStatusResult->accountStatusDetail->PendingRxCount;?></td>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars( xl('Alert Count'), ENT_NOQUOTES); ?></td>
            <td><?php echo $xml->GetAccountStatusResult->accountStatusDetail->AlertCount;?></td>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars( xl('Fax Count'), ENT_NOQUOTES); ?></td>
            <td><?php echo $xml->GetAccountStatusResult->accountStatusDetail->FaxCount;?></td>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars( xl('Pharm Com Count'), ENT_NOQUOTES); ?></td>
            <td><?php echo $xml->GetAccountStatusResult->accountStatusDetail->PharmComCount;?></td>
        </tr>
    </table>
</body>