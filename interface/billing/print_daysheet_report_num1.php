<?php

/*
 * interface/billing/print_daysheet_report.php Genetating an end of day report.
 *
 * Program for Generating an End of Day report
 *
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2014 Terry Hill <terry@lillysystems.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/daysheet.inc.php");

use OpenEMR\Billing\BillingReport;
use OpenEMR\Core\Header;

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
<body>
    <div class="container">
        <div class="row">
            <a href="javascript:window.close();" target="Main"><p class="title"><?php echo xlt('Day Sheet Report')?></p></a>
            <br />

            <?php
            if ($my_authorized === 'on') {
                $my_authorized = true;
            } else {
                $my_authorized = '%';
            }

            if ($unbilled === 'on') {
                $unbilled = '0';
            } else {
                $unbilled = '%';
            }

            if ($code_type === 'all') {
                $code_type = '%';
            }

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

            if ($my_authorized === 'on') {
                $my_authorized = true;
            } else {
                $my_authorized = '%';
            }

            if ($unbilled === 'on') {
                $unbilled = '0';
            } else {
                $unbilled = '%';
            }

            if ($code_type === 'all') {
                $code_type = '%';
            }

            if (isset($_GET["mode"]) && $_GET["mode"] === 'bill') {
                billCodesList($list);
            }

            $res_count = 0;
            $N = 1;
            $k = 1;
            $anypats = 0;
            $the_first_time = 1;
            $itero = array();

            if ($ret = getBillsBetweendayReport($code_type)) {
            // checking to see if there is any information in the array if not display a message (located after this if statment)
                $anypats = count($ret);
                $run_provider = 0;
                $old_pid = -1;
                $first_time = 1;
                $new_old_pid = -1;

            // $iter has encounter information

            // this loop gathers the user and provider numbers
                foreach ($ret as $iter) {
                    $catch_user[] = $iter['user'];
                    $catch_provider[] = $iter['provider_id'];
                }

            //This statment uniques the arrays removing duplicates

                $user_list = array_unique($catch_user);
                $provider_list = array_unique($catch_provider);

            // reorder the list starting with array element zero
                $user_final_list = array_values($user_list);
                $provider_final_list = array_values($provider_list);
            // sort array in assending order
                sort($user_final_list);
                sort($provider_final_list);
                $all4 = array_natsort($ret, 'pid', 'fulname', 'asc');

                if ($_POST['end_of_day_provider_only'] == 1) {
                    $run_provider = 1;
                }

                if ($_POST['end_of_day_totals_only'] == 1) {
                    $totals_only = 1;
                }

                foreach ($all4 as $iter) {
                    // Case statment to tally information by user
                    switch ($iter['user']) {
                        case $iter['user'] = $user_final_list[0]:
                            $us0_user = $iter['user'];
                            $us0_fee = $us0_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us0_inspay = $us0_inspay + $iter['ins_code'];
                            }

                            $us0_insadj = $us0_insadj + $iter['ins_adjust_dollar'];
                            $us0_patadj = $us0_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us0_patpay = $us0_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us0_insref = $us0_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us0_patref = $us0_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[1]:
                            $us1_user = $iter['user'];
                            $us1_fee = $us1_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us1_inspay = $us1_inspay + $iter['ins_code'];
                            }

                            $us1_insadj = $us1_insadj + $iter['ins_adjust_dollar'];
                            $us1_patadj = $us1_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us1_patpay = $us1_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us1_insref = $us1_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us1_patref = $us1_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[2]:
                            $us2_user = $iter['user'];
                            $us2_fee = $us2_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us2_inspay = $us2_inspay + $iter['ins_code'];
                            }

                            $us2_insadj = $us2_insadj + $iter['ins_adjust_dollar'];
                            $us2_patadj = $us2_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us2_patpay = $us2_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us2_insref = $us2_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us2_patref = $us2_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[3]:
                            $us3_user = $iter['user'];
                            $us3_fee = $us3_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us3_inspay = $us3_inspay + $iter['ins_code'];
                            }

                            $us3_insadj = $us3_insadj + $iter['ins_adjust_dollar'];
                            $us3_patadj = $us3_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us3_patpay = $us3_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us3_insref = $us3_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us3_patref = $us3_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[4]:
                            $us4_user = $iter['user'];
                            $us4_fee = $us4_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us4_inspay = $us4_inspay + $iter['ins_code'];
                            }

                            $us4_insadj = $us4_insadj + $iter['ins_adjust_dollar'];
                            $us4_patadj = $us4_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us4_patpay = $us4_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us4_insref = $us4_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us4_patref = $us4_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[5]:
                            $us5_user = $iter['user'];
                            $us5_fee = $us5_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us5_inspay = $us5_inspay + $iter['ins_code'];
                            }

                            $us5_insadj = $us5_insadj + $iter['ins_adjust_dollar'];
                            $us5_patadj = $us5_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us5_patpay = $us5_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us5_insref = $us5_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us5_patref = $us5_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[6]:
                            $us6_user = $iter['user'];
                            $us6_fee = $us6_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us6_inspay = $us6_inspay + $iter['ins_code'];
                            }

                            $us6_insadj = $us6_insadj + $iter['ins_adjust_dollar'];
                            $us6_patadj = $us6_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us6_patpay = $us6_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us6_insref = $us6_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us6_patref = $us6_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[7]:
                            $us7_user = $iter['user'];
                            $us7_fee = $us7_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us7_inspay = $us7_inspay + $iter['ins_code'];
                            }

                            $us7_insadj = $us7_insadj + $iter['ins_adjust_dollar'];
                            $us7_patadj = $us7_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us7_patpay = $us7_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us7_insref = $us7_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us7_patref = $us7_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[8]:
                            $us8_user = $iter['user'];
                            $us8_fee = $us8_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us8_inspay = $us8_inspay + $iter['ins_code'];
                            }

                            $us8_insadj = $us8_insadj + $iter['ins_adjust_dollar'];
                            $us8_patadj = $us8_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us8_patpay = $us8_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us8_insref = $us8_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us8_patref = $us8_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[9]:
                            $us9_user = $iter['user'];
                            $us9_fee = $us9_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us9_inspay = $us9_inspay + $iter['ins_code'];
                            }

                            $us9_insadj = $us9_insadj + $iter['ins_adjust_dollar'];
                            $us9_patadj = $us9_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us9_patpay = $us9_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us9_insref = $us9_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us9_patref = $us9_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[10]:
                            $us10_user = $iter['user'];
                            $us10_fee = $us10_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us10_inspay = $us10_inspay + $iter['ins_code'];
                            }

                            $us10_insadj = $us10_insadj + $iter['ins_adjust_dollar'];
                            $us10_patadj = $us10_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us10_patpay = $us10_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us10_insref = $us10_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us10_patref = $us10_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[11]:
                            $us11_user = $iter['user'];
                            $us11_fee = $us11_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us11_inspay = $us11_inspay + $iter['ins_code'];
                            }

                            $us11_insadj = $us11_insadj + $iter['ins_adjust_dollar'];
                            $us11_patadj = $us11_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us11_patpay = $us11_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us11_insref = $us11_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us11_patref = $us11_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[12]:
                            $us12_user = $iter['user'];
                            $us12_fee = $us12_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us12_inspay = $us12_inspay + $iter['ins_code'];
                            }

                            $us12_insadj = $us12_insadj + $iter['ins_adjust_dollar'];
                            $us12_patadj = $us12_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us12_patpay = $us12_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us12_insref = $us12_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us12_patref = $us12_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[13]:
                            $us13_user = $iter['user'];
                            $us13_fee = $us13_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us13_inspay = $us13_inspay + $iter['ins_code'];
                            }

                            $us13_insadj = $us13_insadj + $iter['ins_adjust_dollar'];
                            $us13_patadj = $us13_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us13_patpay = $us13_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us13_insref = $us13_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us13_patref = $us13_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[14]:
                            $us14_user = $iter['user'];
                            $us14_fee = $us14_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us14_inspay = $us14_inspay + $iter['ins_code'];
                            }

                            $us14_insadj = $us14_insadj + $iter['ins_adjust_dollar'];
                            $us14_patadj = $us14_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us14_patpay = $us14_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us14_insref = $us14_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us14_patref = $us14_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[15]:
                            $us15_user = $iter['user'];
                            $us15_fee = $us15_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us15_inspay = $us15_inspay + $iter['ins_code'];
                            }

                            $us15_insadj = $us15_insadj + $iter['ins_adjust_dollar'];
                            $us15_patadj = $us15_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us15_patpay = $us15_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us15_insref = $us15_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us15_patref = $us15_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[16]:
                            $us16_user = $iter['user'];
                            $us16_fee = $us16_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us16_inspay = $us16_inspay + $iter['ins_code'];
                            }

                            $us16_insadj = $us16_insadj + $iter['ins_adjust_dollar'];
                            $us16_patadj = $us16_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us16_patpay = $us16_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us16_insref = $us16_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us16_patref = $us16_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[17]:
                            $us17_user = $iter['user'];
                            $us17_fee = $us17_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us17_inspay = $us17_inspay + $iter['ins_code'];
                            }

                            $us17_insadj = $us17_insadj + $iter['ins_adjust_dollar'];
                            $us17_patadj = $us17_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us17_patpay = $us17_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us17_insref = $us17_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us17_patref = $us17_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $user_final_list[18]:
                            $us18_user = $iter['user'];
                            $us18_fee = $us18_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us18_inspay = $us18_inspay + $iter['ins_code'];
                            }

                            $us18_insadj = $us18_insadj + $iter['ins_adjust_dollar'];
                            $us18_patadj = $us18_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us18_patpay = $us18_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us18_insref = $us18_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us18_patref = $us18_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['user'] = $fuser_final_list[19]:
                            $us19_user = $iter['user'];
                            $us19_fee = $us19_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us19_inspay = $us19_inspay + $iter['ins_code'];
                            }

                            $us19_insadj = $us19_insadj + $iter['ins_adjust_dollar'];
                            $us19_patadj = $us19_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $us19_patpay = $us19_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $us19_insref = $us19_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $us19_patref = $us19_patref + $iter['pat_code'];
                            }
                            break;
                    }

                    // Case statment to tally information by Provider
                    switch ($iter['provider_id']) {
                        case $iter['provider_id'] = $provider_final_list[0]:
                            $pro0_user = $iter['provider_id'];
                            $pro0_fee = $pro0_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro0_inspay = $pro0_inspay + $iter['ins_code'];
                            }

                                $pro0_insadj = $pro0_insadj + $iter['ins_adjust_dollar'];
                                $pro0_patadj = $pro0_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro0_patpay = $pro0_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro0_insref = $pro0_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro0_patref = $pro0_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[1]:
                            $pro1_user = $iter['provider_id'];
                            $pro1_fee = $pro1_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro1_inspay = $pro1_inspay + $iter['ins_code'];
                            }

                            $pro1_insadj = $pro1_insadj + $iter['ins_adjust_dollar'];
                            $pro1_patadj = $pro1_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro1_patpay = $pro1_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro1_insref = $pro1_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro1_patref = $pro1_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[2]:
                            $pro2_user = $iter['provider_id'];
                            $pro2_fee = $pro2_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro2_inspay = $pro2_inspay + $iter['ins_code'];
                            }

                            $pro2_insadj = $pro2_insadj + $iter['ins_adjust_dollar'];
                            $pro2_patadj = $pro2_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro2_patpay = $pro2_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro2_insref = $pro2_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro2_patref = $pro2_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[3]:
                            $pro3_user = $iter['provider_id'];
                            $pro3_fee = $pro3_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro3_inspay = $pro3_inspay + $iter['ins_code'];
                            }

                            $pro3_insadj = $pro3_insadj + $iter['ins_adjust_dollar'];
                            $pro3_patadj = $pro3_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro3_patpay = $pro3_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro3_insref = $pro3_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro3_patref = $pro3_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[4]:
                            $pro4_user = $iter['provider_id'];
                            $pro4_fee = $pro4_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro4_inspay = $pro4_inspay + $iter['ins_code'];
                            }

                            $pro4_insadj = $pro4_insadj + $iter['ins_adjust_dollar'];
                            $pro4_patadj = $pro4_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro4_patpay = $pro4_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro4_insref = $pro4_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro4_patref = $pro4_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[5]:
                            $pro5_user = $iter['provider_id'];
                            $pro5_fee = $pro5_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro5_inspay = $pro5_inspay + $iter['ins_code'];
                            }

                            $pro5_insadj = $pro5_insadj + $iter['ins_adjust_dollar'];
                            $pro5_patadj = $pro5_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                    $pro5_patpay = $pro5_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro5_insref = $pro5_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro5_patref = $pro5_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[6]:
                            $pro6_user = $iter['provider_id'];
                            $pro6_fee = $pro6_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro6_inspay = $pro6_inspay + $iter['ins_code'];
                            }

                            $pro6_insadj = $pro6_insadj + $iter['ins_adjust_dollar'];
                            $pro6_patadj = $pro6_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro6_patpay = $pro6_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro6_insref = $pro6_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro6_patref = $pro6_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[7]:
                            $pro7_user = $iter['provider_id'];
                            $pro7_fee = $pro7_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro7_inspay = $pro7_inspay + $iter['ins_code'];
                            }

                            $pro7_insadj = $pro7_insadj + $iter['ins_adjust_dollar'];
                            $pro7_patadj = $pro7_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro7_patpay = $pro7_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro7_insref = $pro7_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro7_patref = $pro7_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[8]:
                            $pro8_user = $iter['provider_id'];
                            $pro8_fee = $pro8_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro8_inspay = $pro8_inspay + $iter['ins_code'];
                            }

                            $pro8_insadj = $pro8_insadj + $iter['ins_adjust_dollar'];
                            $pro8_patadj = $pro8_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro8_patpay = $pro8_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro8_insref = $pro8_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro8_patref = $pro8_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[9]:
                            $pro9_user = $iter['provider_id'];
                            $pro9_fee = $pro9_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro9_inspay = $pro9_inspay + $iter['ins_code'];
                            }

                            $pro9_insadj = $pro9_insadj + $iter['ins_adjust_dollar'];
                            $pro9_patadj = $pro9_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro9_patpay = $pro9_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro9_insref = $pro9_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro9_patref = $pro9_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[10]:
                            $pro10_user = $iter['provider_id'];
                            $pro10_fee = $pro10_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro10_inspay = $pro0_inspay + $iter['ins_code'];
                            }

                            $pro10_insadj = $pro10_insadj + $iter['ins_adjust_dollar'];
                            $pro10_patadj = $pro10_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro10_patpay = $pro10_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro10_insref = $pro10_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro10_patref = $pro10_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[11]:
                            $pro11_user = $iter['provider_id'];
                            $pro11_fee = $pro11_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro11_inspay = $pro11_inspay + $iter['ins_code'];
                            }

                            $pro11_insadj = $pro11_insadj + $iter['ins_adjust_dollar'];
                            $pro11_patadj = $pro11_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro11_patpay = $pro11_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro11_insref = $pro11_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro11_patref = $pro11_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[12]:
                            $pro12_user = $iter['provider_id'];
                            $pro12_fee = $pro12_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro12_inspay = $pro12_inspay + $iter['ins_code'];
                            }

                            $pro12_insadj = $pro12_insadj + $iter['ins_adjust_dollar'];
                            $pro12_patadj = $pro12_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro12_patpay = $pro12_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro12_insref = $pro12_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro12_patref = $pro12_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[13]:
                            $pro13_user = $iter['provider_id'];
                            $pro13_fee = $pro13_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro13_inspay = $pro13_inspay + $iter['ins_code'];
                            }

                            $pro13_insadj = $pro13_insadj + $iter['ins_adjust_dollar'];
                            $pro13_patadj = $pro13_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro13_patpay = $pro13_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro13_insref = $pro13_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro13_patref = $pro13_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[14]:
                            $pro14_user = $iter['provider_id'];
                            $pro14_fee = $pro14_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro14_inspay = $pro14_inspay + $iter['ins_code'];
                            }

                            $pro14_insadj = $pro14_insadj + $iter['ins_adjust_dollar'];
                            $pro14_patadj = $pro14_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro14_patpay = $pro14_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro14_insref = $pro14_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro14_patref = $pro14_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[15]:
                            $pro15_user = $iter['provider_id'];
                            $pro15_fee = $pro15_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro15_inspay = $pro15_inspay + $iter['ins_code'];
                            }

                            $pro15_insadj = $pro15_insadj + $iter['ins_adjust_dollar'];
                            $pro15_patadj = $pro15_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro15_patpay = $pro15_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro15_insref = $pro15_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro15_patref = $pro15_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[16]:
                            $pro16_user = $iter['provider_id'];
                            $pro16_fee = $pro16_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro16_inspay = $pro16_inspay + $iter['ins_code'];
                            }

                            $pro16_insadj = $pro16_insadj + $iter['ins_adjust_dollar'];
                            $pro16_patadj = $pro16_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro16_patpay = $pro16_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro16_insref = $pro16_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro16_patref = $pro16_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[17]:
                            $pro17_user = $iter['provider_id'];
                            $pro17_fee = $pro17_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro17_inspay = $pro17_inspay + $iter['ins_code'];
                            }

                            $pro17_insadj = $pro17_insadj + $iter['ins_adjust_dollar'];
                            $pro17_patadj = $pro17_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro17_patpay = $pro17_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro17_insref = $pro17_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro17_patref = $pro17_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[18]:
                            $pro18_user = $iter['provider_id'];
                            $pro18_fee = $pro18_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro18_inspay = $pro18_inspay + $iter['ins_code'];
                            }

                            $pro18_insadj = $pro18_insadj + $iter['ins_adjust_dollar'];
                            $pro18_patadj = $pro18_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro18_patpay = $pro18_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro18_insref = $pro18_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro18_patref = $pro18_patref + $iter['pat_code'];
                            }
                            break;
                        case $iter['provider_id'] = $provider_final_list[19]:
                            $pro19_user = $iter['provider_id'];
                            $pro19_fee = $pro19_fee + $iter['fee'];
                            if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro19_inspay = $pro19_inspay + $iter['ins_code'];
                            }

                            $pro19_insadj = $pro19_insadj + $iter['ins_adjust_dollar'];
                            $pro19_patadj = $pro19_patadj + $iter['pat_adjust_dollar'];
                            if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment') {
                                $pro19_patpay = $pro19_patpay + $iter['pat_code'];
                            }

                            if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                                $pro19_insref = $pro19_insref + $iter['ins_code'];
                            }

                            if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                                $pro19_patref = $pro19_patref + $iter['pat_code'];
                            }
                            break;
                    }

                    if ($the_first_time === 1) {
                        $user = $iter['user'];
                        $new_old_pid = $iter['pid'];
                        $the_first_time = 0;
                    }

                    if ($totals_only != 1) {
                        if ($old_pid != $iter['pid'] and ($iter['code_type'] != 'payment_info')) {
                            if ($old_pid === $new_old_pid) {
                                $line_total = 0;
                                $line_total_pay = 0;
                            }

                            if ($first_time) {
                                print "<div class='table-responsive'><table class='table'>";     // small table
                                $first_time = 0;
                            } ?>

                            <!-- TODO: Further replace with classes -->
                            <tr>
                                <td class='text text-center font-weight-bold' width='70'>
                                    <?php echo xlt("Date"); ?>
                                </td>
                                <td class='text text-center font-weight-bold' width='50'>
                                    <?php echo xlt("Acct"); ?>#
                                </td>
                                <td class='text text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Name"); ?>
                                </td>
                                <td class='text text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Source"); ?>
                                </td>
                                <td class='text text-center font-weight-bold' width='100'>
                                    <?php echo xlt("CPT"); ?>
                                </td>
                                <td class='small text-center font-weight-bold' width='100'>
                                    <?php echo xlt("ICD"); ?>
                                </td>
                                <td class='small text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Charges"); ?>
                                </td>
                                <td class='small text-center font-weight-bold' width='100'>
                                    <?php echo xlt("Payments") . '/' . xlt("Adj"); ?>.
                                </td>
                            </tr>
                            <?php
                            //Next patient
                            $old_pid = $iter['pid'];
                        }

                        // get dollar amounts to appear on pat,ins payments and copays

                        if ($iter['code_type'] != 'payment_info') {
                            if ($iter['code_type'] === 'COPAY' || $iter['code_type'] === 'Patient Payment' || $iter['code_type'] === 'Insurance Payment') { ?>
                                <tr>
                                    <td class='text text-center' width='70'>
                                        <?php echo text(date("Y-m-d", strtotime($iter['date']))); ?>
                                    </td>
                                    <td class='text text-center' width='50'>
                                        <?php echo text($iter['pid']); ?>
                                    </td>
                                    <td class='text text-center' width='180'>
                                        <?php echo text($iter['last']) . ", " . text($iter['first']) ?>
                                    </td>

                                <?php if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Insurance Adjustment'); ?>
                                    </td>
                                <?php } ?>

                                <?php
                                if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Patient Adjustment'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Insurance Payment'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Patient Payment'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Insurance Credit'); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('Patient Credit'); ?>
                                    </td>
                                <?php } ?>

                                <?php if ($iter['paytype'] === 'PCP') { ?>
                                    <td class='text' width='180'>
                                        <?php echo xlt('COPAY'); ?>
                                    </td>
                                <?php } ?>

                                <td class='text' width='100'>
                                </td>
                                <td class='text' width='100'>
                                </td>
                                <td class='text' width='100'>
                                </td>

                                <?php if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                                    $line_total_pay = $line_total_pay + $iter['ins_adjust_dollar']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['ins_adjust_dollar']) ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['ins_code']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                                    $line_total_pay = $line_total_pay + $iter['ins_code']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['ins_code']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['code_type']) != 'Patient Payment' and ($iter['code_type']) != 'Insurance Payment') {
                                    $line_total_pay = $line_total_pay + $iter['code']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['code']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                                    $line_total_pay = $line_total_pay + $iter['pat_adjust_dollar']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['pat_adjust_dollar']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['pat_code']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                                    $line_total_pay = $line_total_pay + $iter['pat_code']; ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['pat_code']); ?>
                                    </td>
                                <?php } ?>

                                <?php if (($iter['code_type']) != 'Insurance Payment' and ($iter['code_type']) != 'Patient Payment' and $iter['paytype'] != 'PCP') { ?>
                                    <td class='text' width='100'>
                                        <?php echo text($iter['code_type']); ?>
                                    </td>
                                <?php } ?>

                                <td class='text' width='100'>
                                </td>

                            <?php } else { ?>
                                    <?php if ($iter['fee'] != 0) {
                                        $line_total = $line_total + $iter['fee']; ?>
                                        <td class='text' width='70'>
                                            <?php echo text(date("Y-m-d", strtotime($iter['date']))); ?>
                                        </td>
                                        <td class='text' width='50'>
                                            <?php echo text($iter['pid']); ?>
                                        </td>
                                        <td class='text' width='180'>
                                            <?php echo text($iter['last']) . ", " . text($iter['first']); ?>
                                        </td>

                                        <?php if ($GLOBALS['language_default'] === 'English (Standard)') { ?>
                                            <td class='text' width='100'>
                                                <?php echo text(ucwords(strtolower(substr($iter['code_text'], 0, 25)))); ?>
                                            </td>
                                        <?php } else { ?>
                                            <td class='text' width='100'>
                                                <?php echo text(substr($iter['code_text'], 0, 25)); ?>
                                            </td>
                                        <?php } ?>

                                        <td class='text' width='100'>
                                            <?php echo text($iter['code']); ?>
                                        </td>
                                        <td class='small' width='100'>
                                            <?php echo text(substr($iter['justify'], 5, 3)); ?>
                                        </td>
                                        <td class='small' width='100'>
                                            <?php echo text($iter['fee']); ?>
                                        </td>
                                            <?php
                                    }
                            }

                            if ($iter['code_type'] === 'COPAY' || $iter['code_type'] === 'Patient Payment' || $iter['code_type'] === 'Insurance Payment' || $iter['fee'] != 0) {
                                $res_count++;
                            }

                            if ($res_count === $N) {
                                print "</tr><tr>\n";
                                $res_count = 0;
                            }

                            $itero = $iter;

                            if ($old_pid != $new_old_pid and ($iter['code_type'] != 'payment_info')) {
                                $new_old_pid = $old_pid;
                            }
                        }
                    }

                    // end totals only
                }

            // end for
            }


            if ($anypats === 0) {
                ?><p><?php echo xlt('No Data to Process')?></p><?php
            }

            // TEST TO SEE IF THERE IS INFORMATION IN THE VARAIBLES THEN ADD TO AN ARRAY FOR PRINTING
            if ($run_provider != 1) {
                if ($us0_fee != 0 || $us0_inspay != 0 || $us0_insadj != 0 || $us0_patadj != 0 || $us0_patpay != 0 || $us0_insref != 0 || $us0_patref != 0) {
                    $user_info['user'][$k] = $us0_user;
                    $user_info['fee'][$k]  = $us0_fee;
                    $user_info['inspay'][$k]  = $us0_inspay;
                    $user_info['insadj'][$k]  = $us0_insadj;
                    $user_info['insref'][$k]  = $us0_insref;
                    $user_info['patadj'][$k]  = $us0_patadj;
                    $user_info['patpay'][$k]  = $us0_patpay;
                    $user_info['patref'][$k]  = $us0_patref;
                    ++$k;
                }

                if ($us1_fee != 0 || $us1_inspay != 0 || $us1_insadj != 0 || $us1_patadj != 0 || $us1_patpay != 0 || $us1_insref != 0 || $us1_patref != 0) {
                    $user_info['user'][$k] = $us1_user;
                    $user_info['fee'][$k]  = $us1_fee;
                    $user_info['inspay'][$k]  = $us1_inspay;
                    $user_info['insadj'][$k]  = $us1_insadj;
                    $user_info['insref'][$k]  = $us1_insref;
                    $user_info['patadj'][$k]  = $us1_patadj;
                    $user_info['patpay'][$k]  = $us1_patpay;
                    $user_info['patref'][$k]  = $us1_patref;
                    ++$k;
                }

                if ($us2_fee != 0 || $us2_inspay != 0 || $us2_insadj != 0 || $us2_patadj != 0 || $us2_patpay != 0 || $us2_insref != 0 || $us2_patref != 0) {
                    $user_info['user'][$k] = $us2_user;
                    $user_info['fee'][$k]  = $us2_fee;
                    $user_info['inspay'][$k]  = $us2_inspay;
                    $user_info['insadj'][$k]  = $us2_insadj;
                    $user_info['insref'][$k]  = $us2_insref;
                    $user_info['patadj'][$k]  = $us2_patadj;
                    $user_info['patpay'][$k]  = $us2_patpay;
                    $user_info['patref'][$k]  = $us2_patref;
                    ++$k;
                }

                if ($us3_fee != 0 || $us3_inspay != 0 || $us3_insadj != 0 || $us3_patadj != 0 || $us3_patpay != 0 || $us3_insref != 0 || $us3_patref != 0) {
                    $user_info['user'][$k] = $us3_user;
                    $user_info['fee'][$k]  = $us3_fee;
                    $user_info['inspay'][$k]  = $us3_inspay;
                    $user_info['insadj'][$k]  = $us3_insadj;
                    $user_info['insref'][$k]  = $us3_insref;
                    $user_info['patadj'][$k]  = $us3_patadj;
                    $user_info['patpay'][$k]  = $us3_patpay;
                    $user_info['patref'][$k]  = $us3_patref;
                    ++$k;
                }

                if ($us4_fee != 0 || $us4_inspay != 0 || $us4_insadj != 0 || $us4_patadj != 0 || $us4_patpay != 0 || $us4_insref != 0 || $us4_patref != 0) {
                    $user_info['user'][$k] = $us4_user;
                    $user_info['fee'][$k]  = $us4_fee;
                    $user_info['inspay'][$k]  = $us4_inspay;
                    $user_info['insadj'][$k]  = $us4_insadj;
                    $user_info['insref'][$k]  = $us4_insref;
                    $user_info['patadj'][$k]  = $us4_patadj;
                    $user_info['patpay'][$k]  = $us4_patpay;
                    $user_info['patref'][$k]  = $us4_patref;
                    ++$k;
                }

                if ($us5_fee != 0 || $us5_inspay != 0 || $us5_insadj != 0 || $us5_patadj != 0 || $us5_patpay != 0 || $us5_insref != 0 || $us5_patref != 0) {
                    $user_info['user'][$k] = $us5_user;
                    $user_info['fee'][$k]  = $us5_fee;
                    $user_info['inspay'][$k]  = $us5_inspay;
                    $user_info['insadj'][$k]  = $us5_insadj;
                    $user_info['insref'][$k]  = $us5_insref;
                    $user_info['patadj'][$k]  = $us5_patadj;
                    $user_info['patpay'][$k]  = $us5_patpay;
                    $user_info['patref'][$k]  = $us5_patref;
                    ++$k;
                }

                if ($us6_fee != 0 || $us6_inspay != 0 || $us6_insadj != 0 || $us6_patadj != 0 || $us6_patpay != 0 || $us6_insref != 0 || $us6_patref != 0) {
                    $user_info['user'][$k] = $us6_user;
                    $user_info['fee'][$k]  = $us6_fee;
                    $user_info['inspay'][$k]  = $us6_inspay;
                    $user_info['insadj'][$k]  = $us6_insadj;
                    $user_info['insref'][$k]  = $us6_insref;
                    $user_info['patadj'][$k]  = $us6_patadj;
                    $user_info['patpay'][$k]  = $us6_patpay;
                    $user_info['patref'][$k]  = $us6_patref;
                    ++$k;
                }

                if ($us7_fee != 0 || $us7_inspay != 0 || $us7_insadj != 0 || $us7_patadj != 0 || $us7_patpay != 0 || $us7_insref != 0 || $us7_patref != 0) {
                    $user_info['user'][$k] = $us7_user;
                    $user_info['fee'][$k]  = $us7_fee;
                    $user_info['inspay'][$k]  = $us7_inspay;
                    $user_info['insadj'][$k]  = $us7_insadj;
                    $user_info['insref'][$k]  = $us7_insref;
                    $user_info['patadj'][$k]  = $us7_patadj;
                    $user_info['patpay'][$k]  = $us7_patpay;
                    $user_info['patref'][$k]  = $us7_patref;
                    ++$k;
                }

                if ($us8_fee != 0 || $us8_inspay != 0 || $us8_insadj != 0 || $us8_patadj != 0 || $us8_patpay != 0 || $us8_insref != 0 || $us8_patref != 0) {
                    $user_info['user'][$k] = $us8_user;
                    $user_info['fee'][$k]  = $us8_fee;
                    $user_info['inspay'][$k]  = $us8_inspay;
                    $user_info['insadj'][$k]  = $us8_insadj;
                    $user_info['insref'][$k]  = $us8_insref;
                    $user_info['patadj'][$k]  = $us8_patadj;
                    $user_info['patpay'][$k]  = $us8_patpay;
                    $user_info['patref'][$k]  = $us8_patref;
                    ++$k;
                }

                if ($us9_fee != 0 || $us9_inspay != 0 || $us9_insadj != 0 || $us9_patadj != 0 || $us9_patpay != 0 || $us9_insref != 0 || $us9_patref != 0) {
                    $user_info['user'][$k] = $us9_user;
                    $user_info['fee'][$k]  = $us9_fee;
                    $user_info['inspay'][$k]  = $us9_inspay;
                    $user_info['insadj'][$k]  = $us9_insadj;
                    $user_info['insref'][$k]  = $us9_insref;
                    $user_info['patadj'][$k]  = $us9_patadj;
                    $user_info['patpay'][$k]  = $us9_patpay;
                    $user_info['patref'][$k]  = $us9_patref;
                    ++$k;
                }

                if ($us10_fee != 0 || $us10_inspay != 0 || $us10_insadj != 0 || $us10_patadj != 0 || $us10_patpay != 0 || $us10_insref != 0 || $us10_patref != 0) {
                    $user_info['user'][$k] = $us10_user;
                    $user_info['fee'][$k]  = $us10_fee;
                    $user_info['inspay'][$k]  = $us10_inspay;
                    $user_info['insadj'][$k]  = $us10_insadj;
                    $user_info['insref'][$k]  = $us10_insref;
                    $user_info['patadj'][$k]  = $us10_patadj;
                    $user_info['patpay'][$k]  = $us10_patpay;
                    $user_info['patref'][$k]  = $us10_patref;
                    ++$k;
                }

                if ($us11_fee != 0 || $us11_inspay != 0 || $us11_insadj != 0 || $us11_patadj != 0 || $us11_patpay != 0 || $us11_insref != 0 || $us11_patref != 0) {
                    $user_info['user'][$k] = $us11_user;
                    $user_info['fee'][$k]  = $us11_fee;
                    $user_info['inspay'][$k]  = $us11_inspay;
                    $user_info['insadj'][$k]  = $us11_insadj;
                    $user_info['insref'][$k]  = $us11_insref;
                    $user_info['patadj'][$k]  = $us11_patadj;
                    $user_info['patpay'][$k]  = $us11_patpay;
                    $user_info['patref'][$k]  = $us11_patref;
                    ++$k;
                }

                if ($us12_fee != 0 || $us12_inspay != 0 || $us12_insadj != 0 || $us12_patadj != 0 || $us12_patpay != 0 || $us12_insref != 0 || $us12_patref != 0) {
                    $user_info['user'][$k] = $us12_user;
                    $user_info['fee'][$k]  = $us12_fee;
                    $user_info['inspay'][$k]  = $us12_inspay;
                    $user_info['insadj'][$k]  = $us12_insadj;
                    $user_info['insref'][$k]  = $us12_insref;
                    $user_info['patadj'][$k]  = $us12_patadj;
                    $user_info['patpay'][$k]  = $us12_patpay;
                    $user_info['patref'][$k]  = $us12_patref;
                    ++$k;
                }

                if ($us13_fee != 0 || $us13_inspay != 0 || $us13_insadj != 0 || $us13_patadj != 0 || $us13_patpay != 0 || $us13_insref != 0 || $us13_patref != 0) {
                    $user_info['user'][$k] = $us13_user;
                    $user_info['fee'][$k]  = $us13_fee;
                    $user_info['inspay'][$k]  = $us13_inspay;
                    $user_info['insadj'][$k]  = $us13_insadj;
                    $user_info['insref'][$k]  = $us13_insref;
                    $user_info['patadj'][$k]  = $us13_patadj;
                    $user_info['patpay'][$k]  = $us13_patpay;
                    $user_info['patref'][$k]  = $us13_patref;
                    ++$k;
                }

                if ($us14_fee != 0 || $us14_inspay != 0 || $us14_insadj != 0 || $us14_patadj != 0 || $us14_patpay != 0 || $us14_insref != 0 || $us14_patref != 0) {
                    $user_info['user'][$k] = $us14_user;
                    $user_info['fee'][$k]  = $us14_fee;
                    $user_info['inspay'][$k]  = $us14_inspay;
                    $user_info['insadj'][$k]  = $us14_insadj;
                    $user_info['insref'][$k]  = $us14_insref;
                    $user_info['patadj'][$k]  = $us14_patadj;
                    $user_info['patpay'][$k]  = $us14_patpay;
                    $user_info['patref'][$k]  = $us14_patref;
                    ++$k;
                }

                if ($us15_fee != 0 || $us15_inspay != 0 || $us15_insadj != 0 || $us15_patadj != 0 || $us15_patpay != 0 || $us15_insref != 0 || $us15_patref != 0) {
                    $user_info['user'][$k] = $us15_user;
                    $user_info['fee'][$k]  = $us15_fee;
                    $user_info['inspay'][$k]  = $us15_inspay;
                    $user_info['insadj'][$k]  = $us15_insadj;
                    $user_info['insref'][$k]  = $us15_insref;
                    $user_info['patadj'][$k]  = $us15_patadj;
                    $user_info['patpay'][$k]  = $us15_patpay;
                    $user_info['patref'][$k]  = $us15_patref;
                    ++$k;
                }

                if ($us16_fee != 0 || $us16_inspay != 0 || $us16_insadj != 0 || $us16_patadj != 0 || $us16_patpay != 0 || $us16_insref != 0 || $us16_patref != 0) {
                    $user_info['user'][$k] = $us16_user;
                    $user_info['fee'][$k]  = $us16_fee;
                    $user_info['inspay'][$k]  = $us16_inspay;
                    $user_info['insadj'][$k]  = $us16_insadj;
                    $user_info['insref'][$k]  = $us16_insref;
                    $user_info['patadj'][$k]  = $us16_patadj;
                    $user_info['patpay'][$k]  = $us16_patpay;
                    $user_info['patref'][$k]  = $us16_patref;
                    ++$k;
                }

                if ($us17_fee != 0 || $us17_inspay != 0 || $us17_insadj != 0 || $us17_patadj != 0 || $us17_patpay != 0 || $us17_insref != 0 || $us17_patref != 0) {
                    $user_info['user'][$k] = $us17_user;
                    $user_info['fee'][$k]  = $us17_fee;
                    $user_info['inspay'][$k]  = $us17_inspay;
                    $user_info['insadj'][$k]  = $us17_insadj;
                    $user_info['insref'][$k]  = $us17_insref;
                    $user_info['patadj'][$k]  = $us17_patadj;
                    $user_info['patpay'][$k]  = $us17_patpay;
                    $user_info['patref'][$k]  = $us17_patref;
                    ++$k;
                }

                if ($us18_fee != 0 || $us18_inspay != 0 || $us18_insadj != 0 || $us18_patadj != 0 || $us18_patpay != 0 || $us18_insref != 0 || $us18_patref != 0) {
                    $user_info['user'][$k] = $us18_user;
                    $user_info['fee'][$k]  = $us18_fee;
                    $user_info['inspay'][$k]  = $us18_inspay;
                    $user_info['insadj'][$k]  = $us18_insadj;
                    $user_info['insref'][$k]  = $us18_insref;
                    $user_info['patadj'][$k]  = $us18_patadj;
                    $user_info['patpay'][$k]  = $us18_patpay;
                    $user_info['patref'][$k]  = $us18_patref;
                    ++$k;
                }

                if ($us19_fee != 0 || $us19_inspay != 0 || $us19_insadj != 0 || $us19_patadj != 0 || $us19_patpay != 0 || $us19_insref != 0 || $us19_patref != 0) {
                    $user_info['user'][$k] = $us19_user;
                    $user_info['fee'][$k]  = $us19_fee;
                    $user_info['inspay'][$k]  = $us19_inspay;
                    $user_info['insadj'][$k]  = $us19_insadj;
                    $user_info['insref'][$k]  = $us19_insref;
                    $user_info['patadj'][$k]  = $us19_patadj;
                    $user_info['patpay'][$k]  = $us19_patpay;
                    $user_info['patref'][$k]  = $us19_patref;
                    ++$k;
                }
            }

            if ($run_provider === 1) {
                if ($pro0_fee != 0 || $pro0_inspay != 0 || $pro0_insadj != 0 || $pro0_patadj != 0 || $pro0_patpay != 0 || $pro0_insref != 0 || $pro0_patref != 0) {
                    $provider_info['user'][$k] = $pro0_user;
                    $provider_info['fee'][$k]  = $pro0_fee;
                    $provider_info['inspay'][$k]  = $pro0_inspay;
                    $provider_info['insadj'][$k]  = $pro0_insadj;
                    $provider_info['insref'][$k]  = $pro0_insref;
                    $provider_info['patadj'][$k]  = $pro0_patadj;
                    $provider_info['patpay'][$k]  = $pro0_patpay;
                    $provider_info['patref'][$k]  = $pro0_patref;
                    ++$k;
                }

                if ($pro1_fee != 0 || $pro1_inspay != 0 || $pro1_insadj != 0 || $pro1_patadj != 0 || $pro1_patpay != 0 || $pro1_insref != 0 || $pro1_patref != 0) {
                    $provider_info['user'][$k] = $pro1_user;
                    $provider_info['fee'][$k]  = $pro1_fee;
                    $provider_info['inspay'][$k]  = $pro1_inspay;
                    $provider_info['insadj'][$k]  = $pro1_insadj;
                    $provider_info['insref'][$k]  = $pro1_insref;
                    $provider_info['patadj'][$k]  = $pro1_patadj;
                    $provider_info['patpay'][$k]  = $pro1_patpay;
                    $provider_info['patref'][$k]  = $pro1_patref;
                    ++$k;
                }

                if ($pro2_fee != 0 || $pro2_inspay != 0 || $pro2_insadj != 0 || $pro2_patadj != 0 || $pro2_patpay != 0 || $pro2_insref != 0 || $pro2_patref != 0) {
                    $provider_info['user'][$k] = $pro2_user;
                    $provider_info['fee'][$k]  = $pro2_fee;
                    $provider_info['inspay'][$k]  = $pro2_inspay;
                    $provider_info['insadj'][$k]  = $pro2_insadj;
                    $provider_info['insref'][$k]  = $pro2_insref;
                    $provider_info['patadj'][$k]  = $pro2_patadj;
                    $provider_info['patpay'][$k]  = $pro2_patpay;
                    $provider_info['patref'][$k]  = $pro2_patref;
                    ++$k;
                }

                if ($pro3_fee != 0 || $pro3_inspay != 0 || $pro3_insadj != 0 || $pro3_patadj != 0 || $pro3_patpay != 0 || $pro3_insref != 0 || $pro3_patref != 0) {
                    $provider_info['user'][$k] = $pro3_user;
                    $provider_info['fee'][$k]  = $pro3_fee;
                    $provider_info['inspay'][$k]  = $pro3_inspay;
                    $provider_info['insadj'][$k]  = $pro3_insadj;
                    $provider_info['insref'][$k]  = $pro3_insref;
                    $provider_info['patadj'][$k]  = $pro3_patadj;
                    $provider_info['patpay'][$k]  = $pro3_patpay;
                    $provider_info['patref'][$k]  = $pro3_patref;
                    ++$k;
                }

                if ($pro4_fee != 0 || $pro4_inspay != 0 || $pro4_insadj != 0 || $pro4_patadj != 0 || $pro4_patpay != 0 || $pro4_insref != 0 || $pro4_patref != 0) {
                    $provider_info['user'][$k] = $pro4_user;
                    $provider_info['fee'][$k]  = $pro4_fee;
                    $provider_info['inspay'][$k]  = $pro4_inspay;
                    $provider_info['insadj'][$k]  = $pro4_insadj;
                    $provider_info['insref'][$k]  = $pro4_insref;
                    $provider_info['patadj'][$k]  = $pro4_patadj;
                    $provider_info['patpay'][$k]  = $pro4_patpay;
                    $provider_info['patref'][$k]  = $pro4_patref;
                    ++$k;
                }

                if ($pro5_fee != 0 || $pro5_inspay != 0 || $pro5_insadj != 0 || $pro5_patadj != 0 || $pro5_patpay != 0 || $pro5_insref != 0 || $pro5_patref != 0) {
                    $provider_info['user'][$k] = $pro5_user;
                    $provider_info['fee'][$k]  = $pro5_fee;
                    $provider_info['inspay'][$k]  = $pro5_inspay;
                    $provider_info['insadj'][$k]  = $pro5_insadj;
                    $provider_info['insref'][$k]  = $pro5_insref;
                    $provider_info['patadj'][$k]  = $pro5_patadj;
                    $provider_info['patpay'][$k]  = $pro5_patpay;
                    $provider_info['patref'][$k]  = $pro5_patref;
                    ++$k;
                }

                if ($pro6_fee != 0 || $pro6_inspay != 0 || $pro6_insadj != 0 || $pro6_patadj != 0 || $pro6_patpay != 0 || $pro6_insref != 0 || $pro6_patref != 0) {
                    $provider_info['user'][$k] = $pro6_user;
                    $provider_info['fee'][$k]  = $pro6_fee;
                    $provider_info['inspay'][$k]  = $pro6_inspay;
                    $provider_info['insadj'][$k]  = $pro6_insadj;
                    $provider_info['insref'][$k]  = $pro6_insref;
                    $provider_info['patadj'][$k]  = $pro6_patadj;
                    $provider_info['patpay'][$k]  = $pro6_patpay;
                    $provider_info['patref'][$k]  = $pro6_patref;
                    ++$k;
                }

                if ($pro7_fee != 0 || $pro7_inspay != 0 || $pro7_insadj != 0 || $pro7_patadj != 0 || $pro7_patpay != 0 || $pro7_insref != 0 || $pro7_patref != 0) {
                    $provider_info['user'][$k] = $pro7_user;
                    $provider_info['fee'][$k]  = $pro7_fee;
                    $provider_info['inspay'][$k]  = $pro7_inspay;
                    $provider_info['insadj'][$k]  = $pro7_insadj;
                    $provider_info['insref'][$k]  = $pro7_insref;
                    $provider_info['patadj'][$k]  = $pro7_patadj;
                    $provider_info['patpay'][$k]  = $pro7_patpay;
                    $provider_info['patref'][$k]  = $pro7_patref;
                    ++$k;
                }

                if ($pro8_fee != 0 || $pro8_inspay != 0 || $pro8_insadj != 0 || $pro8_patadj != 0 || $pro8_patpay != 0 || $pro8_insref != 0 || $pro8_patref != 0) {
                    $provider_info['user'][$k] = $pro8_user;
                    $provider_info['fee'][$k]  = $pro8_fee;
                    $provider_info['inspay'][$k]  = $pro8_inspay;
                    $provider_info['insadj'][$k]  = $pro8_insadj;
                    $provider_info['insref'][$k]  = $pro8_insref;
                    $provider_info['patadj'][$k]  = $pro8_patadj;
                    $provider_info['patpay'][$k]  = $pro8_patpay;
                    $provider_info['patref'][$k]  = $pro8_patref;
                    ++$k;
                }

                if ($pro9_fee != 0 || $pro9_inspay != 0 || $pro9_insadj != 0 || $pro9_patadj != 0 || $pro9_patpay != 0 || $pro9_insref != 0 || $pro9_patref != 0) {
                    $provider_info['user'][$k] = $pro9_user;
                    $provider_info['fee'][$k]  = $pro9_fee;
                    $provider_info['inspay'][$k]  = $pro9_inspay;
                    $provider_info['insadj'][$k]  = $pro9_insadj;
                    $provider_info['insref'][$k]  = $pro9_insref;
                    $provider_info['patadj'][$k]  = $pro9_patadj;
                    $provider_info['patpay'][$k]  = $pro9_patpay;
                    $provider_info['patref'][$k]  = $pro9_patref;
                    ++$k;
                }

                if ($pro10_fee != 0 || $pro10_inspay != 0 || $pro10_insadj != 0 || $pro10_patadj != 0 || $pro10_patpay != 0 || $pro10_insref != 0 || $pro10_patref != 0) {
                    $provider_info['user'][$k] = $pro10_user;
                    $provider_info['fee'][$k]  = $pro10_fee;
                    $provider_info['inspay'][$k]  = $pro10_inspay;
                    $provider_info['insadj'][$k]  = $pro10_insadj;
                    $provider_info['insref'][$k]  = $pro10_insref;
                    $provider_info['patadj'][$k]  = $pro10_patadj;
                    $provider_info['patpay'][$k]  = $pro10_patpay;
                    $provider_info['patref'][$k]  = $pro10_patref;
                    ++$k;
                }

                if ($pro11_fee != 0 || $pro11_inspay != 0 || $pro11_insadj != 0 || $pro11_patadj != 0 || $pro11_patpay != 0 || $pro11_insref != 0 || $pro11_patref != 0) {
                    $provider_info['user'][$k] = $pro11_user;
                    $provider_info['fee'][$k]  = $pro11_fee;
                    $provider_info['inspay'][$k]  = $pro11_inspay;
                    $provider_info['insadj'][$k]  = $pro11_insadj;
                    $provider_info['insref'][$k]  = $pro11_insref;
                    $provider_info['patadj'][$k]  = $pro11_patadj;
                    $provider_info['patpay'][$k]  = $pro11_patpay;
                    $provider_info['patref'][$k]  = $pro11_patref;
                    ++$k;
                }

                if ($pro12_fee != 0 || $pro12_inspay != 0 || $pro12_insadj != 0 || $pro12_patadj != 0 || $pro12_patpay != 0 || $pro12_insref != 0 || $pro12_patref != 0) {
                    $provider_info['user'][$k] = $pro12_user;
                    $provider_info['fee'][$k]  = $pro12_fee;
                    $provider_info['inspay'][$k]  = $pro12_inspay;
                    $provider_info['insadj'][$k]  = $pro12_insadj;
                    $provider_info['insref'][$k]  = $pro12_insref;
                    $provider_info['patadj'][$k]  = $pro12_patadj;
                    $provider_info['patpay'][$k]  = $pro12_patpay;
                    $provider_info['patref'][$k]  = $pro12_patref;
                    ++$k;
                }

                if ($pro13_fee != 0 || $pro13_inspay != 0 || $pro13_insadj != 0 || $pro13_patadj != 0 || $pro13_patpay != 0 || $pro13_insref != 0 || $pro13_patref != 0) {
                    $provider_info['user'][$k] = $pro13_user;
                    $provider_info['fee'][$k]  = $pro13_fee;
                    $provider_info['inspay'][$k]  = $pro13_inspay;
                    $provider_info['insadj'][$k]  = $pro13_insadj;
                    $provider_info['insref'][$k]  = $pro13_insref;
                    $provider_info['patadj'][$k]  = $pro13_patadj;
                    $provider_info['patpay'][$k]  = $pro13_patpay;
                    $provider_info['patref'][$k]  = $pro13_patref;
                    ++$k;
                }

                if ($pro14_fee != 0 || $pro14_inspay != 0 || $pro14_insadj != 0 || $pro14_patadj != 0 || $pro14_patpay != 0 || $pro14_insref != 0 || $pro14_patref != 0) {
                    $provider_info['user'][$k] = $pro14_user;
                    $provider_info['fee'][$k]  = $pro14_fee;
                    $provider_info['inspay'][$k]  = $pro14_inspay;
                    $provider_info['insadj'][$k]  = $pro14_insadj;
                    $provider_info['insref'][$k]  = $pro14_insref;
                    $provider_info['patadj'][$k]  = $pro14_patadj;
                    $provider_info['patpay'][$k]  = $pro14_patpay;
                    $provider_info['patref'][$k]  = $pro14_patref;
                    ++$k;
                }

                if ($pro15_fee != 0 || $pro15_inspay != 0 || $pro15_insadj != 0 || $pro15_patadj != 0 || $pro15_patpay != 0 || $pro15_insref != 0 || $pro15_patref != 0) {
                    $provider_info['user'][$k] = $pro15_user;
                    $provider_info['fee'][$k]  = $pro15_fee;
                    $provider_info['inspay'][$k]  = $pro15_inspay;
                    $provider_info['insadj'][$k]  = $pro15_insadj;
                    $provider_info['insref'][$k]  = $pro15_insref;
                    $provider_info['patadj'][$k]  = $pro15_patadj;
                    $provider_info['patpay'][$k]  = $pro15_patpay;
                    $provider_info['patref'][$k]  = $pro15_patref;
                    ++$k;
                }

                if ($pro16_fee != 0 || $pro16_inspay != 0 || $pro16_insadj != 0 || $pro16_patadj != 0 || $pro16_patpay != 0 || $pro16_insref != 0 || $pro16_patref != 0) {
                    $provider_info['user'][$k] = $pro16_user;
                    $provider_info['fee'][$k]  = $pro16_fee;
                    $provider_info['inspay'][$k]  = $pro16_inspay;
                    $provider_info['insadj'][$k]  = $pro16_insadj;
                    $provider_info['insref'][$k]  = $pro16_insref;
                    $provider_info['patadj'][$k]  = $pro16_patadj;
                    $provider_info['patpay'][$k]  = $pro16_patpay;
                    $provider_info['patref'][$k]  = $pro16_patref;
                    ++$k;
                }

                if ($pro17_fee != 0 || $pro17_inspay != 0 || $pro17_insadj != 0 || $pro17_patadj != 0 || $pro17_patpay != 0 || $pro17_insref != 0 || $pro17_patref != 0) {
                    $provider_info['user'][$k] = $pro17_user;
                    $provider_info['fee'][$k]  = $pro17_fee;
                    $provider_info['inspay'][$k]  = $pro17_inspay;
                    $provider_info['insadj'][$k]  = $pro17_insadj;
                    $provider_info['insref'][$k]  = $pro17_insref;
                    $provider_info['patadj'][$k]  = $pro17_patadj;
                    $provider_info['patpay'][$k]  = $pro17_patpay;
                    $provider_info['patref'][$k]  = $pro17_patref;
                    ++$k;
                }

                if ($pro18_fee != 0 || $pro18_inspay != 0 || $pro18_insadj != 0 || $pro18_patadj != 0 || $pro18_patpay != 0 || $pro18_insref != 0 || $pro18_patref != 0) {
                    $provider_info['user'][$k] = $pro18_user;
                    $provider_info['fee'][$k]  = $pro18_fee;
                    $provider_info['inspay'][$k]  = $pro18_inspay;
                    $provider_info['insadj'][$k]  = $pro18_insadj;
                    $provider_info['insref'][$k]  = $pro18_insref;
                    $provider_info['patadj'][$k]  = $pro18_patadj;
                    $provider_info['patpay'][$k]  = $pro18_patpay;
                    $provider_info['patref'][$k]  = $pro18_patref;
                    ++$k;
                }

                if ($pro19_fee != 0 || $pro19_inspay != 0 || $pro19_insadj != 0 || $pro19_patadj != 0 || $pro19_patpay != 0 || $pro19_insref != 0 || $pro19_patref != 0) {
                    $provider_info['user'][$k] = $pro19_user;
                    $provider_info['fee'][$k]  = $pro19_fee;
                    $provider_info['inspay'][$k]  = $pro19_inspay;
                    $provider_info['insadj'][$k]  = $pro19_insadj;
                    $provider_info['insref'][$k]  = $pro19_insref;
                    $provider_info['patadj'][$k]  = $pro19_patadj;
                    $provider_info['patpay'][$k]  = $pro19_patpay;
                    $provider_info['patref'][$k]  = $pro19_patref;
                    ++$k;
                }
            }

            if ($totals_only === 1) {
                $from_date = oeFormatShortDate(substr($query_part_day, 37, 10));
                $to_date = oeFormatShortDate(substr($query_part_day, 63, 10));?>
                <br />
                <br />
                <p><?php echo xlt('Totals for ') . text($from_date) . ' ' . xlt('To{{Range}}') . ' ' . text($to_date) ?></p>
            <?php } ?>


            <?php if ($run_provider != 1) { ?>
                <table class='table table-borderless'>
                    <tr>
                        <br />
                        <br />
                        <td class='text' width='25'>
                        </td>
                        <td class='text text-center font-weight-bold' width='250'>
                            <?php echo xlt("User"); ?>
                        </td>
                        <td class='text' width='125'>
                        </td>
                        <td class='text text-center font-weight-bold' width='250'>
                            <?php echo xlt("Charges"); ?>
                        </td>
                        <td class='text' width='125'>
                        </td>
                        <td class='text text-center font-weight-bold' width='250'>
                            <?php echo xlt("Payments"); ?>
                        </td>
                        <td class='text' width='25'>

                <?php for ($i = 1; $i < $k;) { ?>
                            <br />
                        </td>
                        <table class='table table-borderless'>
                            <tr>
                                <td class='text' width='25'>
                                </td>
                                <td class='text' width='250'>
                                    <?php echo text($user_info['user'][$i]); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Charges") . ': ' . "%1\$.2f", text($user_info['fee'][$i])); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Payments") . ': ' . "(%1\$.2f)", text($user_info['inspay'][$i] + $user_info['patpay'][$i])); ?>
                                </td>
                                <td class='text' width='25'>
                                    <br />
                                </td>

                                <table class='table table-borderless'>
                                    <tr>
                                        <td class='text' width='25'>
                                        </td>
                                        <td class='text' width='250'>
                                        </td>
                                        <td class='text' width='125'>
                                        </td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Total Adj") . '.: ' . "(%1\$.2f)", text($user_info['patadj'][$i] + $user_info['insadj'][$i])); ?>
                                        </td>
                                        <td width='125'><span class='text'></span></td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Refund") . ': ' . "(%1\$.2f)", text($user_info['patref'][$i] + $user_info['insref'][$i])); ?>
                                        </td>
                                        <td class='text' width='25'>
                                            <br />
                                        </td>
                                        <table class='table table-borderless'>
                                            <tr>
                                                <td class='text' width='25'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text font-weight-bold' width='175' height='5'>
                                                    <hr />
                                                </td>
                                                <td class='text' width='25'>
                                                    <br />
                                                </td>

                                                <table class='table table-borderless'>
                                                    <tr>
                                                        <td class='text' width='25'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text font-weight-bold' width='250'>
                                                            <?php printf(xlt("Actual Receipts") . ': ' . "(%1\$.2f)", text($user_info['patref'][$i] + $user_info['insref'][$i] + $user_info['inspay'][$i] + $user_info['patpay'][$i])); ?>
                                                        </td>
                                                        <td class='text' width='25'>
                                                            <br />
                                                        </td>

                                                        <table class='table table-borderless'>
                                                            <tr>
                                                                <td  class='text' width='25'>
                                                                </td>
                                                                <td  class='text' width='250'>
                                                                </td>
                                                                <td  class='text' width='125'>
                                                                </td>
                                                                <td  class='text' width='250'>
                                                                </td>
                                                                <td  class='text' width='125'>
                                                                </td>
                                                                <td  class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='25'>

                    <?php
                    $gtotal_fee = $gtotal_fee + $user_info['fee'][$i];
                    $gtotal_insadj = $gtotal_insadj + $user_info['insadj'][$i];
                    $gtotal_inspay = $gtotal_inspay + $user_info['inspay'][$i];
                    $gtotal_patadj = $gtotal_patadj + $user_info['patadj'][$i];
                    $gtotal_patpay = $gtotal_patpay + $user_info['patpay'][$i];

                    ++$i;

                    print "<br /></td></tr>";
                } ?>

                            <br />
                        </td>
                    </table>
                </div>
            <?php } else { ?>
                <table class='table table-borderless'>
                    <tr>
                        <br />
                        <br />
                        <td class='text' width='25'>
                        </td>
                        <td class='text text-center' width='250'>
                            <?php echo xlt("Provider"); ?>
                        </td>
                        <td class='text' width='125'></td>
                        <td class='text text-center' width='250'>
                            <?php echo xlt("Charges"); ?>
                        </td>
                        <td class='text' width='125'>
                        </td>
                        <td class='text text-center' width='250'>
                            <?php echo xlt("Payments"); ?>
                        </td>
                        <td class='text' width='25'>

                <?php for ($i = 1; $i < $k;) { ?>
                            <br />
                        </td>
                        <table class='table table-borderless'>
                            <tr>
                                <td class='text' width='25'>
                                </td>
                                <td class='text text-center' width='250'>
                                    <?php echo text($provider_info['user'][$i]); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Charges") . ': ' . " %1\$.2f ", text($provider_info['fee'][$i])); ?>
                                </td>
                                <td class='text' width='125'>
                                </td>
                                <td class='text font-weight-bold' width='250'>
                                    <?php printf(xlt("Total Payments") . ': ' . "(%1\$.2f)", text($provider_info['inspay'][$i] + $provider_info['patpay'][$i])); ?>
                                </td>
                                <td class='text' width='25'>
                                    <br />
                                </td>

                                <table class='table table-borderless'>
                                    <tr>
                                        <td class='text' width='25'>
                                        </td>
                                        <td class='text' width='250'>
                                        </td>
                                        <td class='text' width='125'>
                                        </td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Total Adj") . '.: ' . "(%1\$.2f)", text($provider_info['patadj'][$i] + $provider_info['insadj'][$i])); ?>
                                        </td>
                                        <td class='text' width='125'>
                                        </td>
                                        <td class='text font-weight-bold' width='250'>
                                            <?php printf(xlt("Refund") . ': ' . "(%1\$.2f)", text($provider_info['patref'][$i] + $provider_info['insref'][$i])); ?>
                                        </td>
                                        <td class='text' width='25'>
                                            <br />
                                        </td>

                                        <table class='table table-borderless'>
                                            <tr>
                                                <td class='text' width='25'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text' width='250'>
                                                </td>
                                                <td class='text' width='125'>
                                                </td>
                                                <td class='text' width='175' height='5'>
                                                   <hr />
                                                </td>
                                                <td class='text' width='25'>
                                                    <br />
                                                </td>

                                                <table class='table table-borderless'>
                                                    <tr>
                                                        <td class='text' width='25'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text' width='250'>
                                                        </td>
                                                        <td class='text' width='125'>
                                                        </td>
                                                        <td class='text font-weight-bold' width='250'>
                                                            <?php printf(xlt("Actual Receipts") . ': ' . "(%1\$.2f)", text($provider_info['patref'][$i] + $provider_info['insref'][$i] + $provider_info['inspay'][$i] + $provider_info['patpay'][$i])); ?>
                                                        </td>
                                                        <td class='text' width='25'>
                                                        </td>

                                                        <table class='table table-borderless'>
                                                            <tr>
                                                                <td class='text' width='25'>
                                                                </td>
                                                                <td class='text' width='250'>
                                                                </td>
                                                                <td class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='250'>
                                                                </td>
                                                                <td class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='125'>
                                                                </td>
                                                                <td class='text' width='25'>

                    <?php
                        $gtotal_fee = $gtotal_fee + $provider_info['fee'][$i];
                        $gtotal_insadj = $gtotal_insadj + $provider_info['insadj'][$i];
                        $gtotal_inspay = $gtotal_inspay + $provider_info['inspay'][$i];
                        $gtotal_insref = $gtotal_insref + $provider_info['insref'][$i];
                        $gtotal_patadj = $gtotal_patadj + $provider_info['patadj'][$i];
                        $gtotal_patpay = $gtotal_patpay + $provider_info['patpay'][$i];
                        $gtotal_patref = $gtotal_patref + $provider_info['patref'][$i];

                        ++$i;

                        print "<br /></td></tr>";
                }

                print "<br /></td>";
                print "</table>";
            }
            ?>
        </div>
    </div>
</body>
</html>
