<?php

declare(strict_types=1);

# loads the composer class loader
require_once(__DIR__ . "/../vendor/autoload.php");

# configure session/globals
@session_start();
$_SESSION["site_id"] = "default";
$backpic = "";
$ignoreAuth = true;

# load globals
require_once(__DIR__ . "/../interface/globals.php");
