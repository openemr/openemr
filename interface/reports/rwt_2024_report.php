<?php

/**
 * Real world testing report for 2024.
 *  Dates are hard-coded from 2024-04-01 to 2024-09-30
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Reports\RealWorldTesting;

if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl('2024 Real World Testing Report')]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST['csrf_token_form'], 'rwt_2024_report')) {
        CsrfUtils::csrfNotVerified();
    }
}

// dates for this report are hard-coded (see header for details)
$begin_date = '2024-04-01';
$end_date = '2024-09-30';

// can override dates below for development/testing purposes
// $begin_date = '2022-10-01';
// $end_date = '2022-12-01';
?>

<html>

<head>
    <title><?php echo xlt('2024 Real World Testing Report'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
        @media print {
            #no_print_area {
                display: none;
            }
        }
    </style>
</head>

<body class='body_top'>

<span class='title'><?php echo xlt('2024 Real World Testing Report'); ?></span>

<?php if (empty($_POST['start_button'])) { ?>
    <div class='mt-2 alert alert-primary' role='alert'>
        <?php echo xlt("This report is required for OpenEMR instances in the United States that utilize ONC 2015 certification. This reports collects metrics that are used in Real World Testing that are required for the OpenEMR Foundation to maintain the ONC 2015 certification. This report calculates metrics from April 1, 2024 to September 30, 2024. Please run this report sometime in October or November of 2024 and then print it to a pdf and email the pdf to the OpenEMR Foundation at hello@open-emr.org. In the email, please confirm your practice is in the United States and state the clinical setting of your practice (this can be 'Primary/Specialty Care' setting, 'Behavioral Health Care' setting, or any other setting).") ?>
    </div>
    <form method='post' name='theform' id='theform' action='rwt_2024_report.php' onsubmit='return top.restoreSession()'>
        <input type='hidden' name='csrf_token_form' value='<?php echo attr(CsrfUtils::collectCsrfToken('rwt_2024_report')); ?>' />
        <div class='mt-4'>
            <button type='submit' class='btn btn-primary' name='start_button' value='start_button' onclick='document.getElementById("start_button_spinner").style.display = "inline-block"'>
                <span id='start_button_spinner' style='display: none;' class='spinner-border spinner-border-sm' role='status' aria-hidden='true'></span> <?php echo xlt('Start Report'); ?>
            </button>
        </div>
    </form>
<?php } else { ?>
    <div id='no_print_area' >
        <div class='mt-2 alert alert-primary' role='alert'>
            <?php echo xlt("Please print this report to a pdf and email the pdf to the OpenEMR Foundation at hello@open-emr.org. In the email, please confirm your practice is in the United States and state the clinical setting of your practice (this can be 'Primary/Specialty Care' setting, 'Behavioral Health Care' setting, or any other setting).") ?>
        </div>
        <div class='mt-4'>
            <button type='button' class='btn btn-primary' onclick='window.print()'>
                <?php echo xlt('Print Report'); ?>
            </button>
        </div>
    </div>
    <div class='mt-4'>
        <?php echo (new RealWorldTesting($begin_date, $end_date))->renderReport(); ?>
    </div>
<?php } ?>

</body>

</html>
