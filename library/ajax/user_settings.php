<?php
// Copyright (C) 2010 Brady Miller <brady.g.miller@gmail.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This file contains functions that manage custom user
// settings
//



require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../user.inc");

//If 'mode' is either a 1 or 0 and 'target' ends with _expand
//  Then will update the appropriate user _expand flag
if (( $_POST['mode'] == 1 || $_POST['mode'] == 0 ) && ( substr($_POST['target'], -7, 7) == "_expand" )) {
  //set the user setting
    setUserSetting($_POST['target'], $_POST['mode']);
}

//mdsupport : Generic user setting
if ((isset($_POST['lab'])) && (isset($_POST['val']))) {
    setUserSetting($_POST['lab'], $_POST['val']);
}

// even more generic
if ((isset($_POST['target'])) && (isset($_POST['setting']))) {
    setUserSetting($_POST['target'], $_POST['setting']);
}
