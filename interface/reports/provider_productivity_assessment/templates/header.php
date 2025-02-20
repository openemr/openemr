<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

global $headerData;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\Help\HelpServices;

$help_icon = (new HelpServices())->getHelpIcon();

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt($headerData['title']) ?></title>
    <?php Header::setupHeader(['opener', 'datetime-picker']) ?>
    <style>
        thead tr {
            position: sticky;
            top: 0;
            background-color: #fff; /* Optional: set background to avoid overlap issues */
            z-index: 10; /* Ensures header is above content when scrolling */
        }
    </style>
    <script>
        function createExport() {
            const table = document.getElementById("reportTable");
            const rows = table.rows;
            let csv = [];
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                for (let j = 0; j < rows[i].cells.length; j++) {
                    row.push(rows[i].cells[j].innerText);
                }
                csv.push(row.join(","));
            }
            const csvData = csv.join("\n");
            const blob = new Blob([csvData], {type: 'text/csv'});
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = "report.csv";
            a.click();
        }

        function printReport() {
            const printContents = document.getElementById("printableReport").innerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
        }

    </script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 mt-5">
                <div class="page-header">
                    <h1 id="header_title" class="text-center"> <span id="header_text"><?php echo xlt($headerData['title']) ?></span><?php echo $help_icon; ?></h1>
                </div>
            </div>
        </div>
        <?php
        if (!AclMain::aclCheckCore('acct', 'rep_a')) {
            echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl($headerData['title'])]);
            exit;
        }
        ?>
        <div class="row">
            <div class="col-md-12 mt-5">
                <form action="index.php" method="post">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div class="form-group  row">
                        <label for="fromDate" class="col-md-1 col-form-label"><b><?php echo xlt('From Date') ?></b></label>
                        <div class="col-md-3">
                            <input type="text" class="form-control datepicker" id="fromDate" name="fromDate" value="<?php echo attr($_POST['fromDate'] ?? '') ?>">
                        </div>
                        <label for="toDate" class="col-md-1 col-form-label"><b><?php echo xlt('To Date') ?></b></label>
                        <div class="col-md-3">
                            <input type="text" class="form-control datepicker" id="toDate" name="toDate" value="<?php echo attr($_POST['toDate'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary"><?php echo xlt('Submit') ?></button>
                            <button  onclick="printReport()" class="btn btn-primary"><?php echo xlt('Print') ?></button>
                            <button  onclick="createExport()" class="btn btn-primary"><?php echo xlt('Export') ?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script>
            $(function () {
                $('.datepicker').datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = false; ?>
                    <?php $datetimepicker_formatInput = true; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });
        </script>







