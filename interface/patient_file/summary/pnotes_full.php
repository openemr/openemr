<?php

/**
 * Display, enter, modify and manage patient notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018-2020 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once('../../globals.php');
require_once($GLOBALS['srcdir'] . '/pnotes.inc');
require_once($GLOBALS['srcdir'] . '/patient.inc');
require_once($GLOBALS['srcdir'] . '/options.inc.php');
require_once($GLOBALS['srcdir'] . '/gprelations.inc.php');

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;
use OpenEMR\Services\UserService;

if (!empty($_GET['set_pid'])) {
    require_once($GLOBALS['srcdir'] . '/pid.inc');
    setpid($_GET['set_pid']);
}

// form parameter docid can be passed to restrict the display to a document.
$docid = empty($_REQUEST['docid']) ? 0 : 0 + $_REQUEST['docid'];

// form parameter orderid can be passed to restrict the display to a procedure order.
$orderid = empty($_REQUEST['orderid']) ? 0 : intval($_REQUEST['orderid']);

$patient_id = $pid;

$userService = new UserService();

if ($docid) {
    $row = sqlQuery("SELECT foreign_id FROM documents WHERE id = ?", array($docid));
    $patient_id = intval($row['foreign_id']);
} elseif ($orderid) {
    $row = sqlQuery("SELECT patient_id FROM procedure_order WHERE procedure_order_id = ?", array($orderid));
    $patient_id = intval($row['patient_id']);
}

// Check authorization.
if (!AclMain::aclCheckCore('patients', 'notes', '', array('write','addonly'))) {
    die(xlt('Not authorized'));
}

$tmp = getPatientData($patient_id, "squad");
if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
    die(xlt('Not authorized for this squad.'));
}

//the number of records to display per screen
$N = 15;
$M = 15;

$mode   = $_REQUEST['mode'] ?? null;
$offset = $_REQUEST['offset'] ?? null;
$offset_sent = $_REQUEST['offset_sent'] ?? null;
$form_active = $_REQUEST['form_active'] ?? null;
$form_inactive = $_REQUEST['form_inactive'] ?? null;
$noteid = $_REQUEST['noteid'] ?? null;
$form_doc_only = isset($_POST['mode']) ? (empty($_POST['form_doc_only']) ? 0 : 1) : 1;
if (!empty($_REQUEST['s']) && ($_REQUEST['s'] == '1')) {
    $inbox = "";
    $outbox = "current";
    $inbox_style = "style='display:none;border:5px solid var(--white);'";
    $outbox_style = "style='border:5px solid var(--white);'";
} else {
    $inbox = "current";
    $outbox = "";
    $inbox_style = "style='border:5px solid var(--white);'";
    $outbox_style = "style='display:none;border:5px solid var(--white);'";
}

if (!isset($offset)) {
    $offset = 0;
}

if (!isset($offset_sent)) {
    $offset_sent = 0;
}

// Collect active variable and applicable html code for links
if ($form_active) {
    $active = '1';
    $activity_string_html = 'form_active=1';
} elseif ($form_inactive) {
    $active = '0';
    $activity_string_html = 'form_inactive=1';
} else {
    $active = 'all';
    $activity_string_html = '';
    $form_active = $form_inactive = '0';
}

// this code handles changing the state of activity tags when the user updates
// them through the interface
if (isset($mode)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($mode == "update") {
        foreach ($_POST as $var => $val) {
            if (strncmp($var, 'act', 3) == 0) {
                $id = str_replace("act", "", $var);
                if ($_POST["chk$id"]) {
                    reappearPnote($id);
                } else {
                    disappearPnote($id);
                }

                if ($docid) {
                    setGpRelation(1, $docid, 6, $id, !empty($_POST["lnk$id"]));
                }

                if ($orderid) {
                    setGpRelation(2, $orderid, 6, $id, !empty($_POST["lnk$id"]));
                }
            }
        }
    } elseif ($mode == "new") {
        $note = $_POST['note'];
        if ($noteid) {
            updatePnote($noteid, $note, $_POST['form_note_type'], $_POST['assigned_to'], '', !empty($_POST['form_datetime']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_datetime']) : '');
        } else {
            $noteid = addPnote(
                $patient_id,
                $note,
                $userauthorized,
                '1',
                $_POST['form_note_type'],
                $_POST['assigned_to'],
                !empty($_POST['form_datetime']) ? DateTimeToYYYYMMDDHHMMSS($_POST['form_datetime']) : ''
            );
        }

        if ($docid) {
            setGpRelation(1, $docid, 6, $noteid);
        }

        if ($orderid) {
            setGpRelation(2, $orderid, 6, $noteid);
        }

        $noteid = '';
    } elseif ($mode == "delete") {
        if ($noteid) {
            deletePnote($noteid);
            EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "pnotes: id " . $noteid);
        }

        $noteid = '';
    }
    if ($mode != "delete" && $mode != "update") {
        exit(); // add exit for ajax save from pnotes_full_add.php sjp 12/20/2017
    }
}

$title = '';
$assigned_to = $_SESSION['authUser'];
if ($noteid) {
    $prow = getPnoteById($noteid, 'title,assigned_to,body');
    $title = $prow['title'];
    $assigned_to = $prow['assigned_to'];
}

// Get the users list.  The "Inactive" test is a kludge, we should create
// a separate column for this.
$ures = sqlStatement("SELECT username, fname, lname FROM users " .
 "WHERE username != '' AND active = 1 AND " .
 "( info IS NULL OR info NOT LIKE '%Inactive%' ) " .
 "ORDER BY lname, fname");

$pres = getPatientData($patient_id, "lname, fname");
$patientname = $pres['lname'] . ", " . $pres['fname'];

//retrieve all notes
$result = getPnotesByDate(
    "",
    $active,
    'id,date,body,user,activity,title,assigned_to,message_status,update_date,update_by',
    $patient_id,
    $N,
    $offset,
    '',
    $docid,
    '',
    $orderid
);
$result_sent = getSentPnotesByDate(
    "",
    $active,
    'id,date,body,user,activity,title,assigned_to,message_status,update_date,update_by',
    $patient_id,
    $M,
    $offset_sent,
    '',
    $docid,
    '',
    $orderid
);
?>
<!DOCTYPE html>
<html>
<head>

    <?php Header::setupHeader(['common', 'opener']); ?>

<script>
/// todo, move this to a common library

$(function () {

    $("#dem_view").click( function() {
        toggle( $(this), "#DEM" );
    });

    // load divs

    // I can't find a reason to load this!
    /*$("#stats_div").load("stats.php",
        {
            csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
        }
    );*/

    $("#notes_div").load("pnotes_fragment.php",
        {
            csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
        }
    );

    tabbify();

    $(".note_modal").on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dlgopen('', '', 700, 400, '', '', {
            buttons: [
                {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ],
            allowResize: true,
            allowDrag: true,
            dialogId: '',
            type: 'iframe',
            url: $(this).attr('href')
        });
    });

});

