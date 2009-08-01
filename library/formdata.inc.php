<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

function formData($name, $type='P') {
  if ($type == 'P')
    $s = isset($_POST[$name]) ? $_POST[$name] : '';
  else if ($type == 'G')
    $s = isset($_GET[$name]) ? $_GET[$name] : '';
  else
    $s = isset($_REQUEST[$name]) ? $_REQUEST[$name] : '';
  if (!get_magic_quotes_gpc()) $s = addslashes($s);
  return $s;
}

function formTrim($s) {
  if (!get_magic_quotes_gpc()) $s = addslashes($s);
  return trim($s);
}
?>
