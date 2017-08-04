<?php
/**
 * Viewing of office notes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @Copyright (C) 2011-2017  Brady Miller <brady.g.miller@gmail.com>
 * @Copyright (C) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

include_once("../../globals.php");

//display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
$N = 10;

$oNoteService = new \services\ONoteService();
?>

<html>
<head>

<?php Header::setupHeader(); ?>
<title><?php echo xlt('Office Notes'); ?></title>
</head>
<body class="body_top">

<div class="container">
   
   <div class="row">
      <div class="col-sm-6">
         <div class="form-group form-horizontal page-header">
            <h3><label class='col-sm-4 text-right'><?php echo xlt('Office Notes'); ?></label></h3>
            <p class="more control-label col-sm-1"><a href="office_comments_full.php" onclick='top.restoreSession()'><?php echo text($tmore); ?></a></p>
         </div>
      </div>
   </div>

<?php

$notes = $oNoteService->getNotes(1, 0, ($N + 1));

//retrieve all active notes
if ($notes) {
    $notes_count = 0;//number of notes so far displayed
   ?>
       <div id="report_results">
       <table>
   <?php
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
            $card .= '<thead><th align="center">';
            $card .= '        <h3 class="panel-title">'.text($date_string).' <strong>('.text($note->getUser()->getUsername()).')</strong></h3>';
            $card .= '</th></thead>';
            $card .= '<tr><td>';
            $card .= nl2br(text($note->getBody()));
            $card .= '</th></td>';
            $card .= '';
            print $card;

            $notes_count++;
    }
}
?>
</table>
</div>
</div>
</body>
</html>
