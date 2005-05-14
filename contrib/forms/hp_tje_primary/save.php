<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormHpTje.class.php");
$c = new C_FormHpTje();
echo $c->default_action_process($_POST);
@formJump();
?>
