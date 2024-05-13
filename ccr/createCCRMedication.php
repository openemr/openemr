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

    $result = getMedicationData();
    $value = sqlFetchArray($result);

do {
    $e_Medication = $ccr->createElement('Medication');
    $e_Medications->appendChild($e_Medication);

    $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
    $e_Medication->appendChild($e_CCRDataObjectID);

    $e_DateTime = $ccr->createElement('DateTime');
    $e_Medication->appendChild($e_DateTime);

    $date = date_create($value['date_added'] ?? '');

    $e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
    $e_DateTime->appendChild($e_ExactDateTime);

    $e_Type = $ccr->createElement('Type');
    $e_Medication->appendChild($e_Type);

    $e_Text = $ccr->createElement('Text', 'Medication');
    $e_Type->appendChild($e_Text);

    $e_Status = $ccr->createElement('Status');
    $e_Medication->appendChild($e_Status);

    $e_Text = $ccr->createElement('Text', $value['active'] ?? '');
    $e_Status->appendChild($e_Text);

    $e_Medication->appendChild(sourceType($ccr, $sourceID));

    $e_Product = $ccr->createElement('Product');
    $e_Medication->appendChild($e_Product);

    $e_ProductName = $ccr->createElement('ProductName');
    $e_Product->appendChild($e_ProductName);

    $e_Text = $ccr->createElement('Text', $value['drug'] ?? '');
    $e_ProductName->appendChild(clone $e_Text);

    $e_Code = $ccr->createElement('Code');
    $e_ProductName->appendChild($e_Code);

    $e_Value = $ccr->createElement('Value', $value['rxnorm_drugcode'] ?? '');
    $e_Code->appendChild($e_Value);

    $e_Value = $ccr->createElement('CodingSystem', 'RxNorm');
    $e_Code->appendChild($e_Value);

    $e_Strength = $ccr->createElement('Strength');
    $e_Product->appendChild($e_Strength);

    $e_Value = $ccr->createElement('Value', $value['size'] ?? '');
    $e_Strength->appendChild($e_Value);

    $e_Units = $ccr->createElement('Units');
    $e_Strength->appendChild($e_Units);

    $e_Unit = $ccr->createElement('Unit', $value['title'] ?? '');
    $e_Units->appendChild($e_Unit);

    $e_Form = $ccr->createElement('Form');
    $e_Product->appendChild($e_Form);

    $e_Text = $ccr->createElement('Text', $value['form'] ?? '');
    $e_Form->appendChild($e_Text);

    $e_Quantity = $ccr->createElement('Quantity');
    $e_Medication->appendChild($e_Quantity);

    $e_Value = $ccr->createElement('Value', $value['quantity'] ?? '');
    $e_Quantity->appendChild($e_Value);

    $e_Units = $ccr->createElement('Units');
    $e_Quantity->appendChild($e_Units);

    $e_Unit = $ccr->createElement('Unit', '');
    $e_Units->appendChild($e_Unit);

    $e_Directions = $ccr->createElement('Directions');
    $e_Medication->appendChild($e_Directions);

    $e_Direction = $ccr->createElement('Direction');
    $e_Directions->appendChild($e_Direction);

    $e_Description = $ccr->createElement('Description');
    $e_Direction->appendChild($e_Description);

    $e_Text = $ccr->createElement('Text', '');
    $e_Description->appendChild(clone $e_Text);

    $e_Route = $ccr->createElement('Route');
    $e_Direction->appendChild($e_Route);

    $e_Text = $ccr->createElement('Text', 'Tablet');
    $e_Route->appendChild($e_Text);

    $e_Site = $ccr->createElement('Site');
    $e_Direction->appendChild($e_Site);

    $e_Text = $ccr->createElement('Text', 'Oral');
    $e_Site->appendChild($e_Text);

    $e_PatientInstructions = $ccr->createElement('PatientInstructions');
    $e_Medication->appendChild($e_PatientInstructions);

    $e_Instruction = $ccr->createElement('Instruction');
    $e_PatientInstructions->appendChild($e_Instruction);

    $e_Text = $ccr->createElement('Text', $value['note'] ?? '');
    $e_Instruction->appendChild($e_Text);

    $e_Refills = $ccr->createElement('Refills');
    $e_Medication->appendChild($e_Refills);

    $e_Refill = $ccr->createElement('Refill');
    $e_Refills->appendChild($e_Refill);

    $e_Number = $ccr->createElement('Number', $value['refills'] ?? '');
    $e_Refill->appendChild($e_Number);
} while ($value = sqlFetchArray($result));
