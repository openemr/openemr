<?php
// Copyright (C) 2010-2013 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__).'/../../globals.php');
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once($GLOBALS["srcdir"] . "/options.inc.php");
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");

function procedure_order_report($pid, $encounter, $cols, $id) {
  generate_order_report($id);
}
?>
