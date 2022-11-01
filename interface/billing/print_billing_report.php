<?php

/*
 * Print billing report.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Julia Longtin
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2012 Julia Longtin
 * @copyright Copyright (c) 2018-2020 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/forms.inc.php");
require_once("$srcdir/report.inc.php");

use OpenEMR\Billing\BillingReport;
use OpenEMR\Core\Header;

//how many columns to use when displaying information
$COLS = 6;

//global variables:
if (!isset($_GET["mode"])) {
    if (!isset($_GET["from_date"])) {
        $from_date = date("Y-m-d");
    } else {
        $from_date = $_GET["from_date"];
    }

    if (!isset($_GET["to_date"])) {
        $to_date = date("Y-m-d");
    } else {
        $to_date = $_GET["to_date"];
    }

    if (!isset($_GET["code_type"])) {
        $code_type = "all";
    } else {
        $code_type = $_GET["code_type"];
    }

    if (!isset($_GET["unbilled"])) {
        $unbilled = "on";
    } else {
        $unbilled = $_GET["unbilled"];
    }

    if (!isset($_GET["authorized"])) {
        $my_authorized = "on";
    } else {
        $my_authorized = $_GET["authorized"];
    }
} else {
    $from_date = $_GET["from_date"];
    $to_date = $_GET["to_date"];
    $code_type = $_GET["code_type"];
    $unbilled = $_GET["unbilled"];
    $my_authorized = $_GET["authorized"];
}

?>

<html>
<head>

<?php Header::setupHeader(); ?>

</head>
<body bgcolor="var(--white)" topmargin="0" rightmargin="0" leftmargin="2" bottommargin="0" marginwidth="2" marginheight="0">

<a href="javascript:window.close();" target="Main"><font class="title"><?php echo xlt('Billing Report')?></font></a>
<br />

<?php
if ($my_authorized == "on") {
    $my_authorized = 1;
} else {
    $my_authorized = "%";
}

if ($unbilled == "on") {
    $unbilled = "0";
} else {
    $unbilled = "%";
}

if ($code_type == "all") {
    $code_type = "%";
}

$list = BillingReport::getBillsListBetween($code_type);

if (!isset($_GET["mode"])) {
    if (!isset($_GET["from_date"])) {
        $from_date = date("Y-m-d");
    } else {
        $from_date = $_GET["from_date"];
    }

    if (!isset($_GET["to_date"])) {
        $to_date = date("Y-m-d");
    } else {
        $to_date = $_GET["to_date"];
    }

    if (!isset($_GET["code_type"])) {
        $code_type = "all";
    } else {
        $code_type = $_GET["code_type"];
    }

    if (!isset($_GET["unbilled"])) {
        $unbilled = "on";
    } else {
        $unbilled = $_GET["unbilled"];
    }

    if (!isset($_GET["authorized"])) {
        $my_authorized = "on";
    } else {
        $my_authorized = $_GET["authorized"];
    }
} else {
    $from_date = $_GET["from_date"];
    $to_date = $_GET["to_date"];
    $code_type = $_GET["code_type"];
    $unbilled = $_GET["unbilled"];
    $my_authorized = $_GET["authorized"];
}

if ($my_authorized == "on") {
    $my_authorized = 1;
} else {
    $my_authorized = "%";
}

if ($unbilled == "on") {
    $unbilled = "0";
} else {
    $unbilled = "%";
}

if ($code_type == "all") {
    $code_type = "%";
}

$list = BillingReport::getBillsListBetween($code_type);

if (isset($_GET["mode"]) && $_GET["mode"] == "bill") {
    BillingReport::billCodesList($list);
}

$res_count = 0;
$N = 1;

$itero = array();
if ($ret = BillingReport::getBillsBetweenReport($code_type)) {
    $old_pid = -1;
    $first_time = 1;
    $encid = 0;
    foreach ($ret as $iter) {
        if ($old_pid != $iter["pid"]) {
            $name = getPatientData($iter["pid"]);
            if (!$first_time) {
                print "</tr></table>\n";
                print "</td><td>";
                print "<table border='0'><tr>\n";   // small table
            } else {
                print "<table border='0'><tr>\n";     // small table
                $first_time = 0;
            }

            print "<tr><td colspan='5'><hr /><span class='font-weight-bold'>" . text($name["fname"]) . " " . text($name["lname"]) . "</span><br /><br />\n";
            //==================================


            print "<font class='font-weight-bold'>" . xlt("Patient Data") . ":</font><br />";
            printRecDataOne($patient_data_array, getRecPatientData($iter["pid"]), $COLS);

            print "<font class='font-weight-bold'>" . xlt("Employer Data") . ":</font><br />";
            printRecDataOne($employer_data_array, getRecEmployerData($iter["pid"]), $COLS);

            print "<font class='font-weight-bold'>" . xlt("Primary Insurance Data") . ":</font><br />";
            printRecDataOne($insurance_data_array, getRecInsuranceData($iter["pid"], "primary"), $COLS);

            print "<font class='font-weight-bold'>" . xlt("Secondary Insurance Data") . ":</font><br />";
            printRecDataOne($insurance_data_array, getRecInsuranceData($iter["pid"], "secondary"), $COLS);

            print "<font class='font-weight-bold'>" . xlt("Tertiary Insurance Data") . ":</font><br />";
            printRecDataOne($insurance_data_array, getRecInsuranceData($iter["pid"], "tertiary"), $COLS);

            //==================================
            print "</td></tr><tr>\n";
            $old_pid = $iter["pid"];
        }

        print "<td width='100'><span class='text'>" . text($iter["code_type"]) . ": </span></td><td width='100'><span class='text'>" . text($iter["code"]) . "</span></td><td width='100'><span class='small'>(" . text(date("Y-m-d", strtotime($iter["date"]))) . ")</span></td>\n";
        $res_count++;
        if ($res_count == $N) {
            print "</tr><tr>\n";
            $res_count = 0;
        }

        $itero = $iter;
    }

    print "</tr></table>\n"; // small table
}

?>
</body>
</html>
