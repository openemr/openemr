<?php

$sanitize_all_escapes = true;		// SANITIZE ALL ESCAPES

$fake_register_globals = false;		// STOP FAKE REGISTER GLOBALS

require_once('../globals.php');

$drugs = file_get_contents('drugspaidinsert.sql'); 

sqlInsert($drugs);

header('Location: ' . $_SERVER['HTTP_REFERER']);
