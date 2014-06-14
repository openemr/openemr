<?php

/**
 * interface/eRx_xml.php Functions for interacting with NewCrop communications.
 *
 * Copyright (C) 2011 ZMG LLC <sam@zhservices.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option) any
 * later version.  This program is distributed in the hope that it will be
 * useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General
 * Public License for more details.  You should have received a copy of the GNU
 * General Public License along with this program.
 * If not, see <http://opensource.org/licenses/gpl-license.php>.
 * 
 * @package    OpenEMR
 * @subpackage NewCrop
 * @author     Eldho Chacko <eldho@zhservices.com>
 * @author     Vinish K <vinish@zhservices.com>
 * @link       http://www.open-emr.org
 */

function getErxPath()
{
    //if($GLOBALS['erx_source']==1)
    //return $GLOBALS['erx_path'];
    //else if($GLOBALS['erx_source']==2)
    return $GLOBALS['erx_path_production'];
}

function getErxSoapPath()
{
    //if($GLOBALS['erx_source']==1)
    //return $GLOBALS['erx_path_soap'];
    //else if($GLOBALS['erx_source']==2)
    return $GLOBALS['erx_path_soap_production'];
}

function getErxCredentials()
{
    $cred=array();
    //if($GLOBALS['erx_source']==1)
    //{
    //    $cred[]=$GLOBALS['partner_name'];
    //    $cred[]=$GLOBALS['erx_name'];
    //    $cred[]=$GLOBALS['erx_password'];
    //}
    //else if($GLOBALS['erx_source']==2)
    //{
        $cred[]=$GLOBALS['partner_name_production'];
        $cred[]=$GLOBALS['erx_name_production'];
        $cred[]=$GLOBALS['erx_password_production'];
    //}
    return $cred;
}

function validation($val_check,$val,$msg)
{
    if(!$val)
    $msg .= $val_check.' '.xl('missing').'<br>';
    return $msg;
}

function stripSpecialCharacterFacility($str)
{
    $str=preg_replace("/[^a-zA-Z0-9 '().,#:\/\-@_%]/","",$str);
    return $str;
}

function stripSpecialCharacter($str)
{
    $str=preg_replace("/[^a-zA-Z0-9 '().,#:\/\-@_%]/","",$str);
    return $str;
}

function stripPhoneSlashes($str)
{
    $str=preg_replace('/-/','',$str);
    return $str;
}

function trimData($str,$length)
{
    $str=substr($str,0,($length-1));
    return $str;
}

function stringToNumeric($str)
{	
	if(is_numeric($str)){
    return array($str,"");
    }
    else{
    for($i=0;$i<strlen($str);$i++){
        $x=substr($str,$i,1);
        if(is_numeric($x) && !$txt){
        $num.=$x;
        }
        else{
        $txt.=$x;
        
        }
    }
    return array($num,$txt);
    }
    $str=substr($str,0,($length-1));
    return $str;
}
function credentials($doc,$r)
{
    global $msg;
    $cred=getErxCredentials();
    $msg = validation(xl('Partner Name'),$cred['0'],$msg);
    $b = $doc->createElement( "Credentials" );
    $partnerName = $doc->createElement( "partnerName" );
    $partnerName->appendChild(
        $doc->createTextNode( $cred['0'] )
    );
    $b->appendChild( $partnerName );
    $msg = validation(xl('ERX Name'),$cred['1'],$msg);
    $name = $doc->createElement( "name" );
    $name->appendChild(
        $doc->createTextNode( $cred['1'] )
    );
    $b->appendChild( $name );
    $msg = validation(xl('ERX Password'),$cred['2'],$msg);
    $password = $doc->createElement( "password" );
    $password->appendChild(
        $doc->createTextNode( $cred['2'] )
    );
    $b->appendChild( $password );
    $productName = $doc->createElement( "productName" );
    $productName->appendChild(
        $doc->createTextNode( 'OpenEMR' )
    );
    $b->appendChild( $productName );
    $productVersion = $doc->createElement( "productVersion" );
    $productVersion->appendChild(
        $doc->createTextNode( $GLOBALS['openemr_version'] )
    );
    $b->appendChild( $productVersion );
    $r->appendChild( $b );
}

function user_role($doc,$r)
{
    global $msg;
    $userRole=sqlQuery("select * from users where username=?",array($_SESSION['authUser']));
    if(!$userRole['newcrop_user_role'])
    {echo xl('Unauthorized access to ePrescription');die;}
    $userRole['newcrop_user_role'] = preg_replace('/erx/','',$userRole['newcrop_user_role']);
    if($userRole['newcrop_user_role'] == 'doctor')
    $userRole['eRxUser'] = 'LicensedPrescriber';
    elseif($userRole['newcrop_user_role'] == 'admin' || $userRole['newcrop_user_role'] == 'manager' || $userRole['newcrop_user_role'] == 'nurse')
    $userRole['eRxUser'] = 'Staff';
    elseif($userRole['newcrop_user_role'] == 'midlevelPrescriber')
    $userRole['eRxUser'] = 'MidlevelPrescriber';
    elseif($userRole['newcrop_user_role'] == 'supervisingDoctor')
    $userRole['eRxUser'] = 'SupervisingDoctor';
    $msg = validation(xl('ERX User'),$userRole['eRxUser'],$msg);
    $b = $doc->createElement( "UserRole" );
    $user = $doc->createElement( "user" );
    $user->appendChild(
        $doc->createTextNode( $userRole['eRxUser'] )
    );
    $b->appendChild( $user );
    $msg = validation(xl('ERX Role'),$userRole['newcrop_user_role'],$msg);
    $role = $doc->createElement( "role" );
    $role->appendChild(
        $doc->createTextNode( $userRole['newcrop_user_role'] )
    );
    $b->appendChild( $role );
    $r->appendChild( $b );
}

