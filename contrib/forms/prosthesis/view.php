<?php

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");

require("C_FormProsthesis.class.php");

$c = new C_FormProsthesis();
echo $c->view_action($_GET['id']);
