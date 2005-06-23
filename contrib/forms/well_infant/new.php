<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormWellInfant.class.php");

$c = new C_FormWellInfant();
echo $c->default_action();
?>
