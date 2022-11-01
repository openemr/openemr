<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");

require("C_FormSOAP.class.php");

$c = new C_FormSOAP();
echo $c->default_action();
