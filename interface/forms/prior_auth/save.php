<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormPriorAuth.class.php");
$c = new C_FormPriorAuth();
echo $c->default_action_process($_POST);
@formJump();
?>
