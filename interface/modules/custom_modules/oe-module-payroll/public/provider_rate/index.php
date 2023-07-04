<?php

/**
 *  package   OpenEMR
 *  link      http://www.open-emr.org
 *  author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once dirname(__FILE__, 6) . "/globals.php";


use Juggernaut\Modules\Payroll\ProviderRates;
use OpenEMR\Core\Header;

$providerdata = new ProviderRates();

if (!empty($_POST)) {
    $status = $providerdata->savePayrollData($_POST['userid'], $percentage = $_POST['percentage'], $flat = $_POST['flat']);
}

$providers = $providerdata->getProviders();

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo xlt("Provider Rate Manager"); ?></title>
    <?php Header::setupHeader(['common']); ?>
    <style>
        .htitle {
            padding: 3em;
        }
        .report {

        }
    </style>
</head>
<body>
<div class="container">
    <div class="htitle">
        <h2><?php echo xlt("Provider Rate Manager") ?></h2>
    </div>
    <div class="report">
            <table class="table table-striped">
                <tr>
                    <th><?php echo xlt("User ID") ?></th>
                    <th><?php echo xlt("Provider Name") ?></th>
                    <th><?php echo xlt("Percentage Rate") ?></th>
                    <th><?php echo xlt("Flat Rate") ?></th>
                    <th><?php echo xlt("Update") ?></th>
                </tr>
            <?php
                while ($row = sqlFetchArray($providers)) {
                    $rate = $providerdata->retreiveRates($row['id']);
                    print "<tr><td>" . text($row['id']) . "</td>";
                    print "<td>" .  text($row['fname']) . " " . text($row['lname']) .
                        "<input type='hidden' name='userid' value='" .
                        text($row['id']) . "'></td>";
                    print "<td><input type='text' id='percent_" . text($row['id']) . "' value='";
                    if (!empty($rate['percentage'])) {
                        print text($rate['percentage']);
                    }
                    print "' name='percentage' onkeyup='togglePercentRate(" . text($row['id']) . ")'></td>";
                    print "<td><input type='text' id='flat_" . text($row['id']) . "' value='";
                     if (!empty($rate['flat'])) {
                        print text($rate['flat']);
                    }
                    print "' name='flat' onkeyup='toggleFlatRate(" . text($row['id']) . ")'></td>";
                    print "<td><button onclick='saveLine(" . text($row['id']) . ")' id='submit'>" . xlt("Update") . "</button></td>";
                    print "</tr>";
                }
            ?>
            </table>
    </div>
</div>
<script>
    function saveLine(id) {
        let percent = 'percent_' + id;
        let flat = 'flat_' + id;
        let flatValue = $("#"+flat).val();
        let percentValue = $("#"+percent).val();

        let url = '<?php echo $GLOBALS['webroot'] ?>/interface/modules/custom_modules/oe-module-payroll/public/provider_rate/postupdate.php?userid='+id+'&percent='+percentValue+'&flat='+flatValue;
        top.restoreSession();
        fetch(url
        ).then(res => {
            return res.text()
        })
        .then(data => alert(data))
        .catch(error => console.log(error))
    }

    function togglePercentRate(row) {
        let rowid = 'flat_' + row;
        let rowvalue = document.getElementById(rowid).value;
        if (rowvalue.length > 0) {
            document.getElementById(rowid).value = '0.00';
        }
    }

    function toggleFlatRate(row) {
        let rowid = 'percent_' + row;
        let rowvalue = document.getElementById(rowid).value;
        if (rowvalue.length > 0) {
            document.getElementById(rowid).value = '0.00';
        }
    }
</script>
</body>
</html>
