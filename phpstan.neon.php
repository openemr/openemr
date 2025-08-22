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
$config['includes'] = ['phpstan.local.neon'];
echo 'Note: including configuration file ' . __DIR__ . '/phpstan.local.neon' . PHP_EOL;

return $config;
