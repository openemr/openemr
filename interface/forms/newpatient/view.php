<?php
include_once("../../globals.php");
include_once("$srcdir/acl.inc");
include_once("$srcdir/lists.inc");

$disabled = "disabled";

// If we are allowed to change encounter dates...
if (acl_check('encounters', 'date_a')) {
  $disabled = "";
}

$viewmode = true;
require_once("common.php");
?>
