<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

 require_once("../../globals.php");
 require_once("$srcdir/pnotes.inc");
 require_once("$srcdir/acl.inc");
 require_once("$srcdir/patient.inc");
 require_once("$srcdir/options.inc.php");
 require_once("$srcdir/classes/Document.class.php");
 require_once("$srcdir/formatting.inc.php");

 // form parameter docid can be passed to restrict the display to a document.
 $docid = empty($_REQUEST['docid']) ? 0 : 0 + $_REQUEST['docid'];
?>

<div id='pnotes' style='margin-top:3px; margin-left:10px; margin-right:10px'>


    <?php
    //display all of the notes for the day, as well as others that are active from previous dates, up to a certain number, $N
    $N = 3; ?>

    <br/>

    <?php

     $has_notes = 0;
     $thisauth = acl_check('patients', 'notes');
     if ($thisauth) {
      $tmp = getPatientData($pid, "squad");
      if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
       $thisauth = 0;
     }
     if (!$thisauth) {
      echo "<p>(" . htmlspecialchars(xl('Notes not authorized'),ENT_NOQUOTES) . ")</p>\n";
     } else { ?>

    <table width='100%'>

    <?php

    // Get the billing note if there is one.
    $billing_note = "";
    $colorbeg = "";
    $colorend = "";
    $sql = "select genericname2, genericval2 " .
        "from patient_data where pid = ? limit 1";
    $resnote = sqlQuery($sql, array($pid) );
    if ($resnote) {
      if ($resnote['genericname2'] == 'Billing') {
        $billing_note = $resnote['genericval2'];
        $colorbeg = "<span style='color:red'>";
        $colorend = "</span>";
      }
    }

    //Display what the patient owes
    $balance = get_patient_balance($pid);
    if ($balance != "0") {
      $has_note = 1;
      $formatted = oeFormatMoney($balance);
      echo " <tr class='text billing'>\n";
      echo "  <td>".$colorbeg.
        htmlspecialchars(xl('Balance Due'),ENT_NOQUOTES).$colorend."</td><td>".$colorbeg.
	htmlspecialchars($formatted,ENT_NOQUOTES).$colorend."</td>\n";
      echo " </tr>\n";
    }

    if ($billing_note) {
      $has_note = 1;
      echo " <tr class='text billing'>\n";
      echo "  <td>".$colorbeg.
        htmlspecialchars(xl('Billing Note'),ENT_NOQUOTES).$colorend."</td><td>".$colorbeg.
	htmlspecialchars($billing_note,ENT_NOQUOTES).$colorend."</td>\n";
      echo " </tr>\n";
    }

    //retrieve all active notes
    $result = getPnotesByDate("", 1, "id,date,body,user,title,assigned_to",
      $pid, "$N", 0, '', $docid);

    if ($result != null) {
      $notes_count = 0;//number of notes so far displayed
      foreach ($result as $iter) {
        $has_note = 1;

        $body = $iter['body'];
        if (preg_match('/^\d\d\d\d-\d\d-\d\d \d\d\:\d\d /', $body)) {
          $body = nl2br(htmlspecialchars(oeFormatPatientNote($body),ENT_NOQUOTES);
        } else {
          $body = htmlspecialchars(oeFormatSDFT(strtotime($iter['date'])) . date(' H:i', strtotime($iter['date'])) .
            ' (' . $iter['user'] . ') ',ENT_NOQUOTES) .
	    nl2br(htmlspecialchars(oeFormatPatientNote($body),ENT_NOQUOTES));
        }

        echo " <tr class='text' id='".htmlspecialchars($iter['id'],ENT_QUOTES)."' style='border-bottom:1px dashed' >\n";

        // Modified 6/2009 by BM to incorporate the patient notes into the list_options listings
        echo "  <td valign='top' class='text'><b>";
        echo generate_display_field(array('data_type'=>'1','list_id'=>'note_type'), $iter['title']);
        echo "</b></td>\n";

        echo "  <td valign='top' class='text'>$body</td>\n";
        echo " </tr>\n";

        $notes_count++;
      }
    } ?>

    </table>

    <?php
    if ( $has_note < 1 ) { ?>
        <span class='text'>
            <?php echo htmlspecialchars(xl( "There are no notes on file for this patient."),ENT_NOQUOTES);
                  echo " ";
	          echo htmlspecialchars(xl("To add notes, please click "),ENT_NOQUOTES);
	          echo "<a href='pnotes_full.php'>";
	          echo htmlspecialchars(xl("here"),ENT_NOQUOTES);
	          echo "</a>."; ?>
        </span>
    <?php } else {
        ?>
        <br/>
        <span class='text'>
	    <?php echo htmlspecialchars(xl('Displaying the following number of most recent notes:'),ENT_NOQUOTES); ?> 
	    <b><?php echo $N;?></b><br>
	    <a href='pnotes_full.php'><?php echo htmlspecialchars(xl('Click here to view them all.'),ENT_NOQUOTES); ?></a>
        </span>
        <?php
    } ?>

    <br/>
    <br/>

<?php } ?>

</div> <!-- end pnotes -->

<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".noterow").mouseover(function() { $(this).toggleClass("highlight"); });
    $(".noterow").mouseout(function() { $(this).toggleClass("highlight"); });
});

</script>

