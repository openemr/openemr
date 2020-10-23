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

$result = getAlertData();
$row = sqlFetchArray($result);

do {
//while ($row = sqlFetchArray($result)) {

    $e_Alert = $ccr->createElement('Alert');
    $e_Alerts->appendChild($e_Alert);

    $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
    $e_Alert->appendChild($e_CCRDataObjectID);

    $e_DateTime = $ccr->createElement('DateTime');
    $e_Alert->appendChild($e_DateTime);

    $date = date_create($row['date']);

    $e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
    $e_DateTime->appendChild($e_ExactDateTime);

    $e_IDs = $ccr->createElement('IDs');
    $e_Alert->appendChild($e_IDs);

    $e_ID = $ccr->createElement('ID', $row['pid']);
    $e_IDs->appendChild($e_ID);

    $e_IDs->appendChild(sourceType($ccr, $sourceID));

    $e_Type = $ccr->createElement('Type');
    $e_Alert->appendChild($e_Type);

    $e_Text = $ccr->createElement('Text', $row['type'] . '-' . $row['alert_title']);
    $e_Type->appendChild($e_Text);

    $e_Description = $ccr->createElement('Description');
    $e_Alert->appendChild($e_Description);

    $e_Text = $ccr->createElement('Text', $row['code_text']);
    $e_Description->appendChild($e_Text);

    $e_Code = $ccr->createElement('Code');
    $e_Description->appendChild($e_Code);

    $e_Value = $ccr->createElement('Value', $row['diagnosis']);
    $e_Code->appendChild($e_Value);

    $e_Alert->appendChild(sourceType($ccr, $sourceID));

    $e_Agent = $ccr->createElement('Agent');
    $e_Alert->appendChild($e_Agent);

    $e_EnvironmentalAgents = $ccr->createElement('EnvironmentalAgents');
    $e_Agent->appendChild($e_EnvironmentalAgents);

    $e_EnvironmentalAgent = $ccr->createElement('EnvironmentalAgent');
    $e_EnvironmentalAgents->appendChild($e_EnvironmentalAgent);

    $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', getUuid());
    $e_EnvironmentalAgent->appendChild($e_CCRDataObjectID);

    $e_DateTime = $ccr->createElement('DateTime');
    $e_EnvironmentalAgent->appendChild($e_DateTime);

    $e_ExactDateTime = $ccr->createElement('ExactDateTime', $row['date']);
    $e_DateTime->appendChild($e_ExactDateTime);

    $e_Description = $ccr->createElement('Description');
    $e_EnvironmentalAgent->appendChild($e_Description);

    $e_Text = $ccr->createElement('Text', $row['alert_title']);
    $e_Description->appendChild($e_Text);

    $e_Code = $ccr->createElement('Code');
    $e_Description->appendChild($e_Code);

    $e_Value = $ccr->createElement('Value');//,$row['codetext']
    $e_Code->appendChild($e_Value);

    $e_Status = $ccr->createElement('Status');
    $e_EnvironmentalAgent->appendChild($e_Status);

    $e_Text = $ccr->createElement('Text', $row['outcome']);
    $e_Status->appendChild($e_Text);

    $e_EnvironmentalAgent->appendChild(sourceType($ccr, $sourceID));

    $e_Reaction = $ccr->createElement('Reaction');
    $e_Alert->appendChild($e_Reaction);

    $e_Description = $ccr->createElement('Description');
    $e_Reaction->appendChild($e_Description);

    $e_Text = $ccr->createElement('Text', $row['reaction']);
    $e_Description->appendChild($e_Text);

    $e_Status = $ccr->createElement('Status');
    $e_Reaction->appendChild($e_Status);

    $e_Text = $ccr->createElement('Text', 'None');
    $e_Status->appendChild($e_Text);
} while ($row = sqlFetchArray($result));
    //}
