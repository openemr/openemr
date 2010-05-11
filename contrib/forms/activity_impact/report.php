<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
//------------Forms generated from formsWiz
include_once("../../globals.php");
include_once($GLOBALS["srcdir"] . "/api.inc");
function activity_impact_report( $pid, $encounter, $cols, $id) {
  $count = 0;
  $cols = 2;
  $data = formFetch("form_activity_impact", $id);
  $width = 100/$cols;
  if ($data) {
	  $value = $data['activity_impact'];
	  $value = str_replace( "\n", "<br/>", $value );
	  print "$value";
  }
}
?>
