<?php
/**
* Script to display results for a given procedure order.
*
* Copyright (C) 2013 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package   OpenEMR
* @author    Rod Roark <rod@sunsetsystems.com>
*/

$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once("../globals.php");
require_once($GLOBALS["include_root"] . "/orders/single_order_results.inc.php");

// Check authorization.
$thisauth = acl_check('patients', 'med');
if (!$thisauth) die(xl('Not authorized'));

$orderid = intval($_GET['orderid']);

if (!empty($_POST['form_sign_list'])) {
  if (!acl_check('patients', 'sign')) {
    die(xl('Not authorized to sign results'));
  }
  // When signing results we are careful to sign only those reports that were
  // in the sending form. While this will usually be all the reports linked to
  // the order it's possible for a new report to come in while viewing these,
  // and it would be very bad to sign results that nobody has seen!
  $arrSign = explode(',', $_POST['form_sign_list']);
  foreach ($arrSign as $id) {
  sqlStatement("UPDATE procedure_report SET " .
    "review_status = 'reviewed' WHERE " .
    "procedure_report_id = ?", array($id));
  }
}
?>
<html>
<head>
<?php html_header_show(); ?>
<link rel="stylesheet" href='<?php echo $css_header; ?>' type='text/css'>
<title><?php echo xlt('Order Results'); ?></title>
<style>
body {
 margin: 9pt;
 font-family: sans-serif; 
 font-size: 1em;
}
</style>
</head>
<body>
<?php
  generate_order_report($orderid, true);
?>
</body>
</html>
