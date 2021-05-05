<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormDAP.class.php");

$c = new C_FormDAP();
echo $c->view_action($_GET['id']);
?>
