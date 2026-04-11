<?php

// Print command for spooling to printers, used by statement.inc.php.
// This is the command to be used for printing (without the filename).
// The word following "-P" should be the name of your printer. This
// example is designed for 8.5x11-inch paper with 1-inch margins,
// 10 CPI, 6 LPI, 65 columns, 54 lines per page.
// If lpr services are installed on Windows this setting will be similar.
// Otherwise configure it as needed (print /d:PRN) might be an option for Windows parallel printers.
const OPENEMR_PRINT_COMMAND = 'lpr -P HPLaserjet6P -o cpi=10 -o lpi=6 -o page-left=72 -o page-top=72';

// Enscript command used by HylaFAX (fax_dispatch.php).
const OPENEMR_HYLAFAX_ENSCRIPT = 'enscript -M Letter -B -e^ --margins=36:36:36:36';

//used differently by different applications, intuit programs only like numbers
$GLOBALS['oer_config']['ofx']['bankid']     = "123456789";

//you can use this to match to an existing account in you accounting application
$GLOBALS['oer_config']['ofx']['acctid']     = "123456789";

//use FL for FLORIDA compatible format, leave blank for default
$GLOBALS['oer_config']['prescriptions']['format'] = "";

// Document storage repository document root. Must include a trailing slash.
$GLOBALS['oer_config']['documents']['repopath'] = $GLOBALS['OE_SITE_DIR'] . "/documents/";
$GLOBALS['oer_config']['documents']['file_command_path'] = "/usr/bin/file";

//Name of prescription graphic in interface/pic/ directory without preceding slash. Can be JPEG or PNG, normally 3 inches wide.
$GLOBALS['oer_config']['prescriptions']['logo_pic'] = "Rx.png";

// Name of signature graphic in interface/pic/ directory without preceding
// slash. Normally 3 inches wide.  This filename may include the string
// "{userid}" to indicate the numeric ID of the user, so that prescriptions
// can print with the correct provider's signature if you have multiple
// providers.  Also signature images are used only for faxed prescriptions,
// not printed prescriptions.
$GLOBALS['oer_config']['prescriptions']['sig_pic'] = "sig.png";
//Option to used signature graphic or not
$GLOBALS['oer_config']['prescriptions']['use_signature'] = false;

// To print the prescription medication area on a grey background:
$GLOBALS['oer_config']['prescriptions']['shading'] = false;

// only works with hylafax sendfax client, and sendfax must be in PATH
// assign 'sendfax' to turn fax sending on
$GLOBALS['oer_config']['prescriptions']['sendfax'] = '';

// assign a value here if there is any prefix needed to get dialing tone
// you can also append a comma to add a one second delay
// i.e. 9, will dial 9 for external tone, and wait a second.
$GLOBALS['oer_config']['prescriptions']['prefix'] = '';

// Similarly for bottle labels if you are dispensing drugs.  Note that paper
// size here or for prescriptions may be an array (0, 0, width, height).
// As above, these measurements are in points.
$GLOBALS['oer_config']['druglabels']['paper_size'] = [0, 0, 216, 216];
$GLOBALS['oer_config']['druglabels']['left']   = 18;
$GLOBALS['oer_config']['druglabels']['right']  = 18;
$GLOBALS['oer_config']['druglabels']['top']    = 18;
$GLOBALS['oer_config']['druglabels']['bottom'] = 18;
$GLOBALS['oer_config']['druglabels']['logo_pic'] = 'druglogo.png';
$GLOBALS['oer_config']['druglabels']['disclaimer'] =
  'Caution: Federal law prohibits dispensing without a prescription. ' .
  'Use only as directed.';

//don't alter below this line unless you are an advanced user and know what you are doing

$GLOBALS['oer_config']['prescriptions']['logo'] = __DIR__ .
  "/../../interface/pic/" . $GLOBALS['oer_config']['prescriptions']['logo_pic'];
$GLOBALS['oer_config']['prescriptions']['signature'] = __DIR__ .
  "/../../interface/pic/" . $GLOBALS['oer_config']['prescriptions']['sig_pic'];

$GLOBALS['oer_config']['druglabels']['logo'] = __DIR__ .
  "/../../interface/pic/" . $GLOBALS['oer_config']['druglabels']['logo_pic'];

$GLOBALS['oer_config']['documents']['repository'] = $GLOBALS['oer_config']['documents']['repopath'];
