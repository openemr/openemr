<?php
// Kevin Yeh ::  This page was a security risk that allowed verification of users.  
// It should not be called anymore, but rather than deleting the file it is being left as
// stub because it is not possible for the current patch mechanism to delete files.

// Use new security method
$fake_register_globals=false;
$sanitize_all_escapes=true;

$ignoreAuth=true;
include_once("../globals.php");
include_once("$srcdir/sql.inc");


echo xlt("This page deactivated for security reasons.");
?>
