<?php

/**
 * PHPUnit bootstrap file for SSO Module
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    A CTO, LLC
 * @copyright Copyright (c) 2026 A CTO, LLC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

// Make sure this can only be run on the command line
if (php_sapi_name() !== 'cli') {
    exit;
}

$_GET['site'] = 'default';
$ignoreAuth = true;
require_once(__DIR__ . "/../../../../globals.php");
