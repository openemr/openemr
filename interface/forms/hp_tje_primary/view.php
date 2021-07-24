<?php

require_once("../../globals.php");
require_once("$srcdir/api.inc");

require("C_FormHpTje.class.php");

$c = new C_FormHpTje();
echo $c->view_action($_GET['id']);
