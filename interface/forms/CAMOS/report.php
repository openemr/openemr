<?php
//------------report.php
include_once("../../globals.php");
include_once("../../../library/api.inc");
include_once("content_parser.php");
function CAMOS_report( $pid, $encounter, $cols, $id) {
  $data = formFetch("form_CAMOS", $id);
  if ($data) {
    //echo "(category) ".stripslashes($data['category'])." | ";
    //echo "(subcategory) ".stripslashes($data['subcategory'])." | ";
    //echo "(item) ".stripslashes($data['item']);
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/rx_print.php?sigline=embossed' target=_new>" . xl('Rx') . "</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/rx_print.php?sigline=signed' target=_new>" . xl('Signed Rx') . "</a>\n";
    echo "<br>";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=patient' target=_new>" . xl('Letterhead that patient signs') . "</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=doctor' target=_new>" . xl('Letterhead that doctor signs') . "</a>\n";
    echo "<br>";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/notegen.php?pid=".$GLOBALS['pid']."&encounter=".$GLOBALS['encounter']."' target=_new>" . xl('Print This Encounter') . "</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/notegen.php' target=_new>" . xl('Print Any Encounter') . "</a>\n";
//    echo "<pre>".wordwrap(stripslashes(content_parser($data['content'])))."</pre><hr>\n";
    echo "<pre>".wordwrap(stripslashes(replace($GLOBALS['pid'],$GLOBALS['encounter'],$data['content'])))."</pre><hr>\n";
  }
}
?> 
