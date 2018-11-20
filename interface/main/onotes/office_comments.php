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

use OpenEMR\Core\Header;
use OpenEMR\Services\ONoteService;

//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 10;

$oNoteService = new ONoteService();
?>

<html>
<head>

<?php Header::setupHeader(); ?>

</head>
<body class="body_top">

<div id="officenotes_list">
<a href="office_comments_full.php" onclick='top.restoreSession()'>
<font class="title"><?php echo xlt('Office Notes'); ?></font>
<font class="more"><?php echo text($tmore);?></font></a>

<br>

<table border=0 width=100%>

<?php

$notes = $oNoteService->getNotes(1, 0, ($N + 1));

//retrieve all active notes
if ($notes) {
    $notes_count = 0;//number of notes so far displayed
    foreach ($notes as $note) {
        if ($notes_count >= $N) {
            //we have more active notes to print, but we've reached our display maximum (defined at top of this file)
            $notice  = '';
            $notice .= '<div class="alert alert-info">';
            $notice .= '  <a href=\'office_comments_full.php?active=-1\' onclick=\'top.restoreSession()\'>'.xlt("Some office notes were not displayed. Click here to view all.").'</a>';
            $notice .= '</div>';
            print $notice;
            break;
        }

        $date = $note->getDate()->format('Y-m-d');

        $todaysDate = new DateTime();
        if ($todaysDate->format('Y-m-d') == $date) {
            $date_string = xl("Today") . ", " . oeFormatShortDate($date);
        } else {
            $date_string = oeFormatShortDate($date);
        }

            $card  = '';
            $card .= '<div class="panel panel-default">';
            $card .= '    <div class="panel-heading">';
            $card .= '        <h3 class="panel-title">'.text($date_string).' <strong>('.text($note->getUser()->getUsername()).')</strong></h3>';
            $card .= '    </div>';
            $card .= '    <div class="panel-body">';
            $card .=          nl2br(text($note->getBody()));
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
