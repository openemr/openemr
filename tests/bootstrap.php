<?php

declare(strict_types=1);

# loads the composer class loader
require __DIR__ . '/../vendor/autoload.php';

# load up session/globals
@session_start();
$_SESSION["site_id"] = "default";
$backpic = "";
$authUser = "admin";
$authProvider = "pass";
$ignoreAuth = true;

require __DIR__ . '/../interface/globals.php';
