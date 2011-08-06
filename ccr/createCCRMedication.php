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


	$result = getMedicationData();
	$value = sqlFetchArray($result);

do {
	
	$e_Medication = $ccr->createElement('Medication');
	$e_Medications->appendChild($e_Medication);

	$e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
	$e_Medication->appendChild($e_CCRDataObjectID);

	$e_DateTime = $ccr->createElement('DateTime');
	$e_Medication->appendChild($e_DateTime);
	
	$date = date_create($value['date_added']);
	
	$e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
	$e_DateTime->appendChild($e_ExactDateTime);

	$e_Type = $ccr->createElement('Type');
	$e_Medication->appendChild($e_Type);

	// $e_Text = $ccr->createElement('Text', $value['medication']);
	$e_Text = $ccr->createElement('Text', 'Medication');
	$e_Type->appendChild($e_Text);

	$e_Status = $ccr->createElement('Status');
	$e_Medication->appendChild($e_Status);

	// $e_Text = $ccr->createElement('Text',$value['active']);
	$e_Text = $ccr->createElement('Text', 'Active');
	$e_Status->appendChild($e_Text);

	$e_Medication->appendChild(sourceType($ccr, $sourceID));

	$e_Product = $ccr->createElement('Product');
	$e_Medication->appendChild($e_Product);

	$e_ProductName = $ccr->createElement('ProductName');
	$e_Product->appendChild($e_ProductName);

	$e_Text = $ccr->createElement('Text',$value['drug']);
	$e_ProductName->appendChild(clone $e_Text);

	$e_Strength = $ccr->createElement('Strength');
	$e_Product->appendChild($e_Strength);

	$e_Value = $ccr->createElement('Value', $value['size']);
	$e_Strength->appendChild($e_Value);

	$e_Units = $ccr->createElement('Units');
	$e_Strength->appendChild($e_Units);

	$e_Unit = $ccr->createElement('Unit', $value['title']);
	$e_Units->appendChild($e_Unit);
  
  $e_Form = $ccr->createElement('Form');
	$e_Product->appendChild($e_Form);

	$e_Text = $ccr->createElement('Text', 'Tablets');
	$e_Form->appendChild($e_Text);

	$e_Quantity = $ccr->createElement('Quantity');
	$e_Medication->appendChild($e_Quantity);

	$e_Value = $ccr->createElement('Value', $value['quantity']);
	$e_Quantity->appendChild($e_Value);

	$e_Units = $ccr->createElement('Units');
	$e_Quantity->appendChild($e_Units);

	$e_Unit = $ccr->createElement('Unit', 'Tablets');
	$e_Units->appendChild($e_Unit);

	$e_Directions = $ccr->createElement('Directions');
	$e_Medication->appendChild($e_Directions);

	$e_Direction = $ccr->createElement('Direction');
	$e_Directions->appendChild($e_Direction);

	$e_Description = $ccr->createElement('Description', $value['note']);
	$e_Direction->appendChild($e_Description);

	// $e_Text = $ccr->createElement('Text',$value['note']);
	$e_Text = $ccr->createElement('Text', 'Note');
	$e_Description->appendChild(clone $e_Text);

	$e_Route = $ccr->createElement('Route');
	$e_Direction->appendChild($e_Route);

	$e_Text = $ccr->createElement('Text', 'Tablet');
	$e_Route->appendChild($e_Text);
	
	$e_Site = $ccr->createElement('Site');
	$e_Direction->appendChild($e_Site);

	$e_Text = $ccr->createElement('Text', 'Oral');
	$e_Site->appendChild($e_Text);
	
	//$e_Indications = $ccr->createElement('Indications');
	//$e_Medication->appendChild($e_Indications);
	//
	//$e_Indication = $ccr->createElement('Indication');
	//$e_Indications->appendChild($e_Indication);

	// $e_Indication->appendChild(sourceType($ccr, $authorID));

	//$e_InternalCCRLink = $ccr->createElement('InternalCCRLink');
	//$e_Indication->appendChild($e_InternalCCRLink);
	//
	//$e_LinkID = $ccr->createElement('LinkID', 'PROB1');
	//$e_InternalCCRLink->appendChild($e_LinkID);

	$e_PatientInstructions = $ccr->createElement('PatientInstructions');
	$e_Medication->appendChild($e_PatientInstructions);

	$e_Instruction = $ccr->createElement('Instruction');
	$e_PatientInstructions->appendChild($e_Instruction);

	$e_Text = $ccr->createElement('Text', 'Patient Instructions');
	$e_Instruction->appendChild($e_Text);

	$e_Refills = $ccr->createElement('Refills');
	$e_Medication->appendChild($e_Refills);

	$e_Refill = $ccr->createElement('Refill');
	$e_Refills->appendChild($e_Refill);

	$e_Number = $ccr->createElement('Number', 4);
	$e_Refill->appendChild($e_Number);

} while ($value = sqlFetchArray($result));

?>
