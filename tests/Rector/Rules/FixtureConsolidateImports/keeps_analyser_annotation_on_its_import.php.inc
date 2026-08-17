<?php

use Vendor\Alpha;

/** @phpstan-ignore-next-line */
use Vendor\Beta;

require_once __DIR__ . '/globals.php';

echo Alpha::run() . Beta::run();

?>
