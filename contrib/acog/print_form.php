<?php

$special_timeout = 3600;
require_once("../../../interface/globals.php");
// if (!allowed("frmprint")){ msgDenied(); }

// ensure the path variable has no illegal characters
check_file_dir_name($_GET["formname"]);

include_once($incdir . "/forms/" . $_GET["formname"]."/printable.php");
?>