function destination($doc,$r,$page='',$pid)
{
    global $msg,$page;
    $userRole=sqlQuery("select * from users where username=?",array($_SESSION['authUser']));
    $userRole['newcrop_user_role'] = preg_replace('/erx/','',$userRole['newcrop_user_role']);
    if(!$page)
    {
        $page='compose';
        if($userRole['newcrop_user_role']=='admin')
        $page='admin';
        elseif($userRole['newcrop_user_role']=='manager')
        $page='manager';        
    }
    $b = $doc->createElement( "Destination" );
    $requestedPage = $doc->createElement( "requestedPage" );
    $requestedPage->appendChild(
        $doc->createTextNode( $page )
    );
    $b->appendChild( $requestedPage );
    $r->appendChild( $b );
}

function account($doc,$r)
{
    global $msg;
    $erxSiteID=sqlQuery("SELECT * FROM facility WHERE primary_business_entity='1'");
    if(!$erxSiteID['federal_ein'])
    {
	echo htmlspecialchars( xl("Please select a Primary Business Entity facility with 'Tax ID' as your facility Tax ID. If you are an individual practitioner, use your tax id. This is used for identifying you in the NewCrop system."), ENT_NOQUOTES);
	die;
    }
    $b = $doc->createElement( "Account" );
    $b->setAttribute('ID', $GLOBALS['erx_account_id']);
    $erxSiteID['name']=stripSpecialCharacterFacility($erxSiteID['name']);
    $erxSiteID['name']=trimData($erxSiteID['name'],35);
    $msg = validation(xl('Account Name'),$erxSiteID['name'],$msg);
    $accountName = $doc->createElement( "accountName" );
    $accountName->appendChild(
        $doc->createTextNode( $erxSiteID['name'] )
    );
    $b->appendChild( $accountName );
    $msg = validation(xl('Site ID'),$_SESSION['site_id'],$msg);
    $siteID = $doc->createElement( "siteID" );
    $siteID->appendChild(
        $doc->createTextNode( $erxSiteID['federal_ein'] )
    );
    $b->appendChild( $siteID );
    $erxSiteID['street']=stripSpecialCharacterFacility($erxSiteID['street']);
    $erxSiteID['street']=trimData($erxSiteID['street'],35);
    $AccountAddress = $doc->createElement( "AccountAddress" );
        $msg = validation(xl('Facility Street'),$erxSiteID['street'],$msg);
        $address1 = $doc->createElement( "address1" );
        $address1->appendChild(
            $doc->createTextNode( $erxSiteID['street'] )
        );
        $AccountAddress->appendChild( $address1 );
        $msg = validation(xl('Facility City'),$erxSiteID['city'],$msg);
        $city = $doc->createElement( "city" );
        $city->appendChild(
            $doc->createTextNode( $erxSiteID['city'] )
        );
        $AccountAddress->appendChild( $city );
        $msg = validation(xl('Facility State'),$erxSiteID['state'],$msg);
        $state = $doc->createElement( "state" );
        $state->appendChild(
            $doc->createTextNode( $erxSiteID['state'] )
        );
        $AccountAddress->appendChild( $state );
        $jasonbigzip=$erxSiteID['postal_code'];
	$jasonbigzip=preg_replace('/[^0-9]/','',$jasonbigzip);
	if(strlen($jasonbigzip) >=5){
	    $jasonzip=substr($jasonbigzip,0,5);
	    $zip4=substr($jasonbigzip,5,4);
	}
	else{
	    $msg = validation(xl('Facility Zip'),$jasonzip,$msg);
	}
        $zip = $doc->createElement( "zip" );
        $zip->appendChild(
            $doc->createTextNode( $jasonzip )
        );
        $AccountAddress->appendChild( $zip );
	if(strlen($zip4)==4){
	    $zipFour = $doc->createElement( "zip4" );
	    $zipFour->appendChild(
		$doc->createTextNode( $zip4 )
	    );
	    $AccountAddress->appendChild( $zipFour );
	}
        $msg = validation(xl('Facility Country code'),$erxSiteID['country_code'],$msg);
        $county_code = substr($erxSiteID['country_code'],0,2);
        $country = $doc->createElement( "country" );
        $country->appendChild(
            $doc->createTextNode( $county_code )
        );    
        $AccountAddress->appendChild( $country );
    $b->appendChild( $AccountAddress );
    $msg = validation(xl('Facility Phone'),$erxSiteID['phone'],$msg);
    $accountPrimaryPhoneNumber = $doc->createElement( "accountPrimaryPhoneNumber" );
    $erxSiteID['phone'] = stripPhoneSlashes($erxSiteID['phone']);
    $accountPrimaryPhoneNumber->appendChild(        
        $doc->createTextNode( $erxSiteID['phone'] )
    );
    $b->appendChild( $accountPrimaryPhoneNumber );
    $msg = validation(xl('Facility Fax'),$erxSiteID['fax'],$msg);
    $accountPrimaryFaxNumber = $doc->createElement( "accountPrimaryFaxNumber" );
    $erxSiteID['fax'] = stripPhoneSlashes($erxSiteID['fax']);
    $accountPrimaryFaxNumber->appendChild(
        $doc->createTextNode( $erxSiteID['fax'] )
    );
    $b->appendChild( $accountPrimaryFaxNumber );
    $r->appendChild( $b );
}

