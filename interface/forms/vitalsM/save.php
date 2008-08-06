<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
require ("C_FormVitalsM.class.php");

$c = new C_FormVitalsM();
echo $c->default_action_process($_POST);
@formJump();
?>
