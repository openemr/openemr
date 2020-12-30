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

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Rx\Weno\AdminProperties;

$tableHasData = sqlQuery("SELECT count(drug_id) AS count FROM erx_weno_drugs");

$data = new AdminProperties();


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
#loader {
    border: 6px solid #f3f3f3;
    border-radius: 50%;
    border-top: 6px solid blue;
    border-bottom: 6px solid blue;
    width: 22px;
    height: 22px;
    -webkit-animation: spin 2s linear infinite;
    animation: spin 2s linear infinite;
}
@-webkit-keyframes spin {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(360deg); }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
</head>

<body class="body_top text-center">
<div class="container center-block">
<?php

// check to make sure only administrators access this page.
if (!AclMain::aclCheckCore('admin', 'super')) {
    die(xlt("You are not authorized!"));
}

if ($GLOBALS['weno_rx_enable'] != 1) {
    print xlt("You must activate Weno first! Go to Administration, Globals, Connectors");
    exit;
} else {
    print xlt("Weno Service is Enabled") . "<br /><br />";
}

if ($tableHasData['count'] > 1) {
    print xlt("Formularies are inserted into table") . "<br />";
} else {
    echo "<a href='drugDataInsert.php?csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' class='btn btn-secondary'>" . xlt("Import Formularies") . "</a> <br />" . xlt("Be patient, this can take a while.");
}

?>

<br /><br />
<?php


?>
    <h2><?php echo xlt("Pharmacy Import / Update"); ?></h2><br />
    <form class="form-inline" id="addpharmacies">
        <div class="form-group">
            <label for="city" ><?php print xlt("City");?></label>
            <input type="text" class="form-control" id="city">
        </div>
        <div class="form-group">
            <label for="state"><?php print xlt("State");?></label>
            <input type="text" class="form-control" id="state">
        </div>
        <div class="form-group" id="form_btn">
            <button type="submit"  class="btn btn-primary" ><?php echo xlt("Submit"); ?></button>
        </div>
        <div class="form-group" id="loader"></div>

    </form>
    <p>
        <h4><?php echo xlt("Disclaimer"); ?>:</h4>
    <?php echo xlt("This is public information maintained by HHS/CMS. We cannot be responsible for the data received"); ?>.<br />
    <?php echo xlt("Utilize at your own risk. Always verify data received"); ?>.
    </p>
</div>
<script>

let f = document.getElementById('addpharmacies');
f.addEventListener('submit', importPharm);
$('#loader').hide();

function importPharm(e) {
    top.restoreSession();
    let city = document.getElementById("city").value;
    let state = document.getElementById("state").value;
    if (city.length === 0 || state.length ===0) {
        alert(<?php echo xlj("City and state must both be filled out"); ?>);
        e.preventDefault();
        return false;
    }
     //user experience hide the submit button and show spinner
    $('#loader').show();
    $('#form_btn').hide();
    e.preventDefault();
    $.ajax({
        type: 'GET',
        dataType: 'JSON',
        url: 'pharmacyHelper.php?csrf_token_form=' + <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>,
        data: {"textData": [city, state] },
        success: function (response) {
            let msg =  response.saved + " " + <?php echo xlj("Pharmacies Imported"); ?>;
            alert(msg);
            $('#loader').hide();
            $('#form_btn').show();
        },
        error: function (xhr, status, error) {
            console.log(xhr);
            console.log(status);
            console.log(error);
            console.warn(xhr.responseText);
        }

 });

}

</script>
</body>
</html>
