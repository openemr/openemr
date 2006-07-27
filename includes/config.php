<?php
$GLOBALS['oer_config']['freeb']['claim_file_dir'] 	= "/usr/share/freeb/public/";
//currently can be pdf or txt
$GLOBALS['oer_config']['freeb']['default_format'] 	= "pdf";
$GLOBALS['oer_config']['freeb']['username'] 		= "freeb";
$GLOBALS['oer_config']['freeb']['password'] 		= "12345";
$GLOBALS['oer_config']['freeb']['print_command'] 	= "/usr/bin/lpr";
$GLOBALS['oer_config']['freeb']['printer_name'] 	= "HP_LaserJet4L";
// This does not seem useful for PDF HCFAs, see freeb/targetbin/ascii2pdf instead:
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
//Option to used signature graphic or not
$GLOBALS['oer_config']['prescriptions']['use_signature'] = false;

// only works with hylafax sendfax client, and sendfax must be in PATH
// assign 'sendfax' to turn fax sending on
$GLOBALS['oer_config']['prescriptions']['sendfax'] = '';

// asign a value here if there is any prefix needed to get dialing tone
// you can also append a comma to add a one second delay
// i.e. 9, will dial 9 for external tone, and wait a second.
$GLOBALS['oer_config']['prescriptions']['prefix'] = '';

// select paper size for prescription printing
// see library/classes/class.ezpdf.php for complete list of paper sizes
// ex. "LETTER", "A4", "LEGAL" ...
$GLOBALS['oer_config']['prescriptions']['paper_size'] = "LETTER";

// change page margins for prescription printing
// note, values are in pixels (72 dots per inch)
// to convert from centimeters use the following: (centimeters / 2.54 ) * 72;
$GLOBALS['oer_config']['prescriptions']['left']   = 30;
$GLOBALS['oer_config']['prescriptions']['right']  = 30;
$GLOBALS['oer_config']['prescriptions']['top']    = 72;
$GLOBALS['oer_config']['prescriptions']['bottom'] = 30;

// Similarly for bottle labels if you are dispensing drugs.  Note that paper
// size here or for prescriptions may be an array (0, 0, width, height).
// As above, these measurements are in points.
$GLOBALS['oer_config']['druglabels']['paper_size'] = array(0, 0, 216, 216);
$GLOBALS['oer_config']['druglabels']['left']   = 18;
$GLOBALS['oer_config']['druglabels']['right']  = 18;
$GLOBALS['oer_config']['druglabels']['top']    = 18;
$GLOBALS['oer_config']['druglabels']['bottom'] = 18;
$GLOBALS['oer_config']['druglabels']['logo_pic'] = 'druglogo.png';
$GLOBALS['oer_config']['druglabels']['disclaimer'] =
  'Caution: Federal law prohibits dispensing without a prescription. ' .
  'Use only as directed.';

//accounting system web services integration
//whether to use the system
$GLOBALS['oer_config']['ws_accounting']['enabled'] = false;
$GLOBALS['oer_config']['ws_accounting']['server'] = "localhost";
$GLOBALS['oer_config']['ws_accounting']['port'] = "80";
$GLOBALS['oer_config']['ws_accounting']['url'] = "/sql-ledger/ws_server.pl";
$GLOBALS['oer_config']['ws_accounting']['username'] = "unused";
$GLOBALS['oer_config']['ws_accounting']['password'] = "unused";
$GLOBALS['oer_config']['ws_accounting']['url_path'] = "http://" .
  $_SERVER["SERVER_NAME"] . "/sql-ledger/login.pl";
$GLOBALS['oer_config']['ws_accounting']['income_acct'] = "4320";

//don't alter below this line unless you are an advanced user and know what you are doing

$GLOBALS['oer_config']['prescriptions']['logo'] = dirname(__FILE__) .
  "/../interface/pic/" . $GLOBALS['oer_config']['prescriptions']['logo_pic'];
$GLOBALS['oer_config']['prescriptions']['signature'] = dirname(__FILE__) .
  "/../interface/pic/" . $GLOBALS['oer_config']['prescriptions']['sig_pic'];
$GLOBALS['oer_config']['prescriptions']['signature'] = '';

$GLOBALS['oer_config']['druglabels']['logo'] = dirname(__FILE__) .
  "/../interface/pic/" . $GLOBALS['oer_config']['druglabels']['logo_pic'];

if (strpos($GLOBALS['oer_config']['documents']['repository'],"/") !== 0) {
	$GLOBALS['oer_config']['documents']['repository'] = realpath(dirname(__FILE__) . "/../" . $GLOBALS['oer_config']['documents']['repository']) . "/";
}

?>
