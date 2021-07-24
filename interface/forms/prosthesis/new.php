<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc");

require("C_FormProsthesis.class.php");

$c = new C_FormProsthesis();
echo $c->default_action();
