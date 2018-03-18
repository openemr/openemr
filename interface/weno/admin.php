<?php
/**
 * weno admin.
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2016-2017 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../globals.php');
require_once("$srcdir/options.inc.php");
use OpenEMR\Core\Header;
use OpenEMR\Rx\Weno\AdminProperties;

$tables   = new AdminProperties();

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
     <title><?php print xlt("Weno Admin"); ?></title>
        <?php Header::setupHeader(); ?>

</head>
<style>
.row.text-center > div {
    display: inline-block;
    float: none;
}
</style>
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

   $drugData = $tables->drugTableInfo();
if (!$drugData['ndc']) {
    echo "<a href='drugPaidInsert.php' class='btn btn-default'>".xlt("Import Formularies")."</a> <br>".xlt("Be patient, this can take a while.");
} else {
    print xlt("Formularies inserted into table")."<br>";
}

?>

<h3><?php echo xlt("Select State and City to Import for Pharmacy"); ?></h3>

    <form method="post" action="import_pharmacies.php" class="form-inline text-center">

        <div class="row text-center">
            <div class="form-group well col-lg-5 text-center">
                <?php echo generate_form_field(array('data_type' => $GLOBALS['state_data_type'], 'list_id' => $GLOBALS['state_list'], 'field_id' => 'state'), ''); ?>
                <input type="text" class="form-control" id="form_city" name="form_city" placeholder="<?php echo xla('Enter City')?>"><br/><br/>
                <button type="submit" class="btn btn-default   n-save" value=><?php echo xlt("Import Pharmacies"); ?></button>
                <h4><?php echo xlt("Be patient, this can take a while."); ?></h4>
            </div>
        </div>

    </form>
<br><br>

<?php if (!empty($finish)) {
    echo $finish . xlt("with import");
} ?>

</div>
<script>
$(document).ready(function(){
    $("#addtolistid_state").hide();
 });
</script>
</body>
</html>
