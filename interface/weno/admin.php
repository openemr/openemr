<?php
/**
 * weno admin.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016-2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Rx\Weno\AdminProperties;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
    <form class="form-inline" id="addpharmacies">
        <div class="form-group">
            <label for="city" ><?php print xlt("City");?></label>
            <input type="text" class="form-control" id="city">
        </div>
        <div class="form-group">
            <label for="state"><?php print xlt("State");?></label>
            <input type="text" class="form-control" id="state">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary" >Submit</button>
        </div>
    </form>

</div>
<script>

let f = document.getElementById('addpharmacies');
f.addEventListener('submit', importPharm);

function importPharm() {
    top.restoreSession();
    let city = document.getElementById("city").value;
    let state = document.getElementById("state").value;
    if (city.length === 0 || state.length ===0) {
        return "City and state must both be filled out"  // Do some better error handling here
    }
    let body = {"city": city, "state": state};
    let req = new Request('pharmacyHelper.php', {method: "POST", body: body});
    fetch(req)
        .then(response=> {
            if (response.status === 200) {
                return response.json()
            } else {
                throw new Error("Bad response from the server");
            }
        })
        .then(json => {
            alert(json) // Not a great UI experience
        })
}

</script>
</body>
</html>
