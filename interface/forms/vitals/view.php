<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormVitals.class.php");

$c = new C_FormVitals();
$c->setFormId($_GET['id']);
echo $c->default_action();
?>
