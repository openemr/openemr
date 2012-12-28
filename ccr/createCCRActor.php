<?php
/**
 * CCR Script.
 *
 * Copyright (C) 2010 Garden State Health Systems <http://www.gshsys.com/>
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
 * @author  Garden State Health Systems <http://www.gshsys.com/>
 * @link    http://www.open-emr.org
 */


$result = getActorData();
while ($row = sqlFetchArray($result[0])) {

	$e_Actor = $ccr->createElement('Actor');
	$e_Actors->appendChild($e_Actor);

	$e_ActorObjectID = $ccr->createElement('ActorObjectID', 'A1234'); // Refer createCCRHeader.php
	$e_Actor->appendChild($e_ActorObjectID);
	
	$e_Person = $ccr->createElement('Person');
	$e_Actor->appendChild($e_Person);

	$e_Name = $ccr->createElement('Name');
	$e_Person->appendChild($e_Name);
	
	$e_CurrentName = $ccr->createElement('CurrentName');
	$e_Name->appendChild($e_CurrentName);

	$e_Given = $ccr->createElement('Given',$row['fname']);
	$e_CurrentName->appendChild($e_Given);

	$e_Family = $ccr->createElement('Family',$row['lname']);
	$e_CurrentName->appendChild($e_Family);

	$e_Suffix = $ccr->createElement('Suffix');
	$e_CurrentName->appendChild($e_Suffix);

	$e_DateOfBirth = $ccr->createElement('DateOfBirth');
	$e_Person->appendChild($e_DateOfBirth);
	
	$dob = date_create($row['DOB']);
	
	$e_ExactDateTime = $ccr->createElement('ExactDateTime',$dob->format('Y-m-d\TH:i:s\Z'));
	$e_DateOfBirth->appendChild($e_ExactDateTime);
	
	$e_Gender = $ccr->createElement('Gender');
	$e_Person->appendChild($e_Gender);
	
	$e_Text = $ccr->createElement('Text',$row['sex']);
	$e_Gender->appendChild($e_Text);
	
	$e_Code = $ccr->createElement('Code');
	$e_Gender->appendChild($e_Code);
	
	$e_Value = $ccr->createElement('Value');
	$e_Code->appendChild($e_Value);

	$e_IDs = $ccr->createElement('IDs');
	$e_Actor->appendChild($e_IDs);
	
	$e_Type = $ccr->createElement('Type');
	$e_IDs->appendChild($e_Type);

	$e_Text = $ccr->createElement('Text', 'Patient ID');
	$e_Type->appendChild($e_Text);
	
	$e_ID = $ccr->createElement('ID', $row['pid']);
	$e_IDs->appendChild($e_ID);

	$e_Source = $ccr->createElement('Source');
	$e_IDs->appendChild($e_Source);

	$e_SourceActor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_SourceActor);

	$e_ActorID = $ccr->createElement('ActorID', getUuid());
	$e_SourceActor->appendChild($e_ActorID);
	
	// address
	$e_Address = $ccr->createElement('Address');
	$e_Actor->appendChild($e_Address);
    
  $e_Type = $ccr->createElement('Type');
  $e_Address->appendChild($e_Type);
  
  $e_Text = $ccr->createElement('Text', 'H');
	$e_Type->appendChild($e_Text);
	
	$e_Line1 = $ccr->createElement('Line1', $row['street']);
	$e_Address->appendChild($e_Line1);
	
	$e_Line2 = $ccr->createElement('Line2');
	$e_Address->appendChild($e_Line1);

	$e_City = $ccr->createElement('City', $row['city']);
	$e_Address->appendChild($e_City);

	$e_State = $ccr->createElement('State', $row['state']);
	$e_Address->appendChild($e_State);
	
	$e_PostalCode = $ccr->createElement('PostalCode', $row['postal_code']);
	$e_Address->appendChild($e_PostalCode);

	$e_Telephone = $ccr->createElement('Telephone');
	$e_Actor->appendChild($e_Telephone);
	
	$e_Value = $ccr->createElement('Value', $row['phone_contact']);
	$e_Telephone->appendChild($e_Value);

  $e_Source = $ccr->createElement('Source');
	$e_Actor->appendChild($e_Source);

	$e_Actor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_Actor);

	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_Actor->appendChild($e_ActorID);

}

