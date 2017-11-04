<?php
// Copyright (C) 2009 Aron Racho <aron@mi-squared.com>
// Copyright (C) 2017 Roland Wick <ronhen_at_yandex_com>
//	version 0.9 
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
include_once("../../globals.php");
include_once("$srcdir/api.inc");

require("C_Formvet_genphys_exam.class.php");

$c = new C_Formvet_genphys_exam();
echo $c->view_action($_GET['id']);
?>
