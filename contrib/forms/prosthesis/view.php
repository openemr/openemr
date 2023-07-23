<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

require("C_FormProsthesis.class.php");

$c = new C_FormProsthesis();
echo $c->view_action($_GET['id']);