function location($doc,$r)
{
    global $msg;
    $userRole=sqlQuery("SELECT * FROM users AS u LEFT JOIN facility AS f ON f.id=u.facility_id WHERE u.username=?",array($_SESSION['authUser']));
    $b = $doc->createElement( "Location" );
    $b->setAttribute('ID',$userRole['id']);
    $userRole['name']=stripSpecialCharacterFacility($userRole['name']);
    $userRole['name']=trimData($userRole['name'],35);
    $locationName = $doc->createElement( 'locationName' );
    $locationName->appendChild(
        $doc->createTextNode( $userRole['name'] )
    );
    $b->appendChild($locationName);
    $userRole['street']=stripSpecialCharacterFacility($userRole['street']);
    $userRole['street']=trimData($userRole['street'],35);
    $LocationAddress = $doc->createElement( 'LocationAddress' );
        if($userRole['street']){
        $address1 = $doc->createElement( 'address1' );
        $address1->appendChild(
            $doc->createTextNode( $userRole['street'] )
        );
        $LocationAddress->appendChild($address1);
        }
        if($userRole['city']){
        $city = $doc->createElement( 'city' );
        $city->appendChild(
            $doc->createTextNode( $userRole['city'] )
        );
        $LocationAddress->appendChild( $city );
        }
        if($userRole['state']){
        $state = $doc->createElement( 'state' );
        $state->appendChild(
            $doc->createTextNode( $userRole['state'] )
        );
        $LocationAddress->appendChild($state);
        }
	$jasonbigzip=$userRole['postal_code'];
	$jasonbigzip=preg_replace('/[^0-9]/','',$jasonbigzip);
	if(strlen($jasonbigzip) >=5){
	    $jasonzip=substr($jasonbigzip,0,5);
	    $zip4=substr($jasonbigzip,5,4);
	}
	else{
	    $msg = validation(xl('Facility Zip'),$jasonzip,$msg);
	}
        $zip = $doc->createElement( "zip" );
        $zip->appendChild(
            $doc->createTextNode( $jasonzip )
        );
        $LocationAddress->appendChild( $zip );
	if(strlen($zip4)==4){
	    $zipFour = $doc->createElement( "zip4" );
	    $zipFour->appendChild(
		$doc->createTextNode( $zip4 )
	    );
	    $LocationAddress->appendChild( $zipFour );
	}
        if($userRole['country_code']){
        $county_code = substr($userRole['country_code'],0,2);
        $country = $doc->createElement( 'country' );
        $country->appendChild(
            $doc->createTextNode( $county_code )
        );
        $LocationAddress->appendChild($country);
        }
    $b->appendChild($LocationAddress);
    if($userRole['phone']){
    $userRole['phone'] = stripPhoneSlashes($userRole['phone']);
    $primaryPhoneNumber = $doc->createElement( 'primaryPhoneNumber' );
    $primaryPhoneNumber->appendChild(
        $doc->createTextNode( $userRole['phone'] )
    );
    $b->appendChild($primaryPhoneNumber);
    }
    if($userRole['fax']){
    $userRole['fax'] = stripPhoneSlashes($userRole['fax']);
    $primaryFaxNumber = $doc->createElement( 'primaryFaxNumber' );
    $primaryFaxNumber->appendChild(
        $doc->createTextNode( $userRole['fax'] )
    );
    $b->appendChild($primaryFaxNumber);
    }
    $pharmacyContactNumber = $doc->createElement( 'pharmacyContactNumber' );
    $pharmacyContactNumber->appendChild(
        $doc->createTextNode( $userRole['phone'] )
    );
    $b->appendChild($pharmacyContactNumber);
    $r->appendChild( $b );
}

