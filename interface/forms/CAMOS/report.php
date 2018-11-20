<?php
//------------report.php
require_once(dirname(__FILE__).'/../../globals.php');
require_once("../../../library/api.inc");
require_once("content_parser.php");
function CAMOS_report($pid, $encounter, $cols, $id)
{
    $data = formFetch("form_CAMOS", $id);
    if ($data) {
        echo "<div class='navigateLink'><a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?sigline=embossed' target=_new>" . xlt('Rx') . "</a>\n";
        echo " | ";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?sigline=signed' target=_new>" . xlt('Signed Rx') . "</a>\n";
        echo "<br>";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=patient' target=_new>" . xlt('Letterhead that patient signs') . "</a>\n";
        echo " | ";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=doctor' target=_new>" . xlt('Letterhead that doctor signs') . "</a>\n";
        echo "<br>";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/notegen.php?pid=".$pid."&encounter=".$encounter."' target=_new>" . xlt('Print This Encounter') . "</a>\n";
        echo " | ";
        echo "<a href='" . $GLOBALS['webroot'] .
        "/interface/forms/CAMOS/notegen.php' target=_new>" . xlt('Print Any Encounter') . "</a></div>\n";
        echo "<pre>".text(wordwrap(stripslashes(replace($pid, $encounter, $data['content']))))."</pre><hr>\n";
    }
}
