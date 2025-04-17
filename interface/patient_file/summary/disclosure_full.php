<?php

/**
 * Patient disclosures main screen.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Control access
if (!AclMain::aclCheckCore('patients', 'disclosure')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Disclosures")]);
    exit;
}
$authWrite = AclMain::aclCheckCore('patients', 'disclosure', '', 'write');
$authAddonly = AclMain::aclCheckCore('patients', 'disclosure', '', 'addonly');

//retrieve the user name
$res = sqlQuery("select username from users where username=?", array($_SESSION["authUser"]));
$uname = $res["username"];
//if the mode variable is set to disclosure, retrieve the values from 'disclosure_form ' in record_disclosure.php to store it in database.
if (isset($_POST["mode"]) and  $_POST["mode"] == "disclosure") {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $dates = trim($_POST['dates']);
    $event = trim($_POST['form_disclosure_type']);
    $recipient_name = trim($_POST['recipient_name']);
    $disclosure_desc = trim($_POST['desc_disc']);
    $disclosure_id = trim($_POST['disclosure_id'] ?? '');
    if (isset($_POST["updatemode"]) and $_POST["updatemode"] == "disclosure_update") {
        if (!$authWrite) {
            echo xlt('Not Authorized');
            exit;
        }
        //update the recorded disclosure in the extended_log table.
        EventAuditLogger::instance()->updateRecordedDisclosure($dates, $event, $recipient_name, $disclosure_desc, $disclosure_id);
    } else {
        if (!$authWrite && !$authAddonly) {
            echo xlt('Not Authorized');
            exit;
        }
        //insert the disclosure records in the extended_log table.
        EventAuditLogger::instance()->recordDisclosure($dates, $event, $pid, $recipient_name, $disclosure_desc, $uname);
    }
    // added ajax submit to record_disclosure thus an exit() 12/19/17
    exit();
}

if (isset($_GET['deletelid'])) {
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if (!$authWrite) {
        echo xlt('Not Authorized');
        exit;
    }

    $deletelid = $_GET['deletelid'];
    //function to delete the recorded disclosures
    EventAuditLogger::instance()->deleteDisclosure($deletelid);
}
?>
<html>
<head>

    <?php Header::setupHeader(['common']); ?>

</head>

<body>
<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <h2 class="title">
                <?php echo xlt('Disclosures'); ?>
                <?php echo xlt('for'); ?>&nbsp;
                <a href="../summary/demographics.php" onclick="top.restoreSession()">
                    <?php $pname = getPatientName($pid);
                    echo text($pname); ?>
                </a>
            </h2>
        </div>
        <div class="col-12">
            <?php if ($authWrite || $authAddonly) { ?>
                <a href="record_disclosure.php" class="btn btn-primary iframe" onclick="top.restoreSession()"><?php echo xlt('Record'); ?></a>
            <?php } ?>
            <a href="demographics.php" class="btn btn-primary" onclick="top.restoreSession()"> <?php echo xlt('View Patient') ?></a>
        </div>
        <div class="col-12 jumbotron mt-3 p-4">
            <?php
            $N = 15;
            $offset = $_REQUEST['offset'] ?? 0;

            $disclQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el" .
            " LEFT JOIN users u ON u.username = el.user " .
            " WHERE el.patient_id = ? AND el.event IN (SELECT option_id FROM list_options WHERE list_id='disclosure_type' AND activity = 1)" .
            " ORDER BY el.date DESC ";
            $r2 = sqlStatement($disclQry, array($pid));
            $totalRecords = sqlNumRows($r2);

            $disclInnerQry = " SELECT el.id, el.event, el.recipient, el.description, el.date, CONCAT(u.fname, ' ', u.lname) as user_fullname FROM extended_log el" .
            " LEFT JOIN users u ON u.username = el.user" .
            " WHERE patient_id = ? AND event IN (SELECT option_id FROM list_options WHERE list_id = 'disclosure_type' AND activity = 1)" .
            " ORDER BY date DESC LIMIT " . escape_limit($offset) . " , " . escape_limit($N);

            $r1 = sqlStatement($disclInnerQry, array($pid));
            $n = sqlNumRows($r1);
            $noOfRecordsLeft = ($totalRecords - $offset);
            if ($n > 0) {?>
            <div class="table-responsive">
                <table class="table table-borderless">
                    <tr>
                        <td colspan='5'><a href="disclosure_full.php" id='Submit' onclick="top.restoreSession()"><span><?php echo xlt('Refresh'); ?></span></a></td>
                    </tr>
                </table>
                <div id='pnotes'>
                    <table class="table table-borderless">
                        <tr class="showborder_head">
                            <th style='width: 120px';>&nbsp;</th>
                            <th width="140px"><?php echo xlt('Recipient Name'); ?></th>
                            <th width="140px"><?php echo xlt('Disclosure Type'); ?></th>
                            <th><?php echo xlt('Description'); ?></th>
                            <th><?php echo xlt('Provider'); ?></th>
                        </tr>
                    <?php
                    $result2 = array();
                    for ($iter = 0; $frow = sqlFetchArray($r1); $iter++) {
                        $result2[$iter] = $frow;
                    }

                    foreach ($result2 as $iter) { ?>
                        <!-- List the recipient name, description, date and edit and delete options-->
                        <tr class="noterow" height='25'>
                            <!--buttons for edit and delete.-->
                            <td class="align-top text-nowrap">
                                <?php if ($authWrite) { ?>
                                    <a href='record_disclosure.php?editlid=<?php echo attr_url($iter['id']); ?>' class='btn btn-primary btn-sm btn-edit iframe' onclick='top.restoreSession()'><?php echo xlt('Edit');?></a>
                                    <a href='#' class='deletenote btn btn-danger btn-delete btn-sm' id='<?php echo attr($iter['id']); ?>' onclick='top.restoreSession()'><?php echo xlt('Delete');?></a>
                                <?php } ?>
                            </td>
                            <td class="align-top" valign='top'><?php echo text($iter['recipient']);?>&nbsp;</td>
                            <td class='align-top' valign='top'><?php echo text(getListItemTitle('disclosure_type', $iter['event'])); ?>&nbsp;</td>
                            <td><?php echo text($iter['date']) . " " . nl2br(text($iter['description']));?>&nbsp;</td>
                            <td><?php echo text($iter['user_fullname']);?></td>
                        </tr>
                        <?php
                    }
            } else {?>
                    <br />
                    <!-- Display None, if there is no disclosure -->
                    <span colspan='3'><?php echo xlt('None{{Disclosure}}');?></span>
                    <?php
            }
            ?>
                    </table>
                    <table class="table table-borderless">
                        <tr>
                            <td>
                            <?php
                            if ($offset > ($N - 1) && $n != 0) {
                                echo "   <a class='link' href='disclosure_full.php?active=" . attr_url($active) .
                                    "&offset=" . attr_url($offset - $N) . "' onclick='top.restoreSession()'>[" .
                                    xlt('Previous') . "]</a>\n";
                            }
                            ?>

                            <?php

                            if ($n >= $N && $noOfRecordsLeft != $N) {
                                echo "&nbsp;&nbsp;   <a class='link' href='disclosure_full.php?active=" . attr_url($active) .
                                    "&offset=" . attr_url($offset + $N)  . "&leftrecords=" . attr_url($noOfRecordsLeft) . "' onclick='top.restoreSession()'>[" .
                                    xlt('Next') . "]</a>\n";
                            }
                            ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function () {
    // todo, move this to a common library
    //for row highlight.
    $(".noterow").mouseover(function () {
        $(this).toggleClass("highlight");
    });
    $(".noterow").mouseout(function () {
        $(this).toggleClass("highlight");
    });

    //for deleting the disclosures
    $(".deletenote").click(function () {
        DeleteNote(this);
    });

    var DeleteNote = function (logevent) {
        if (confirm(<?php echo xlj('Are you sure you want to delete this disclosure?'); ?> + "\n " + <?php echo xlj('This action CANNOT be undone.'); ?>)) {
            top.restoreSession();
            window.location.replace("disclosure_full.php?deletelid=" + encodeURIComponent(logevent.id) + "&csrf_token_form=" + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>);
        }
    }

    $(".iframe").on('click', function(e) {
        e.preventDefault();e.stopPropagation();
        dlgopen('', '', 500, 310, '', '', {
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});
// for record disclosure dlgclose callback
function refreshme() {
    top.restoreSession();
    document.location.reload();
}
</script>
</body>
</html>


