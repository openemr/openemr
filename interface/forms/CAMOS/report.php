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
      "/interface/forms/CAMOS/rx_print.php' target=_new>rx</a>\n";
    echo "<pre>".wordwrap(stripslashes(content_parser($data['content'])))."</pre><hr>\n";
  }
}
?> 
