<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormWellChild.class.php");

$c = new C_FormWellChild();
echo $c->view_action($_GET['id']);
?>
