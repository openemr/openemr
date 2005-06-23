<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormProsthesis.class.php");

$c = new C_FormProsthesis();
echo $c->default_action();
?>
