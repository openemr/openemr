<?php
/**
 * weno admin.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('../globals.php');
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Rx\Weno\AdminProperties;

$tableHasData = sqlQuery("SELECT count(drug_id) AS count FROM erx_weno_drugs");

$tables   = new AdminProperties();

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
     <title><?php print xlt("Weno Admin"); ?></title>
        <?php Header::setupHeader(); ?>

<style>
.row.text-center > div {
    display: inline-block;
    float: none;
}
</style>
</head>

<body class="body_top text-center">
<div class="container center-block">
<?php

// check to make sure only administrators access this page.
if (!acl_check('admin', 'super')) {
    die(xlt("You are not authorized!"));
}

if ($GLOBALS['weno_rx_enable'] != 1) {
    print xlt("You must activate Weno first! Go to Administration, Globals, Connectors");
    exit;
} else {
    print xlt("Weno Service is Enabled")."<br><br>";
}

if ($tableHasData['count'] > 1) {
    print xlt("Formularies are inserted into table")."<br>";
} else {
    echo "<a href='drugDataInsert.php?csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' class='btn btn-default'>".xlt("Import Formularies")."</a> <br>".xlt("Be patient, this can take a while.");
}

?>

<br><br>
<?php
if (file_exists('../../contrib/weno/pharmacyList.csv')) {
    echo "<a href='import_pharmacies.php?csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' class='btn btn-default'>" . xlt("Import Pharmacies Script") . "</a> <br>";
}
?>

</div>
<script>
$(function(){
    $("#addtolistid_state").hide();
 });
</script>
</body>
</html>
