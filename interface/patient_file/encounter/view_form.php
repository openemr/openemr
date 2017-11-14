<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

include_once("../../globals.php");

$clean_formname=filter_var($_GET["formname"],FILTER_SANITIZE_STRING);
$clean_id=intval($_GET["id"]);

if (substr($clean_formname, 0, 3) === 'LBF') {
  // Use the List Based Forms engine for all LBFxxxxx forms.
    include_once("$incdir/forms/LBF/view.php");
} else {
  // ensure the path variable has no illegal characters
    check_file_dir_name($clean_formname);

    include_once("$incdir/forms/" . $clean_formname . "/view.php");
}

$id = $clean_id;
