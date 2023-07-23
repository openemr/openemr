<?php

/**
 * Viewing and modification/creation of office notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\ONoteService;

// Control access
if (!AclMain::aclCheckCore('encounters', 'notes')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Office Notes")]);
    exit;
}

$oNoteService = new ONoteService();

//the number of records to display per screen
$N = 10;

$offset = (isset($_REQUEST['offset'])) ? $_REQUEST['offset'] : 0;
$active = (isset($_REQUEST['active'])) ? $_REQUEST['active'] : -1;

//this code handles changing the state of activity tags when the user updates them through the interface
if (isset($_POST['mode'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($_POST['mode'] == "update") {
        foreach ($_POST as $var => $val) {
            if ($val == "true" || $val == "false") {
                $id = str_replace("act", "", $var);
                if ($val == "true") {
                    $oNoteService->enableNoteById($id);
                } elseif ($val == "false") {
                    $oNoteService->disableNoteById($id);
                }
            }
        }
    } elseif ($_POST['mode'] == "new") {
        $oNoteService->add($_POST["note"]);
    }
}
?>
<html>
<head>

<?php Header::setupHeader(); ?>
</head>
<body class="body_top">

    <div id="officenotes_edit">

        <form method="post" name="new_note" action="office_comments_full.php" onsubmit='return top.restoreSession()'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

            <?php
            /* BACK should go to the main Office Notes screen */
            if ($userauthorized) {
                $backurl = "office_comments.php";
            } else {
                $backurl = "../main_info.php";
            }
            ?>

            <a href="office_comments.php" onclick='top.restoreSession()'>

            <span class="title"><?php echo xlt('Office Notes'); ?></span>
            <span class="back"><?php echo text($tback); ?></span></a>

            <br />
            <input type="hidden" name="mode" value="new">
            <input type="hidden" name="offset" value="<?php echo attr($offset); ?>">
            <input type="hidden" name="active" value="<?php echo attr($active); ?>">

            <textarea name="note" class="form-control" rows="3" placeholder="<?php echo xla("Enter new office note here"); ?>" required="required"></textarea>
            <input class="btn btn-primary mt-3" type="submit" value="<?php echo xla('Add New Note'); ?>" />
        </form>
        <br/>
        <hr>

        <form method="post" name="update_activity" action="office_comments_full.php" onsubmit='return top.restoreSession()'>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

            <?php //change the view on the current mode, whether all, active, or inactive
            if ($active === "1") {
                $active_class = null;
                $inactive_class = "_small";
                $all_class = "_small";
            } elseif ($active === "0") {
                $active_class = "_small";
                $inactive_class = null;
                $all_class = "_small";
            } else {
                $active_class = "_small";
                $inactive_class = "_small";
                $all_class = null;
            }
            ?>

            <a href="office_comments_full.php?offset=0&active=-1" class="btn btn-primary<?php echo attr($all_class);?>" onclick='top.restoreSession()'><?php echo xlt('All'); ?></a>
            <a href="office_comments_full.php?offset=0&active=1" class="btn btn-primary<?php echo attr($active_class);?>" onclick='top.restoreSession()'><?php echo xlt('Only Active'); ?></a>
            <a href="office_comments_full.php?offset=0&active=0" class="btn btn-primary<?php echo attr($inactive_class);?>" onclick='top.restoreSession()'><?php echo xlt('Only Inactive'); ?></a>

            <input type="hidden" name="mode" value="update">
            <input type="hidden" name="offset" value="<?php echo attr($offset);?>">
            <input type="hidden" name="active" value="<?php echo attr($active);?>">
            <br/>

            <table class="existingnotes table table-striped">
                <?php
                //display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N

                $notes = $oNoteService->getNotes($active, $offset, $N);

                $result_count = 0;
                //retrieve all notes
                if (!empty($notes)) {
                    print "<thead><tr><th>" . xlt("Active") . "</th><th>" . xlt("Date") . " (" . xlt("Sender") . ")</th><th>" . xlt("Office Note") . "</th></tr></thead><tbody>";
                    foreach ($notes as $note) {
                        $result_count++;

                        $date = (new DateTime($note['date']))->format('Y-m-d');

                        $todaysDate = new DateTime();
                        if ($todaysDate->format('Y-m-d') == $date) {
                            $date_string = xl("Today") . ", " . oeFormatShortDate($date);
                        } else {
                            $date_string = oeFormatShortDate($date);
                        }

                        if ($note['activity'] == 1) {
                            $checked = "checked";
                        } else {
                            $checked = "";
                        }

                        print "<tr><td><input type=hidden value='' name='act" . attr($note['id']) . "' id='act" . attr($note['id']) . "'>";
                        print "<input name='box" . attr($note['id']) . "' id='box" . attr($note['id']) . "' onClick='javascript:document.update_activity.act" . attr($note['id']) . ".value=this.checked' type=checkbox $checked></td>";
                        print "<td><label for='box" . attr($note['id']) . "' class='bold'>" . text($date_string) . "</label>";
                        print " <label for='box" . attr($note['id']) . "' class='bold'>(" . text($note['user']) . ")</label></td>";
                        print "<td><label for='box" . attr($note['id']) . "' class='text'>" . nl2br(text($note['body'])) . "&nbsp;</label></td></tr>";
                    }
                    print "</tbody>\n";
                } else {
                //no results
                    print "<tr><td></td><td></td><td></td></tr>\n";
                }

                ?>
            </table>

            <input class="btn btn-primary" type="submit" value="<?php echo xla('Save Activity'); ?>" />
        </form>
        <hr>

        <table width="400" cellpadding="0" cellspacing="0" class="table">
            <tr><td>
            <?php
            if ($offset > ($N - 1)) {
                echo "<a class='btn btn-secondary' href=office_comments_full.php?active=" . attr_url($active) . "&offset=" . attr_url($offset - $N) . " onclick='top.restoreSession()'>" . xlt('Previous') . "</a>";
            }
            ?>
            </td><td align='right'>
            <?php
            if ($result_count == $N) {
                echo "<a class='btn btn-secondary' href=office_comments_full.php?active=" . attr_url($active) . "&offset=" . attr_url($offset + $N) . " onclick='top.restoreSession()'>" . xlt('Next') . "</a>";
            }
            ?>
            </td></tr>
        </table>
    </div>
</body>
</html>
