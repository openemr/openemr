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
require_once($GLOBALS['fileroot'] . "/library/amc.php");
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

$xml1_0['prescriptionHistoryRequest']['StartHistory']='2011-01-01T00:00:00.000';
$xml1_0['prescriptionHistoryRequest']['EndHistory']=date('Y-m-d').'T23:59:59.000';
$xml1_0['prescriptionHistoryRequest']['PrescriptionStatus']='C';
$xml1_0['prescriptionHistoryRequest']['PrescriptionSubStatus']='%';
$xml1_0['prescriptionHistoryRequest']['PrescriptionArchiveStatus']='N';

$user_details = sqlQuery("SELECT * FROM users WHERE id = ?",array($_SESSION['authUserID']));
$xml1_0['patientInformationRequester']['UserType']='D';
$xml1_0['patientInformationRequester']['UserId']=$user_details['id'];

$xml1_0['patientIdType']='';
$xml1_0['includeSchema']='';

$xml = $client->GetPatientFullMedicationHistory6($xml1_0);

$xml_response=$xml->GetPatientFullMedicationHistory6Result->XmlResponse;
$xml_response_count=$xml->GetPatientFullMedicationHistory6Result->RowCount;
$xml_response = base64_decode($xml_response);

$xmltoarray = new xmltoarray_parser_htmlfix(); //create instance of class
$xmltoarray->xmlparser_setoption(XML_OPTION_SKIP_WHITE, 1); //set options same as xml_parser_set_option
$xmltoarray->xmlparser_setoption(XML_OPTION_CASE_FOLDING, 0);
$xmltoarray->xmlparser_fix_into_struct($xml_response); //fixes html values for XML
$array = $xmltoarray->createArray(); //creates an array with fixed html values
foreach($array as $key => $value){ 
    $array[$key] = $xmltoarray->fix_html_entities($value); //returns proper html values
}
$medArray=$array['NewDataSet']['Table'];
//print_r($medArray);die;
sqlQuery("update prescriptions set active=0 where patient_id=? and erx_source='1'",array($patientid));
for($i=0;$i<sizeof($medArray);$i++)
{
    $provider=sqlQuery("select id from users where username=?",array($medArray[$i]['ExternalPhysicianID']));    
    if($medArray[$i]['DosageForm']){
    $qin=sqlStatement("SELECT option_id FROM list_options WHERE list_id='drug_form' AND title = ?",array($medArray[$i]['DosageForm']));
    $rin=sqlFetchArray($qin);
    if(!$rin['option_id'])
    {
        $rin=sqlQuery("SELECT option_id AS option_id FROM list_options WHERE list_id='drug_form' ORDER BY ABS(option_id) DESC LIMIT 1");
        sqlQuery("INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('drug_form',?,?,?)",array(($rin['option_id']+1),$medArray[$i]['DosageForm'],($rin['option_id']+1)));
        $rin['option_id']=$rin['option_id']+1;
    }
    }
    
    if($medArray[$i]['Route']){
    $qroute=sqlStatement("SELECT option_id FROM list_options WHERE list_id='drug_route' AND title = ?",array($medArray[$i]['Route']));
    $rroute=sqlFetchArray($qroute);
    if(!$rroute['option_id'])
    {
        $rroute=sqlQuery("SELECT option_id AS option_id FROM list_options WHERE list_id='drug_route' ORDER BY ABS(option_id) DESC LIMIT 1");
        sqlQuery("INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('drug_route',?,?,?)",array(($rroute['option_id']+1),$medArray[$i]['Route'],($rroute['option_id']+1)));
        $rroute['option_id']=$rroute['option_id']+1;
    }
    }
    
    if($medArray[$i]['StrengthUOM']){
    $qunit=sqlStatement("SELECT option_id FROM list_options WHERE list_id='drug_units' AND title = ?",array($medArray[$i]['StrengthUOM']));
    $runit=sqlFetchArray($qunit);
    if(!$runit['option_id'])
    {
        $runit=sqlQuery("SELECT option_id AS option_id FROM list_options WHERE list_id='drug_units' ORDER BY ABS(option_id) DESC LIMIT 1");
        sqlQuery("INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('drug_units',?,?,?)",array(($runit['option_id']+1),$medArray[$i]['StrengthUOM'],($runit['option_id']+1)));
        $runit['option_id']=$runit['option_id']+1;
    }
    }
    
    if($medArray[$i]['DosageFrequencyDescription']){
    $qint=sqlStatement("SELECT option_id FROM list_options WHERE list_id='drug_interval' AND title = ?",array($medArray[$i]['DosageFrequencyDescription']));
    $rint=sqlFetchArray($qint);
    if(!$rint['option_id'])
    {
        $rint=sqlQuery("SELECT option_id AS option_id FROM list_options WHERE list_id='drug_interval' ORDER BY ABS(option_id) DESC LIMIT 1");
        sqlQuery("INSERT INTO list_options (list_id,option_id,title,seq) VALUES ('drug_interval',?,?,?)",array(($rint['option_id']+1),$medArray[$i]['DosageFrequencyDescription'],($rint['option_id']+1)));
        $rint['option_id']=$rint['option_id']+1;
    }                
    }
    
    $check=sqlStatement("select * from prescriptions where prescriptionguid=? and patient_id=? and prescriptionguid is not null",array($medArray[$i]['PrescriptionGuid'],$medArray[$i]['ExternalPatientID']));
    $prescription_id='';
    if(sqlNumRows($check)==0)
    {        
        $prescription_id=sqlInsert("insert into prescriptions 
        (
            patient_id,provider_id,date_added,drug,drug_id,form,dosage,size,unit,route,`INTERVAL`,refills,note,`DATETIME`,
            `USER`,site,prescriptionguid,erx_source,rxnorm_drugcode
        )
        values
        (?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),?,?,?,'1',?)",
        array($medArray[$i]['ExternalPatientID'], $provider['id'], substr($medArray[$i]['PrescriptionDate'],0,10), $medArray[$i]['DrugName'],
        $medArray[$i]['DrugID'], $rin['option_id'], $medArray[$i]['DosageNumberDescription'], $medArray[$i]['Strength'], $runit['option_id'],
        $rroute['option_id'], $rint['option_id'], $medArray[$i]['Refills'], $medArray[$i]['PrescriptionNotes'], 
        $_SESSION['authUserID'], $medArray[$i]['SiteID'], $medArray[$i]['PrescriptionGuid'], $medArray[$i]['rxcui']));
    }
    else
    {
        sqlQuery("update prescriptions set 
        provider_id=?, drug=?, drug_id=?, form=?, dosage=?, size=? ,unit=?, route=?, `INTERVAL`=?, refills=?, note=?, 
        `DATETIME`=NOW(),`USER`=?, site=? ,erx_source='1', rxnorm_drugcode=?, active='1'
        WHERE prescriptionguid=? AND patient_id=?
        ",array($provider['id'],$medArray[$i]['DrugName'],$medArray[$i]['DrugID'],$rin['option_id'],$medArray[$i]['DosageNumberDescription'],
        $medArray[$i]['Strength'],$runit['option_id'],$rroute['option_id'],$rint['option_id'],$medArray[$i]['Refills'],
        $medArray[$i]['PrescriptionNotes'],$_SESSION['authUserID'],
        $medArray[$i]['SiteID'],$medArray[$i]['rxcui'],$medArray[$i]['PrescriptionGuid'],$medArray[$i]['ExternalPatientID']));
    }
    $result=sqlFetchArray($check);
    if($result['id'])
    $prescription_id=$result['id'];
    processAmcCall('e_prescribe_amc', true, 'add', $medArray[$i]['ExternalPatientID'], 'prescriptions', $prescription_id);
}
sqlQuery("update patient_data set soap_import_status=? where pid=?",array('2',$pid));
if($xml_response_count==0)
echo htmlspecialchars( xl("Nothing to import for Prescription"), ENT_NOQUOTES);
elseif($xml_response_count>0)
echo htmlspecialchars( xl("Prescription History import successfully completed"), ENT_NOQUOTES);
?>

