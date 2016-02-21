<?php
require_once("interface/globals.php");
require_once("library/classes/Controller.class.php");

$controller = new Controller();
echo $controller->act($_GET);

?>
