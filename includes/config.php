<?php
$GLOBALS['oer_config']['freeb']['claim_file_dir'] 	= "/usr/share/freeb/public/";
//currently can be pdf or txt
$GLOBALS['oer_config']['freeb']['default_format'] 	= "pdf";
$GLOBALS['oer_config']['freeb']['username'] 		= "freeb";
$GLOBALS['oer_config']['freeb']['password'] 		= "12345";
$GLOBALS['oer_config']['freeb']['print_command'] 	= "/usr/bin/lpr";
$GLOBALS['oer_config']['freeb']['printer_name'] 	= "HP_LaserJet4L";
$GLOBALS['oer_config']['freeb']['printer_extras'] 	= "-o PageSize=Letter -o portrait";

//used differently by different applications, intuit programs only like numbers
$GLOBALS['oer_config']['ofx']['bankid'] 	= "123456789";

//you can use this to match to an existing account in you accounting application
$GLOBALS['oer_config']['ofx']['acctid'] 	= "123456789";

//use FL for FLORIDA compatible format, leave blank for default
$GLOBALS['oer_config']['prescriptions']['format'] = "";

//Document storage repository document root, if it does not begin with a slash it is set relative to the file root
//you must include a trailing slash in either case
$GLOBALS['oer_config']['documents']['repository'] = "documents/";
$GLOBALS['oer_config']['documents']['file_command_path'] = "/usr/bin/file";

//Name of prescription graphic in interface/pic/ directory without preceding slash. Can be JPEG or PNG, normally 3 inches wide.
$GLOBALS['oer_config']['prescriptions']['logo_pic'] = "prescription_logo.png";

//Name of signature graphic in interface/pic/ directory without preceding slash. Normally 3 inches wide.
$GLOBALS['oer_config']['prescriptions']['sig_pic'] = "sig.png";
//accounting system web services integration
//whether to use the system
$GLOBALS['oer_config']['ws_accounting']['enabled'] = false;
$GLOBALS['oer_config']['ws_accounting']['server'] = "localhost";
$GLOBALS['oer_config']['ws_accounting']['port'] = "80";
$GLOBALS['oer_config']['ws_accounting']['url'] = "/sql-ledger/ws_server.pl";
$GLOBALS['oer_config']['ws_accounting']['username'] = "admin";
$GLOBALS['oer_config']['ws_accounting']['password'] = "12345";
$GLOBALS['oer_config']['ws_accounting']['url_path'] = "http://localhost/sql-ledger/login.pl";
$GLOBALS['oer_config']['ws_accounting']['income_acct'] = "10035";




//don't alter below this line unless you are an advanced user and know what you are doing

$GLOBALS['oer_config']['prescriptions']['logo'] = dirname(__FILE__) ."/../interface/pic/" . $GLOBALS['oer_config']['prescriptions']['logo_pic'];
$GLOBALS['oer_config']['prescriptions']['signature'] = dirname(__FILE__) ."/../interface/pic/" . $GLOBALS['oer_config']['prescriptions']['sig_pic'];

if (strpos($GLOBALS['oer_config']['documents']['repository'],"/") !== 0) {
	$GLOBALS['oer_config']['documents']['repository'] = realpath(dirname(__FILE__) . "/../" . $GLOBALS['oer_config']['documents']['repository']) . "/";
}

?>
