<?php

/**
 * interface/eRx.php Redirect to NewCrop pages.
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

$sanitize_all_escapes = true;		//SANITIZE ALL ESCAPES

$fake_register_globals = false;		//STOP FAKE REGISTER GLOBALS

require_once(__DIR__.'/globals.php');
require_once($GLOBALS['fileroot'].'/interface/eRxGlobals.php');
require_once($GLOBALS['fileroot'].'/interface/eRxStore.php');
require_once($GLOBALS['fileroot'].'/interface/eRxXMLBuilder.php');
require_once($GLOBALS['fileroot'].'/interface/eRxPage.php');

set_time_limit(0);

function array_key_exists_default($key, $search, $default = null) {
	if(array_key_exists($key, $search)) {
		$value = $search[$key];
	} else {
		$value = $default;
	}

	return $value;
}

$eRxPage = new eRxPage(
	new eRxXMLBuilder(
		new eRxGlobals($GLOBALS),
		new eRxStore
	)
);

$eRxPage->setAuthUserId(array_key_exists_default('authUserID', $_SESSION))
	->setDestination(array_key_exists_default('page', $_REQUEST))
	->setPatientId(array_key_exists_default('pid', $GLOBALS))
	->setPrescriptionIds(array_key_exists_default('id', $_REQUEST))
	->setPrescriptionCount(60);

?>
<html>
	<body>
<?php

$missingExtensions = $eRxPage->checkForMissingExtensions();

if(count($missingExtensions) > 0) {

?>
		<strong><?php echo xlt('Error'); ?>:</strong>
		<p><?php echo xlt('Please contact your systems administrator, the following component(s) are required but are missing.'); ?></p>
		<ul>
			<?php foreach($missingExtensions as $missingExtension) { echo '<li>'.text($missingExtension).'</li>'; } ?>
		<ul>
<?php

} else {

	$messages = $eRxPage->buildXML();

	if(count($messages['demographics']) > 0) {

?>
		<strong><?php echo xlt('Warning'); ?>:</strong>
		<p><?php echo xlt('The following fields have to be filled to send a request.'); ?></p>
		<ul>
			<?php foreach($messages['demographics'] as $message) { echo '<li>'.text($message).'</li>'; } ?>
		<ul>
		<p><?php echo xlt('You will be automatically redirected to Demographics.  You may make the necessary corrections and navigate to NewCrop again.'); ?></p>
<?php

		ob_end_flush();

?>
		<script type="text/javascript">
			window.setTimeout(function() {
				window.location = "<?php echo $GLOBALS['webroot']; ?>/interface/patient_file/summary/demographics_full.php";
			}, <?php echo (count($messages) * 2000) + 3000; ?>);
		</script>
<?php

	} elseif(count($messages['empty']) > 0) {

?>
		<p><?php echo xlt('The following fields have to be filled to send a request.'); ?></p>
		<ul>
			<?php foreach($messages['empty'] as $message) { echo '<li>'.text($message).'</li>'; } ?>
		<ul>
<?php

	} else {

		if(count($messages['warning']) > 0) {

?>
		<strong><?php echo xlt('Warning'); ?></strong>
		<p><?php echo xlt('The following fields are empty.'); ?></p>
		<ul>
			<?php foreach($messages['warning'] as $message) { echo '<li>'.text($message).'</li>'; } ?>
		<ul>
		<p><strong><?php echo xlt('This will not prevent you from going to the e-Prescriptions site.'); ?></strong></p>
<?php

			ob_end_flush();
			$delay = (count($messages) * 2000) + 3000;
		} else {
			$delay = 1;
		}

		$xml = $eRxPage->getXML();

		$errors = $eRxPage->checkError($xml);

		if(count($errors) > 0) {

?>
		<strong><?php echo xlt('NewCrop call failed'); ?></strong>
		<ul>
			<?php foreach($errors as $message) { echo '<li>'.$message.'</li>'; } ?>
		<ul>
<?php

		} else {

			$eRxPage->updatePatientData();

?>
		<script type="text/javascript">
		<?php require($GLOBALS['srcdir'].'/restoreSession.php'); ?>
		</script>
		<form name="info" method="post" action="<?php echo $GLOBALS['erx_newcrop_path']; ?>" onsubmit="return top.restoreSession()">
			<input type="submit" style="display:none">
			<input type="hidden" id="RxInput" name="RxInput" value="<?php echo $xml; ?>">
		</form>
		<script type="text/javascript" src="../library/js/jquery.1.3.2.js"></script>
		<script type="text/javascript">
			window.setTimeout(function() {
				document.forms[0].submit();
			}, <?php echo $delay; ?>);
		</script>
<?php

		}
	}
}

?>
	</body>
</html>