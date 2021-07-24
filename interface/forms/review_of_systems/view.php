<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc");

require("C_FormReviewOfSystems.class.php");

$c = new C_FormReviewOfSystems();
echo $c->view_action($_GET['id']);
