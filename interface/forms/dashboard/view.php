<?php
include_once('../../globals.php');
$tmp_id= $_GET['id'];
$address= "{$GLOBALS['rootdir']}/forms/dashboard/new.php?mode=update&id=$tmp_id";
echo "\n<script type='text/javascript'>top.restoreSession();window.location='$address';</script>\n";
exit;
?>
