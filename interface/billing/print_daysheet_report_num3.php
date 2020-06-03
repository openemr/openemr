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
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2014 Terry Hill <terry@lillysystems.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

/* TODO: Code Cleanup */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
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
<body class="body_top">

<a href="javascript:window.close();" target="Main"><span class="title"><?php echo xlt('Day Sheet Report')?></span></a>
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


    $old_pid = -1;
    $first_time = 1;
    $new_old_pid = -1;

// $iter has encounter information

// this loop gathers the user numbers
    foreach ($ret as $iter) {
          $catch_user[] = $iter['user'];
    }

//This statment uniques the array removing duplicates
    $user_list = array_unique($catch_user);
// reorder the list starting with array element zero
    $final_list = array_values($user_list);

// sort array in assending order
    sort($final_list);

    $all4 = array_natsort($ret, pid, fulname, asc);
    if ($_POST['end_of_day_totals_only'] == 1) {
        $totals_only = 1;
    }

    foreach ($all4 as $iter) {
        // Case statment to tally information by user
        switch ($iter['user']) {
            case $iter['user'] = $final_list[0]:
                $us0_user = $iter['user'];
                $us0_fee = $us0_fee + $iter['fee'];
                $us0_inspay = $us0_inspay + $iter['ins_code'];
                $us0_insadj = $us0_insadj + $iter['ins_adjust_dollar'];
                $us0_patadj = $us0_patadj + $iter['pat_adjust_dollar'];
                $us0_patpay = $us0_patpay + $iter['pat_code'];
                break;
            case $ite['user'] = $final_list[1]:
                $us1_user = $iter['user'];
                $us1_fee = $us1_fee + $iter['fee'];
                $us1_inspay = $us1_inspay + $iter['ins_code'];
                $us1_insadj = $us1_insadj + $iter['ins_adjust_dollar'];
                $us1_patadj = $us1_patadj + $iter['pat_adjust_dollar'];
                $us1_patpay = $us1_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[2]:
                $us2_user = $iter['user'];
                $us2_fee = $us2_fee + $iter['fee'];
                $us2_inspay = $us2_inspay + $iter['ins_code'];
                $us2_insadj = $us2_insadj + $iter['ins_adjust_dollar'];
                $us2_patadj = $us2_patadj + $iter['pat_adjust_dollar'];
                $us2_patpay = $us2_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[3]:
                $us3_user = $iter['user'];
                $us3_fee = $us3_fee + $iter['fee'];
                $us3_inspay = $us3_inspay + $iter['ins_code'];
                $us3_insadj = $us3_insadj + $iter['ins_adjust_dollar'];
                $us3_patadj = $us3_patadj + $iter['pat_adjust_dollar'];
                $us3_patpay = $us3_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[4]:
                $us4_user = $iter['user'];
                $us4_fee = $us4_fee + $iter['fee'];
                $us4_inspay = $us4_inspay + $iter['ins_code'];
                $us4_insadj = $us4_insadj + $iter['ins_adjust_dollar'];
                $us4_patadj = $us4_patadj + $iter['pat_adjust_dollar'];
                $us4_patpay = $us4_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[5]:
                $us5_user = $iter['user'];
                $us5_fee = $us5_fee + $iter['fee'];
                $us5_inspay = $us5_inspay + $iter['ins_code'];
                $us5_insadj = $us5_insadj + $iter['ins_adjust_dollar'];
                $us5_patadj = $us5_patadj + $iter['pat_adjust_dollar'];
                $us5_patpay = $us5_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[6]:
                $us6_user = $iter['user'];
                $us6_fee = $us6_fee + $iter['fee'];
                $us6_inspay = $us6_inspay + $iter['ins_code'];
                $us6_insadj = $us6_insadj + $iter['ins_adjust_dollar'];
                $us6_patadj = $us6_patadj + $iter['pat_adjust_dollar'];
                $us6_patpay = $us6_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[7]:
                $us7_user = $iter['user'];
                $us7_fee = $us7_fee + $iter['fee'];
                $us7_inspay = $us7_inspay + $iter['ins_code'];
                $us7_insadj = $us7_insadj + $iter['ins_adjust_dollar'];
                $us7_patadj = $us7_patadj + $iter['pat_adjust_dollar'];
                $us7_patpay = $us7_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[8]:
                $us8_user = $iter['user'];
                $us8_fee = $us8_fee + $iter['fee'];
                $us8_inspay = $us8_inspay + $iter['ins_code'];
                $us8_insadj = $us8_insadj + $iter['ins_adjust_dollar'];
                $us8_patadj = $us8_patadj + $iter['pat_adjust_dollar'];
                $us8_patpay = $us8_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[9]:
                $us9_user = $iter['user'];
                $us9_fee = $us9_fee + $iter['fee'];
                $us9_inspay = $us9_inspay + $iter['ins_code'];
                $us9_insadj = $us9_insadj + $iter['ins_adjust_dollar'];
                $us9_patadj = $us9_patadj + $iter['pat_adjust_dollar'];
                $us9_patpay = $us9_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[10]:
                $us10_user = $iter['user'];
                $us10_fee = $us10_fee + $iter['fee'];
                $us10_inspay = $us10_inspay + $iter['ins_code'];
                $us10_insadj = $us10_insadj + $iter['ins_adjust_dollar'];
                $us10_patadj = $us10_patadj + $iter['pat_adjust_dollar'];
                $us10_patpay = $us10_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[11]:
                $us11_user = $iter['user'];
                $us11_fee = $us11_fee + $iter['fee'];
                $us11_inspay = $us11_inspay + $iter['ins_code'];
                $us11_insadj = $us11_insadj + $iter['ins_adjust_dollar'];
                $us11_patadj = $us11_patadj + $iter['pat_adjust_dollar'];
                $us11_patpay = $us11_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[12]:
                $us12_user = $iter['user'];
                $us12_fee = $us12_fee + $iter['fee'];
                $us12_inspay = $us12_inspay + $iter['ins_code'];
                $us12_insadj = $us12_insadj + $iter['ins_adjust_dollar'];
                $us12_patadj = $us12_patadj + $iter['pat_adjust_dollar'];
                $us12_patpay = $us12_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[13]:
                $us13_user = $iter['user'];
                $us13_fee = $us13_fee + $iter['fee'];
                $us13_inspay = $us13_inspay + $iter['ins_code'];
                $us13_insadj = $us13_insadj + $iter['ins_adjust_dollar'];
                $us13_patadj = $us13_patadj + $iter['pat_adjust_dollar'];
                $us13_patpay = $us13_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[14]:
                $us14_user = $iter['user'];
                $us14_fee = $us14_fee + $iter['fee'];
                $us14_inspay = $us14_inspay + $iter['ins_code'];
                $us14_insadj = $us14_insadj + $iter['ins_adjust_dollar'];
                $us14_patadj = $us14_patadj + $iter['pat_adjust_dollar'];
                $us14_patpay = $us14_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[15]:
                $us15_user = $iter['user'];
                $us15_fee = $us15_fee + $iter['fee'];
                $us15_inspay = $us15_inspay + $iter['ins_code'];
                $us15_insadj = $us15_insadj + $iter['ins_adjust_dollar'];
                $us15_patadj = $us15_patadj + $iter['pat_adjust_dollar'];
                $us15_patpay = $us15_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[16]:
                $us16_user = $iter['user'];
                $us16_fee = $us16_fee + $iter['fee'];
                $us16_inspay = $us16_inspay + $iter['ins_code'];
                $us16_insadj = $us16_insadj + $iter['ins_adjust_dollar'];
                $us16_patadj = $us16_patadj + $iter['pat_adjust_dollar'];
                $us16_patpay = $us16_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[17]:
                $us17_user = $iter['user'];
                $us17_fee = $us17_fee + $iter['fee'];
                $us17_inspay = $us17_inspay + $iter['ins_code'];
                $us17_insadj = $us17_insadj + $iter['ins_adjust_dollar'];
                $us17_patadj = $us17_patadj + $iter['pat_adjust_dollar'];
                $us17_patpay = $us17_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[18]:
                $us18_user = $iter['user'];
                $us18_fee = $us18_fee + $iter['fee'];
                $us18_inspay = $us18_inspay + $iter['ins_code'];
                $us18_insadj = $us18_insadj + $iter['ins_adjust_dollar'];
                $us18_patadj = $us18_patadj + $iter['pat_adjust_dollar'];
                $us18_patpay = $us18_patpay + $iter['pat_code'];
                break;
            case $iter['user'] = $final_list[19]:
                $us19_user = $iter['user'];
                $us19_fee = $us19_fee + $iter['fee'];
                $us19_inspay = $us19_inspay + $iter['ins_code'];
                $us19_insadj = $us19_insadj + $iter['ins_adjust_dollar'];
                $us19_patadj = $us19_patadj + $iter['pat_adjust_dollar'];
                $us19_patpay = $us19_patpay + $iter['pat_code'];
                break;
        }

        if ($the_first_time == 1) {
              $user = $iter['user'];
              $first_user = $iter['user'];
              $new_old_pid = $iter['pid'];
              $the_first_time = 0;
        }

        if ($totals_only != 1) {
            if ($old_pid != $iter['pid'] and ($iter['code_type'] != 'payment_info')) {
               // $name has patient information
                $name = getPatientData($iter['pid']);

               // formats the displayed text
               //

                if ($old_pid == $new_old_pid) {
                    if ($line_total != 0) {
                        print "<td class='w-100'><br /><span class='text'><strong><center>" . xlt('Total') . "</strong></center>";
                        Printf("<br /></span></td><td class='w-100'><span class='text'><center>" . " %1\$.2f", text($line_total)) . "</center></td>";
                    } else {
                        print "<td class='w-100'><br /><span class='text'><strong><center>" . xlt('Total') . "</strong></center>";
                        Printf("<br /></span></td><td class='w-100'><span class='text'><center>" . " %1\$.2f", text($line_total_pay)) . "</center></td>";
                    }

                    $line_total = 0;
                    $line_total_pay = 0;
                }

                if ($first_time) {
                     print "<table class='border-0'><tr>\n";     // small table
                     $first_time = 0;
                }

                // Displays name

                print "<tr><td colspan='10'><hr><span class='font-weight-bold'>" . text($name["fname"]) . " " . text($name["lname"]) . "</span><br /><br /></td></tr><tr>\n";
                //==================================

                if ($iter['code_type'] === 'COPAY' || $iter['code_type'] === 'Patient Payment' || $iter['code_type'] === 'Insurance Payment') {
                      print "<td class='w-100'><span class='text'><center><strong>" . xlt("Units") . "</strong></center>";
                      print "</span></td><td class='w-100'><span class='text'><center><strong>" . xlt("Fee") . "</strong></center>" ;
                      print "</span></td><td class='w-100'><span class='text'><center><strong>" . xlt("Code") . "</strong></center>" ;
                      print "</span></td><td class='w-100'><span class='text'><strong>";
                      print "</span></td><td class='w-100'><span class='text'><center><strong>" . xlt("User") . "</strong></center>";
                      print "</span></td><td class='w-100'><span class='small'><strong>" ;
                      print "</span></td><td class='w-100'><span class='small'><center><strong>" . xlt("Post Date") . "</strong></center>";
                      print "</span></td><td></tr><tr>\n";
                } else {
                    print "<td class='w-100'><span class='text'><strong><center>" . xlt("Units") . "</strong></center>";
                    print "</span></td><td class='w-100'><span class='text'><center><strong>" . xlt("Fee") . "</strong></center>";
                    print "</span></td><td class='w-100'><span class='text'><center><strong>" . xlt("Code") . "</strong></center>";
                    print "</span></td><td class='w-100'><span class='text'><strong><center>" . xlt("Provider Id") . "</strong></center>";
                    print "</span></td><td class='w-100'><span class='text'><strong><center>" . xlt("User") . "</strong></center>";
                    print "</span></td><td class='w-100'><span class='small'><center><strong>" . xlt("Bill Date") . "</strong></center>";
                    print "</span></td><td class='w-100'><span class='small'><center><strong>" . xlt("Date of Service") . "</strong></center>";
                    print "</span></td><td></tr><tr>\n";
                }

                //Next patient

                $old_pid = $iter['pid'];
            }

            // get dollar amounts to appear on pat,ins payments and copays

            if ($iter['code_type'] != 'payment_info') {
                if ($iter['code_type'] === 'COPAY' || $iter['code_type'] === 'Patient Payment' || $iter['code_type'] === 'Insurance Payment') {
                       print "<td class='w-100'><span class='text'><center>" . "1" . "</center>" ;

                      // start fee output
                      //    [pat_code] => 0.00
                      //    [ins_code] => 0.00
                      //    [pat_adjust_dollar] => 0.00
                      //    [ins_adjust_dollar] => 0.00
                    if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                        $line_total_pay = $line_total_pay + $iter['ins_adjust_dollar'];
                        print  "</span></td><td class='w-100'><span class='text'><center>" . text($iter['ins_adjust_dollar']) . "</center>";
                    }

                    if (($iter['ins_code']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                           $line_total_pay = $line_total_pay + $iter['ins_code'];
                        print  "</span></td><td class='w-100'><span class='text'><center>" . text($iter['ins_code']) . "</center>";
                    }

                    if (($iter['code_type']) != 'Patient Payment' and ($iter['code_type']) != 'Insurance Payment') {
                           $line_total_pay = $line_total_pay + $iter["code"];
                        print  "</span></td><td class='w-100'><span class='text'><center>" . text($iter["code"]) . "</center>";
                    }

                    if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                           $line_total_pay = $line_total_pay + $iter['pat_adjust_dollar'];
                        print  "</span></td><td class='w-100'><span class='text'><center>" . text($iter['pat_adjust_dollar']) . "</center>";
                    }

                    if (($iter['pat_code']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                           $line_total_pay = $line_total_pay + $iter['pat_code'];
                        print  "</span></td><td class='w-100'><span class='text'><center>" . text($iter['pat_code']) . "</center>";
                    }

                      // end fee output

                    if (($iter['ins_adjust_dollar']) != 0 and ($iter['code_type']) === 'Insurance Payment') {
                           print  "</span></td><td width='250'><span class='text'><center>" . xlt("Insurance Adjustment") . "</center>";
                    }

                    if (($iter['pat_adjust_dollar']) != 0 and ($iter['code_type']) === 'Patient Payment') {
                           print  "</span></td><td width='250'><span class='text'><center>" . xlt("Patient Adjustment") . "</center>";
                    }

                    if (($iter['ins_code']) > 0 and ($iter['code_type']) === 'Insurance Payment') {
                           print  "</span></td><td width='250'><span class='text'><center>" . xlt('Insurance Payment') . "</center>";
                    }

                    if (($iter['pat_code']) > 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                           print  "</span></td><td width='250'><span class='text'><center>" . xlt('Patient Payment') . "</center>";
                    }

                    if (($iter['ins_code']) < 0 and ($iter['code_type']) === 'Insurance Payment') {
                           print  "</span></td><td width='250'><span class='text'><center>" . xlt("Insurance Credit") . "</center>";
                    }

                    if (($iter['pat_code']) < 0 and ($iter['code_type']) === 'Patient Payment' and $iter['paytype'] != 'PCP') {
                           print  "</span></td><td width='250'><span class='text'><center>" . xlt("Patient Credit") . "</center>";
                    }

                    if ($iter['paytype'] == 'PCP') {
                        print  "</span></td><td width='250'><span class='text'><center>" . xlt('COPAY') . "</center>";
                    }

                    if (($iter['code_type']) != 'Insurance Payment' and ($iter['code_type']) != 'Patient Payment' and $iter['paytype'] != 'PCP') {
                           print  "</span></td><td width='250'><span class='text'><center>" . text($iter['code_type']) . "</center>";
                    }

                      print  "</span></td><td class='w-100'><span class='text'><center>" . text($iter['provider_id']) . "</center>";
                      print  "</span></td><td class='w-100'><span class='text'><center>" . text($iter['user']) . "</center>" ;
                      print  "</span></td><td class='w-100'><span class='text'>";
                      print  "</span></td><td class='w-100'><span class='small'><center>" . text(date("Y-m-d", strtotime($iter['date']))) . "</center>";
                      print  "</span></td>\n";
                } else {
                    if (date("Y-m-d", strtotime($iter['bill_date'])) == "1969-12-31") {
                        print "<td class='w-100'><span class='text'><center>" . text($iter['units']) . "</center>" ;
                        print "</span></td><td class='w-100'><span class='text'><center>" . text($iter['fee']) . "</center>";
                        if ($GLOBALS['language_default'] === 'English (Standard)') {
                            print "</span></td><td width='250'><span class='text'><center>" . text(ucwords(strtolower(substr($iter['code_text'], 0, 38)))) . "</center>";
                        } else {
                            print "</span></td><td width='250'><span class='text'><center>" . text(substr($iter['code_text'], 0, 38)) . "</center>";
                        }

                        print "</span></td><td class='w-100'><span class='text'><center>" . text($iter['provider_id']) . "</center>" ;
                        print "</span></td><td class='w-100'><span class='text'><center>" . text($iter['user']) . "</center>" ;
                        print "</span></td><td class='w-100'><span class='text'><center>" . xlt('Not Billed') . "</center>";
                        print "</span></td><td class='w-100'><span class='small'><center>" . text(date("Y-m-d", strtotime($iter['date']))) . "</center>";
                        print "</span></td>\n";
                    } else {
                        if ($iter['fee'] != 0) {
                            $line_total = $line_total + $iter['fee'];
                            print "<td class='w-100'><span class='text'><center>" . text($iter["units"]) . "</center>";
                            print "</span></td><td class='w-100'><span class='text'><center>" . text($iter['fee']) . "</center>";
                            if ($GLOBALS['language_default'] === 'English (Standard)') {
                                 print "</span></td><td width='250'><span class='text'><center>" . text(ucwords(strtolower(substr($iter['code_text'], 0, 38)))) . "</center>";
                            } else {
                                 print "</span></td><td width='250'><span class='text'><center>" . text(substr($iter['code_text'], 0, 38)) . "</center>";
                            }

                            print "</span></td><td class='w-100'><span class='text'><center>" . text($iter['provider_id']) . "</center>";
                            print "</span></td><td class='w-100'><span class='text'><center>" . text($iter['user']) . "</center>";
                            print "</span></td><td class='w-100'><span class='small'><center>" . text(date("Y-m-d", strtotime($iter['bill_date']))) . "</center>";
                            print "</span></td><td class='w-100'><span class='small'><center>" . text(date("Y-m-d", strtotime($iter["date"]))) . "</center>";
                            print "</span></td>\n";
                        }
                    }
                }

                $res_count++;

                if ($res_count == $N) {
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


if ($anypats == 0) {
    ?><span><?php echo xlt('No Data to Process')?></span><?php
}

// TEST TO SEE IF THERE IS INFORMATION IN THE VARAIBLES THEN ADD TO AN ARRAY FOR PRINTING

if ($us0_fee != 0 || $us0_inspay != 0 || $us0_insadj != 0 || $us0_patadj != 0 || $us0_patpay != 0) {
    $user_info['user'][$k] = $us0_user;
    $user_info['fee'][$k]  = $us0_fee;
    $user_info['inspay'][$k]  = $us0_inspay;
    $user_info['insadj'][$k]  = $us0_insadj;
    $user_info['patadj'][$k]  = $us0_patadj;
    $user_info['patpay'][$k]  = $us0_patpay;
    ++$k;
}

if ($us1_fee != 0 || $us1_inspay != 0 || $us1_insadj != 0 || $us1_patadj != 0 || $us1_patpay != 0) {
    $user_info['user'][$k] = $us1_user;
    $user_info['fee'][$k]  = $us1_fee;
    $user_info['inspay'][$k]  = $us1_inspay;
    $user_info['insadj'][$k]  = $us1_insadj;
    $user_info['patadj'][$k]  = $us1_patadj;
    $user_info['patpay'][$k]  = $us1_patpay;
    ++$k;
}

if ($us2_fee != 0 || $us2_inspay != 0 || $us2_insadj != 0 || $us2_patadj != 0 || $us2_patpay != 0) {
    $user_info['user'][$k] = $us2_user;
    $user_info['fee'][$k]  = $us2_fee;
    $user_info['inspay'][$k]  = $us2_inspay;
    $user_info['insadj'][$k]  = $us2_insadj;
    $user_info['patadj'][$k]  = $us2_patadj;
    $user_info['patpay'][$k]  = $us2_patpay;
    ++$k;
}

if ($us3_fee != 0 || $us3_inspay != 0 || $us3_insadj != 0 || $us3_patadj != 0 || $us3_patpay != 0) {
    $user_info['user'][$k] = $us3_user;
    $user_info['fee'][$k]  = $us3_fee;
    $user_info['inspay'][$k]  = $us3_inspay;
    $user_info['insadj'][$k]  = $us3_insadj;
    $user_info['patadj'][$k]  = $us3_patadj;
    $user_info['patpay'][$k]  = $us3_patpay;
    ++$k;
}

if ($us4_fee != 0 || $us4_inspay != 0 || $us4_insadj != 0 || $us4_patadj != 0 || $us4_patpay != 0) {
    $user_info['user'][$k] = $us4_user;
    $user_info['fee'][$k]  = $us4_fee;
    $user_info['inspay'][$k]  = $us4_inspay;
    $user_info['insadj'][$k]  = $us4_insadj;
    $user_info['patadj'][$k]  = $us4_patadj;
    $user_info['patpay'][$k]  = $us4_patpay;
    ++$k;
}

if ($us5_fee != 0 || $us5_inspay != 0 || $us5_insadj != 0 || $us5_patadj != 0 || $us5_patpay != 0) {
    $user_info['user'][$k] = $us5_user;
    $user_info['fee'][$k]  = $us5_fee;
    $user_info['inspay'][$k]  = $us5_inspay;
    $user_info['insadj'][$k]  = $us5_insadj;
    $user_info['patadj'][$k]  = $us5_patadj;
    $user_info['patpay'][$k]  = $us5_patpay;
    ++$k;
}

if ($us6_fee != 0 || $us6_inspay != 0 || $us6_insadj != 0 || $us6_patadj != 0 || $us6_patpay != 0) {
    $user_info['user'][$k] = $us6_user;
    $user_info['fee'][$k]  = $us6_fee;
    $user_info['inspay'][$k]  = $us6_inspay;
    $user_info['insadj'][$k]  = $us6_insadj;
    $user_info['patadj'][$k]  = $us6_patadj;
    $user_info['patpay'][$k]  = $us6_patpay;
    ++$k;
}

if ($us7_fee != 0 || $us7_inspay != 0 || $us7_insadj != 0 || $us7_patadj != 0 || $us7_patpay != 0) {
    $user_info['user'][$k] = $us7_user;
    $user_info['fee'][$k]  = $us7_fee;
    $user_info['inspay'][$k]  = $us7_inspay;
    $user_info['insadj'][$k]  = $us7_insadj;
    $user_info['patadj'][$k]  = $us7_patadj;
    $user_info['patpay'][$k]  = $us7_patpay;
    ++$k;
}

if ($us8_fee != 0 || $us8_inspay != 0 || $us8_insadj != 0 || $us8_patadj != 0 || $us8_patpay != 0) {
    $user_info['user'][$k] = $us8_user;
    $user_info['fee'][$k]  = $us8_fee;
    $user_info['inspay'][$k]  = $us8_inspay;
    $user_info['insadj'][$k]  = $us8_insadj;
    $user_info['patadj'][$k]  = $us8_patadj;
    $user_info['patpay'][$k]  = $us8_patpay;
    ++$k;
}

if ($us9_fee != 0 || $us9_inspay != 0 || $us9_insadj != 0 || $us9_patadj != 0 || $us9_patpay != 0) {
    $user_info['user'][$k] = $us9_user;
    $user_info['fee'][$k]  = $us9_fee;
    $user_info['inspay'][$k]  = $us9_inspay;
    $user_info['insadj'][$k]  = $us9_insadj;
    $user_info['patadj'][$k]  = $us9_patadj;
    $user_info['patpay'][$k]  = $us9_patpay;
    ++$k;
}

if ($us10_fee != 0 || $us10_inspay != 0 || $us10_insadj != 0 || $us10_patadj != 0 || $us10_patpay != 0) {
    $user_info['user'][$k] = $us10_user;
    $user_info['fee'][$k]  = $us10_fee;
    $user_info['inspay'][$k]  = $us10_inspay;
    $user_info['insadj'][$k]  = $us10_insadj;
    $user_info['patadj'][$k]  = $us10_patadj;
    $user_info['patpay'][$k]  = $us10_patpay;
    ++$k;
}

if ($us11_fee != 0 || $us11_inspay != 0 || $us11_insadj != 0 || $us11_patadj != 0 || $us11_patpay != 0) {
    $user_info['user'][$k] = $us11_user;
    $user_info['fee'][$k]  = $us11_fee;
    $user_info['inspay'][$k]  = $us11_inspay;
    $user_info['insadj'][$k]  = $us11_insadj;
    $user_info['patadj'][$k]  = $us11_patadj;
    $user_info['patpay'][$k]  = $us11_patpay;
    ++$k;
}

if ($us12_fee != 0 || $us12_inspay != 0 || $us12_insadj != 0 || $us12_patadj != 0 || $us12_patpay != 0) {
    $user_info['user'][$k] = $us12_user;
    $user_info['fee'][$k]  = $us12_fee;
    $user_info['inspay'][$k]  = $us12_inspay;
    $user_info['insadj'][$k]  = $us12_insadj;
    $user_info['patadj'][$k]  = $us12_patadj;
    $user_info['patpay'][$k]  = $us12_patpay;
    ++$k;
}

if ($us13_fee != 0 || $us13_inspay != 0 || $us13_insadj != 0 || $us13_patadj != 0 || $us13_patpay != 0) {
    $user_info['user'][$k] = $us13_user;
    $user_info['fee'][$k]  = $us13_fee;
    $user_info['inspay'][$k]  = $us13_inspay;
    $user_info['insadj'][$k]  = $us13_insadj;
    $user_info['patadj'][$k]  = $us13_patadj;
    $user_info['patpay'][$k]  = $us13_patpay;
    ++$k;
}

if ($us14_fee != 0 || $us14_inspay != 0 || $us14_insadj != 0 || $us14_patadj != 0 || $us14_patpay != 0) {
    $user_info['user'][$k] = $us14_user;
    $user_info['fee'][$k]  = $us14_fee;
    $user_info['inspay'][$k]  = $us14_inspay;
    $user_info['insadj'][$k]  = $us14_insadj;
    $user_info['patadj'][$k]  = $us14_patadj;
    $user_info['patpay'][$k]  = $us14_patpay;
    ++$k;
}

if ($us15_fee != 0 || $us15_inspay != 0 || $us15_insadj != 0 || $us15_patadj != 0 || $us15_patpay != 0) {
    $user_info['user'][$k] = $us15_user;
    $user_info['fee'][$k]  = $us15_fee;
    $user_info['inspay'][$k]  = $us15_inspay;
    $user_info['insadj'][$k]  = $us15_insadj;
    $user_info['patadj'][$k]  = $us15_patadj;
    $user_info['patpay'][$k]  = $us15_patpay;
    ++$k;
}

if ($us16_fee != 0 || $us16_inspay != 0 || $us16_insadj != 0 || $us16_patadj != 0 || $us16_patpay != 0) {
    $user_info['user'][$k] = $us16_user;
    $user_info['fee'][$k]  = $us16_fee;
    $user_info['inspay'][$k]  = $us16_inspay;
    $user_info['insadj'][$k]  = $us16_insadj;
    $user_info['patadj'][$k]  = $us16_patadj;
    $user_info['patpay'][$k]  = $us16_patpay;
    ++$k;
}

if ($us17_fee != 0 || $us17_inspay != 0 || $us17_insadj != 0 || $us17_patadj != 0 || $us17_patpay != 0) {
    $user_info['user'][$k] = $us17_user;
    $user_info['fee'][$k]  = $us17_fee;
    $user_info['inspay'][$k]  = $us17_inspay;
    $user_info['insadj'][$k]  = $us17_insadj;
    $user_info['patadj'][$k]  = $us17_patadj;
    $user_info['patpay'][$k]  = $us17_patpay;
    ++$k;
}

if ($us18_fee != 0 || $us18_inspay != 0 || $us18_insadj != 0 || $us18_patadj != 0 || $us18_patpay != 0) {
    $user_info['user'][$k] = $us18_user;
    $user_info['fee'][$k]  = $us18_fee;
    $user_info['inspay'][$k]  = $us18_inspay;
    $user_info['insadj'][$k]  = $us18_insadj;
    $user_info['patadj'][$k]  = $us18_patadj;
    $user_info['patpay'][$k]  = $us18_patpay;
    ++$k;
}

if ($us19_fee != 0 || $us19_inspay != 0 || $us19_insadj != 0 || $us19_patadj != 0 || $us19_patpay != 0) {
    $user_info['user'][$k] = $us19_user;
    $user_info['fee'][$k]  = $us19_fee;
    $user_info['inspay'][$k]  = $us19_inspay;
    $user_info['insadj'][$k]  = $us19_insadj;
    $user_info['patadj'][$k]  = $us19_patadj;
    $user_info['patpay'][$k]  = $us19_patpay;
    ++$k;
}

if ($totals_only != 1) {
    if ($line_total != 0) {
        print "<td class='w-100'><br /><span class='text'><strong><center>" . xlt('Total') . "</strong></center>";
        Printf("<br /></span></td><td class='w-100'><span class='text'><center>" . " %1\$.2f", text($line_total)) . "</center></span></td>\n<br />";
        print "</tr><tr>\n";
    } else {
        print "<td class='w-100'><br /><span class='text'><strong><center>" . xlt('Total') . "</strong></center>";
        Printf("<br /></span></td><td class='w-100'><span class='text'><center>" . " %1\$.2f", text($line_total_pay)) . "</center></td>\n<br />";
        print "</tr><tr>\n";
    }
}

if ($totals_only == 1) {
    $from_date = oeFormatShortDate(substr($query_part_day, 37, 10));
    $to_date = oeFormatShortDate(substr($query_part_day, 63, 10));
    print "<br /><br />";

    ?><span><?php echo xlt('Totals for ') . text($from_date) . ' ' . xlt('To{{Range}}') . ' ' . text($to_date) ?></span><?php
}

for ($i = 1; $i < $k;) {
    print "<table border='1'><tr>\n";
    print "<br /><br />";

    Printf("<td width='70'><span class='text'><strong><center>" . xlt("User") . ' ' . "</center></strong><center>" . text($user_info['user'][$i])) . "</center>";
    Printf("<td width='140'><span class='text'><strong><center>" . xlt("Charges") . ' ' . "</center></strong><center>" . " %1\$.2f", text($user_info['fee'][$i])) . "</center>";
    Printf("<td width='140'><span class='text'><strong><center>" . xlt("Insurance Adj") . '. ' . "</center></strong><center>" . "%1\$.2f", text($user_info['insadj'][$i])) . "</center>";
    Printf("<td width='140'><span class='text'><strong><center>" . xlt("Insurance Payments") . ' ' . "</center></strong><center>" . "%1\$.2f", text($user_info['inspay'][$i])) . "</center>";
    Printf("<td width='140'><span class='text'><strong><center>" . xlt("Patient Adj") . '. ' . "</center></strong><center>" . "%1\$.2f", text($user_info['patadj'][$i])) . "</center>";
    Printf("<td width='140'><span class='text'><strong><center>" . xlt("Patient Payments") . ' ' . "</center></strong><center>" . "%1\$.2f", text($user_info['patpay'][$i])) . "</center>";

    $gtotal_fee = $gtotal_fee + $user_info[fee][$i];
    $gtotal_insadj = $gtotal_insadj + $user_info[insadj][$i];
    $gtotal_inspay = $gtotal_inspay + $user_info[inspay][$i];
    $gtotal_patadj = $gtotal_patadj + $user_info[patadj][$i];
    $gtotal_patpay = $gtotal_patpay + $user_info[patpay][$i];

    ++$i;

    print "<br /></td>";
}

print "<table border='1'><tr>\n";
print "<br /><br />";

Printf("<td width='70'><span class='text'><strong><center>" . xlt("Grand Totals") . ' ');
Printf("<td width='140'><span class='text'><strong><center>" . xlt("Total Charges") . ' ' . "</center></strong><center>" . " %1\$.2f", text($gtotal_fee)) . "</center>";
Printf("<td width='140'><span class='text'><strong><center>" . xlt("Insurance Adj") . '. ' . "</center></strong><center>" . "%1\$.2f", text($gtotal_insadj)) . "</center>";
Printf("<td width='140'><span class='text'><strong><center>" . xlt("Insurance Payments") . ' ' . "</center></strong><center>" . "%1\$.2f", text($gtotal_inspay)) . "</center>";
Printf("<td width='140'><span class='text'><strong><center>" . xlt("Patient Adj") . '.' . "</center></strong><center>" . "%1\$.2f", text($gtotal_patadj)) . "</center>";
Printf("<td width='140'><span class='text'><strong><center>" . xlt("Patient Payments") . ' ' . "</center></strong><center>" . "%1\$.2f", text($gtotal_patpay)) . "</center>";

print "<br /></td>";
print "</table>";

?>
</body>
</html>
