<?php
// Copyright (C) 2008 Phyaura, LLC <info@phyaura.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

	include_once('globals.php');
	include_once('../library/auth.inc');
	include_once('../library/relayhealth.inc.php');

	// verify globals are set
	if ($GLOBALS['ssi']['rh']['ApplicationName']
	&&  $GLOBALS['ssi']['rh']['ApplicationPassword']
	&&  $GLOBALS['ssi']['rh']['PartnerName']
	&&  $GLOBALS['ssi']['rh']['wsdl']) {

		// verify user has a login set in user admin
		$result = sqlQuery("select ssi_relayhealth from users where id={$_SESSION['authUserID']}");
		$trimmed = trim($result['ssi_relayhealth']);
		if (empty($trimmed))  {
			xl('Relay Health credentials are missing from this user account.', 'e');
			die;
		}

		// make soap call to RH
		//$client = new SoapClient($GLOBALS['ssi']['rh']['wsdl'], array($classmap));
		$client = new SoapClient($GLOBALS['ssi']['rh']['wsdl'], array(
			'classmap' => $classmap,
			'trace' => 1
		));

		$rh = new RelayHealthHeader();
		$rh->PartnerName = 		$GLOBALS['ssi']['rh']['PartnerName'];
		$rh->ApplicationName = 		$GLOBALS['ssi']['rh']['ApplicationName'];
		$rh->ApplicationPassword =	$GLOBALS['ssi']['rh']['ApplicationPassword'];
		$header = new SoapHeader("http://api.relayhealth.com/7.3/SSI", 'RelayHealthHeader', $rh, 1);

		$vw = new ViewWelcome();
		$vw->partnerUserId = $result['ssi_relayhealth'];
		$params = new SoapVar($vw, SOAP_ENC_OBJECT, "ViewWelcome", "http://api.relayhealth.com/7.3/SSI");

		try {
			$token = $client->__soapCall("ViewWelcome", 
				array('partnerUserId' => $vw), 
				array(
					'location' => $GLOBALS['ssi']['rh']['location'],
					'uri' => 'http://api.relayhealth.com/7.3/SSI'
				),
				array($header));
		} catch (Exception $e) {
			echo "<pre>error $e\n";
		}
		header("Location: ". $token->Url);
	}
?>
