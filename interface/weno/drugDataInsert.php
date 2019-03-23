<?php
/**
 * weno drug paid insert
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');
use OpenEMR\Core\Header;

if (!verifyCsrfToken($_GET["csrf_token_form"])) {
    csrfNotVerified();
}

?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/html">
    <head>
        <title><?php print xlt("Data Insert"); ?></title>
        <?php Header::setupHeader(); ?>
        <style>
            /*Create a good user experience*/
            .row.text-center > div {
                display: inline-block;
                float: none;
            }
            .loader {
                border: 16px solid #f3f3f3; /* Light grey */
                border-top: 16px solid #3498db; /* Blue */
                border-radius: 50%;
                width: 120px;
                height: 120px;
                animation: spin 2s linear infinite;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

        </style>
    </head>

    <body class="body_top text-center">
    <div class="container center-block">
        <h1>Importing Data</h1>
        <div class="loader"></div>
    </div>
    </body>
    </html>
<?php
try {
    $drugs = file_get_contents('../../contrib/weno/erx_weno_drugs.sql');
} catch (Exception $e) {
    echo "SQL file Not found" . $e->getMessage();
}

try {
    $drugsArray = explode(";\n", $drugs);

} catch (Exception $e) {
    echo "Error occurred in array" . $e->getMessage();

}
// Settings to drastically speed up import with InnoDB
sqlStatementNoLog("SET autocommit=0");
sqlStatementNoLog("START TRANSACTION");

foreach ($drugsArray as $drug) {
    if (empty($drug)) {
        continue;
    }
    sqlStatementNoLog($drug);
}

// Settings to drastically speed up import with InnoDB
    sqlStatementNoLog("COMMIT");
    sqlStatementNoLog("SET autocommit=1");


header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
