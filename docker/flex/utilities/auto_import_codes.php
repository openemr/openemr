#!/usr/bin/env php
<?php
/**
 * =======================================
 * OpenEMR Automated Code Import
 * =======================================
 * This script walks through a directory tree up to one level to find archives of Code System databases.
 *
 * The import limit here is dictated by available functionality in OpenEMR. Currently, expect to import RXNORM,
 * SNOMED, ICD9, ICD10 CM, ICD10 PCS, CQM_VALUESET, and any arbitrary dataset that conforms to the VALUESET format.
 *
 * Usage:
 *   php auto_import_codes.php [dir_root]
 *
 * Example:
 *   php auto_import_codes.php
 *   php auto_import_codes.php codes
 * ============================================================================
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Luis M. Santos, MD <lsantos@medicalmasses.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
declare(strict_types=1);
$sitePath = '/var/www/localhost/htdocs/openemr';
$_GET['site'] = 'default';
$ignoreAuth = true;
$sessionAllowWrite = true;
chdir($sitePath);

// Load OpenEMR's Composer autoloader
// This gives us access to all OpenEMR classes, including the Installer class
require_once 'interface/globals.php';
require_once 'library/standard_tables_capture.inc.php';

function import_dir(string $type, $importFunction): void {
    foreach (glob("*.zip") as $file) {
        # Copy to temp
        echo " [" . $type . "] Copying file => " . $file  . "!\n";
        if (!temp_copy($file, $type)) {
            error_log("Failed to copy " . $file . " of type " . $type);
            return;
        }

        # Unpack
        echo " [" . $type . "] Uncompressing file => " . $file  . "!\n";
        if (!temp_unarchive($file, $type)) {
            error_log("Failed to unzip " . $file . " of type " . $type);
            return;
        }

        # Import data
        echo " [" . $type . "] Importing file => " . $file  . "!\n";
        $importFunction($type);

        # Cleanup
        echo " [" . $type . "] Cleaning up import for file => " . $file  . "!\n";
        temp_dir_cleanup($type);
    }
}

function import_snomed(string $path): void {
    // TODO: Consider including auto detection in OpenEMR at a later date.
    try {
        if (!snomedRF2_import()) throw new Exception("Failed to import SNOMED with format => snomedRF2");
    } catch (Throwable $e) {
        try {
            if(!snomed_import(true)) throw new Exception("Failed to import SNOMED with format => US Extension");
        } catch (Throwable $e) {
            if (!snomed_import()) {
                error_log("Failed to import SNOMED with format => Generic");
            };
        }
    }
}

function import(string $path): void {
    # Change to import directory
    chdir($path);

    # Scan directory
    $dirs = scandir('./');
    echo "Available directories => ". $dirs . "\n";
    foreach ($dirs as $dir) {
        if ($dir == "." || $dir == "..") {
            continue;
        }

        if(is_dir($dir)) {
            # Go into directory
            echo "Entering directory => ". $dir . "\n";
            chdir($dir);

            // Specialty CODE import cases; defaults to the valueset_import function and attempts to import assuming that
            // format!
            match ($dir) {
                "icd9" => import_dir("ICD9", function ($type) {icd_import($type);}),
                "icd10" => import_dir("ICD10", function ($type) {icd_import($type);}),
                "rxnorm" => import_dir("RXNORM", function () {rxnorm_import(false);}),
                "snomed" => import_dir("SNOMED", function ($file) {import_snomed($file);}),
                "cqm", "cqm_valueset" => import_dir("CQM_VALUESET", function ($type) {valueset_import($type);}),
                default => import_dir("VALUESET", function ($type) {valueset_import($type);}),
            };

            # Restore parent path for the next iteration
            echo "Exiting directory => ". $dir . " back to ". $path . "\n";
            chdir($path);
        }
    }
}

// Ensure we're running from CLI
if (php_sapi_name() !== 'cli') {
    throw RuntimeException('This tool can only be run from the command line');
}

$contribPath = realpath($sitePath . '/contrib');
if ($argc > 1) {
    if (strlen($argv[1])) {
        $contribPath = realpath($argv[1]);
    }
}

import($contribPath);