function show_div(name){
  if(name == 'inbox'){
    document.getElementById('inbox_div').style.display = '';
    document.getElementById('outbox_div').style.display = 'none';
  }else{
    document.getElementById('inbox_div').style.display = 'none';
    document.getElementById('outbox_div').style.display = '';
  }
}

function refreshme() {
    top.restoreSession();
    document.location.reload();
}

function restoreSession() {
    return opener.top.restoreSession();
}
</script>
</head>
<body>

<div class="container mt-3" id="pnotes"> <!-- large outer DIV -->

    <form method='post' name='new_note' id="new_note" action='pnotes_full.php?docid=<?php echo attr_url($docid); ?>&orderid=<?php echo attr_url($orderid); ?>&<?php echo $activity_string_html; ?>' onsubmit='return top.restoreSession()'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

        <?php
        $title_docname = "";
        if ($docid) {
            $title_docname .= " " . xl("linked to document") . " ";
            $d = new Document($docid);
            $title_docname .= $d->get_url_file();
        }

        if ($orderid) {
            $title_docname .= " " . xl("linked to procedure order") . " $orderid";
        }

        $urlparms = "docid=" . attr_url($docid) . "&orderid=" . attr_url($orderid);
        $title = text(getPatientName($patient_id));
        ?>
    <title><?php echo $title; ?></title>
    <div class="row">
        <div class="col-12">
            <h3><?php echo xlt('Patient Messages') . text($title_docname) . " " . xlt('for');
            if (!$orderid) {
                ?><span>
                    <a href="../summary/demographics.php" onclick="return top.restoreSession()"><?php echo $title; ?></a>
                </span>
                <?php } else { ?>
                    <span><?php echo $title; ?></span><?php } ?>
            </h3>
        </div>
        <div class="row oe-margin-b-10">
            <div class="col-12">
            <div class="btn-group">
                    <a href="pnotes_full_add.php?<?php echo $urlparms; ?>" class="btn btn-primary btn-add note_modal" onclick='return top.restoreSession()'><?php echo xlt('Add'); ?></a>
                    <?php if (!$orderid) { ?>
                        <a href="demographics.php" class="btn btn-secondary btn-back" onclick="top.restoreSession()"><?php echo xlt('Back to Patient'); ?></a>
                    <?php } ?>
                    <a href="pnotes_full.php?<?php echo $urlparms; ?>&<?php echo $activity_string_html;?>" class="btn btn-secondary btn-update" id='Submit' onclick='return top.restoreSession()'><?php echo xlt('Refresh'); ?></a>
                    <a href="#" class="change_activity btn btn-secondary btn"><?php echo xlt('Update Active'); ?></a>
            </div>
            </div>

        </div>
        <div class="row oe-margin-b-10">
            <div class="col-12">
                <?php
                // Get the billing note if there is one.
                $billing_note = "";
                $colorbeg = "";
                $colorend = "";
                $resnote = getPatientData($patient_id, "billing_note");
                if (!empty($resnote['billing_note'])) {
                    $billing_note = $resnote['billing_note'];
                    $colorbeg = "<span class='text-danger'>";
                    $colorend = "</span>";
                }

                //Display what the patient owes
                $balance = get_patient_balance($patient_id);
                ?>

                <?php if ($billing_note || $balance) { ?>
                    <div class="table-responsive mt-1">
                        <table class="table">
                            <?php
                            if ($balance != "0") {
                                // $formatted = sprintf((xl('$').'%01.2f'), $balance);
                                $formatted = oeFormatMoney($balance);
                                echo " <tr class='text billing'>\n";
                                echo "  <td>" . $colorbeg . xlt('Balance Due') .
                                    $colorend . "&nbsp;" . $colorbeg . text($formatted) .
                                    $colorend . "</td>\n";
                                echo " </tr>\n";
                            }

                            if ($billing_note) {
                                echo " <tr class='text billing'>\n";
                                echo "  <td>" . $colorbeg . xlt('Billing Note') .
                                    $colorend . "&nbsp;" . $colorbeg . text($billing_note) .
                                    $colorend . "</td>\n";
                                echo " </tr>\n";
                            }
                            ?>
                        </table>
                    </div>
                <?php } ?>
            </div>
        </div>

        <input type='hidden' name='mode' id="mode" value="new" />
        <input type='hidden' name='offset' id="offset" value="<?php echo attr($offset); ?>" />
        <input type='hidden' name='offset_sent' id="offset_sent" value="<?php echo attr($offset_sent); ?>" />
        <input type='hidden' name='form_active' id="form_active" value="<?php echo attr($form_active); ?>" />
        <input type='hidden' name='form_inactive' id="form_inactive" value="<?php echo attr($form_inactive); ?>" />
        <input type='hidden' name='noteid' id="noteid" value="<?php echo attr($noteid); ?>" />
        <input type='hidden' name='form_doc_only' id="form_doc_only" value="<?php echo attr($form_doc_only); ?>" />
    </form>

    <div class='tabContainer jumbotron p-4'>
        <?php if ($active == "all") { ?>
            <span><?php echo xlt('Show All'); ?></span>
        <?php } else { ?>
            <a href="pnotes_full.php?<?php echo $urlparms; ?>" class="link btn btn-secondary" onclick="return top.restoreSession()"><span><?php echo xlt('Show All'); ?></span></a>
        <?php } ?>
        |
        <?php if ($active == '1') { ?>
            <span><?php echo xlt('Show Active'); ?></span>
        <?php } else { ?>
            <a href="pnotes_full.php?form_active=1&<?php echo $urlparms; ?>" class="link btn btn-secondary" onclick="return top.restoreSession()"><span><?php echo xlt('Show Active'); ?></span></a>
        <?php } ?>
        |
        <?php if ($active == '0') { ?>
            <span><?php echo xlt('Show Inactive'); ?></span>
        <?php } else { ?>
            <a href="pnotes_full.php?form_inactive=1&<?php echo $urlparms; ?>" class="link btn btn-secondary" onclick="return top.restoreSession()"><span><?php echo xlt('Show Inactive'); ?></span></a>
        <?php } ?>
        <div id='inbox_div' class="table-responsive">
            <form method='post' name='update_activity' id='update_activity'
                action="pnotes_full.php?<?php echo $urlparms; ?>&<?php echo $activity_string_html;?>" onsubmit='return top.restoreSession()'>
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

                <!-- start of previous notes DIV -->
                <div class="pat_notes">
                    <input type='hidden' name='mode' value="update" />
                    <input type='hidden' name='offset' id='offset' value="<?php echo attr($offset); ?>" />
                    <input type='hidden' name='offset_sent' id='offset_sent' value="<?php echo attr($offset_sent); ?>" />
                    <input type='hidden' name='noteid' id='noteid' value="0" />

                    <table class="table table-borderless text">
                    <?php if ($result != "") : ?>
                    </table>
                    <?php endif; ?>

                    <table class="table table-borderless text">
                        <?php
                        // display all of the notes for the day, as well as others that are active
                        // from previous dates, up to a certain number, $N

                        if ($result != "") {
                            echo " <tr class='showborder_head'>\n";
                            echo "  <th>" . xlt('Actions') . "</th>\n";
                            echo "  <th>" . xlt('Active{{Note}}') . "&nbsp;</th>\n";
                            echo "  <th>" . (($docid || $orderid) ? xlt('Linked') : '') . "</th>\n";
                            echo "  <th>" . xlt('Type') . "</th>\n";
                            echo "  <th>" . xlt('Content') . "</th>\n";
                            echo "  <th>" . xlt('Status') . "</th>\n";
                            echo "  <th>" . xlt('Last update') . "</th>\n";
                            echo "  <th>" . xlt('Update by') . "</th>\n";
                            echo " </tr>\n";

                            $result_count = 0;
                            foreach ($result as $iter) {
                                $result_count++;
                                $row_note_id = $iter['id'];

                                $linked = "";
                                if ($docid) {
                                    if (isGpRelation(1, $docid, 6, $row_note_id)) {
                                        $linked = "checked";
                                    } else {
                                        // Skip unlinked notes if that is requested.
                                        if ($form_doc_only) {
                                            continue;
                                        }
                                    }
                                } elseif ($orderid) {
                                    if (isGpRelation(2, $orderid, 6, $row_note_id)) {
                                        $linked = "checked";
                                    } else {
                                        // Skip unlinked notes if that is requested.
                                        if ($form_doc_only) {
                                            continue;
                                        }
                                    }
                                }

                                $body = $iter['body'];
                                $body = preg_replace('/(\sto\s)-patient-(\))/', '${1}' . $patientname . '${2}', $body);
                                $body = preg_replace('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}\s\([^)(]+\s)(to)(\s[^)(]+\))/', '${1}' . xl('to{{Destination}}') . '${3}', $body);
                                if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
                                    $body = pnoteConvertLinks(nl2br(text(oeFormatPatientNote($body))));
                                } else {
                                    $body = text(oeFormatSDFT(strtotime($iter['date'])) . date(' H:i', strtotime($iter['date']))) .
                                    ' (' . text($iter['user']) . ') ' . pnoteConvertLinks(nl2br(text(oeFormatPatientNote($body))));
                                }

                                if (($iter["activity"]) && ($iter['message_status'] != "Done")) {
                                    $checked = "checked";
                                } else {
                                    $checked = "";
                                }

                                // highlight the row if it's been selected for updating
                                if (!empty($_REQUEST['noteid']) && ($_REQUEST['noteid'] == $row_note_id)) {
                                    echo " <tr class='noterow highlightcolor' id='" . attr($row_note_id) . "'>\n";
                                } else {
                                    echo " <tr class='noterow' id='" . attr($row_note_id) . "'>\n";
                                }


                                echo "  <td class='text-nowrap'><a href='pnotes_full_add.php?$urlparms&trigger=edit&noteid=" . attr_url($row_note_id) .
                                "' class='btn btn-primary btn-sm btn-edit note_modal' onclick='return top.restoreSession()'>" . xlt('Edit') . "</a>\n";

                                // display, or not, a button to delete the note
                                // if the user is an admin or if they are the author of the note, they can delete it
                                if (($iter['user'] == $_SESSION['authUser']) || (AclMain::aclCheckCore('admin', 'super', '', 'write'))) {
                                    echo " <a href='#' class='deletenote btn btn-danger btn-sm btn-delete' id='del" . attr($row_note_id) .
                                    "' title='" . xla('Delete this note') . "' onclick='return top.restoreSession()'>" .
                                    xlt('Delete') . "</a>\n";
                                }

                                echo "  </td>\n";


                                echo "  <td class='text font-weight-bold'>\n";
                                echo "   <input type='hidden' name='act" . attr($row_note_id) . "' value='1' />\n";
                                echo "   <input type='checkbox' name='chk" . attr($row_note_id) . "' $checked />\n";
                                echo "  </td>\n";

                                echo "  <td class='text font-weight-bold'>\n";
                                if ($docid || $orderid) {
                                    echo "   <input type='checkbox' name='lnk" . attr($row_note_id) . "' $linked />\n";
                                }

                                echo "  </td>\n";

                                echo "  <td class='font-weight-bold notecell' id='" . attr($row_note_id) . "'>" .
                                "<a href='pnotes_full_add.php?$urlparms&trigger=edit&noteid=" . attr_url($row_note_id) . "' class='note_modal' onclick='return top.restoreSession()'>\n";
                                // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
                                echo generate_display_field(array('data_type' => '1','list_id' => 'note_type'), $iter['title']);
                                echo "  </a></td>\n";

                                echo "  <td class='notecell' id='" . attr($row_note_id) . "'>\n";
                                echo "   $body";
                                echo "  </td>\n";
                                echo "  <td class='notecell' id='" . attr($row_note_id) . "'>\n";
                                echo getListItemTitle("message_status", $iter['message_status']);
                                echo "  </td>\n";
                                echo "  <td class='notecell'>";
                                echo text(oeFormatDateTime($iter['update_date']));
                                echo "  </td>\n";
                                echo "  <td class='notecell'>";
                                $updateBy = $userService->getUser($iter['update_by']);
                                echo !is_null($updateBy) ? text($updateBy['fname']) . ' ' . text($updateBy['lname']) : '';
                                echo "  </td>\n";
                                echo " </tr>\n";
                            }
                        } else {
                            //no results
                            print "<tr><td colspan='3' class='text'>" . xlt('None{{Note}}') . ".</td></tr>\n";
                        }
                        ?>
                    </table>
                </div>
            </form>

            <table class="table table-borderless">
                <tr>
                    <td>
                        <?php
                        if ($offset > ($N - 1)) {
                            $offsetN = $offset - $N;
                            echo "   <a class='link' href='pnotes_full.php" .
                            "?$urlparms" .
                            "&form_active=" . attr_url($form_active) .
                            "&form_inactive=" . attr_url($form_inactive) .
                            "&form_doc_only=" . attr_url($form_doc_only) .
                            "&offset=" . attr_url($offsetN) . "&" . $activity_string_html . "' onclick='return top.restoreSession()'>[" .
                                xlt('Previous') . "]</a>\n";
                        }
                        ?>
                    </td>
                    <td class="text-right">
                        <?php
                        if ($result_count == $N) {
                            $offsetN = $offset + $N;
                            echo "   <a class='link' href='pnotes_full.php" .
                            "?$urlparms" .
                            "&form_active=" . attr_url($form_active) .
                            "&form_inactive=" . attr_url($form_inactive) .
                            "&form_doc_only=" . attr_url($form_doc_only) .
                            "&offset=" . attr_url($offsetN) . "&" . $activity_string_html . "' onclick='return top.restoreSession()'>[" .
                                xlt('Next') . "]</a>\n";
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <div id='outbox_div table-resonsive' <?php echo $outbox_style; ?> >
            <table class="table table-borderless text">
                <?php if ($result_sent != "") : ?>
                    <tr>
                        <td colspan="5" class="p-1">
                            <a href="pnotes_full.php?<?php echo $urlparms; ?>&s=1&<?php echo $activity_string_html; ?>"
                                id='Submit' onclick='return top.restoreSession()'><?php echo xlt('Refresh'); ?></a>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <table class="table table-borderless text w-75">
                <?php
                // display all of the notes for the day, as well as others that are active
                // from previous dates, up to a certain number, $N

                if ($result_sent != "") {
                    echo " <tr class='showborder_head'>\n";
                    echo "  <th>&nbsp;</th>\n";
                    echo "  <th>" . xlt('Active{{Note}}') . "&nbsp;</th>\n";
                    echo "  <th>" . (($docid || $orderid) ? xlt('Linked') : '') . "</th>\n";
                    echo "  <th>" . xlt('Type') . "</th>\n";
                    echo "  <th>" . xlt('Content') . "</th>\n";
                    echo " </tr>\n";

                    $result_sent_count = 0;
                    foreach ($result_sent as $iter) {
                        $result_sent_count++;
                        $row_note_id = $iter['id'];

                        $linked = "";
                        if ($docid) {
                            if (isGpRelation(1, $docid, 6, $row_note_id)) {
                                $linked = "checked";
                            } else {
                                // Skip unlinked notes if that is requested.
                                if ($form_doc_only) {
                                    continue;
                                }
                            }
                        } elseif ($orderid) {
                            if (isGpRelation(2, $orderid, 6, $row_note_id)) {
                                $linked = "checked";
                            } else {
                                // Skip unlinked notes if that is requested.
                                if ($form_doc_only) {
                                    continue;
                                }
                            }
                        }

                        $body = $iter['body'];
                        if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
                            $body = pnoteConvertLinks(nl2br(text(oeFormatPatientNote($body))));
                        } else {
                            $body = text(oeFormatSDFT(strtotime($iter['date'])) . date(' H:i', strtotime($iter['date']))) .
                            ' (' . text($iter['user']) . ') ' . pnoteConvertLinks(nl2br(text(oeFormatPatientNote($body))));
                        }

                        $body = preg_replace('/(:\d{2}\s\()' . $patient_id . '(\sto\s)/', '${1}' . $patientname . '${2}', $body);
                        if (($iter["activity"]) && ($iter['message_status'] != "Done")) {
                            $checked = "checked";
                        } else {
                            $checked = "";
                        }

                        // highlight the row if it's been selected for updating
                        if ($_REQUEST['noteid'] == $row_note_id) {
                            echo " <tr class='noterow highlightcolor' id='" . attr($row_note_id) . "'>\n";
                        } else {
                            echo " <tr class='noterow' id='" . attr($row_note_id) . "'>\n";
                        }

                        echo "  <td><a href='pnotes_full_add.php?$urlparms&trigger=edit&noteid=" . attr_url($row_note_id) .
                        "' class='btn btn-primary btn-sm btn-edit note_modal' onclick='return top.restoreSession()'>" . xlt('Edit') . "</a>\n";

                        // display, or not, a button to delete the note
                        // if the user is an admin or if they are the author of the note, they can delete it
                        if (($iter['user'] == $_SESSION['authUser']) || (AclMain::aclCheckCore('admin', 'super', '', 'write'))) {
                            echo " <a href='#' class='deletenote btn btn-danger btn-sm btn-delete' id='del" . attr($row_note_id) .
                            "' title='" . xla('Delete this note') . "' onclick='return restoreSession()'><span>" .
                            xlt('Delete') . "</span>\n";
                        }

                        echo "  </td>\n";


                        echo "  <td class='text font-weight-bold'>\n";
                        echo "   <input type='hidden' name='act" . attr($row_note_id) . "' value='1' />\n";
                        echo "   <input type='checkbox' name='chk" . attr($row_note_id) . "' $checked />\n";
                        echo "  </td>\n";

                        echo "  <td class='text font-weight-bold'>\n";
                        if ($docid || $orderid) {
                            echo "   <input type='checkbox' name='lnk" . attr($row_note_id) . "' $linked />\n";
                        }

                        echo "  </td>\n";

                        echo "  <td class='font-weight-bold notecell' id='" . attr($row_note_id) . "'>" .
                        "<a href='pnotes_full_add.php?$urlparms&trigger=edit&noteid=" . attr_url($row_note_id) . "' class='note_modal' onclick='return top.restoreSession()'>\n";
                        // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
                        echo generate_display_field(array('data_type' => '1','list_id' => 'note_type'), $iter['title']);
                        echo "  </a></td>\n";

                        echo "  <td class='notecell' id='" . attr($row_note_id) . "'>\n";
                        echo "   $body";
                        echo "  </td>\n";
                        echo " </tr>\n";

                        $notes_sent_count++;
                    }
                } else {
                    //no results
                    print "<tr><td colspan='3' class='text'>" . xlt('None{{Result}}') . ".</td></tr>\n";
                }
                ?>
            </table>

            <table class="table table-borderless">
                <tr>
                    <td>
                        <?php
                        if ($offset_sent > ($M - 1)) {
                            $offsetSentM = $offset_sent - $M;
                            echo "   <a class='link' href='pnotes_full.php" .
                            "?$urlparms" .
                            "&s=1" .
                            "&form_active=" . attr_url($form_active) .
                            "&form_inactive=" . attr_url($form_inactive) .
                            "&form_doc_only=" . attr_url($form_doc_only) .
                            "&offset_sent=" . attr_url($offsetSentM) . "&" . $activity_string_html . "' onclick='return top.restoreSession()'>[" .
                            xlt('Previous') . "]</a>\n";
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if ($result_sent_count == $M) {
                            $offsetSentM = $offset_sent + $M;
                            echo "   <a class='link' href='pnotes_full.php" .
                            "?$urlparms" .
                            "&s=1" .
                            "&form_active=" . attr_url($form_active) .
                            "&form_inactive=" . attr_url($form_inactive) .
                            "&form_doc_only=" . attr_url($form_doc_only) .
                            "&offset_sent=" .  attr_url($offsetSentM) . "&" . $activity_string_html . "' onclick='return top.restoreSession()'>[" .
                            xlt('Next') . "]</a>\n";
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div> <!-- end outer 'pnotes' -->
</body>
<script>
<?php
if (!empty($_GET['set_pid'])) {
    $ndata = getPatientData($patient_id, "fname, lname, pubpid");
    ?>
 parent.left_nav.setPatient(<?php echo js_escape($ndata['fname'] . " " . $ndata['lname']) . "," .
     js_escape($patient_id) . "," . js_escape($ndata['pubpid']) . ",window.name"; ?>);
    <?php
}

// If this note references a new patient document, pop up a display
// of that document.
//
if ($noteid /* && $title == 'New Document' */) {
    $prow = getPnoteById($noteid, 'body');
    if (preg_match('/New scanned document (\d+): [^\n]+\/([^\n]+)/', $prow['body'], $matches)) {
        $docid = $matches[1];
        $docname = $matches[2];
        ?>
     window.open('../../../controller.php?document&retrieve&patient_id=<?php echo attr_url($patient_id); ?>&document_id=<?php echo attr_url($docid); ?>&<?php echo attr_url($docname);?>&as_file=true',
  '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
        <?php
    }
}
?>
</script>

<script>
// jQuery stuff to make the page a little easier to use

$(function () {
    $("#appendnote").click(function() { AppendNote(); });
    $("#newnote").click(function() { NewNote(); });
    $("#printnote").click(function() { PrintNote(); });

    $(".change_activity").click(function() { top.restoreSession(); $("#update_activity").submit(); });

    $(".deletenote").click(function() { DeleteNote(this); });

    $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });

    $("#note").focus();

    var NewNote = function () {
        top.restoreSession();
        $("#noteid").val('');
        $("#new_note").submit();
    }

    var AppendNote = function () {
        top.restoreSession();
        $("#new_note").submit();
    }

    var PrintNote = function () {
        top.restoreSession();
        window.open('pnotes_print.php?noteid=<?php echo attr_url($noteid); ?>', '_blank', 'resizable=1,scrollbars=1,width=600,height=500');
    }

    var DeleteNote = function(note) {
        if (confirm(<?php echo xlj('Are you sure you want to delete this note?'); ?> + '\n ' + <?php echo xlj('This action CANNOT be undone.'); ?>)) {
            top.restoreSession();
            // strip the 'del' part of the object's ID
            $("#noteid").val(note.id.replace(/del/, ""));
            $("#mode").val("delete");
            $("#new_note").submit();
        }
    }

});

</script>

</html>
