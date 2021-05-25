<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/clinical_rules.php");
require_once("$srcdir/options.js.php");

require ("C_FormAssessmentForm.class.php");

$c = new C_FormAssessmentForm();
echo $c->view_action($_GET['id']);
?>
