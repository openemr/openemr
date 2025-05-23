<?php

/**
 * Note this is the presentation view for the ControllerLog::_action_view method
 * This file is part of the Clinical Decision Rules (CDR) module for OpenEMR
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Aron Racho <aron@mi-squared.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2010-2011 Aron Racho <aron@mi-squared.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 * @license   There are segments of code in this file that have been generated via ChatGPT and are licensed as Public Domain, they are marked with a header and footer.
 */

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (empty($viewBean)) {
    // should never get here...
    die("Invalid viewBean");
}

$form_begin_date = $viewBean->form_begin_date;
$form_end_date = $viewBean->form_end_date;
$search = $viewBean->search;
$records = $viewBean->records ?>

<html>

<head>
    <title><?php echo xlt('Alerts Log'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

    <script>
        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = true; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });

            $('#download_button').on('click', function(event) {
                top.restoreSession();
                event.preventDefault();
                event.stopPropagation();

                // old school but it works and I'm too tired to refactor it.
                let action = $("#theform").attr("action");
                let uri = new URL(action, window.location.href);
                uri.searchParams.set("action", "log!download");
                uri.searchParams.set("csrf_token_form", "<?php echo attr(CsrfUtils::collectCsrfToken()); ?>");
                uri.searchParams.set("form_begin_date", $("#form_begin_date").val());
                uri.searchParams.set("form_end_date", $("#form_end_date").val());
                window.location.href = uri.href;
                return false;
            })

        });
    </script>

    <style>
        /* specifically include & exclude from printing */
        @media print {
            #report_parameters {
                visibility: hidden;
                display: none;
            }
            #report_parameters_daterange {
                visibility: visible;
                display: inline;
            }
            #report_results table {
                margin-top: 0px;
            }
        }

        /* specifically exclude some from the screen */
        @media screen {
            #report_parameters_daterange {
                visibility: hidden;
                display: none;
            }
        }
    </style>
</head>

<body class="blah/blah">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Alerts Log'); ?></span>

