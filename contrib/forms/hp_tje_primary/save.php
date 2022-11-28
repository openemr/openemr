<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

require("C_FormHpTje.class.php");
$c = new C_FormHpTje();
echo $c->default_action_process($_POST);
@formJump();
