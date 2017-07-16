<?php
//
// Copyright (C) 2010 Brady Miller (brady.g.miller@gmail.com)
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This simply shows the Clinical Reminder Widget
//



require_once(dirname(__FILE__) . "/../../globals.php");
require_once("$srcdir/reminders.php");

//To improve performance and not freeze the session when running this
// report, turn off session writing. Note that php session variables
// can not be modified after the line below. So, if need to do any php
// session work in the future, then will need to remove this line.
session_write_close();

patient_reminder_widget($pid);
