<?php

/**
 * =======================================
 * OpenEMR Automated Code Import
 * =======================================
 * This class walks through a directory tree up to one level to find archives of Code System databases.
 *
 * The import limit here is dictated by available functionality in OpenEMR. Currently, expect to import RXNORM,
 * SNOMED, ICD9, ICD10 CM, ICD10 PCS, CQM_VALUESET, and any arbitrary dataset that conforms to the VALUESET format.
 *
 * By default, we look into `/var/www/localhost/htdocs/openemr/contrib`, which is the default path used by the frontend importer.
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

namespace OpenEMR\Services\CodeTypes;

use OpenEMR\Services\Traits\ServiceFSTrait;
use RuntimeException;
use Throwable;

class CodeTypeImporter
{
    use ServiceFSTrait;

    public function import_dir(string $type, $importFunction): void {
        foreach (glob("*.zip") as $file) {
            # Copy to temp
            echo " [ $type ]  Copying file =>  $file  !\n";
            if (!temp_copy($file, $type)) {
                error_log("Failed to copy $file of type $type");
                return;
            }

            # Unpack
            echo " [ $type ]  Uncompressing file => $file!\n";
            if (!temp_unarchive($file, $type)) {
                error_log("Failed to unzip $file of type $type");
                return;
            }

            # Import data
            echo " [ $type ]  Importing file =>  $file  !... \n";
            if ($importFunction($type)) {
                echo "SUCCESS\n";
            } else {
                error_log("Failed to import SNOMED !\n");
            }

            # Cleanup
            echo " [ $type ] Cleaning up import for file =>  $file  !\n";
            temp_dir_cleanup($type);
        }
    }

    public function import_snomed(string $path): bool {
        // TODO: Consider including auto detection in OpenEMR at a later date.
        try {
            if (!snomedRF2_import()) throw new RuntimeException("Failed to import SNOMED with format => snomedRF2\n");
            return true;
        } catch (Throwable $e) {
            try {
                error_log("\t$e");
                if(!snomed_import(true)) throw new RuntimeException("Failed to import SNOMED with format => US Extension\n");
                return true;
            } catch (Throwable $e) {
                error_log("\t$e");
                return snomed_import();
            }
        }
    }

    public function import($path): void {
        # Change to import directory
        $this->pushd($path);

        # Scan directory
        $dirs = scandir('./');
        echo "Available directories =>  $dirs \n";
        foreach ($dirs as $dir) {
            if ($dir == "." || $dir == "..") {
                continue;
            }

            # Go into directory
            echo "Entering directory => $dir \n";
            $this->pushd($dir);

            // Specialty CODE import cases; defaults to the valueset_import function and attempts to import assuming that
            // format!
            match ($dir) {
                "icd9" => $this->import_dir("ICD9", function ($type) {icd_import($type);}),
                "icd10" => $this->import_dir("ICD10", function ($type) {icd_import($type);}),
                "rxnorm" => $this->import_dir("RXNORM", function () {rxnorm_import(false);}),
                "snomed" => $this->import_dir("SNOMED", function ($file) {$this->import_snomed($file);}),
                "cqm", "cqm_valueset" => $this->import_dir("CQM_VALUESET", function ($type) {valueset_import($type);}),
                default => $this->import_dir("VALUESET", function ($type) {valueset_import($type);}),
            };

            # Restore parent path for the next iteration
            echo "Exiting directory => $dir\n";
            $this->popd();
        }
    }
}
