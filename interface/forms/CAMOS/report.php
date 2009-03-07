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
      "/interface/forms/CAMOS/rx_print.php?sigline=embossed' target=_new>rx</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/rx_print.php?sigline=signed' target=_new>sig_rx</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=patient' target=_new>letterhead; patient signs</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/rx_print.php?letterhead=true&signer=doctor' target=_new>letterhead; doctor signs</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/notegen.php?pid=".$GLOBALS['pid']."&encounter=".$GLOBALS['encounter']."' target=_new>Print This Encounter</a>\n";
    echo " | ";
    echo "<a href='" . $GLOBALS['webroot'] .
      "/interface/forms/CAMOS/notegen.php' target=_new>Print Any Encounter</a>\n";
//    echo "<pre>".wordwrap(stripslashes(content_parser($data['content'])))."</pre><hr>\n";
    echo "<pre>".wordwrap(stripslashes(replace($GLOBALS['pid'],$GLOBALS['encounter'],$data['content'])))."</pre><hr>\n";
  }
}
?> 
