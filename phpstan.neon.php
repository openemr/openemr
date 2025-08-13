<?php

/**
 * Dynamically configure phpstan depending on
 * whether it's running locally or in Github actions.
 *
 * @category  Tool
 * @package   OpenEMR
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc.
 * @license   GNU General Public License 3
 * @link      https://phpstan.org/config-reference
 */

$config = [];

// Check if running in GitHub Actions
if (getenv('GITHUB_ACTIONS') === 'true') {
    // Running in GitHub Actions - include GitHub-specific baseline
    if (file_exists(__DIR__ . '/phpstan.github.neon')) {
        $config['includes'] = ['phpstan.github.neon'];
        echo 'Note: including configuration file ' . __DIR__ . '/phpstan.github.neon' . PHP_EOL;
    }
} else {
    // Running locally - include local baseline if it exists
    if (file_exists(__DIR__ . '/phpstan.local.neon')) {
        $config['includes'] = ['phpstan.local.neon'];
        echo 'Note: including configuration file ' . __DIR__ . '/phpstan.local.neon' . PHP_EOL;
    }
}

return $config;
