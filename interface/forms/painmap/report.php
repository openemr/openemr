<?php
/*
  *  Copyright Medical Information Integration,LLC info@mi-squared.com
  * This program is free software; you can redistribute it and/or
  * modify it under the terms of the GNU General Public License
  * as published by the Free Software Foundation; either version 2
  * of the License, or (at your option) any later version.
  */

/* include globals.php, required. */
require_once('../../globals.php');

/* include api.inc, required. */
require_once($GLOBALS['srcdir'].'/api.inc');

/* include our smarty derived controller class. */
require('C_FormPainMap.class.php');

/**
 * @brief report function, to display a form in the 'view enounter' page, and in the medical records reports.
 */
function painmap_report( $pid, $encounter, $cols, $id) {
    /* Create a form object. */
    $c = new C_FormPainMap();
    /* Render the form. */
    echo $c->report_action($id);
}
?>