function LicensedPrescriber($doc,$r)
{
    global $msg;
    $user_details = sqlQuery("SELECT * FROM users WHERE id = ?",array($_SESSION['authUserID']));
    $b = $doc->createElement( "LicensedPrescriber" );
    $b->setAttribute('ID',$user_details['npi']);
    $LicensedPrescriberName = $doc->createElement( "LicensedPrescriberName" );
        $user_details['lname']=stripSpecialCharacter($user_details['lname']);
        $msg = validation(xl('LicensedPrescriber Last name'),$user_details['lname'],$msg);
        $last = $doc->createElement( "last" );
        $last->appendChild(
            $doc->createTextNode( $user_details['lname'] )
        );
        $LicensedPrescriberName->appendChild( $last );
        $user_details['fname']=stripSpecialCharacter($user_details['fname']);
        $msg = validation(xl('User First name'),$user_details['fname'],$msg);
        $first = $doc->createElement( "first" );
        $first->appendChild(
            $doc->createTextNode( $user_details['fname'] )
        );
        $LicensedPrescriberName->appendChild( $first );
        $user_details['mname']=stripSpecialCharacter($user_details['mname']);
        $middle = $doc->createElement( "middle" );
        $middle->appendChild(
            $doc->createTextNode( $user_details['mname'] )
        );
        $LicensedPrescriberName->appendChild( $middle );
    $b->appendChild( $LicensedPrescriberName );
    $msg = validation(xl('DEA'),$user_details['federaldrugid'],$msg);
    $dea = $doc->createElement( "dea" );
    $dea->appendChild(
        $doc->createTextNode( $user_details['federaldrugid'] )
    );
    $b->appendChild( $dea );
    if($user_details['upin']){
    $upin = $doc->createElement( "upin" );
    $upin->appendChild(
        $doc->createTextNode( $user_details['upin'] )
    );
    $b->appendChild( $upin );
    }
    $licenseNumber = $doc->createElement( "licenseNumber" );
    $licenseNumber->appendChild(
        $doc->createTextNode( $user_details['state_license_number'] )
    );
    $b->appendChild( $licenseNumber );
    $msg = validation(xl('LicensedPrescriber NPI'),$user_details['npi'],$msg);
    $npi = $doc->createElement( "npi" );
    $npi->appendChild(
        $doc->createTextNode( $user_details['npi'] )
    );
    $b->appendChild( $npi );
    $r->appendChild( $b );
}

function Staff($doc,$r)
{
    global $msg;
    $user_details = sqlQuery("SELECT * FROM users WHERE id = ?",array($_SESSION['authUserID']));
    $b = $doc->createElement( "Staff" );
    $b->setAttribute('ID',$user_details['username']);
    $StaffName = $doc->createElement( "StaffName" );
        $user_details['lname']=stripSpecialCharacter($user_details['lname']);
        $last = $doc->createElement( "last" );
        $last->appendChild(
            $doc->createTextNode( $user_details['lname'] )
        );
        $StaffName->appendChild( $last );
        $user_details['fname']=stripSpecialCharacter($user_details['fname']);
        $first = $doc->createElement( "first" );
        $first->appendChild(
            $doc->createTextNode( $user_details['fname'] )
        );
        $StaffName->appendChild( $first );
        $user_details['mname']=stripSpecialCharacter($user_details['mname']);
        $middle = $doc->createElement( "middle" );
        $middle->appendChild(
            $doc->createTextNode( $user_details['mname'] )
        );
        $StaffName->appendChild( $middle );
    $b->appendChild( $StaffName );
    $license = $doc->createElement( "license" );
    $license->appendChild(
        $doc->createTextNode( $user_details['license'] )
    );
    $b->appendChild( $license );
    $r->appendChild( $b );
}

function SupervisingDoctor($doc,$r)
{
    global $msg;
    $user_details = sqlQuery("SELECT * FROM users WHERE id = ?",array($_SESSION['authUserID']));
    $b = $doc->createElement( "SupervisingDoctor" );
    $b->setAttribute('ID',$user_details['npi']);
    $LicensedPrescriberName = $doc->createElement( "LicensedPrescriberName" );
        $user_details['lname']=stripSpecialCharacter($user_details['lname']);
        $msg = validation(xl('Supervising Doctor Last name'),$user_details['lname'],$msg);
        $last = $doc->createElement( "last" );
        $last->appendChild(
            $doc->createTextNode( $user_details['lname'] )
        );
        $LicensedPrescriberName->appendChild( $last );
        $user_details['fname']=stripSpecialCharacter($user_details['fname']);
        $msg = validation(xl('Supervising Doctor First name'),$user_details['fname'],$msg);
        $first = $doc->createElement( "first" );
        $first->appendChild(
            $doc->createTextNode( $user_details['fname'] )
        );
        $LicensedPrescriberName->appendChild( $first );
        $user_details['mname']=stripSpecialCharacter($user_details['mname']);
        $middle = $doc->createElement( "middle" );
        $middle->appendChild(
            $doc->createTextNode( $user_details['mname'] )
        );
        $LicensedPrescriberName->appendChild( $middle );
    $b->appendChild( $LicensedPrescriberName );
    $msg = validation(xl('Supervising Doctor DEA'),$user_details['federaldrugid'],$msg);
    $dea = $doc->createElement( "dea" );
    $dea->appendChild(
        $doc->createTextNode( $user_details['federaldrugid'] )
    );
    $b->appendChild( $dea );
    if($user_details['upin']){
    $upin = $doc->createElement( "upin" );
    $upin->appendChild(
        $doc->createTextNode( $user_details['upin'] )
    );
    $b->appendChild( $upin );
    }
    $licenseNumber = $doc->createElement( "licenseNumber" );
    $licenseNumber->appendChild(
        $doc->createTextNode( $user_details['state_license_number'] )
    );
    $b->appendChild( $licenseNumber );
    $msg = validation(xl('Supervising Doctor NPI'),$user_details['npi'],$msg);
    $npi = $doc->createElement( "npi" );
    $npi->appendChild(
        $doc->createTextNode( $user_details['npi'] )
    );
    $b->appendChild( $npi );
    $r->appendChild( $b );
}

