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

	$result = getProcedureData();
	$row = sqlFetchArray($result);

	do {

		echo 'encounter :'.$row['encounter'].'\n';

		$e_Procedure = $ccr->createElement('Procedure');
		$e_Procedures->appendChild($e_Procedure);

		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
		$e_Procedure->appendChild($e_CCRDataObjectID);

		$e_DateTime = $ccr->createElement('DateTime');
		$e_Procedure->appendChild($e_DateTime);
		
		$date = date_create($row['date']);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
		$e_DateTime->appendChild($e_ExactDateTime);

		$e_Type = $ccr->createElement('Type');
		$e_Procedure->appendChild($e_Type);

		$e_Text = $ccr->createElement('Text', $row['type']);
		$e_Type->appendChild($e_Text);

		
		$e_Description = $ccr->createElement('Description' );
		$e_Procedure->appendChild($e_Description);

		$e_Text->nodeValue = $row['proc_title'];
		$e_Description->appendChild(clone $e_Text);

		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', 'None');
		$e_Code->appendChild($e_Value);

		$e_Status = $ccr->createElement('Status');
		$e_Procedure->appendChild($e_Status);

		$e_Text->nodeValue = $row['outcome'];
		$e_Status->appendChild(clone $e_Text);
		
		$e_Procedure->appendChild(sourceType($ccr, $authorID));

		$e_Locations = $ccr->createElement('Locations');
		$e_Procedure->appendChild($e_Locations);

		$e_Location = $ccr->createElement('Location');
		$e_Locations->appendChild($e_Location);

		$e_Description = $ccr->createElement('Description' );
		$e_Location->appendChild($e_Description);

		$e_Text->nodeValue = 'body_location'; //$row['laterality'];
		$e_Description->appendChild(clone $e_Text);
		
		$e_Practitioners = $ccr->createElement('Practitioners');
		$e_Procedure->appendChild($e_Practitioners);

		$e_Practitioner = $ccr->createElement('Practitioner');
		$e_Practitioners->appendChild($e_Practitioner);
		
		$e_ActorRole = $ccr->createElement('ActorRole');
		$e_Practitioner->appendChild($e_ActorRole);
		
		$e_Text->nodeValue = 'None';
		$e_ActorRole->appendChild(clone $e_Text);

		$e_Duration = $ccr->createElement('Duration');
		$e_Procedure->appendChild($e_Duration);

		$e_Description = $ccr->createElement('Description' );
		$e_Duration->appendChild($e_Description);

		$e_Text->nodeValue = 'None';
		$e_Description->appendChild(clone $e_Text);

		$e_Substance = $ccr->createElement('Substance');
		$e_Procedure->appendChild($e_Substance);

		$e_Text->nodeValue = 'substance';
		$e_Substance->appendChild(clone $e_Text);
			
		$e_Method = $ccr->createElement('Method');
		$e_Procedure->appendChild($e_Method);

		$e_Text->nodeValue = 'method'; //?
		$e_Method->appendChild(clone $e_Text);
		
		$e_Position = $ccr->createElement('Position'); //$row['laterality']
		$e_Procedure->appendChild($e_Position);

		$e_Text->nodeValue = 'body_position';// $row['laterality'];
		$e_Position->appendChild(clone $e_Text);

		$e_Site = $ccr->createElement('Site');
		$e_Procedure->appendChild($e_Site);

		$e_Text->nodeValue = 'body_site';//$row['body_site'];
		$e_Site->appendChild(clone $e_Text);
		
	} while ($row = sqlFetchArray($result));

?>
