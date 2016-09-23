<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormVitals.class.php");

$c = new C_FormVitals();
#echo $c->view_action(0);
echo $c->default_action(0);
?>
