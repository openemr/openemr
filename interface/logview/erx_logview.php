<?php

/**
 * interface/logview/erx_logview.php Display NewCrop errors.
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

require_once(__DIR__.'/../globals.php');
require_once($srcdir.'/log.inc');
require_once($srcdir.'/formdata.inc.php');
require_once($srcdir.'/formatting.inc.php');

$error_log_path = $GLOBALS['OE_SITE_DIR'].'/documents/erx_error';

if(array_key_exists('filename', $_REQUEST)) {
	$filename = $_REQUEST['filename'];
} else {
	$filename = '';
}

if(array_key_exists('start_date', $_REQUEST)) {
	$start_date = $_REQUEST['start_date'];
} else {
	$start_date = '';
}

if($filename) {
	$bat_content = '';

	preg_match('/erx_error-\d{4}-\d{1,2}-\d{1,2}\.log/', $filename, $matches);

	if($matches) {
		if($fd = fopen($error_log_path.'/'.$filename, 'r')) {
			$bat_content = fread($fd, filesize($error_log_path.'/'.$filename));
		}

		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename='.$filename);
		header('Content-Description: File Transfer');
		header('Content-Length: '.strlen($bat_content));

		echo $bat_content;

		die;
	}
}

?>
<html>
	<head>
		<?php html_header_show(); ?>
		<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
		<link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css" type="text/css">
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.js"></script>
		<?php include_once($GLOBALS['srcdir'].'/dynarch_calendar_en.inc.php'); ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar_setup.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.2.2.min.js"></script>
	</head>
	<body class="body_top">
		<form method="post">
		<font class="title"><?php echo xlt('eRx Logs'); ?></font><br><br>
		<table>
			<tr>
				<td>
					<span class="text"><?php echo xlt('Date'); ?>: </span>
				</td>
				<td>
					<input type="text" size="10" name="start_date" id="start_date" value="<?php echo $start_date ? substr($start_date, 0, 10) : date('Y-m-d'); ?>" title="<?php echo xlt('yyyy-mm-dd Date of service'); ?>" onkeyup="datekeyup(this, mypcc)" onblur="dateblur(this, mypcc)" />
					<img src="<?php echo $GLOBALS['webroot']; ?>/interface/pic/show_calendar.gif" align="absbottom" width="24" height="22" id="img_begin_date" border="0" alt="[?]" style="cursor: pointer; cursor: hand" title="<?php echo xlt('Click here to choose a date'); ?>">&nbsp;
				</td>
				<td>
					<input type="submit" name="search_logs" value="<?php echo xlt('Search'); ?>">
				</td>
			</tr>
		</table>
		</form>
<?php

	$check_for_file = 0;
	if(array_key_exists('search_logs', $_REQUEST)) {
		if ($handle = opendir($error_log_path)) {
			while(false !== ($file = readdir($handle))) {
				$file_as_in_folder = 'erx_error-'.$start_date.'.log';

				if($file != '.' && $file != '..' && $file_as_in_folder == $file) {
					$check_for_file = 1;
					$fd = fopen ($error_log_path.'/'.$file, 'r');
					$bat_content = fread($fd, filesize($error_log_path.'/'.$file));
?>
					<p><?php echo xlt('Download'); ?>: <a href="erx_logview.php?filename=<?php echo htmlspecialchars($file, ENT_QUOTES); ?>"><?php echo htmlspecialchars($file, ENT_NOQUOTES); ?></a></p>
					<textarea rows="35" cols="132"><?php echo htmlspecialchars($bat_content, ENT_QUOTES); ?></textarea>
<?php
				}
			}
		}
		if($check_for_file == 0) {
			echo xlt('No log file exist for the selected date').': '.$start_date;
		}
	}

?>
	</body>
	<script type="text/javascript">
		Calendar.setup({inputField:"start_date", ifFormat:"%Y-%m-%d", button:"img_begin_date"});
	</script>
</html>