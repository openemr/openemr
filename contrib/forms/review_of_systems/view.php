<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormReviewOfSystems.class.php");

$c = new C_FormReviewOfSystems();
echo $c->view_action($_GET['id']);
?>