function MidlevelPrescriber($doc,$r)
{
    global $msg;
    $user_details = sqlQuery("SELECT * FROM users WHERE id = ?",array($_SESSION['authUserID']));
    $b = $doc->createElement( "MidlevelPrescriber" );
    $b->setAttribute('ID',$user_details['npi']);
    $LicensedPrescriberName = $doc->createElement( "LicensedPrescriberName" );
        $user_details['lname']=stripSpecialCharacter($user_details['lname']);
        $msg = validation(xl('Midlevel Prescriber Last name'),$user_details['lname'],$msg);
        $last = $doc->createElement( "last" );
        $last->appendChild(
            $doc->createTextNode( $user_details['lname'] )
        );
        $LicensedPrescriberName->appendChild( $last );
        $user_details['fname']=stripSpecialCharacter($user_details['fname']);
        $msg = validation(xl('Midlevel Prescriber First name'),$user_details['fname'],$msg);
        $first = $doc->createElement( "first" );
        $first->appendChild(
            $doc->createTextNode( $user_details['fname'] )
        );
        $LicensedPrescriberName->appendChild( $first );
        $user_details['mname']=stripSpecialCharacter($user_details['mname']);
        $middle = $doc->createElement( "middle" );
        $middle->appendChild(
            $doc->createTextNode( $user_details['mname'] )
        );
        $LicensedPrescriberName->appendChild( $middle );
        if($user_details['title']){
        $msg = validation(xl('Midlevel Prescriber Prefix'),$user_details['title'],$msg);
        $prefix = $doc->createElement( "prefix" );
        $prefix->appendChild(
            $doc->createTextNode( $user_details['title'] )
        );
        $LicensedPrescriberName->appendChild( $prefix );
        }
    $b->appendChild( $LicensedPrescriberName );
    $msg = validation(xl('Midlevel Prescriber DEA'),$user_details['federaldrugid'],$msg);
    $dea = $doc->createElement( "dea" );
    $dea->appendChild(
        $doc->createTextNode( $user_details['federaldrugid'] )
    );
    $b->appendChild( $dea );
    if($user_details['upin']){
    $upin = $doc->createElement( "upin" );
    $upin->appendChild(
        $doc->createTextNode( $user_details['upin'] )
    );
    $b->appendChild( $upin );
    }
    $licenseNumber = $doc->createElement( "licenseNumber" );
    $licenseNumber->appendChild(
        $doc->createTextNode( $user_details['state_license_number'] )
    );
    $b->appendChild( $licenseNumber );
    $r->appendChild( $b );
}

