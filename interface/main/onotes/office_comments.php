<?php

/**
 * Viewing of office notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Services\ONoteService;

// Control access
if (!AclMain::aclCheckCore('encounters', 'notes')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Office Notes")]);
    exit;
}

//display all the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 8;
$oNoteService = new ONoteService();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Office Notes'); ?></title>
    <?php Header::setupHeader(); ?>
</head>
<body class="body_top">
    <div id="officenotes_list" class="container">
        <div class="row my-3">
            <div class="col-12">
                <a class="my-3" href="office_comments_full.php" onclick='top.restoreSession()'>
                    <span class="h2"><?php echo xlt('Office Notes'); ?></span>
                    <span class="text"><?php echo text($tmore); ?></span>
                </a>
            </div>
        </div>
        <table class="table table-hover table-striped">
            <?php
            $notes = $oNoteService->getNotes(1, 0, ($N + 1));
            //retrieve all active notes
            if (!empty($notes)) {
                $notes_count = 0;//number of notes so far displayed
                foreach ($notes as $note) {
                    if ($notes_count >= $N) {
                        //we have more active notes to print, but we've reached our display maximum (defined at top of this file)
                        $notice = '';
                        $notice .= '<div class="alert alert-info">';
                        $notice .= '  <a href=\'office_comments_full.php?active=-1\' onclick=\'top.restoreSession()\'>' . xlt("Some office notes were not displayed. Click here to view all.") . '</a>';
                        $notice .= '</div>';
                        print $notice;
                        break;
                    }

                    $date = (new DateTime($note['date']))->format('Y-m-d H:i:s');
                    $todaysDate = new DateTime();
                    if ($todaysDate->format('Y-m-d') == $date) {
                        $date_string = xl("Today") . ", " . oeFormatDateTime($date);
                    } else {
                        $date_string = oeFormatDateTime($date);
                    }
                    $card = '';
                    $card .= '<div class="card panel-default">';
                    $card .= '    <div class="card-heading bg-dark text-light">';
                    $card .= '        <h6 class="card-title m-0 mt-1 pb-1">' . text($date_string) . ' <strong>(' . text($note['user']) . ')</strong></h6>';
                    $card .= '    </div>';
                    $card .= '    <div class="card-body p-2">';
                    $card .= nl2br(text($note['body']));
                    $card .= '    </div>';
                    $card .= '</div>';

                    print $card;

                    $notes_count++;
                }
            }
            ?>
        </table>
    </div>
</body>
</html>
