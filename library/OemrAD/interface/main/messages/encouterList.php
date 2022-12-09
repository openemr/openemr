<?php

include_once("../../globals.php");
include_once($GLOBALS['srcdir'] . "/OemrAD/oemrad.globals.php");

use OpenEMR\OemrAd\EmailMessage;

if(!isset($_REQUEST['pid'])) $_REQUEST['pid'] = '';
$pid = strip_tags($_REQUEST['pid']);
$list = strip_tags($_REQUEST['list']);

if($pid) {
	echo '<div class="counterListContainer">';
	echo EmailMessage::getEncouterList($pid, $list);
	echo '</div>';
}