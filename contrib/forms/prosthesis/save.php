<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

require("C_FormProsthesis.class.php");
$c = new C_FormProsthesis();
echo $c->default_action_process($_POST);
@formJump();
