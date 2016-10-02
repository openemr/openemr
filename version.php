<?php
// Software version identification.
// This is used for display purposes, and also the major/minor/patch
// numbers are stored in the database and used to determine which sql
// upgrade file is the starting point for the next upgrade.
$v_major = '5';
$v_minor = '0';
$v_patch = '0';
$v_tag   = '-dev'; // minor revision number, should be empty for production releases

// A real patch identifier. This is incremented when we release a patch for a
// production release. Note the above $v_patch variable is a misnomer and actually
// stores release version information.
$v_realpatch = '0';

// Database version identifier, this is to be incremented whenever there
// is a database change in the course of development.  It is used
// internally to determine when a database upgrade is needed.
//
$v_database = 194;

// Access control version identifier, this is to be incremented whenever there
// is a access control change in the course of development.  It is used
// during installation to determine what the installed version of the access
// controls is (subsequently the acl_upgrade.php script then is used to
// upgrade and track this value)
//
$v_acl = 3;

//Offsite Portal SOAP functions version, which are at myportal directory.
$v_offsite_portal='1.47';

// Version for JavaScript includes. Increment whenever a .js file changes.
// Also whenever you change a .js file, make sure that all URLs referencing it
// end with "?v=$v_js_includes".  Search the code for examples of doing this.
// All this is to keep browsers from using an older cached version.
$v_js_includes = 7;
?>
