<?php

/**
 * interface/soap_functions/soap_patientfullmedication.php Check current
 * patients Medications TTL status and perform a SOAP call to NewCrop if
 * elapsed.
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
 * @author     Sam Likins <sam.likins@wsi-services.com>
 * @link       http://www.open-emr.org
 */

$sanitize_all_escapes = true;		// SANITIZE ALL ESCAPES

$fake_register_globals = false;		// STOP FAKE REGISTER GLOBALS

require_once('../globals.php');
require_once($GLOBALS['fileroot'] . '/interface/eRx_xml.php');
require_once($GLOBALS['fileroot'] . '/interface/eRxSOAP.php');

set_time_limit(0);

$eRxSOAP = new eRxSOAP;
$eRxSOAP->setStore(new eRxStore)
	->setAuthUserId($_SESSION['authUserID'])
	->setSoapPath(explode(';', getErxSoapPath()))
	->setCredentials(getErxCredentials());

if((array_key_exists('refresh', $_REQUEST)
		&& $_REQUEST['refresh'] == 'true')
	|| $eRxSOAP->elapsedTTL(eRxSOAP::ACTION_MEDICATIONS)
	|| $eRxSOAP->checkPatientImportStatus(eRxSOAP::FLAG_PRESCRIPTION_PRESS)
) {
	$insertedRows = $eRxSOAP->insertUpdateMedications();

	$message = xlt($insertedRows
		? 'Prescription History import successfully completed'
		: 'Nothing to import for Prescription'
	);

	$eRxSOAP->updatePatientImportStatus(eRxSOAP::FLAG_PRESCRIPTION_IMPORT)
		->updateTTL(eRxSOAP::ACTION_MEDICATIONS);
} else {
	$message = xlt('Import deferred for time-to-live');
}

echo $message;