<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc");

require("C_FormPriorAuth.class.php");

$c = new C_FormPriorAuth();
echo $c->default_action();
