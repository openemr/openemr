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
require_once("{$GLOBALS['srcdir']}/lists.inc");
set_time_limit(0);
if(!$patientid)
$patientid=$pid;
if($_REQUEST['patient'])
$patientid=$_REQUEST['patient'];
$cred=getErxCredentials();
$path = getErxSoapPath();
$path = explode(';',$path);
$client = new SoapClient($path[0]);
$xml1_0['credentials']['PartnerName']=$cred['0'];
$xml1_0['credentials']['Name']=$cred['1'];
$xml1_0['credentials']['Password']=$cred['2'];

$erxSiteID=sqlQuery("SELECT federal_ein FROM facility WHERE primary_business_entity='1'");
$account=sqlQuery("SELECT * FROM users AS u LEFT JOIN facility AS f ON f.id=u.facility_id WHERE u.username=?",array($_SESSION['authUser']));
$xml1_0['accountRequest']['AccountId']='1';
$xml1_0['accountRequest']['SiteId']=$erxSiteID['federal_ein'];

$xml1_0['patientRequest']['PatientId']=$patientid;

$user_details = sqlQuery("SELECT * FROM users WHERE id = ?",array($_SESSION['authUserID']));
$xml1_0['patientInformationRequester']['UserType']='D';
$xml1_0['patientInformationRequester']['UserId']=$user_details['id'];

$xml = $client->GetPatientAllergyHistoryV3($xml1_0);

$xml_response=$xml->GetPatientAllergyHistoryV3Result->XmlResponse;
$xml_response_count=$xml->GetPatientAllergyHistoryV3Result->RowCount;
$xml_response = base64_decode($xml_response);

$xmltoarray = new xmltoarray_parser_htmlfix(); //create instance of class
$xmltoarray->xmlparser_setoption(XML_OPTION_SKIP_WHITE, 1); //set options same as xml_parser_set_option
$xmltoarray->xmlparser_setoption(XML_OPTION_CASE_FOLDING, 0);
$xmltoarray->xmlparser_fix_into_struct($xml_response); //fixes html values for XML
$array = $xmltoarray->createArray(); //creates an array with fixed html values
foreach($array as $key => $value){ 
    $array[$key] = $xmltoarray->fix_html_entities($value); //returns proper html values
}
$allergyArray=$array['NewDataSet']['Table'];

sqlQuery("update lists set enddate=NOW() where type='allergy' and pid=?",array($patientid));
for($i=0;$i<sizeof($allergyArray);$i++)
{
    $qoutcome=sqlStatement("SELECT option_id FROM list_options WHERE list_id='outcome' AND title = ?",array($allergyArray[$i]['AllergySeverityName']));
    $routcome=sqlFetchArray($qoutcome);
    if(!$routcome['option_id'])
    {
        $routcome=sqlQuery("SELECT option_id AS option_id FROM list_options WHERE list_id='outcome' ORDER BY ABS(option_id) DESC LIMIT 1");
        sqlQuery("INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('outcome',?,?,?)",array(($routcome['option_id']+1),$allergyArray[$i]['AllergySeverityName'],($routcome['option_id']+1)));
        $routcome['option_id']=$routcome['option_id']+1;
    }
    $res=sqlStatement("select * from lists where pid=? and type='allergy' and title=?",array($patientid,$allergyArray[$i]['AllergyName']));
    $row=sqlFetchArray($res);
    if(sqlNumRows($res)==0)
    {
        sqlQuery("insert into lists (date,type,title,pid,user,outcome,external_allergyid,erx_source) values (NOW(),'allergy',?,?,?,?,?,'1')",
        array($allergyArray[$i]['AllergyName'], $patientid, $_SESSION['authUserID'], $routcome['option_id'], $allergyArray[$i]['AllergyId']));
        setListTouch ($patientid,'allergy');
    }
    elseif($row['erx_source']==0)
    {
        sqlQuery("update lists set outcome=?, erx_source='1', external_allergyid=? where pid=? and title=?",
        array($routcome['option_id'], $allergyArray[$i]['AllergyId'], $patientid, $allergyArray[$i]['AllergyName']));
    }
    else
    {
        sqlQuery("update lists set outcome=? where pid=? and erx_source='1' and external_allergyid=? and title=?",
        array($routcome['option_id'], $patientid, $allergyArray[$i]['AllergyId'], $allergyArray[$i]['AllergyName']));
    }
	sqlQuery("update lists set enddate = null where type='allergy' and pid=? and title=?",array($patientid,$allergyArray[$i]['AllergyName']));
}
sqlQuery("update patient_data set soap_import_status=? where pid=?",array('4',$patientid));
if($xml_response_count==0)
echo htmlspecialchars( xl("Nothing to import for Allergy"), ENT_NOQUOTES);
elseif($xml_response_count>0)
echo htmlspecialchars( xl("Allergy import successfully completed"), ENT_NOQUOTES);
?>

