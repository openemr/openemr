<?php
// Copyright (C) 2012 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

$sanitize_all_escapes=true;
$fake_register_globals=false;

require_once("../../globals.php");
require_once($GLOBALS["srcdir"] . "/formdata.inc.php");

$ptid     = $_GET['ptid'] + 0;
$encid    = $_GET['encid'] + 0;
$formname = strtr($_GET['formname'],
  array('.' => '', '\\' => '', '/' => '', '\'' => '', '"' => '', "\r" => '', "\n" => ''));
$formid   = $_GET['formid'] + 0;

if (substr($formname, 0, 3) == 'LBF') {
  include_once("{$GLOBALS['incdir']}/forms/LBF/report.php");
  lbf_report($ptid, $encid, 2, $formid, $formname);
}
else {
  include_once("{$GLOBALS['incdir']}/forms/$formname/report.php");
  $report_function = $formname . '_report';
  if (!function_exists($report_function)) exit;
  call_user_func($report_function, $ptid, $encid, 2, $formid);
}
?>