function Patient($doc,$r,$pid)
{
    global $msg,$warning_msg,$dem_check;
    $patient_data=sqlQuery("select *, DATE_FORMAT(DOB,'%Y%m%d') AS date_of_birth from patient_data where pid=?",array($pid));
    $b = $doc->createElement( "Patient" );
    $b->setAttribute('ID',$patient_data['pid']);
    $PatientName = $doc->createElement( "PatientName" );
        $patient_data['lname']=stripSpecialCharacter($patient_data['lname']);    
        $patient_data['lname']=trimData($patient_data['lname'],35);
        //$msg = validation(xl('Patient Last name'),$patient_data['lname'],$msg);
        if($patient_data['lname']=='')
            $dem_check.=htmlspecialchars( xl("Patient Last name is missing"), ENT_NOQUOTES)."<br>";
        $last = $doc->createElement( "last" );
        $last->appendChild(
            $doc->createTextNode( $patient_data['lname'] )
        );
        $PatientName->appendChild( $last );
        $patient_data['fname']=stripSpecialCharacter($patient_data['fname']);
        $patient_data['fname']=trimData($patient_data['fname'],35);
        //$msg = validation(xl('Patient First name'),$patient_data['fname'],$msg);
        if($patient_data['fname']=='')
            $dem_check.=htmlspecialchars( xl("Patient First name is missing"), ENT_NOQUOTES)."<br>";
        $first = $doc->createElement( "first" );
        $first->appendChild(
            $doc->createTextNode( $patient_data['fname'] )
        );
        $PatientName->appendChild( $first );
        $patient_data['mname']=stripSpecialCharacter($patient_data['mname']);
        $patient_data['mname']=trimData($patient_data['mname'],35);
        $middle = $doc->createElement( "middle" );
        $middle->appendChild(
            $doc->createTextNode( $patient_data['mname'] )
        );
        $PatientName->appendChild( $middle );
    $b->appendChild( $PatientName );
    $PatientAddress = $doc->createElement( "PatientAddress" );
        $patient_data['street']=stripSpecialCharacter($patient_data['street']);
        $patient_data['street']=trimData($patient_data['street'],35);
        $msg = validation(xl('Patient Address'),$patient_data['street'],$msg);
        if(trim($patient_data['street'])=='')
            $warning_msg .= "<br>".htmlspecialchars( xl("Patient Address is missing"), ENT_NOQUOTES);
        $address1 = $doc->createElement( "address1" );
        $address1->appendChild(
            $doc->createTextNode( $patient_data['street'] )
        );
        $PatientAddress->appendChild( $address1 );
        //$msg = validation(xl('Patient City'),$patient_data['city'],$msg);
        if($patient_data['city']=='')
            $dem_check.=htmlspecialchars( xl("Patient City is missing"), ENT_NOQUOTES)."<br>";
        $city = $doc->createElement( "city" );
        $city->appendChild(
            $doc->createTextNode( $patient_data['city'] )
        );
        $PatientAddress->appendChild( $city );
        if($patient_data['state']){
        $state = $doc->createElement( "state" );
        $state->appendChild(
            $doc->createTextNode( $patient_data['state'] )
        );
        $PatientAddress->appendChild( $state );
        }
        if($patient_data['postal_code']){
        $zip = $doc->createElement( "zip" );
        $zip->appendChild(
            $doc->createTextNode( $patient_data['postal_code'] )
        );
        $PatientAddress->appendChild( $zip );
        }
        //$msg = validation(xl('Patient Country'),$patient_data['country_code'],$msg);
        if(trim($patient_data['country_code'])=='' && $GLOBALS['erx_default_patient_country']=='')
            $dem_check.=htmlspecialchars( xl("Patient Country is missing. Also you have not set default Patient Country in Global Settings"), ENT_NOQUOTES)."<br>";
        elseif(trim($patient_data['country_code'])=='')
            $patient_data['country_code'] = $GLOBALS['erx_default_patient_country'];
        $county_code = substr($patient_data['country_code'],0,2);
        $country = $doc->createElement( "country" );
        $country->appendChild(
            $doc->createTextNode( $county_code )
        );
        $PatientAddress->appendChild( $country );
    $b->appendChild( $PatientAddress );
    $PatientContact = $doc->createElement( "PatientContact" );
        $patient_data['phone_home']=stripPhoneSlashes($patient_data['phone_home']);
        if($patient_data['phone_home']){
        $homeTelephone = $doc->createElement( "homeTelephone" );
        $homeTelephone->appendChild(
            $doc->createTextNode( $patient_data['phone_home'] )
        );
        $PatientContact->appendChild( $homeTelephone );
        }
    $b->appendChild( $PatientContact );
    $PatientCharacteristics = $doc->createElement( "PatientCharacteristics" );
        if(trim($patient_data['date_of_birth'])=='' || $patient_data['date_of_birth']=='00000000')
            $warning_msg .= "<br>".htmlspecialchars( xl("Patient Date Of Birth is missing"), ENT_NOQUOTES);
        if($patient_data['date_of_birth'] && $patient_data['date_of_birth']!='00000000'){        
        $dob = $doc->createElement( "dob" );
        $dob->appendChild(
            $doc->createTextNode( $patient_data['date_of_birth'] )
        );
        $PatientCharacteristics->appendChild( $dob );
        }
        if(trim($patient_data['sex'])=='')
            $warning_msg .= "<br>".htmlspecialchars( xl("Patient Gender is missing"), ENT_NOQUOTES);
        if($patient_data['sex']){
        $gender_val=substr($patient_data['sex'],0,1);
        $gender = $doc->createElement( "gender" );
        $gender->appendChild(
            $doc->createTextNode( $gender_val )
        );
        $PatientCharacteristics->appendChild( $gender );
        }
    $b->appendChild( $PatientCharacteristics );
    PatientFreeformHealthplans($doc,$b,$pid);
    $allergyId=PatientFreeformAllergy($doc,$b,$pid);    
    $r->appendChild( $b );
	return $allergyId;
}

