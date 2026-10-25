<?php

declare(strict_types=1);

// Prefer form-local vendor + OpenEMR root vendor for framework classes.
$formRoot = dirname(__DIR__);
$oeRoot = dirname($formRoot, 3);

require_once $formRoot . '/vendor/autoload.php';

if (is_file($oeRoot . '/vendor/autoload.php')) {
    require_once $oeRoot . '/vendor/autoload.php';
}

// Minimal globals bag for unit tests that construct FormContext.
if (!class_exists(\OpenEMR\Core\OEGlobalsBag::class, true)) {
    fwrite(STDERR, "OpenEMR OEGlobalsBag not available; run tests inside openemr85 container.\n");
}