<form method='post' name='theform' id='theform' action='cdr_log.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
    <input type="hidden" id="cdr_action" name="action" value="log!view" />

    <div id="report_parameters">

        <table>
            <tr>
                <td width='470px'>
                    <div style='float: left'>

                        <table class='text'>

                            <tr>
                                <td class='col-form-label'>
                                    <?php echo xlt('Begin Date'); ?>:
                                </td>
                                <td>
                                    <input type='text' name='form_begin_date' id='form_begin_date' size='20' value='<?php echo attr(oeFormatDateTime($form_begin_date, "global", true)); ?>'
                                           class='datepicker form-control'>
                                </td>
                            </tr>

                            <tr>
                                <td class='col-form-label'>
                                    <?php echo xlt('End Date'); ?>:
                                </td>
                                <td>
                                    <input type='text' name='form_end_date' id='form_end_date' size='20' value='<?php echo attr(oeFormatDateTime($form_end_date, "global", true)); ?>'
                                           class='datepicker form-control'>
                                </td>
                            </tr>
                        </table>
                    </div>

                </td>
                <td align='left' valign='middle' height="100%">
                    <table style='border-left: 1px solid; width:100%; height:100%' >
                        <tr>
                            <td>
                                <div class="text-center">
                                    <div class="btn-group" role="group">
                                        <input type="submit" id='search_button' name="search" class='btn btn-secondary btn-search' onclick='top.restoreSession(); $("#cdr_action").val("log!view"); $("#theform").submit()' value="<?php echo attr("Search"); ?>" />
                                        <input type="submit" id='download_button' name="download" class='btn btn-secondary' value="<?php echo attr("Download"); ?>" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <p class="text-left"><i class='ml-2 fa fa-comment' title=" <?php echo xla("Select feedback icon to show feedback") ?>"></i> <?php echo xlt("Rule has user provided feedback, select icon to show feedback"); ?></p>
                </td>
            </tr>
        </table>

    </div>  <!-- end of search parameters -->

    <br />

    <?php if (!empty($search) && ($search == 1)) { ?>
        <div id="report_results">
            <table class="table">

                <thead>
                <th align='center'>
                    <?php echo xlt('Date'); ?>
                </th>

                <th align='center'>
                    <?php echo xlt('Patient ID'); ?>
                </th>

                <th align='center'>
                    <?php echo xlt('User ID'); ?>
                </th>

                <th align='center'>
                    <?php echo xlt('Facility ID'); ?>
                </th>

                <th align='center'>
                    <?php echo xlt('Category'); ?>
                </th>

                <th align='center'>
                    <?php echo xlt('All Alerts'); ?>
                </th>

                <th align='center'>
                    <?php echo xlt('New Alerts'); ?>
                </th>

                </thead>
                <tbody>  <!-- added for better print-ability -->
                <?php foreach ($records as $row) : ?>
                    <tr>
                        <td><?php echo text(oeFormatDateTime($row['date'], "global", true)); ?></td>
                        <td><?php echo text($row['pid']); ?></td>
                        <td><?php echo text($row['uid']); ?></td>
                        <td><?php echo text($row['facility_id']); ?></td>
                        <td><?php echo text($row['category_title']); ?></td>
                        <td>
                            <?php
                            //list off all targets with rule information shown when hover
                            foreach ($row['formatted_all_alerts'] as $alert) {
                                if (!empty($alert['rule_action_category'])) {
                                    echo "<span title='" . attr($alert['title']) . "'>" .
                                        text($alert['rule_action_category']) . ": " . text($alert['rule_action']) .
                                        " (" . text($alert['due_status']) . ")" .
                                        "</span>";
                                    // should never be empty... but just in case
                                } else if (!empty($alert['text'])) {
                                    echo text($alert['text']);
                                }
                                //  need to add comment icon here if we have one
                                if (!empty($alert['feedback'])) {
                                    $id = $row['id'] . "-" . $alert['rule_id'];
                                    echo "<i class='ml-2 fa fa-comment cdr-rule-feedback' title=" . xla("Select feedback icon to show feedback") . " data-feedback='" . attr($alert['feedback']) . "'></i>";
                                }
                                echo "<br />";
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if (!empty($row['formatted_new_alerts'])) {
                                foreach ($row['formatted_new_alerts'] as $alert) {
                                    if (!empty($alert['rule_action_category'])) {
                                        echo "<span title='" . attr($alert['title']) . "'>" .
                                            text($alert['rule_action_category']) . ": " . text($alert['rule_action']) .
                                            " (" . text($alert['due_status']) . ")" .
                                            "</span>";
                                        // should never be empty... but just in case
                                    } else if (!empty($alert['text'])) {
                                        echo text($alert['text']);
                                    }
                                    //  need to add comment icon here if we have one
                                    if (!empty($alert['feedback'])) {
                                        $id = $row['id'] . "-" . $alert['rule_id'];
                                        echo "<i class='ml-2 fa fa-comment cdr-rule-feedback' title=" . xla("Select feedback icon to show feedback") . " data-feedback='" . attr($alert['feedback']) . "'></i>";
                                    }
                                    echo "<br />";
                                }
                            } else {
                                echo "&nbsp;";
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>  <!-- end of search results -->

    <?php } // end of if search button clicked ?>

</form>
<div id="cdr-modal-feedback-template" class="d-none">
    <h1><?php echo xlt("Rule Feedback"); ?></h1>
    <p class="content"></p>
</div>
<script>
    (function(window) {
        window.document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.cdr-rule-feedback').forEach(function (element) {
                element.addEventListener('click', function () {
                    let dialog = document.querySelector('#cdr-modal-feedback-template');
                    let contents = dialog.cloneNode(true);
                    contents.classList.remove("d-none");
                    contents.querySelector('.content').innerText = this.dataset.feedback || <?php echo xlj('No feedback available'); ?>;
                    dlgopen('', '', 800, 200, '', '', {
                        buttons: [{
                            text: <?php echo xlj('Close'); ?>,
                            close: true,
                            style: 'secondary btn-sm'
                        }],
                        allowResize: false,
                        allowDrag: true,
                        dialogId: 'cdr-rule-feedback',
                        html: contents.innerHTML,
                        type: 'alert'
                    });
                });
            });
        });
    })(window);
</script>

</body>

</html>
