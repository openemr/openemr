<?php

require_once("interface/globals.php");

$controller = new Controller();
echo $controller->act($_GET);