$row1 = sqlFetchArray($result[1]);
	//////// Actor Information Systems
	
	$e_Actor = $ccr->createElement('Actor');
	$e_Actors->appendChild($e_Actor);

	$e_ActorObjectID = $ccr->createElement('ActorObjectID', $authorID);
	$e_Actor->appendChild($e_ActorObjectID);

	$e_InformationSystem = $ccr->createElement('InformationSystem');
	$e_Actor->appendChild($e_InformationSystem);

	$e_Name = $ccr->createElement('Name', $row1['facility']);
	$e_InformationSystem->appendChild($e_Name);
  
  $e_Type = $ccr->createElement('Type', 'Facility');
	$e_InformationSystem->appendChild($e_Type);
  
  $e_IDs = $ccr->createElement('IDs');
	$e_Actor->appendChild($e_IDs);
  
  $e_Type = $ccr->createElement('Type');
  $e_IDs->appendChild($e_Type);
  
  $e_Text = $ccr->createElement('Text', '');
	$e_Type->appendChild($e_Text);
  
  $e_ID = $ccr->createElement('ID', '');
	$e_IDs->appendChild($e_ID);
  
  $e_Source = $ccr->createElement('Source');
	$e_IDs->appendChild($e_Source);

	$e_SourceActor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_SourceActor);
  
	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_SourceActor->appendChild($e_ActorID);
  
  $e_Address = $ccr->createElement('Address');
	$e_Actor->appendChild($e_Address);
  
  $e_Type = $ccr->createElement('Type');
  $e_Address->appendChild($e_Type);
  
  $e_Text = $ccr->createElement('Text', 'WP');
	$e_Type->appendChild($e_Text);
	
	$e_Line1 = $ccr->createElement('Line1',$row1['street']);
	$e_Address->appendChild($e_Line1);
	
	$e_Line2 = $ccr->createElement('Line2');
	$e_Address->appendChild($e_Line1);

	$e_City = $ccr->createElement('City',$row1['city']);
	$e_Address->appendChild($e_City);

	$e_State = $ccr->createElement('State',$row1['state'].' ');
	$e_Address->appendChild($e_State);
	
	$e_PostalCode = $ccr->createElement('PostalCode',$row1['postal_code']);
	$e_Address->appendChild($e_PostalCode);
  
  $e_Telephone = $ccr->createElement('Telephone');
	$e_Actor->appendChild($e_Telephone);
  
  $e_Phone = $ccr->createElement('Value',$row1['phone']);
	$e_Telephone->appendChild($e_Phone);
	
	$e_Source = $ccr->createElement('Source');
	$e_Actor->appendChild($e_Source);

	$e_Actor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_Actor);

	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_Actor->appendChild($e_ActorID);
  
  //////// Actor Information Systems
  $e_Actor = $ccr->createElement('Actor');
	$e_Actors->appendChild($e_Actor);

	$e_ActorObjectID = $ccr->createElement('ActorObjectID', $oemrID);
	$e_Actor->appendChild($e_ActorObjectID);

	$e_InformationSystem = $ccr->createElement('InformationSystem');
	$e_Actor->appendChild($e_InformationSystem);

	$e_Name = $ccr->createElement('Name', 'OEMR');
	$e_InformationSystem->appendChild($e_Name);
  
  $e_Type = $ccr->createElement('Type', 'OpenEMR');
	$e_InformationSystem->appendChild($e_Type);
  
  $e_Version = $ccr->createElement('Version', '4.x');
	$e_InformationSystem->appendChild($e_Version);
  
  $e_IDs = $ccr->createElement('IDs');
	$e_Actor->appendChild($e_IDs);
  
  $e_Type = $ccr->createElement('Type');
  $e_IDs->appendChild($e_Type);
  
  $e_Text = $ccr->createElement('Text', 'Certification #');
	$e_Type->appendChild($e_Text);
  
  $e_ID = $ccr->createElement('ID', 'EHRX-OEMRXXXXXX-2011');
	$e_IDs->appendChild($e_ID);
  
  $e_Source = $ccr->createElement('Source');
	$e_IDs->appendChild($e_Source);

	$e_SourceActor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_SourceActor);
  
	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_SourceActor->appendChild($e_ActorID);
  
  $e_Address = $ccr->createElement('Address');
	$e_Actor->appendChild($e_Address);
    
  $e_Type = $ccr->createElement('Type');
  $e_Address->appendChild($e_Type);
  
  $e_Text = $ccr->createElement('Text', 'WP');
	$e_Type->appendChild($e_Text);
	
	$e_Line1 = $ccr->createElement('Line1','2365 Springs Rd. NE');
	$e_Address->appendChild($e_Line1);
	
	$e_Line2 = $ccr->createElement('Line2');
	$e_Address->appendChild($e_Line1);

	$e_City = $ccr->createElement('City','Hickory');
	$e_Address->appendChild($e_City);

	$e_State = $ccr->createElement('State','NC ');
	$e_Address->appendChild($e_State);
	
	$e_PostalCode = $ccr->createElement('PostalCode','28601');
	$e_Address->appendChild($e_PostalCode);
  
  $e_Telephone = $ccr->createElement('Telephone');
	$e_Actor->appendChild($e_Telephone);
  
  $e_Phone = $ccr->createElement('Value','000-000-0000');
	$e_Telephone->appendChild($e_Phone);
  
	$e_Source = $ccr->createElement('Source');
	$e_Actor->appendChild($e_Source);

	$e_Actor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_Actor);

	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_Actor->appendChild($e_ActorID);
  
