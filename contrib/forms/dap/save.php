<?php

include_once("../../globals.php");
include_once("$srcdir/api.inc");

require("C_FormDAP.class.php");
$c = new C_FormDAP();
echo $c->default_action_process($_POST);
@formJump();
