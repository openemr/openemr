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

    $result = getProblemData();
    $row = sqlFetchArray($result);
    $pCount = 0;
    //while ($row = sqlFetchArray($result)) {

do {
    $pCount++;

    $e_Problem = $ccr->createElement('Problem');
    $e_Problems->appendChild($e_Problem);

    $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'PROB' . $pCount);
    $e_Problem->appendChild($e_CCRDataObjectID);

    $e_DateTime = $ccr->createElement('DateTime');
    $e_Problem->appendChild($e_DateTime);

    $date = date_create($row['date'] ?? '');

    $e_ExactDateTime = $ccr->createElement('ExactDateTime', $date->format('Y-m-d\TH:i:s\Z'));
    $e_DateTime->appendChild($e_ExactDateTime);

    $e_IDs = $ccr->createElement('IDs');
    $e_Problem->appendChild($e_IDs);

    $e_ID = $ccr->createElement('ID', $row['pid'] ?? '');
    $e_IDs->appendChild($e_ID);

    $e_IDs->appendChild(sourceType($ccr, $sourceID));

    $e_Type = $ccr->createElement('Type');
    $e_Problem->appendChild($e_Type);

    $e_Text = $ccr->createElement('Text', 'Problem'); // Changed to pass through validator, Problem type must be one of the required string values: Problem, Condition, Diagnosis, Symptom, Finding, Complaint, Functional Limitation.
    //$e_Text = $ccr->createElement('Text', $row['prob_title']);
    $e_Type->appendChild($e_Text);

    $e_Description = $ccr->createElement('Description');
    $e_Problem->appendChild($e_Description);

    $e_Text = $ccr->createElement('Text', lookup_code_descriptions($row['diagnosis'] ?? ''));
    $e_Description->appendChild($e_Text);

    $e_Code = $ccr->createElement('Code');
    $e_Description->appendChild($e_Code);

    $e_Value = $ccr->createElement('Value', $row['diagnosis'] ?? '');
    $e_Code->appendChild($e_Value);

    $e_Value = $ccr->createElement('CodingSystem', 'ICD9-CM');
    $e_Code->appendChild($e_Value);

    $e_Status = $ccr->createElement('Status');
    $e_Problem->appendChild($e_Status);

    // $e_Text = $ccr->createElement('Text', $row['outcome']);
    $e_Text = $ccr->createElement('Text', 'Active');
    $e_Status->appendChild($e_Text);

    //$e_CommentID = $ccr->createElement('CommentID', $row['comments']);
    //$e_Problem->appendChild($e_CommentID);

    $e_Source = $ccr->createElement('Source');

    $e_Actor = $ccr->createElement('Actor');
    $e_Source->appendChild($e_Actor);

    $e_ActorID = $ccr->createElement('ActorID', $uuid ?? '');
    $e_Actor->appendChild($e_ActorID);

    $e_Problem->appendChild($e_Source);

    $e_CommentID = $ccr->createElement('CommentID', $row['comments'] ?? '');
    $e_Problem->appendChild($e_CommentID);

    $e_Episodes = $ccr->createElement('Episodes');
    $e_Problem->appendChild($e_Episodes);

    $e_Number = $ccr->createElement('Number');
    $e_Episodes->appendChild($e_Number);

    $e_Episode = $ccr->createElement('Episode');
    $e_Episodes->appendChild($e_Episode);

    $e_CCRDataObjectID = $ccr->createElement('CCRDataObjectID', 'EP' . $pCount);
    $e_Episode->appendChild($e_CCRDataObjectID);

    $e_Episode->appendChild(sourceType($ccr, $sourceID));

    $e_Episodes->appendChild(sourceType($ccr, $sourceID));

    $e_HealthStatus = $ccr->createElement('HealthStatus');
    $e_Problem->appendChild($e_HealthStatus);

    $e_DateTime = $ccr->createElement('DateTime');
    $e_HealthStatus->appendChild($e_DateTime);

    $e_ExactDateTime = $ccr->createElement('ExactDateTime');
    $e_DateTime->appendChild($e_ExactDateTime);

    $e_Description = $ccr->createElement('Description');
    $e_HealthStatus->appendChild($e_Description);

    $e_Text = $ccr->createElement('Text', $row['reason'] ?? '');
    $e_Description->appendChild($e_Text);

    $e_HealthStatus->appendChild(sourceType($ccr, $sourceID));
} while ($row = sqlFetchArray($result));
    //}

    // complex type should go in different find and should be included in createCCR.php
/*
    function sourceType($ccr, $uuid){

        $e_Source = $ccr->createElement('Source');

        $e_Actor = $ccr->createElement('Actor');
        $e_Source->appendChild($e_Actor);

        $e_ActorID = $ccr->createElement('ActorID',$uuid);
        $e_Actor->appendChild($e_ActorID);

        return $e_Source;
    }
*/
