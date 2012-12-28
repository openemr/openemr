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


	$result = getImmunizationData();
	$row = sqlFetchArray($result);

	do {

		$e_Immunization = $ccr->createElement('Immunization');
		$e_Immunizations->appendChild($e_Immunization);

		$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
		$e_Immunization->appendChild($e_CCRDataObjectID);

		$e_DateTime = $ccr->createElement('DateTime');
		$e_Immunization->appendChild($e_DateTime);
		
		$date = date_create($row['administered_date']);
		
		$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
		$e_DateTime->appendChild($e_ExactDateTime);

		$e_Type = $ccr->createElement('Type');
		$e_Immunization->appendChild($e_Type);

		$e_Text = $ccr->createElement('Text', 'Immunization');
		$e_Type->appendChild($e_Text);

		$e_Status = $ccr->createElement('Status');
		$e_Immunization->appendChild($e_Status);

		$e_Text = $ccr->createElement('Text','ACTIVE');
		$e_Status->appendChild($e_Text);
		
		$e_Immunization->appendChild(sourceType($ccr, $sourceID));

		$e_Product = $ccr->createElement('Product');
		$e_Immunization->appendChild($e_Product);

		$e_ProductName = $ccr->createElement('ProductName');
		$e_Product->appendChild($e_ProductName);

		$e_Text = $ccr->createElement('Text',$row['title']);
		$e_ProductName->appendChild( $e_Text);

		$e_Directions = $ccr->createElement('Directions');
		$e_Immunization->appendChild($e_Directions);

		$e_Direction = $ccr->createElement('Direction');
		$e_Directions->appendChild($e_Direction);

		$e_Description = $ccr->createElement('Description');
		$e_Direction->appendChild($e_Description);

		$e_Text = $ccr->createElement('Text',$row['note']);
		$e_Description->appendChild($e_Text);
		
		$e_Code = $ccr->createElement('Code');
		$e_Description->appendChild($e_Code);
		
		$e_Value = $ccr->createElement('Value', 'None');
		$e_Code->appendChild($e_Value);

	} while ($row = sqlFetchArray($result));

?>
