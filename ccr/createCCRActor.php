<?php
//  ------------------------------------------------------------------------ //
//                     Garden State Health Systems                           //
//                    Copyright (c) 2010 gshsys.com                          //
//                      <http://www.gshsys.com/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

//require_once(dirname(__FILE__) . "/../library/sql-ccr.inc");
//require_once("uuid.php");


$result = getActorData();
while ($row = sqlFetchArray($result)) {

	$e_Actor = $ccr->createElement('Actor');
	$e_Actors->appendChild($e_Actor);

	$e_ActorObjectID = $ccr->createElement('ActorObjectID', $row['pid']);
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
	
	$e_ExactDateTime = $ccr->createElement('ExactDateTime',$row['DOB']);
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






	//////// Actor Information Systems
	
	$e_Actor = $ccr->createElement('Actor');
	$e_Actors->appendChild($e_Actor);

	$e_ActorObjectID = $ccr->createElement('ActorObjectID', $authorID);
	$e_Actor->appendChild($e_ActorObjectID);

	$e_InformationSystem = $ccr->createElement('InformationSystem');
	$e_Actor->appendChild($e_InformationSystem);

	$e_Name = $ccr->createElement('Name', ' Garden Health System v1.0');
	$e_InformationSystem->appendChild($e_Name);
	
	$e_Source = $ccr->createElement('Source');
	$e_IDs->appendChild($e_Source);

	$e_Actor = $ccr->createElement('Actor');
	$e_Source->appendChild($e_Actor);

	$e_ActorID = $ccr->createElement('ActorID', $authorID);
	$e_Actor->appendChild($e_ActorID);

	}

?>

