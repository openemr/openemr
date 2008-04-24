<?php

require_once('sql.inc');

define('TMPDIR_DBC', $webserver_root . '/temp');

// WORKING LINK FOR VEKTIS FILES (as it'll appear in URL)
define('VK_WORKINGLNK','http://localhost:60001/'.$web_root.'/temp/vektis/');

// WORKING LINK FOR DBC FILES (as it'll appear in URL)
define('DB_WORKINGLNK','http://localhost:60001/'.$web_root.'/temp/dbc/');

require_once('DBC_functions.php');
require_once('DBC_files.php');
require_once('DBC_Vektis.php');
require_once('DBC_validations.php');
require_once('DBC_decisiontree.php');

$css_dbc = "{$GLOBALS['rootdir']}/themes/style_dbc.css";

mb_internal_encoding('UTF-8');
?>
