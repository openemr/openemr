<?php

/*
 * Work/School Note Form print.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Nikolai Vitsyn
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com>
 * @copyright Copyright (c) 2004-2005 Nikolai Vitsyn
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Daniel Pflieger <danie@growlingflea.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc");

use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION["authUser"]));

/* name of this form */
$form_name = "note";

// get the record from the database
if ($_GET['id'] != "") {
    $obj = formFetch("form_" . $form_name, $_GET["id"]);
}

/* remove the time-of-day from the date fields */
if ($obj['date_of_signature'] != "") {
    $dateparts = explode(" ", $obj['date_of_signature']);
    $obj['date_of_signature'] = $dateparts[0];
}
?>
<html><head>
<title><?php echo "Form: note"?></title>

<?php Header::setupHeader(); ?>

</head>
<body class="body_top">

<form method=post action="">

    <span class="title"><?php
        $title = 'Work/School Note';
    if ($obj['note_type'] == "PROVIDER COMMUNICATION") {
        $title = "Provider Communication";
    } elseif ($obj['note_type'] == "REFERRAL") {
        $title = "Referral";
    }
        xl($title, 'e'); ?>
    </span><br></br>

<?php echo xlt('Printed'); ?> <?php echo text(dateformat()); ?>
<br /><br />

    <select name="note_type">
        <option value="WORK NOTE" <?php if ($obj['note_type']  == "WORK NOTE") {
            echo " SELECTED";
                                  } ?>><?php xl('WORK NOTE', 'e'); ?></option>
        <option value="SCHOOL NOTE" <?php if ($obj['note_type']  == "SCHOOL NOTE") {
            echo " SELECTED";
                                    } ?>><?php xl('SCHOOL NOTE', 'e'); ?></option>
        <option value="COVID-19 SCHOOL NOTE" <?php if ($obj['note_type']  == "COVID-19 SCHOOL NOTE") {
            echo " SELECTED";
                                             } ?>><?php xl('COVID-19 SCHOOL NOTE', 'e'); ?></option>
        <option value="PROVIDER COMMUNICATION" <?php if ($obj['note_type']  == "PROVIDER COMMUNICATION") {
            echo " SELECTED";
                                               } ?>><?php xl('PROVIDER COMMUNICATION', 'e'); ?></option>
        <option value="REFERRAL" <?php if ($obj['note_type']  == "REFERRAL") {
            echo " SELECTED";
                                 } ?>><?php xl('REFERRAL', 'e'); ?></option>
    </select>
    <br>

<br />
<b><?php echo xlt('MESSAGE:'); ?></b>
<br />
<div style="border: 1px solid black; padding: 5px; margin: 5px;"><?php echo text($obj["message"]);?></div>
<br /><br />

<table>
<tr><td>
<span class=text><?php echo xlt('Doctor:'); ?> </span>
        <input type=text name="doctor" value="<?php echo attr($obj["doctor"]);?>">
</td><td>
<span class="text"><?php echo xlt('Date'); ?></span>
   <input type='text' size='10' name='date_of_signature' id='date_of_signature'
    value='<?php echo attr($obj['date_of_signature']); ?>'
    />
</td></tr>
</table>

    <div id = "covid">
        <input type="checkbox" id="statement1" name="statement1" >
        <label for="statement1"> Statement1</label><br>
        <input type="checkbox" id="vehicle2" name="vehicle2" >
        <label for="statement2">Statement2</label><br>
    </div>

</form>

</body>

<script>
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    var win = top.printLogPrint ? top : opener.top;
    win.printLogPrint(window);
});

</script>

</html>
