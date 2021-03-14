<?php

/**
 * Software version identification.
 *
 * @package OpenEMR
 * @link https://www.open-emr.org/wiki
 * @author Robert Down <robertdown@live.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

// Software version identification.
// This is used for display purposes, and also the major/minor/patch
// numbers are stored in the database and used to determine which sql
// upgrade file is the starting point for the next upgrade.
$v_major = '6';
$v_minor = '1';
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
$v_database = 384;

// Access control version identifier, this is to be incremented whenever there
// is a access control change in the course of development.  It is used
// during installation to determine what the installed version of the access
// controls is (subsequently the acl_upgrade.php script then is used to
// upgrade and track this value)
//
$v_acl = 9;

// Version for JavaScript and stylesheet includes. Increment whenever a .js or .css file changes.
// Also whenever you change a .js or .css file, make sure that all URLs referencing it
// end with "?v=$v_js_includes".  Search the code for examples of doing this.
// All this is to keep browsers from using an older cached version.
// Need to assign it as a global below to work in template scripts.
if (!empty($_ENV['OPENEMR__ENVIRONMENT']) && ($_ENV['OPENEMR__ENVIRONMENT'] === 'dev')) {
    $v_js_includes = md5(microtime());
} else {
    // Change this number when bumping
    $v_js_includes = 63;
}

// Do not modify below
$GLOBALS['v_js_includes'] = $v_js_includes;
