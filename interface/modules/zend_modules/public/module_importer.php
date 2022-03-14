<?php

/*
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use Installer\Controller\ModuleImport;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

require_once dirname(__FILE__, 4) . "/globals.php";

if (!CsrfUtils::verifyCsrfToken($_POST['token'])) {
    echo 'token not verified';
    CsrfUtils::csrfNotVerified();
}

        /*
         * check if the directory exist to download the import. If it does not exist create it on
         * first use.
         */
        $import_dir = ModuleImport::createImportDir();

?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title><?php echo xlt("Module Import") ?></title>
        <?php Header::setupHeader(); ?>
    </head>
    <body>
        <div class="container-lg">
            <div class="m-3">
                <h1><?php echo xlt("Module Import") ?></h1>
            </div>
            <div class="m-3">
                <?php
                /*
                 * get the file name to be imported from the URL supplied
                 */
                $parts = explode('/', $_POST['module_import']);
                $part_count = count($parts);
                $zip = ($part_count - 1);
                echo "<p><strong>" . xlt('Download location given ') . "</strong></p> " .
                    "<span style='color:#0a246a; margin-left: 30px'> " . $_POST['module_import'] . "</span>";
                ?>
            </div>
            <div class="m-5">
                <?php
                echo "<p><strong>" . xlt("Attempting to import file. ") . "</strong></p>";
                /*
                 * download the file to the import folder
                 */
                $import = new ModuleImport($_POST['module_import'], $parts[$zip], $import_dir);

                echo  "<strong>" . xlt('Result of import') . "</strong><br>";
                //Check to see if the zip file is empty
                $za = new ZipArchive();
                $za->open($import_dir.$parts[$zip]);
                $stats = '';
                for ( $i = 0; $i < $za->numFiles; $i++) {
                    $stats = $za->statIndex( $i );
                }

                if (!empty($stats)) {
                    echo xlt("File successfully imported ") . "<br>";
                    $destination = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "custom_modules";
                    $foldername = explode(".", $parts[$zip]);
                    $custom_module = ModuleImport::createDestinationFolder($destination . DIRECTORY_SEPARATOR . $foldername[0] );
                    echo xlt("Moving file to destination ") . $destination . DIRECTORY_SEPARATOR . $foldername[0] . "<br>";
                    if ($custom_module === 'created') {
                        $za->extractTo($destination . DIRECTORY_SEPARATOR . $foldername[0]);
                        $za->close();
                    }
                    echo xlt("Module import completed. Return to module manager and select Unregistered to see this module");
                } else {
                    echo xlt("Zip file has no content. Please check source file and try again");
                    exit;
                }
                //move file to the final destination


                ?>
            </div>
        </div>
    </body>
    </html>
