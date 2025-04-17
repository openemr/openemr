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

global $pid;

           $e_ccrDocObjID = $ccr->createElement('CCRDocumentObjectID', getUuid());
           $e_ccr->appendChild($e_ccrDocObjID);

           $e_Language = $ccr->createElement('Language');
           $e_ccr->appendChild($e_Language);

           $e_Text = $ccr->createElement('Text', 'English');
           $e_Language->appendChild($e_Text);

           $e_Version = $ccr->createElement('Version', 'V1.0');
           $e_ccr->appendChild($e_Version);

           $e_dateTime = $ccr->createElement('DateTime');
           $e_ccr->appendChild($e_dateTime);

           $e_ExactDateTime = $ccr->createElement('ExactDateTime', date('Y-m-d\TH:i:s\Z'));
           $e_dateTime->appendChild($e_ExactDateTime);

           $e_patient = $ccr->createElement('Patient');
           $e_ccr->appendChild($e_patient);

           //$e_ActorID = $ccr->createElement('ActorID', $row['patient_id']);
           $e_ActorID = $ccr->createElement('ActorID', 'A1234'); // This value and ActorID in createCCRActor.php should be same.
           $e_patient->appendChild($e_ActorID);

           //Header From:
           $e_From = $ccr->createElement('From');
           $e_ccr->appendChild($e_From);

           $e_ActorLink = $ccr->createElement('ActorLink');
           $e_From->appendChild($e_ActorLink);

           $e_ActorID = $ccr->createElement('ActorID', $authorID);
           $e_ActorLink->appendChild($e_ActorID);

           $e_ActorRole = $ccr->createElement('ActorRole');
           $e_ActorLink->appendChild($e_ActorRole);

           $e_Text = $ccr->createElement('Text', 'author');
           $e_ActorRole->appendChild($e_Text);

           //Header To:
           $e_To = $ccr->createElement('To');
           $e_ccr->appendChild($e_To);

           $e_ActorLink = $ccr->createElement('ActorLink');
           $e_To->appendChild($e_ActorLink);

           //$e_ActorID = $ccr->createElement('ActorID', $row['patient_id']);
           $e_ActorID = $ccr->createElement('ActorID', 'A1234');
           $e_ActorLink->appendChild($e_ActorID);

           $e_ActorRole = $ccr->createElement('ActorRole');
           $e_ActorLink->appendChild($e_ActorRole);

           $e_Text = $ccr->createElement('Text', 'patient');
           $e_ActorRole->appendChild($e_Text);

           //Header Purpose:
           $e_Purpose = $ccr->createElement('Purpose');
           $e_ccr->appendChild($e_Purpose);

           $e_Description = $ccr->createElement('Description');
           $e_Purpose->appendChild($e_Description);

           $e_Text = $ccr->createElement('Text', 'Summary of patient information');
           $e_Description->appendChild($e_Text);
