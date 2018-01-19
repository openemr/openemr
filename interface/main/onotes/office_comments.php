<?php
/**
 * Viewing of office notes.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
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
        $date = oeFormatShortDate($date);

        $todaysDate = new DateTime();
        if ($todaysDate->format('Y-m-d') == $date) {
            $date_string = xl("Today") . ", " . $date;
        } else {
            $date_string = $date;
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
