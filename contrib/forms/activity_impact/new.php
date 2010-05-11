<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require ("C_FormActivityImpact.class.php");

$c = new C_FormActivityImpact();
echo $c->default_action();
?>