while ($row2 = sqlFetchArray($result[2])) {

	$e_Actor = $ccr->createElement('Actor');
	$e_Actors->appendChild($e_Actor);

	$e_ActorObjectID = $ccr->createElement('ActorObjectID', ${"labID{$row2['id']}"});
	$e_Actor->appendChild($e_ActorObjectID);
	
	$e_InformationSystem = $ccr->createElement('InformationSystem');
	$e_Actor->appendChild($e_InformationSystem);

	$e_Name = $ccr->createElement('Name', $row2['lname']." ".$row2['fname']);
	$e_InformationSystem->appendChild($e_Name);
  
  $e_Type = $ccr->createElement('Type', 'Lab Service');
	$e_InformationSystem->appendChild($e_Type);
  
  $e_IDs = $ccr->createElement('IDs');
	$e_Actor->appendChild($e_IDs);
  
  $e_Type = $ccr->createElement('Type');
  $e_IDs->appendChild($e_Type);
  
  $e_Text = $ccr->createElement('Text', '');
	$e_Type->appendChild($e_Text);
  
  $e_ID = $ccr->createElement('ID', '');
	$e_IDs->appendChild($e_ID);
  
  $e_Source = $ccr->createElement('Source');
	$e_IDs->appendChild($e_Source);

	$e_SourceActor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_SourceActor);
  
	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_SourceActor->appendChild($e_ActorID);
  
  $e_Address = $ccr->createElement('Address');
	$e_Actor->appendChild($e_Address);
    
  $e_Type = $ccr->createElement('Type');
  $e_Address->appendChild($e_Type);
  
  $e_Text = $ccr->createElement('Text', 'WP');
	$e_Type->appendChild($e_Text);
	
	$e_Line1 = $ccr->createElement('Line1',$row2['street']);
	$e_Address->appendChild($e_Line1);
	
	$e_Line2 = $ccr->createElement('Line2');
	$e_Address->appendChild($e_Line1);

	$e_City = $ccr->createElement('City',$row2['city']);
	$e_Address->appendChild($e_City);

	$e_State = $ccr->createElement('State',$row2['state'].' ');
	$e_Address->appendChild($e_State);
	
	$e_PostalCode = $ccr->createElement('PostalCode',$row2['zip']);
	$e_Address->appendChild($e_PostalCode);
  
  $e_Telephone = $ccr->createElement('Telephone');
	$e_Actor->appendChild($e_Telephone);
  
  $e_Phone = $ccr->createElement('Value',$row2['phone']);
	$e_Telephone->appendChild($e_Phone);
	
	$e_Source = $ccr->createElement('Source');
	$e_Actor->appendChild($e_Source);

	$e_Actor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_Actor);

	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_Actor->appendChild($e_ActorID);
}

?>