function OutsidePrescription($doc,$r,$pid,$prescid)
{
    global $msg;
    if($prescid)
    {
        $prec=sqlQuery("SELECT p.note,p.dosage,p.substitute,p.per_refill,p.form,p.route,p.size,p.interval,p.drug,l1.title AS title1,l2.title AS title2,l3.title AS title3,l4.title AS title4,p.id AS prescid,
            DATE_FORMAT(date_added,'%Y%m%d') AS date_added,CONCAT_WS(fname,' ',mname,' ',lname) AS docname,p.quantity
            FROM prescriptions AS p
            LEFT JOIN users AS u ON p.provider_id=u.id
            LEFT JOIN list_options AS l1 ON l1.list_id='drug_form' AND l1.option_id=p.form
            LEFT JOIN list_options AS l2 ON l2.list_id='drug_route' AND l2.option_id=p.route
            LEFT JOIN list_options AS l3 ON l3.list_id='drug_interval' AND l3.option_id=p.interval
            LEFT JOIN list_options AS l4 ON l4.list_id='drug_units' AND l4.option_id=p.unit
            WHERE p.drug<>'' and p.id=?",array($prescid));
        $b = $doc->createElement( "OutsidePrescription" );
            $externalId = $doc->createElement( "externalId" );
            $externalId->appendChild(
                $doc->createTextNode( $prec['prescid'] )
            );
            $b->appendChild( $externalId );
            $date = $doc->createElement( "date" );
            $date->appendChild(
                $doc->createTextNode( $prec['date_added'] )
            );
            $b->appendChild( $date );
            $doctorName = $doc->createElement( "doctorName" );
            $doctorName->appendChild(
                $doc->createTextNode( $prec['docname'] )
            );
            $b->appendChild( $doctorName );
            $s=stripSpecialCharacter($prec['drug']);
            $sig = $doc->createElement( "drug" );
            $sig->appendChild(
                $doc->createTextNode( trimData($s,80) )
            );
            $b->appendChild( $sig );
            $x=stringToNumeric($prec['quantity']);
            $dispenseNumber = $doc->createElement( "dispenseNumber" );
            $dispenseNumber->appendChild(
                $doc->createTextNode( $x[0] )
            );
            $b->appendChild( $dispenseNumber );
            $s=trimData($x[1].$prec['size']." ".$prec['title4']." ".$prec['dosage']." In ".$prec['title1']." ".$prec['title2']." ".$prec['title3'],140);
            $s=stripSpecialCharacter($s);
            $sig = $doc->createElement( "sig" );
            $sig->appendChild(
                $doc->createTextNode( $s )
            );
            $b->appendChild( $sig );
            $refillCount = $doc->createElement( "refillCount" );
            $x=stringToNumeric($prec['per_refill']); 
            $refillCount->appendChild(
                $doc->createTextNode( $x[0])
            );
            $b->appendChild( $refillCount );
            $prescriptionType = $doc->createElement( "prescriptionType" );
            $prescriptionType->appendChild(
                $doc->createTextNode( 'reconcile' )
            );
            $b->appendChild( $prescriptionType );
        $r->appendChild( $b );
    }
}

function PatientMedication($doc,$r,$pid,$med_limit)
{
    global $msg;
    $active='';
    if($GLOBALS['erx_upload_active']==1)
        $active = " and (enddate is null or enddate = '' or enddate = '0000-00-00' )";
    $res_med=sqlStatement("select * from lists where type='medication' and pid=? and title<>'' 
	and erx_uploaded='0' $active order by enddate limit 0,$med_limit",array($pid));
	$uploaded_med_arr="";
    while($row_med=sqlFetchArray($res_med))
    {	
		$uploaded_med_arr[]=$row_med['id'];
        $b = $doc->createElement( "OutsidePrescription" );
            $externalId = $doc->createElement( "externalId" );
            $externalId->appendChild(
                $doc->createTextNode( $row_med['id'] )
            );
            $b->appendChild( $externalId );
            $date = $doc->createElement( "date" );
            $date->appendChild(
                $doc->createTextNode( $row_med['begdate'] )
            );
            $b->appendChild( $date );
            $doctorName = $doc->createElement( "doctorName" );
            $doctorName->appendChild(
                $doc->createTextNode( "" )
            );
            $b->appendChild( $doctorName );
            $row_med['title'] = stripSpecialCharacter($row_med['title']);
            $sig = $doc->createElement( "drug" );
            $sig->appendChild(
                $doc->createTextNode( trimData($row_med['title'],80) )
            );
            $b->appendChild( $sig );
            $dispenseNumber = $doc->createElement( "dispenseNumber" );
            $dispenseNumber->appendChild(
                $doc->createTextNode( $prec['quantity'] )
            );
            $b->appendChild( $dispenseNumber );
            $sig = $doc->createElement( "sig" );
            $sig->appendChild(
                $doc->createTextNode( "" )
            );
            $b->appendChild( $sig );
            $refillCount = $doc->createElement( "refillCount" );
            $refillCount->appendChild(
                $doc->createTextNode( "" )
            );
            $b->appendChild( $refillCount );
            $prescriptionType = $doc->createElement( "prescriptionType" );
            $prescriptionType->appendChild(
                $doc->createTextNode( 'reconcile' )
            );
            $b->appendChild( $prescriptionType );
        $r->appendChild( $b );
        
    }
	return $uploaded_med_arr;
}

function PatientFreeformAllergy($doc,$r,$pid)
{
    $res=sqlStatement("SELECT id,l.title as title1,lo.title as title2,comments FROM lists AS l
    LEFT JOIN list_options AS lo ON l.outcome=lo.option_id AND lo.list_id='outcome'
	WHERE `type`='allergy' AND pid=? AND erx_source='0' and erx_uploaded='0' AND (enddate is null or enddate = '' or enddate = '0000-00-00')",array($pid));
	$allergyId=array();
    while($row=sqlFetchArray($res))    
    {
        $val=array();
        $val['id']=$row['id'];
        $val['title1']=$row['title1'];
        $val['title2']=$row['title2'];
        $val['comments']=$row['comments'];
        $b = $doc->createElement( "PatientFreeformAllergy" );
        $b->setAttribute('ID',$val['id']);
            if($val['title1']){
            $allergyName = $doc->createElement( "allergyName" );
                $allergyName->appendChild(
                    $doc->createTextNode( trimData(stripSpecialCharacter($val['title1']),70) )
                );
            $b->appendChild( $allergyName );
            }
            if($val['title2'] && ($val['title2']=='Mild' || $val['title2']=='Moderate' || $val['title2']=='Severe')){
            $allergySeverityTypeID = $doc->createElement( "allergySeverityTypeID" );
                $allergySeverityTypeID->appendChild(
                    $doc->createTextNode( $val['title2'] )
                );
            $b->appendChild( $allergySeverityTypeID );
            }
            if($val['comments']){
            $allergyComment = $doc->createElement( "allergyComment" );
                $allergyComment->appendChild(
                    $doc->createTextNode( trimData(stripSpecialCharacter($val['comments']),200) )
                );
            $b->appendChild( $allergyComment );
            }
        $r->appendChild( $b );
		$allergyId[]=$row['id'];
    }
	return $allergyId;
}

function PatientFreeformHealthplans($doc, $r, $pid) {
    $resource = sqlStatement('SELECT
            `ins`.`name`
        FROM (
            SELECT
                `id`.`type`,
                `ic`.`name`
            FROM `insurance_data` AS `id`
                LEFT JOIN `insurance_companies` AS `ic` ON `ic`.`id` = `id`.`provider`
            WHERE `id`.`pid` = ?
                AND `id`.`subscriber_relationship` = \'self\'
                AND `id`.`provider` > 0
            ORDER BY `id`.`date` DESC
        ) AS `ins`
        GROUP BY `ins`.`type`;',
        array($pid)
    );

    while($row = sqlFetchArray($resource)) {
        $healthplanName = $doc->createElement('healthplanName');
        $healthplanName->appendChild($doc->createTextNode(
            stripSpecialCharacter(trimData($row['name'], 35))
        ));

        $patientFreeformHealthplans = $doc->createElement('PatientFreeformHealthplans');
        $patientFreeformHealthplans->appendChild($healthplanName);

        $r->appendChild($patientFreeformHealthplans);
     }
 }

function PrescriptionRenewalResponse($doc,$r,$pid)
{
    $b = $doc->createElement( "PrescriptionRenewalResponse" );
        $renewalRequestIdentifier = $doc->createElement( "renewalRequestIdentifier" );
            $renewalRequestIdentifier->appendChild(
                $doc->createTextNode( 'cbf51649-ce3c-44b8-8f91-6fda121a353d' )
            );
        $b->appendChild( $renewalRequestIdentifier );
        $responseCode = $doc->createElement( "responseCode" );
            $responseCode->appendChild(
                $doc->createTextNode( 'Undetermined' )
            );
        $b->appendChild( $responseCode );
    $r->appendChild( $b );
}

function checkError($xml)
{    
    $ch = curl_init($xml);
    
    $data = array('RxInput' => $xml);
    
    curl_setopt($ch, CURLOPT_URL, getErxPath());
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "RxInput=".$xml);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE); 
    //curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookiefile"); 
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile");
    curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
    curl_setopt($ch, CURLOPT_USERAGENT,"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $result=curl_exec($ch)  or die( curl_error($ch)) ;
    preg_match('/<textarea.*>(.*)Original XML:/is',$result,$error_message);
    if(strpos($result,'RxEntry.aspx')){
    erx_error_log($xml);
    erx_error_log($result);
    }
    $arr=split('Error',$error_message[1]);
    //echo "Te: ".count($arr);
    //print_r($arr);
    if(count($arr)==1)
    {
        echo nl2br($error_message[1]);
    }
    else
    {
        for($i=1;$i<count($arr);$i++)
        {
            echo $arr[$i]."<br><br>";
        }
    }
    curl_close($ch);
    if(strpos($result,'RxEntry.aspx'))
        return '1';
    else
        return '0';
}

function erx_error_log($message)
{
    $date = date("Y-m-d");
    if(!is_dir($GLOBALS['OE_SITE_DIR'].'/documents/erx_error'))
    mkdir($GLOBALS['OE_SITE_DIR'].'/documents/erx_error',0777,true);
    $filename = $GLOBALS['OE_SITE_DIR']."/documents/erx_error/erx_error"."-".$date.".log";
    $f=fopen($filename,'a');
    fwrite($f,date("Y-m-d H:i:s")." ==========> ".$message."\r\n");
    fclose($f);
}

function stripStrings($str,$pattern)
{
    $result = $str;
    foreach($pattern as $key=>$value){
        $result = preg_replace("/$key/",$value,$result);
    }
    return $result;
}
?>