<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormEvaluation.class.php");

$c = new C_FormEvaluation();
echo $c->view_action($_GET['id']);
?>
