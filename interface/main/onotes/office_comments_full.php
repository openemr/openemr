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
$offset = $_REQUEST['offset'] ?? 0;
$active = $_REQUEST['active'] ?? 1; // Active Only by default

if (isset($_POST['mode'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($_POST['mode'] == "update") {
        foreach ($_POST as $var => $val) {
            if (strpos($var, 'act') === 0) {
                $id = str_replace("act", "", $var);
                if ($val == "true") {
                    $oNoteService->enableNoteById($id);
                } else {
                    $oNoteService->disableNoteById($id);
                }
            }
        }
    } elseif ($_POST['mode'] == "new") {
        $oNoteService->add($_POST["note"]);
    } elseif ($_POST['mode'] == "edit") {
        $oNoteService->updateNoteById($_POST['note_id'], $_POST['note']);
    } elseif ($_POST['mode'] == "delete") {
        $oNoteService->deleteNoteById($_POST['note_id']);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Office Notes'); ?></title>
    <?php Header::setupHeader(); ?>
    <script>
        // submit the form when the checkbox changes state
        function toggleActivity(noteId, isActive) {
            const form = document.getElementById(`toggleForm${noteId}`);
            const checkbox = document.getElementById(`activityCheckbox${noteId}`);
            checkbox.value = isActive ? "true" : "false";
            form.submit();
        }
    </script>
</head>
<body class="body_top">
    <div id="officenotes_edit" class="container my-4">
        <!-- Add New Note Form -->
        <form method="post" action="office_comments_full.php" class="mb-4">
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
            <input type="hidden" name="mode" value="new">
            <div class="form-group">
                <textarea name="note" class="form-control" rows="3" placeholder="<?php echo xla("Enter new office note here"); ?>" required="required"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo xlt("Add New Note"); ?></button>
            <a href="office_comments.php" type="button" class="btn btn-cancel btn-secondary">
                <?php echo xlt("Front Page"); ?>
            </a>
        </form>
    </div>
    <!-- Active/Inactive View Toggle Buttons -->
    <div class="btn-group mb-4" role="group" aria-label="Filter Notes">
        <a href="office_comments_full.php?offset=0&active=-1" class="btn btn-primary <?php echo ($active == -1) ? 'active' : ''; ?>"><?php echo xlt("All"); ?></a>
        <a href="office_comments_full.php?offset=0&active=1" class="btn btn-primary <?php echo ($active == 1) ? 'active' : ''; ?>"><?php echo xlt("Only Active"); ?></a>
        <a href="office_comments_full.php?offset=0&active=0" class="btn btn-primary <?php echo ($active == 0) ? 'active' : ''; ?>"><?php echo xlt("Only Inactive"); ?></a>
    </div>

    <div class="container-fliud table-responsive">
        <!-- Existing Notes List -->
        <table class="table table-striped">
            <thead>
            <tr>
                <th><?php echo xlt("Active"); ?></th>
                <th class="text-left"><?php echo xlt("Date"); ?></th>
                <th class="text-left"><?php echo xlt("Office Note"); ?></th>
                <th class="text-center"><?php echo xlt("Actions"); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($oNoteService->getNotes($active, $offset, $N) as $note) : ?>
                <tr>
                    <td>
                        <!-- Active Toggle Checkbox -->
                        <form id="toggleForm<?php echo attr($note['id']); ?>" method="post" action="office_comments_full.php">
                            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                            <input type="hidden" name="mode" value="update">
                            <input type="hidden" name="act<?php echo attr($note['id']); ?>" id="activityCheckbox<?php echo attr($note['id']); ?>" value="<?php echo ($note['activity'] == 1) ? 'true' : 'false'; ?>">
                            <input type="checkbox" <?php echo ($note['activity'] == 1) ? 'checked' : ''; ?> onchange="toggleActivity(<?php echo attr($note['id']); ?>, this.checked);">
                        </form>
                    </td>
                    <td class="text-left">
                        <?php
                        $date = (new DateTime($note['date']))->format('Y-m-d H:i:s');
                        echo oeFormatDateTime($date) . " (" . text($note['user']) . ")";
                        ?>
                    </td>
                    <td class="text-left"><?php echo nl2br(text($note['body'])); ?></td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editNoteModal<?php echo attr($note['id']); ?>">
                            <i class="fa fa-pencil"></i><?php echo xlt("Edit"); ?>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteNoteModal<?php echo attr($note['id']); ?>">
                            <i class="fa fa-trash"></i><?php echo xlt("Delete"); ?>
                        </button>
                    </td>
                </tr>
                <!-- Edit Note Modal -->
                <div class="modal fade" id="editNoteModal<?php echo attr($note['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="editNoteModalLabel<?php echo attr($note['id']); ?>" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <form method="post" action="office_comments_full.php">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editNoteModalLabel<?php echo attr($note['id']); ?>"><?php echo text('Edit Note'); ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <input type="hidden" name="mode" value="edit">
                                    <input type="hidden" name="note_id" value="<?php echo attr($note['id']); ?>">
                                    <div class="form-group">
                                        <textarea name="note" class="form-control" rows="3" required="required"><?php echo text($note['body']); ?></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary"><?php echo xlt("Save changes"); ?></button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt("Close"); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Delete Note Confirmation Modal -->
                <div class="modal fade" id="deleteNoteModal<?php echo attr($note['id']); ?>" tabindex="-1" role="dialog" aria-labelledby="deleteNoteModalLabel<?php echo attr($note['id']); ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <form method="post" action="office_comments_full.php">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteNoteModalLabel<?php echo attr($note['id']); ?>"><?php echo xlt("Delete Note"); ?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                                    <input type="hidden" name="mode" value=<?php echo attr("delete"); ?>>
                                    <input type="hidden" name="note_id" value="<?php echo attr($note['id']); ?>">
                                    <p><?php echo xlt("Are you sure you want to delete this note?"); ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger"><?php echo xlt("Delete"); ?></button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt("Cancel"); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
