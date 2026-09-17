<?php

/**
 * PHPStan bootstrap file to set up include paths for static analysis.
 *
 * This file configures the PHP include_path to match the runtime configuration,
 * allowing PHPStan to properly resolve require/include statements that use
 * relative paths.
 */

// Add PostNuke calendar paths
// pnAPI.php includes config.php and pntables.php which are one level up
set_include_path(
    __DIR__ . '/../interface/main/calendar/' . PATH_SEPARATOR .
    __DIR__ . '/../interface/main/calendar/includes/' . PATH_SEPARATOR .
    get_include_path()
);
