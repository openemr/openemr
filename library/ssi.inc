<?php
// Copyright (C) 2008 Phyaura, LLC <info@phyaura.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

	include_once('../../interface/globals.php');

function getRelayHealthLink() {
	$result = sqlQuery("select ssi_relayhealth from users where id={$_SESSION['authUserID']};");
	$trimmed = trim($result['ssi_relayhealth']);
	if (!empty($trimmed)) {
		return '<a href="../jump_to_RelayHealth.php" target="_new" class="navitem" id="rh_link">Relay Health</a>';
	} else {
		// default link
		return '<a href="https://app.relayhealth.com/welcome.asp?bid=phyaura" target="_new" class="navitem" id="rh_link">Relay Health</a>';
	}
}
?>
