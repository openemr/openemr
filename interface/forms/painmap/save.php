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

/* include api.inc. also required. */
require_once($GLOBALS['srcdir'].'/api.inc');

/* include our smarty derived controller class. */
require('C_FormPainMap.class.php');

/* Create a form object. */
$c = new C_FormPainMap();

/* Save the form contents .*/
echo $c->default_action_process($_POST);

/* return to the encounter. */
@formJump();
