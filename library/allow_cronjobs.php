<?php 
// Check if running as a cronjob
if (php_sapi_name() !== 'cli') { return; }

// Cronjobs are expected to specify arguments as -
// php -f full_script_name arg1=value1 arg2=value2

foreach ($argv as $argk => $argval) {
    if ($argk == 0) { continue; }
    $pair = explode("=", $argval);
    $_REQUEST[trim($pair[0])] = (isset($pair[1]) ? trim($pair[1]) : null);
}

// Every job must have at least one argument specifing site
if (!isset($_REQUEST['site'])) { exit ("site=?"); }

// Simulate $_GET and $_POST for use by scripts
$_GET = $_REQUEST;
$_POST = $_REQUEST;

// Ignore auth checks
$ignoreAuth = true;
?>