<?php

/**
 *
 * Installation script.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Scott Wakefield <scott@npclinics.com.au>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2016 Scott Wakefield <scott@npclinics.com.au>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019-2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Checks if the server's PHP version is compatible with OpenEMR:
require_once(dirname(__FILE__) . "/src/Common/Compatibility/Checker.php");
$response = OpenEMR\Common\Compatibility\Checker::checkPhpVersion();
if ($response !== true) {
    die(htmlspecialchars($response));
}

// Set the maximum excution time and time limit to unlimited.
ini_set('max_execution_time', 0);
ini_set('display_errors', 0);
set_time_limit(0);

// Warning. If you set $allow_multisite_setup to true, this is a potential security vulnerability.
// Recommend setting it back to false (or removing this setup.php script entirely) after you
//  are done with the multisite procedure.
$allow_multisite_setup = false;

// Warning. If you set $allow_cloning_setup to true, this is a potential security vulnerability.
// Recommend setting it back to false (or removing this setup.php script entirely) after you
//  are done with the cloning setup procedure.
$allow_cloning_setup = false;
if (!$allow_cloning_setup && !empty($_REQUEST['clone_database'])) {
    die("To turn on support for cloning setup, need to edit this script and change \$allow_cloning_setup to true. After you are done setting up the cloning, ensure you change \$allow_cloning_setup back to false or remove this script altogether");
}

function recursive_writable_directory_test($dir)
{
    // first, collect the directory and subdirectories
    $ri = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    $dirNames = array();
    foreach ($ri as $file) {
        if ($file->isDir()) {
            if (!preg_match("/\.\.$/", $file->getPathname())) {
                $dirName = realpath($file->getPathname());
                if (!in_array($dirName, $dirNames)) {
                    $dirNames[] = $dirName;
                }
            }
        }
    }

    // second, flag the directories that are not writable
    $resultsNegative = array();
    foreach ($dirNames as $value) {
        if (!is_writable($value)) {
            $resultsNegative[] = $value;
        }
    }

    // third, send the output and return if didn't pass the test
    if (!empty($resultsNegative)) {
        echo "<p>";
        $mainDirTest = "";
        $outputs = array();
        foreach ($resultsNegative as $failedDir) {
            if (basename($failedDir) ==  basename($dir)) {
                // need to reorder output so the main directory is at the top of the list
                $mainDirTest = "<span class='text-danger'>UNABLE</span> to open directory '" . text(realpath($failedDir)) . "' for writing by web server.<br />\r\n";
            } else {
                $outputs[] = "<span class='text-danger'>UNABLE</span> to open subdirectory '" . text(realpath($failedDir)) . "' for writing by web server.<br />\r\n";
            }
        }
        if ($mainDirTest) {
            // need to reorder output so the main directory is at the top of the list
            array_unshift($outputs, $mainDirTest);
        }
        foreach ($outputs as $output) {
            echo $output;
        }
        echo "(configure directory permissions; see below for further instructions)</p>\r\n";
        return 1;
    } else {
        echo "<code class='ml-5'>" . text(realpath($dir)) . "</code> directory and its subdirectories are <span class='text-success font-weight-bold'>ready</span>.<br /><br />\r\n";
        return 0;
    }
}

// Include standard libraries/classes
require_once dirname(__FILE__) . "/vendor/autoload.php";

use OpenEMR\Common\Utils\RandomGenUtils;

$COMMAND_LINE = php_sapi_name() == 'cli';

$state = isset($_POST["state"]) ? ($_POST["state"]) : '';
$installer = new Installer($_REQUEST);
// Make this true for IPPF.
$ippf_specific = false;

$error_page_end = <<<EPE
            </div>
        </div>
    </div><!--end of container div-->
</body>
</html>
EPE;

// If this script was invoked with no site ID, then ask for one.
if (!$COMMAND_LINE && empty($_REQUEST['site'])) {
    $site_id = <<<SITEID
    <!DOCTYPE html>
    <html>
    <head>
        <title>OpenEMR Setup Tool</title>
        <link rel="stylesheet" href="public/assets/bootstrap/dist/css/bootstrap.min.css">
        <script src="public/assets/jquery/dist/jquery.min.js"></script>
        <script src="public/assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="public/assets/@fortawesome/fontawesome-free/css/all.min.css">
        <link rel="shortcut icon" href="public/images/favicon.ico" />
        <style>
        .oe-pull-away {
            float:right;
        }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-expand navbar-light bg-light">
           <div class="container">
           <a class="navbar-brand" href="#">OpenEMR Setup</a>
           <div class="collapse navbar-collapse justify-content-end">
               <ul class="navbar-nav">
                   <li class="nav-item active">
                       <a class="nav-link" href="#" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" title="Click to view Help">Help</span></a>
                   </li>
               </ul>
           </div>
           </div>
        </nav>

        <div class='container mt-3'>
            <div class="row">
                <div class="col-12">
                    <h3 class="mb-3 border-bottom">Optional Site ID Selection</h3>
                    <div class="jumbotron p-5">
                        <p>
                            Most OpenEMR installations support only one site.  If that is
                            true for you then ignore the rest of this text and just click Continue.
                        </p>
                        <p class='p-2 bg-warning'>
                            If you are using the multisite setup module for the first time please read the
                            'Multi Site Installation' section of the help file before proceeding.
                        </p>
                        <p>
                            Otherwise please enter a unique Site ID here.
                        </p>
                        <p>
                            A Site ID is a short identifier with no spaces or special
                            characters other than periods or dashes. It is case-sensitive and we
                            suggest sticking to lower case letters for ease of use.
                        </p>
                        <p>
                            If each site will have its own host/domain name, then use that
                            name as the Site ID (e.g. www.example.com).
                        </p>
                        <p>
                            The site ID is used to identify which site you will log in to.
                            If it is a hostname then it is taken from the hostname in the URL.
                            Otherwise you must append "?site=<i>siteid</i>" to the URL used for
                            logging in.
                        </p>
                        <p>
                            It is OK for one of the sites to have "default" as its ID. This
                            is the ID that will be used if it cannot otherwise be determined.
                        </p>
                        <br />
                        <form method='post'>
                            <input type='hidden' name='state' value='0' />
                            <div class="form-row">
                                <div class="col-auto">
                                    Site ID:
                                </div>
                                <div class="col">
                                    <input type='text' class='form-control' name='site' value='default'>
                                </div>
                                <div class="col-12 mt-3">
                                    <button type='submit' class='btn btn-primary' value='Continue'><i class="fas fa-chevron-right"></i>  Continue</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!--end of container div-->
SITEID;
    echo $site_id . "\r\n";
    $installer->setupHelpModal();
    echo "</body>" . "\r\n";
    echo "</html>" . "\r\n";

    exit();
}

// Support "?site=siteid" in the URL, otherwise assume "default".
$site_id = 'default';
if (!$COMMAND_LINE && !empty($_REQUEST['site'])) {
    $site_id = trim($_REQUEST['site']);
}

// Die if site ID is empty or has invalid characters.
if (empty($site_id) || preg_match('/[^A-Za-z0-9\\-.]/', $site_id)) {
    die("Site ID '" . text($site_id) . "' contains invalid characters.");
}

// If multisite is turned off, then only allow default for site.
if (!$allow_multisite_setup && $site_id != 'default') {
    die("To turn on support for multisite setup, need to edit this script and change \$allow_multisite_setup to true. After you are done setting up the cloning, ensure you change \$allow_multisite_setup back to false or remove this script altogether");
}

// Disable file and directory permissions check by setting to false
$checkPermissions = true;

global $OE_SITE_DIR; // The Installer sets this

$docsDirectory = "$OE_SITE_DIR/documents";

//These are files and dir checked before install for
// correct permissions.
if (is_dir($OE_SITE_DIR)) {
    $writableFileList = array($installer->conffile);
    $writableDirList = array($docsDirectory);
} else {
    $writableFileList = array();
    $writableDirList = array($OE_SITES_BASE);
}

// Include the sqlconf file if it exists yet.
$config = 0;
if (file_exists($OE_SITE_DIR)) {
    include_once($installer->conffile);
} elseif ($state > 3) {
  // State 3 should have created the site directory if it is missing.
    die("Internal error, site directory is missing.");
}
?>
<html>
<head>
<title>OpenEMR Setup Tool</title>
<!--<link rel=stylesheet href="interface/themes/style_blue.css">-->
<link rel="stylesheet" href="public/assets/bootstrap/dist/css/bootstrap.min.css">
<script src="public/assets/jquery/dist/jquery.min.js"></script>
<script src="public/assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="public/assets/@fortawesome/fontawesome-free/css/all.min.css">
<link rel="shortcut icon" href="public/images/favicon.ico" />

<style>
    .noclone { }
    table.phpset {
        border-collapse:collapse;
    }
    table.phpset td, table.phpset th {
        font-size:9pt;
        border:1px solid gray;
        padding:2px;
    }
    .table.no-border tr td, .table.no-border tr th {
        border-width: 0;
    }
    td {
        font-size:10pt;
    }
    .inputtext {
         padding-left:2px;
         padding-right:2px;
    }

    .button {
         font-family:sans-serif;
         font-size:9pt;
         font-weight:bold;
    }

    .label-div > a {
        display:none;
    }
    .label-div:hover > a {
       display:inline-block;
    }
    div[id$="_info"] {
        background: #F7FAB3;
        padding: 20px;
        margin: 10px 15px 0px 15px;
    }
    div[id$="_info"] > a {
        margin-left:10px;
    }
    .checkboxgroup {
      display: inline-block;
      text-align: center;
    }
    .checkboxgroup label {
      display: block;
    }
    .oe-pull-away{
        float:right;
    }
    .oe-help-x {
        color: grey;
        padding: 0 5px;
    }
    .oe-superscript {
        position: relative;
        top: -.5em;
        font-size: 70%!important;
    }
    .oe-setup-legend{
        background-color: #f5f5f5;
        padding:0 10px;
    }
    .button-wait {
        color: grey;
        cursor: not-allowed;
        opacity: 0.6;
    }
    @media only screen {
        fieldset > [class*="col-"] {
            width: 100%;
            text-align:left!Important;
        }
    }
</style>
<script>
// onclick handler for "clone database" checkbox
function cloneClicked() {
 var cb = document.forms[0].clone_database;
 $('.noclone').css('display', cb.checked ? 'none' : 'block');
}
</script>

</head>
<body>
    <nav class="navbar navbar-expand navbar-light bg-light">
       <div class="container">
        <a class="navbar-brand" href="#">OpenEMR Setup</a>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="#" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" title="Click to view Help">Help</span></a>
                    </li>
                </ul>
            </div>
       </div>
    </nav>
    <div class='mt-3 container'>
        <div class="row">
            <div class="col-12">
            <?php
            $error = "<span class='text-danger font-weight-bold'>ERROR</span>";
            $caution = "<span class='text-danger font-weight-bold'>CAUTION</span>";
            $ok = "<span class='text-success font-weight-bold'>OK</span>";
            $note = "<span class='text-primary font-weight-bold'>NOTE</span>";

            if (strtolower(ini_get('register_globals')) != 'off' && (bool) ini_get('register_globals')) {
                echo "$caution: It appears that you have register_globals enabled in your php.ini\n" .
                "configuration file.  This causes unacceptable security risks.  You must\n" .
                "turn it off before continuing with installation.\n";
                exit(1);
            }

            if (!extension_loaded("xml")) {
                echo "$error: PHP XML extension missing. To continue, install PHP XML extension, then restart web server.";
                exit(1);
            }

            if (!(extension_loaded("mysql") || extension_loaded("mysqlnd") || extension_loaded("mysqli"))) {
                echo "$error: PHP MySQL extension missing. To continue, install and enable MySQL extension, then restart web server.";
                exit(1);
            }

            if (!(extension_loaded("mbstring") )) {
                echo "$error: PHP mb_string extension missing. To continue, install and enable mb_string extension, then restart web server.";
                exit(1);
            }

            if (!(extension_loaded("openssl") )) {
                echo "$error: PHP openssl extension missing. To continue, install PHP openssl extension, then restart web server.";
                exit(1);
            }
            ?>

            <?php
            if ($state == 7) {
                ?>
            <h3 class="mb-3 border-bottom">Final step - Success</h3>
            <div class="jumbotron p-5">
            <p>Congratulations! OpenEMR is now installed.</p>
            <ul>
                <li>Access controls (php-GACL) are installed for fine-grained security, and can be administered in
                    OpenEMR's admin->acl menu.</li>
                <li>Reviewing <code> <?php echo text($OE_SITE_DIR); ?>/config.php </code> is a good idea. This file
                    contains some settings that you may want to change.</li>
                <li>There's much information and many extra tools bundled within the OpenEMR installation directory.
                    Please refer to openemr/Documentation. Many forms and other useful scripts can be found at openemr/contrib.</li>
                <li>To ensure a consistent look and feel throughout the application,
                    <a href='http://www.mozilla.org/products/firefox/'>Firefox</a> and <a href="https://www.google.com/chrome/browser/desktop/index.html">Chrome</a> are recommended. The OpenEMR development team exclusively tests with modern versions of these browsers.</li>
                <li>The OpenEMR project home page, documentation, and forums can be found at <a href = "https://www.open-emr.org" rel='noopener' target="_blank">https://www.open-emr.org</a></li>
                <li>We pursue grants to help fund the future development of OpenEMR.  To apply for these grants, we need to estimate how many times this program is installed and how many practices are evaluating or using this software.  It would be awesome if you would email us at <a href="mailto:hello@open-emr.org">hello@open-emr.org</a> if you have installed this software. The more details about your plans with this software, the better, but even just sending us an email stating you just installed it is very helpful.</li>
            </ul>
            <p>We recommend you print these instructions for future reference.</p>
                <?php
                echo "<p> The selected theme is :</p>";
                $installer->displayNewThemeDiv();
                if (empty($installer->clone_database)) {
                    echo "<p><b>The initial OpenEMR user is <span class='text-primary'>'" . text($installer->iuser) . "'</span> and the password is <span class='text-primary'>'" . text($installer->iuserpass) . "'</span></b></p>";
                } else {
                    echo "<p>The initial OpenEMR user name and password is the same as that of source site <b>'" . text($installer->source_site_id) . "'</span></b></p>";
                }
                echo "<p>If you edited the PHP or Apache configuration files during this installation process, then we recommend you restart your Apache server before following below OpenEMR link.</p>";
                echo "<p>In Linux use the following command:</p>";
                echo "<p><code>sudo apachectl -k restart</code></p>";

                ?>
            <p>Click to start using OpenEMR.</p>
            <div class="row">
                <div class="col-12">
                    <a href='./?site=<?php echo attr_url($site_id); ?>' class='btn btn-primary'>
                        <i class="fas fa-chevron-right"></i> Start
                    </a>
                </div>
            </div>

            </div>
                <?php
                $installer->setCurrentTheme();

                $end_div = <<<ENDDIV
            </div>
        </div>
    </div><!--end of container div-->
ENDDIV;
                echo $end_div . "\r\n";
                $installer->setupHelpModal();
                echo "</body>" . "\r\n";
                echo "</html>" . "\r\n";

                exit();
            }
            ?>

            <?php

            $inst = isset($_POST["inst"]) ? ($_POST["inst"]) : '';

            if (($config == 1) && ($state < 4)) {
                echo "OpenEMR has already been installed.  If you wish to force re-installation, then edit " . text($installer->conffile) . " (change the 'config' variable to 0), and re-run this script.<br />\n";
            } else {
                switch ($state) {
                    case 1:
                        $state_esc = text($state);
                        $site_id_esc = attr($site_id);
                        $step1 = <<<STP1
                        <h3 class="mb-3 border-bottom">Step $state_esc - Select Database Setup</h3>
                        <div class="jumbotron p-5">
                            <p>Now I need to know whether you want me to create the database on my own or if you have already created the database for me to use. For me to create the database, you will need to supply the MySQL root password.</p>
                            <br />
                            <p class='p-1 bg-warning'>$caution: clicking on <span class="font-weight-bold">Proceed to Step 2</span> may delete or cause damage to existing data on your system. Before you continue <span class="font-weight-bold">please backup your data</span>.</p>
                            <br />
                            <form method='post'>
                                <input name='state' type='hidden' value='2' />
                                <input name='site' type='hidden' value='$site_id_esc' />
                                <div class="form-check">
                                    <input checked class='form-check-input' id='inst1' name='inst' type='radio' value='1' />
                                    <label class="form-check-label" for="inst1">
                                        Have setup create the database
                                    </label>
                                </div>
                                <br />
                                <div class="form-check">
                                    <input id='inst2' class='form-check-input' name='inst' type='radio' value='2' />
                                    <label class="form-check-label" for="inst2">
                                        I have already created the database
                                    </label>
                                </div>
                                <div class="form-group">
                                    <div class="col mt-3">
                                        <button type='submit' class='btn btn-primary' value='Continue'>
                                            <i class="fas fa-chevron-right"></i>  Proceed to Step 2
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <br />
                        </div>
STP1;
                        echo $step1 . "\r\n";
                        break;

                    case 2:
                        $state_esc = text($state);
                        $site_id_esc = attr($site_id);
                        $inst_esc = attr($inst);
                        $step2top = <<<STP2TOP
                        <h3 class="mb-3 border-bottom">Step $state_esc - Database and OpenEMR Initial User Setup Details</h3>
                        <div class="jumbotron p-5">
                            <p>Now you need to supply the MySQL server information and path information. Detailed instructions on each item can be found in the
                                <a href='Documentation/INSTALL' rel='noopener' target='_blank'><u>'INSTALL'</u>
                                </a> manual file.
                            </p>
                            <form method='post' id='myform'>
                                <input name='state' type='hidden' value='3' />
                                <input name='site' type='hidden' value='$site_id_esc' />
                                <input name='inst' type='hidden' value='$inst_esc' />
STP2TOP;
                        echo $step2top . "\r\n";


                        $step2tabletop1 = <<<STP2TBLTOP1
                        <fieldset>
                        <legend name="form_legend" id="form_legend" class='oe-setup-legend'>MySQL Server Details<i id="enter-details-tooltip" class="fa fa-info-circle oe-text-black oe-superscript enter-details-tooltip" aria-hidden="true"></i></legend>
                        <div class="ml-2 row">
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="server">Server Host:</label>
                                        <a href="#server_info" class="info-anchor icon-tooltip" data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='server' id='server' type='text' class='form-control' value='localhost' />
                                    </div>
                                </div>
                                <div id="server_info" class="collapse">
                                    <a href="#server_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>If you run MySQL and Apache/PHP on the same computer, then leave this as 'localhost'.</p>
                                    <p>If they are on separate computers, then enter the IP address of the computer running MySQL.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="port">Server Port:</label>
                                        <a href="#port_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='port' id='port' type='text' class='form-control' value='3306' />
                                    </div>
                                </div>
                                <div id="port_info" class="collapse">
                                    <a href="#port_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the MySQL port.</p>
                                    <p>The default port for MySQL is 3306.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="dbname">Database Name:</label> <a href="#dbname_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='dbname' id='dbname' type='text' class='form-control' value='openemr' />
                                    </div>
                                </div>
                                <div id="dbname_info" class="collapse">
                                    <a href="#dbname_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This will be the name of the OpenEMR database in MySQL.</p>
                                    <p>'openemr' is the recommended name.</p>
                                    <p>This database will contain patient data as well as data pertaining to the OpenEMR installation.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ml-2 row">
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="login">Login Name:</label> <a href="#login_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='login' ID='login' type='text' class='form-control' value='openemr' />
                                    </div>
                                </div>
                                <div id="login_info" class="collapse">
                                    <a href="#login_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the name that OpenEMR will use to login to the MySQL database.</p>
                                    <p>'openemr' is the recommended name.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="pass">Password:</label>
                                        <a href="#pass_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='pass' id='pass' class='form-control' type='password' value='' required />
                                    </div>
                                </div>
                                <div id="pass_info" class="collapse">
                                    <a href="#pass_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the Login Password that OpenEMR will use to accesses the MySQL database.</p>
                                    <p>It should be at least 12 characters long and composed of both numbers and letters.</p>
                                    <p>It cannot contain any of these characters: \ ; ( ) < > / ' "</p>
                                </div>
                            </div>
STP2TBLTOP1;
                        echo $step2tabletop1 . "\r\n";
                        if ($inst != 2) {
                            $step2tabletop2 = <<<STP2TBLTOP2
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="root">Name for Root Account:</label>
                                        <a href="#root_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='root' id='root' type='text' class='form-control' value='root' />
                                    </div>
                                </div>
                                <div id="root_info" class="collapse">
                                    <a href="#root_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is name for the MySQL root account.</p>
                                    <p>For localhost, it is usually ok to leave it as 'root'.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ml-2 row">
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="rootpass">Root Password:</label>
                                        <a href="#rootpass_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='rootpass' id='rootpass' type='password' class='form-control' value='' />
                                    </div>
                                </div>
                                <div id="rootpass_info" class="collapse">
                                    <a href="#rootpass_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is your MySQL server root password.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="loginhost">User Hostname:</label>
                                        <a href="#loginhost_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='loginhost' id='loginhost' type='text' class='form-control' value='localhost' />
                                    </div>
                                </div>
                                <div id="loginhost_info" class="collapse">
                                    <a href="#loginhost_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>If you run Apache/PHP and MySQL on the same computer, then leave this as 'localhost'.</p>
                                    <p>If they are on separate computers, then enter the IP address of the computer running Apache/PHP.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="collate">UTF-8 Collation:</label> <a href="#collate_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <select name='collate' id=='collate' class='form-control'>
                                            <option selected value='utf8mb4_general_ci'>
                                                General (Recommended)
                                            </option>
                                            <option value='utf8mb4_unicode_ci'>
                                                Unicode
                                            </option>
                                            <option value='utf8mb4_roman_ci'>
                                                Classical Latin
                                            </option>
                                            <option value='utf8mb4_croatian_ci'>
                                                Croatian
                                            </option>
                                            <option value='utf8mb4_czech_ci'>
                                                Czech
                                            </option>
                                            <option value='utf8mb4_danish_ci'>
                                                Danish
                                            </option>
                                            <option value='utf8mb4_esperanto_ci'>
                                                Esperanto
                                            </option>
                                            <option value='utf8mb4_estonian_ci'>
                                                Estonian
                                            </option>
                                            <option value='utf8mb4_german2_ci'>
                                                German
                                            </option>
                                            <option value='utf8mb4_hungarian_ci'>
                                                Hungarian
                                            </option>
                                            <option value='utf8mb4_icelandic_ci'>
                                                Icelandic
                                            </option>
                                            <option value='utf8mb4_latvian_ci'>
                                                Latvian
                                            </option>
                                            <option value='utf8mb4_lithuanian_ci'>
                                                Lithuanian
                                            </option>
                                            <option value='utf8mb4_persian_ci'>
                                                Persian
                                            </option>
                                            <option value='utf8mb4_polish_ci'>
                                                Polish
                                            </option>
                                            <option value='utf8mb4_romanian_ci'>
                                                Romanian
                                            </option>
                                            <option value='utf8mb4_sinhala_ci'>
                                                Sinhala
                                            </option>
                                            <option value='utf8mb4_slovak_ci'>
                                                Slovak
                                            </option>
                                            <option value='utf8mb4_slovenian_ci'>
                                                Slovenian
                                            </option>
                                            <option value='utf8mb4_spanish_ci'>
                                                Spanish (Modern)
                                            </option>
                                            <option value='utf8mb4_spanish2_ci'>
                                                Spanish (Traditional)
                                            </option>
                                            <option value='utf8mb4_swedish_ci'>
                                                Swedish
                                            </option>
                                            <option value='utf8mb4_turkish_ci'>
                                                Turkish
                                            </option>
                                            <option value='utf8mb4_vietnamese_ci'>
                                                Vietnamese
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div id="collate_info" class="collapse">
                                    <a href="#collate_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the collation setting for MySQL.</p>
                                    <p>Collation refers to a set of rules that determine how data is sorted and compared in a database.</p>
                                    <p>Leave as 'General' if you are not sure.</p>
                                    <p>If the language you are planning to use in OpenEMR is in the menu, then you can select it.</p>
                                    <p>Otherwise, just select 'General'.</p>
                                </div>
                            </div>
                        </div>
STP2TBLTOP2;
                            echo $step2tabletop2 . "\r\n";
                        }
                        // Include a "source" site ID drop-list and a checkbox to indicate
                        // if cloning its database.  When checked, do not display initial user
                        // and group stuff below.
                        $dh = opendir($OE_SITES_BASE);
                        if (!$dh) {
                            die("Cannot read directory '" . text($OE_SITES_BASE) . "'.");
                        }

                        $siteslist = array();
                        while (false !== ($sfname = readdir($dh))) {
                            if (substr($sfname, 0, 1) == '.') {
                                continue;
                            }

                            if ($sfname == 'CVS') {
                                continue;
                            }

                            if ($sfname == $site_id) {
                                continue;
                            }

                            $sitedir = "$OE_SITES_BASE/$sfname";
                            if (!is_dir($sitedir)) {
                                continue;
                            }

                            if (!is_file("$sitedir/sqlconf.php")) {
                                continue;
                            }

                            $siteslist[$sfname] = $sfname;
                        }

                        closedir($dh);
                        // If this is not the first site...
                        if (!empty($siteslist)) {
                            ksort($siteslist);
                            $source_site_top = <<<SOURCESITETOP
                        <div class="ml-2 row">
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="source_site_id">Source Site:</label>
                                        <a href="#source_site_id_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <select name='source_site_id'id='source_site_id' class='form-control'>
SOURCESITETOP;
                                                echo $source_site_top . "\r\n";
                            foreach ($siteslist as $sfname) {
                                echo "<option value='" . attr($sfname) . "'";
                                if ($sfname == 'default') {
                                    echo " selected";
                                }

                                echo ">" . text($sfname) . "</option>";
                            }
                                        $source_site_bot = <<<SOURCESITEBOT
                                        </select>

                                    </div>
                                </div>
                                <div id="source_site_id_info" class="collapse">
                                    <a href="#source_site_id_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>The site directory that will be a model for the new site.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="clone_database">Clone Source Database:</label>
                                        <a href="#clone_database_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input type='checkbox' name='clone_database' id='clone_database' onclick='cloneClicked()' />
                                    </div>
                                </div>
                                <div id="clone_database_info" class="collapse">
                                    <a href="#clone_database_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>Clone the source site's database instead of creating a fresh one.</p>
                                </div>
                            </div>
                        </div>
SOURCESITEBOT;
                            echo $source_site_bot . "\r\n";
                        }

                        $randomusernamepre = RandomGenUtils::produceRandomString(3, "ABCDEFGHIJKLMNOPQRSTUVWXYZ");
                        $randomusernamepost = RandomGenUtils::produceRandomString(2, "0123456789");
                        $randomusername = $randomusernamepre . "-admin-" . $randomusernamepost;

                        // App Based TOTP secret
                        // Shared key (per rfc6238 and rfc4226) should be 20 bytes (160 bits) and encoded in base32, which should
                        //   be 32 characters in base32
                        // Would be nice to use the OpenEMR\Common\Utils\RandomGenUtils\produceRandomBytes() function and then encode to base32,
                        //   but does not appear to be a standard way to encode binary to base32 in php.
                        $randomsecret = RandomGenUtils::produceRandomString(32, "234567ABCDEFGHIJKLMNOPQRSTUVWXYZ");
                        if (empty($randomsecret) || empty($randomusernamepre) || empty($randomusernamepost)) {
                            error_log('OpenEMR Error : Random String error - exiting');
                            die();
                        }
                        $disableCheckbox = "";
                        if (empty($randomsecret)) {
                            $randomsecret = "";
                            $disableCheckbox = "disabled";
                        }

                        $randomsecret_esc = attr($randomsecret);
                        $randomusername_esc = attr($randomusername);
                        $step2tablebot = <<<STP2TBLBOT
                    </fieldset>
                    <br />
                    <fieldset class='noclone'>
                        <legend name="form_legend" id="form_legend" class='oe-setup-legend'>OpenEMR Initial User Details<i id="enter-details-tooltip" class="fa fa-info-circle oe-text-black oe-superscript enter-details-tooltip" aria-hidden="true"></i></legend>
                        <div class="ml-2 row">
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="iuser">Initial User Login Name:</label> <a href="#iuser_info"  class="info-anchor icon-tooltip"  data-toggle="collapse" ><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='iuser' id='iuser' type='text' class='form-control' value='$randomusername_esc' minlength='12' />
                                    </div>
                                </div>
                                <div id="iuser_info" class="collapse">
                                    <a href="#iuser_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the login name of the first user that will be created for you.</p>
                                    <p>Limit this to one word with at least 12 characters and composed of both numbers and letters.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="iuserpass">Initial User Password:</label>
                                        <a href="#iuserpass_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='iuserpass' id='iuserpass' type='password' class='form-control' value='' minlength='12' />
                                    </div>
                                </div>
                                <div id="iuserpass_info" class="collapse">
                                    <a href="#iuserpass_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the password for the initial user.
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="iufname">Initial User's First Name:</label>
                                        <a href="#iufname_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='iufname' id='iufname 'type='text' class='form-control' value='Administrator' />
                                    </div>
                                </div>
                                <div id="iufname_info" class="collapse">
                                    <a href="#iufname_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the First name of the 'initial user'.</p>
                                </div>
                            </div>
                        </div>
                        <div class="ml-2 row">
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="iuname">Initial User's Last Name:</label>
                                        <a href="#iuname_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='iuname' id='iuname' type='text' class='form-control' value='Administrator' />
                                    </div>
                                </div>
                                <div id="iuname_info" class="collapse">
                                    <a href="#iuname_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the Last name of the 'initial user'.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="igroup">Initial Group:</label>
                                        <a href="#igroup_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='igroup' id='igroup' class='form-control' type='text' value='Default' />
                                    </div>
                                </div>
                                <div id="igroup_info" class="collapse">
                                    <a href="#igroup_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>This is the group that will be created for your users.</p>
                                    <p>This should be the name of your practice.</p>
                                </div>
                            </div>
                        </div>
                    </fieldset>
					<br />
                    <fieldset class='noclone py-2 bg-warning'>
                        <legend name="form_legend" id="form_legend" class='oe-setup-legend text-danger'>Enable 2 Factor Authentication for Initial User (more secure - optional) <i id="2fa-section" class="fa fa-info-circle oe-text-black oe-superscript 2fa-section-tooltip" aria-hidden="true"></i></legend>
                        <div class="ml-2 row">
                            <div class="col-sm-3">
                                <div class="clearfix form-group">
                                    <div class="label-div">
                                        <label class="font-weight-bold" for="i2fa">Configure 2FA:</label>
                                        <a href="#i2fa_info"  class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <div>
                                        <input name='i2faenable' id='i2faenable' type='checkbox' $disableCheckbox/> Enable 2FA
                                        <input type='hidden' name='i2fasecret' id='i2fasecret' value='$randomsecret_esc' />
                                    </div>
                                </div>
                                <div id="i2fa_info" class="collapse">
                                    <a href="#i2fa_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
                                    <p>If selected will allow TOTP 2 factor authentication for the initial user.</p>
                                    <p>Click on the help file for more information.</p>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="clearfix form-group">
                                    <p class="text-danger font-weight-bold">IMPORTANT IF ENABLED</p>
                                    <p>If enabled, you must have an authenticator app on your phone ready to scan the QR code displayed next.</p>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="clearfix form-group">
                                    <p>Example authenticator apps include:</p>
                                    <ul>
                                        <li>Google Auth
                                            (<a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8" target="_blank">iOS</a>, <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&amp;hl=en">Android</a>)</li>
                                        <li>Authy
                                            (<a href="https://itunes.apple.com/us/app/authy/id494168017?mt=8">iOS</a>, <a href="https://play.google.com/store/apps/details?id=com.authy.authy&amp;hl=en">Android</a>)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                            <p class='mt-4 mark'>Click the <b>Create DB and User</b> button below to create the database and first user <a href='#create_db_button' title='Click me'><i class="fa fa-arrow-circle-down" aria-hidden="true"></i></a>. $note: This process will take a few minutes.</p>
                            <p class='p-1 bg-success text-white oe-spinner' style='visibility:hidden;'>Upon successful completion will automatically take you to the next step.<i class='fa fa-spinner fa-pulse fa-fw'></i></p>
                            <div class="form-row">
                                <div class="col-12">
                                    <button type='submit' id='create_db_button' value='Continue' class='wait btn btn-primary'>
                                        <i class="fas fa-chevron-right"></i>  Create DB and User
                                    </button>
                                </div>
                            </div>
                        </form>
                        </div>
STP2TBLBOT;
                        echo $step2tablebot . "\r\n";
                        break;

                    case 3:
                        // Form Validation
                                    //   (applicable if not cloning from another database)

                                    $pass_step2_validation = true;
                                    $error_step2_message   = "$error - ";

                        if (! $installer->char_is_valid($_REQUEST['server'])) {
                            $pass_step2_validation = false;
                            $error_step2_message .=  "A database server host is required <br />\n";
                        }

                        if (! $installer->char_is_valid($_REQUEST['port'])) {
                            $pass_step2_validation = false;
                            $error_step2_message .=  "A database server port value is required <br />\n";
                        }

                        if (! $installer->databaseNameIsValid($_REQUEST['dbname'])) {
                            $pass_step2_validation = false;
                            $error_step2_message .= "A database name is required <br />\n";
                        }

                        if (! $installer->collateNameIsValid($_REQUEST['collate'])) {
                            $pass_step2_validation = false;
                            $error_step2_message .= "A collation name is required <br />\n";
                        }

                        if (! $installer->char_is_valid($_REQUEST['login'])) {
                            $pass_step2_validation = false;
                            $error_step2_message .= "A database login name is required <br />\n";
                        }

                        if (! $installer->char_is_valid($_REQUEST['pass'])) {
                            $pass_step2_validation = false;
                            $error_step2_message .= "A database login password is required <br />\n";
                        }

                        if (!$pass_step2_validation) {
                            $error_step2_message .= $error_page_end . "\r\n";
                            die($error_step2_message);
                        }


                        if (empty($installer->clone_database)) {
                            if (! $installer->login_is_valid()) {
                                echo "$error. Please pick a proper 'Login Name'.<br />\n";
                                echo "Click Back in browser to re-enter.<br />\n";
                                break;
                            }

                            if (! $installer->iuser_is_valid()) {
                                echo "$error. The 'Initial User' field can only contain one word and no spaces.<br />\n";
                                echo "Click Back in browser to re-enter.<br />\n";
                                break;
                            }

                            if (! $installer->user_password_is_valid()) {
                                echo "$error. Please pick a proper 'Initial User Password'.<br />\n";
                                echo "Click Back in browser to re-enter.<br />\n";
                                break;
                            }
                        }

                        if (! $installer->password_is_valid()) {
                            echo "$error. Please pick a proper 'Password'.<br />\n";
                            echo "Click Back in browser to re-enter.<br />\n";
                            break;
                        }

                        if (! $installer->iuname_is_valid()) {
                            echo "$error. Please pick a proper 'Initial User Last Name'.<br />\n";
                            echo "Click Back in browser to re-enter.<br />\n";
                            break;
                        }

                                    echo "<h3 class='mb-3 border-bottom'>Step " . text($state) . " - Creating Database and First User</h3>";
                                    echo "<div class='jumbotron p-5'>";

                                    // Skip below if database shell has already been created.
                        if ($inst != 2) {
                            echo "Connecting to MySQL Server...\n";
                            flush();
                            if (! $installer->root_database_connection()) {
                                echo "$error.  Check your login credentials.\n";
                                echo text($installer->error_message);
                                break;
                            } else {
                                echo "$ok.<br />\n";
                                flush();
                            }
                        }

                                    // Only pertinent if cloning another installation database
                        if ($allow_cloning_setup && !empty($installer->clone_database)) {
                            echo "Dumping source database...";
                            flush();
                            if (! $installer->create_dumpfiles()) {
                                echo text($installer->error_message);
                                break;
                            } else {
                                echo "$ok.<br />\n";
                                flush();
                            }
                        }

                                    // Only pertinent if mirroring another installation directory
                        if (! empty($installer->source_site_id)) {
                            echo "Creating site directory...";
                            if (! $installer->create_site_directory()) {
                                echo text($installer->error_message);
                                break;
                            } else {
                                echo "$ok.<br />";
                                flush();
                            }
                        }

                                    // Skip below if database shell has already been created.
                        if ($inst != 2) {
                            echo "Creating database...\n";
                            flush();
                            if (! $installer->create_database()) {
                                echo "$error.  Check your login credentials.\n";
                                echo text($installer->error_message);
                                break;
                            } else {
                                echo "$ok.<br />\n";
                                flush();
                            }

                            echo "Creating user with permissions for database...\n";
                            flush();
                            $user_mysql_error = true;
                            if (! $installer->create_database_user()) {
                                echo "$error when creating specified user.\n";
                                echo text($installer->error_message);
                                break;
                            } else {
                                $user_mysql_error = false;
                            }
                            if (! $installer->grant_privileges()) {
                                echo "$error when granting privileges to the specified user.\n";
                                echo text($installer->error_message);
                                break;
                            } else {
                                $user_mysql_error = false;
                            }
                            if (!$user_mysql_error) {
                                echo "$ok.<br />\n";
                                flush();
                            }

                            echo "Reconnecting as new user...\n";
                            flush();
                            $installer->disconnect();
                        } else {
                            echo "Connecting to MySQL Server...\n";
                        }

                        if (! $installer->user_database_connection()) {
                            echo "$error.  Check your login credentials.\n";
                            echo text($installer->error_message);
                            break;
                        } else {
                            echo "$ok.<br />\n";
                            flush();
                        }

                                      // Load the database files
                                      $dump_results = $installer->load_dumpfiles();
                        if (! $dump_results) {
                            echo "$error.\n";
                            echo text($installer->error_message);
                            break;
                        } else {
                            echo $dump_results;
                            flush();
                        }

                                      echo "Writing SQL configuration...\n";
                                      flush();
                        if (! $installer->write_configuration_file()) {
                            echo "$error.\n";
                            echo text($installer->error_message);
                            break;
                        } else {
                            echo "$ok.<br />\n";
                            flush();
                        }

                                      // Only pertinent if not cloning another installation database
                        if (empty($installer->clone_database)) {
                            echo "Setting version indicators...\n";
                            flush();
                            if (! $installer->add_version_info()) {
                                echo "$error.\n";
                                echo text($installer->error_message);
                                ;
                                break;
                            } else {
                                echo "$ok<br />\n";
                                flush();
                            }

                            echo "Writing global configuration defaults...\n";
                            flush();
                            if (! $installer->insert_globals()) {
                                echo "$error.\n";
                                echo text($installer->error_message);
                                ;
                                break;
                            } else {
                                echo "$ok<br />\n";
                                flush();
                            }

                            echo "Setting up Access Controls...\n";
                            require("$OE_SITE_DIR/sqlconf.php");
                            if (! $installer->install_gacl()) {
                                echo "$error -.\n";
                                echo text($installer->error_message);
                                break;
                            } else {
                                echo "$ok<br />\n";
                                flush();
                            }


                            echo "Adding Initial User...\n";
                            flush();
                            if (! $installer->add_initial_user()) {
                                echo "$error.\n";
                                echo text($installer->error_message);
                                break;
                            }
                            echo "$ok<br />\n";
                            flush();

                            echo "Adding Additional Users...\n";
                            flush();
                            if (! $installer->install_additional_users()) {
                                echo "$error.\n";
                                echo text($installer->error_message);
                                break;
                            }
                            echo "$ok<br />\n";
                            flush();

                            echo "Configuring Care Coordination Module...\n";
                            flush();
                            if (! $installer->on_care_coordination()) {
                                echo "$error.\n";
                                echo text($installer->error_message);
                                break;
                            }
                            echo "$ok<br />\n";
                            flush();
                        }

                        // If user has selected to set MFA App Based 2FA, display QR code to scan
                        $qr = $installer->get_initial_user_2fa_qr();
                        $qr_esc = attr($qr);
                        if ($qr) {
                            $qrDisplay = <<<TOTP
                                        <br />
                                        <table>
                                            <tr>
                                                <td>
                                                    <strong class='text-danger'>IMPORTANT!!</strong>
                                                    <p><strong>You must scan the following QR code with your preferred authenticator app.</strong></p>
                                                    <img src='$qr_esc' width="150" />
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Example authenticator apps include:
                                                    <ul>
                                                        <li>Google Auth
                                                            (<a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8">iOS</a>, <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en">Android</a>)</li>
                                                        <li>Authy
                                                            (<a href="https://itunes.apple.com/us/app/authy/id494168017?mt=8">iOS</a>, <a href="https://play.google.com/store/apps/details?id=com.authy.authy&hl=en">Android</a>)</li>
                                                    </ul>
                                                </td>
                                            </tr>
                                        </table>
TOTP;
                            echo $qrDisplay;
                        }

                        if ($allow_cloning_setup && !empty($installer->clone_database)) {
                            // Database was cloned, skip ACL setup.
                            $btn_text = 'Proceed to Select a Theme';
                            echo "<br />";
                            echo "<p>The database was cloned, access control list exists therefore skipping ACL setup</p>";
                            echo "<p class='p-1 bg-warning'>Click <b>" . text($btn_text) . "</b> for further instructions.</p>";
                            $next_state = 7;
                        } else {
                            $btn_text = 'Proceed to Step 4';
                            echo "<br />";
                            echo "<p><b>Granted user <span class='text-primary'>" . text($installer->iuser) . "</span> administrator access control (password is <span class='text-primary'>" . text($installer->iuserpass) . "</span>).</b></p>";
                            echo "<p>The next step will configure php.</p>";
                            echo "<p class='mark'>Click <strong>" . text($btn_text) . "</strong> to continue.</p>";
                            $next_state = 4;
                        }

                        echo "<form method='post'>
                              <input name='state' type='hidden' value='" . attr($next_state) . "' />
                              <input name='site' type='hidden' value='" . attr($site_id) . "' />
                              <input name='iuser' type='hidden' value='" . attr($installer->iuser) . "' />
                              <input name='iuserpass' type='hidden' value='" . attr($installer->iuserpass) . "' />
                              <input name='iuname' type='hidden' value='" . attr($installer->iuname) . "' />
                              <input name='iufname' type='hidden' value='" . attr($installer->iufname) . "' />
                              <input name='login' type='hidden' value='" . attr($installer->login) . "' />
                              <input name='pass' type='hidden' value='" . attr($installer->pass) . "' />
                              <input name='server' type='hidden' value='" . attr($installer->server) . "' />
                              <input name='port' type='hidden' value='" . attr($installer->port) . "' />
                              <input name='loginhost' type='hidden' value='" . attr($installer->loginhost) . "' />
                              <input name='dbname' type='hidden' value='" . attr($installer->dbname) . "' />\r\n";

                        if ($allow_cloning_setup) {
                            echo "<input type='hidden' name='clone_database' value='" . attr($installer->clone_database) . "' />";
                            echo "<input name='source_site_id' type='hidden' value='" . attr($installer->source_site_id) . "' />";
                        }
                                    $btn_text_esc = text($btn_text);
                                    $form_bottom = <<<FRMBOT
                                    <div class="form-row">
                                        <div class="col-12">
                                            <button type='submit' id='step-4-btn' class="btn btn-primary" value='Continue'>
                                                <i class="fas fa-chevron-right"></i>  $btn_text_esc
                                            </button>
                                        </div>
                                    </div>
                                    </form>
                                    </div>
FRMBOT;
                                    echo $form_bottom . "\r\n";
                        break;

                    case 4:
                        $state_esc = text($state);
                        $step4_top = <<<STP4TOP
                        <h3 class="mb-3 border-bottom">Step $state_esc - Configure PHP</h3>
                        <div class="jumbotron p-5">
                        <p>Configuration of PHP...</p>
                        <p>We recommend making the following changes to your PHP installation, which can normally be done by editing the php.ini configuration file:</p>
                        <ul>
STP4TOP;
                        echo $step4_top . "\r\n";

                        $gotFileFlag = 0;
                        $phpINIfile  = php_ini_loaded_file();
                        if ($phpINIfile) {
                            echo "<li><span class='text-success'>Your php.ini file can be found at " . text($phpINIfile) . "</span></li>\n";
                            $gotFileFlag = 1;
                        }

                        $short_tag = ini_get('short_open_tag') ? 'On' : 'Off';
                        $short_tag_style = (strcmp($short_tag, 'Off') === 0) ? '' : 'text-danger';
                        $display_errors = ini_get('display_errors') ? 'On' : 'Off';
                        $display_errors_style = (strcmp($display_errors, "Off")  === 0) ? '' : 'text-danger';
                        $register_globals = ini_get('register_globals') ? 'On' : 'Off';
                        $register_globals_style = (strcmp($register_globals, 'Off')  === 0) ? '' : 'text-danger';
                        $max_input_vars = ini_get('max_input_vars');
                        $max_input_vars_style = $max_input_vars < 3000 ? 'text-danger' : '';
                        $max_execution_time = (int)ini_get('max_execution_time');
                        $max_execution_time_style = $max_execution_time >= 60 || $max_execution_time === 0 ? '' : 'text-danger';
                        $max_input_time = ini_get('max_input_time');
                        $max_input_time_style = (strcmp($max_input_time, '-1')  === 0) ? '' : 'text-danger';
                        $post_max_size = ini_get('post_max_size');
                        $post_max_size_style = $post_max_size < 30 ? 'text-danger' : '';
                        $memory_limit = ini_get('memory_limit');
                        $memory_limit_style = $memory_limit < 256 ? 'text-danger' : '';
                        $mysqli_allow_local_infile = ini_get('mysqli.allow_local_infile') ? 'On' : 'Off';
                        $mysqli_allow_local_infile_style = (strcmp($mysqli_allow_local_infile, 'On')  === 0) ? '' : 'text-danger';

                        echo   "<li>To ensure proper functioning of OpenEMR you must make sure that PHP settings include:
                                <table class='phpset'>
                                    <tr>
                                        <th>Setting</th>
                                        <th>Required value</th>
                                        <th>Current value</th>
                                    </tr>
                                    <tr>
                                        <td>short_open_tag</td>
                                        <td>Off</td>
                                        <td class='" . attr($short_tag_style) . "'>" . text($short_tag) . "</td>
                                    </tr>
                                    <tr>
                                        <td>display_errors</td>
                                        <td>Off</td>
                                        <td class='" . attr($display_errors_style) . "'>" . text($display_errors) . "</td>
                                    </tr>
                                    <tr>
                                        <td>register_globals</td>
                                        <td>Off</td>
                                        <td class='" . attr($register_globals_style) . "'>" . text($register_globals) . "</td>
                                    </tr>
                                    <tr>
                                        <td>max_input_vars</td>
                                        <td>at least 3000</td>
                                        <td class='" . attr($max_input_vars_style) . "'>" . text($max_input_vars) . "</td>
                                    </tr>
                                    <tr>
                                        <td>max_execution_time</td>
                                        <td>at least 60</td>
                                        <td class='" . attr($max_execution_time_style) . "'>" . text($max_execution_time) . "</td>
                                    </tr>
                                    <tr>
                                        <td>max_input_time</td>
                                        <td>-1</td>
                                        <td class='" . attr($max_input_time_style) . "'>" . text($max_input_time) . "</td>
                                    </tr>
                                    <tr>
                                        <td>post_max_size</td>
                                        <td>at least 30M</td>
                                        <td class='" . attr($post_max_size_style) . "'>" . text($post_max_size) . "</td>
                                    </tr>
                                    <tr>
                                        <td>memory_limit</td>
                                        <td>at least 256M</td>
                                        <td class='" . attr($memory_limit_style) . "'>" . text($memory_limit) . "</td>
                                    </tr>
                                    <tr>
                                        <td>mysqli.allow_local_infile</td>
                                        <td>On</td>
                                        <td class='" . attr($mysqli_allow_local_infile_style) . "'>" . text($mysqli_allow_local_infile) . "</td>
                                    </tr>
                                </table>
                            </li>
                            <li>In order to take full advantage of the patient documents capability you must make sure that settings in php.ini file include \"file_uploads = On\", that \"upload_max_filesize\" is appropriate for your use and that \"upload_tmp_dir\" is set to a correct value that will work on your system.
                            </li>\r\n";

                        if (!$gotFileFlag) {
                            echo "<li>If you are having difficulty finding your php.ini file, then refer to the <a href='Documentation/INSTALL' rel='noopener' target='_blank'><u>'INSTALL'</u></a> manual for suggestions.</li>\n";
                        }

                        $btn_text = 'Proceed to Step 5';
                        echo "</ul>

                        <p>We recommend you print these instructions for future reference.</p>
                        <p>The next step will configure the Apache web server.</p>
                        <p class='mark'>Click <strong>" . text($btn_text) . "</strong> to continue.</p>
                        <br />
                        <form method='post'>
                            <input type='hidden' name='state' value='5' />
                            <input type='hidden' name='site' value='" . attr($site_id) . "' />
                            <input type='hidden' name='iuser' value='" . attr($installer->iuser) . "' />
                            <input type='hidden' name='iuserpass' value='" . attr($installer->iuserpass) . "' />
                            <input name='login' type='hidden' value='" . attr($installer->login) . "' />
                            <input name='pass' type='hidden' value='" . attr($installer->pass) . "' />
                            <input name='server' type='hidden' value='" . attr($installer->server) . "' />
                            <input name='port' type='hidden' value='" . attr($installer->port) . "' />
                            <input name='loginhost' type='hidden' value='" . attr($installer->loginhost) . "' />
                            <input name='dbname' type='hidden' value='" . attr($installer->dbname) . "' />
                            <div class='form-row'>
                                <div class='col-12'>
                                    <button type='submit' class='btn btn-primary' value='Continue'>
                                        <i class='fas fa-chevron-right'></i>  " . text($btn_text) . "
                                    </button>
                                </div>
                            </div>
                        </form>
                        </div>\r\n";
                        break;

                    case 5:
                        echo "<h3 class='mb-3 border-bottom'>Step " . text($state) . " - Configure Apache Web Server</h3>";
                        echo "<div class='jumbotron p-5'>";
                        echo "<p>Configuration of Apache web server...</p><br />\n";
                        echo "The <code>\"" . text(preg_replace("/${site_id}/", "*", realpath($docsDirectory))) . "\"</code> directory contain patient information, and
                        it is important to secure these directories. Additionally, some settings are required for the Zend Framework to work in OpenEMR. This can be done by pasting the below to end of your apache configuration file:<br /><br />
                        &nbsp;&nbsp;<code>&lt;Directory \"" . text(realpath(dirname(__FILE__))) . "\"&gt;<br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AllowOverride FileInfo<br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Require all granted<br />
                        &nbsp;&nbsp;<code>&lt;/Directory&gt;</code><br />
                        &nbsp;&nbsp;&lt;Directory \"" . text(realpath(dirname(__FILE__))) . "/sites\"&gt;<br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;AllowOverride None<br />
                        &nbsp;&nbsp;&lt;/Directory&gt;</code><br />
                        &nbsp;&nbsp;<code>&lt;Directory \"" . text(preg_replace("/${site_id}/", "*", realpath($docsDirectory))) . "\"&gt;<br />
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Require all denied<br />
                        &nbsp;&nbsp;&lt;/Directory&gt;</code><br /><br />";

                        $btn_text = 'Proceed to Select a Theme';
                        echo "<p>If you are having difficulty finding your apache configuration file, then refer to the <a href='Documentation/INSTALL' rel='noopener' target='_blank'><u>'INSTALL'</u></a> manual for suggestions.</p>
                        <p>We recommend you print these instructions for future reference.</p>
                        <p class='mark'>Click <strong>'" . text($btn_text) . "'</strong> to select a theme.</p>
                        <br />
                        <form method='post'>
                            <input type='hidden' name='state' value='6' />
                            <input type='hidden' name='site' value='" . attr($site_id) . "' />
                            <input type='hidden' name='iuser' value='" . attr($installer->iuser) . "' />
                            <input type='hidden' name='iuserpass' value='" . attr($installer->iuserpass) . "' />
                            <input name='login' type='hidden' value='" . attr($installer->login) . "' />
                            <input name='pass' type='hidden' value='" . attr($installer->pass) . "' />
                            <input name='server' type='hidden' value='" . attr($installer->server) . "' />
                            <input name='port' type='hidden' value='" . attr($installer->port) . "' />
                            <input name='loginhost' type='hidden' value='" . attr($installer->loginhost) . "' />
                            <input name='dbname' type='hidden' value='" . attr($installer->dbname) . "' />
                            <div class='form-row'>
                                <div class='col-12'>
                                    <button type='submit' class='btn btn-primary' value='Continue'>
                                        <i class='fas fa-chevron-right'></i>  " . text($btn_text) . "
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div>\r\n";
                        break;

                    case 6:
                        echo "<h3 class='mb-3 border-bottom'>Step " . text($state) . " - Select a Theme</h3>";
                        echo "<div class='jumbotron p-5'>";
                        echo "<p>Select a theme for OpenEMR...</p>\n";
                        $btn_text = "Proceed to Final Step";
                        $installer->displaySelectedThemeDiv();
                        echo "<div class='row'>
                        <div class='col-12'>
                            <form method='post'>
                                <input type='hidden' name='state' value='7' />
                                <input type='hidden' name='site' value='" . attr($site_id) . "' />
                                <input type='hidden' name='iuser' value='" . attr($installer->iuser) . "' />
                                <input type='hidden' name='iuserpass' value='" . attr($installer->iuserpass) . "' />
                                <input name='login' type='hidden' value='" . attr($installer->login) . "' />
                                <input name='pass' type='hidden' value='" . attr($installer->pass) . "' />
                                <input name='server' type='hidden' value='" . attr($installer->server) . "' />
                                <input name='port' type='hidden' value='" . attr($installer->port) . "' />
                                <input name='loginhost' type='hidden' value='" . attr($installer->loginhost) . "' />
                                <input name='dbname' type='hidden' value='" . attr($installer->dbname) . "' />
                                <input type='hidden' name='new_theme' id = 'new_theme' value='" . attr($installer->getCurrentTheme()) . "' />
                                <input name='clone_database' type='hidden' value='" . attr($installer->clone_database) . "' />
                                <input name='source_site_id' type='hidden' value='" . attr($installer->source_site_id) . "' />
                            <h4>Select One:</h4>
                                <div class='checkbox'>
                                  <label><input type='checkbox' class='check' value='show_theme' />Show More Themes</label>
                                </div>
                                <div class='checkbox'>
                                  <label><input type='checkbox' class='check' value='keep_current' />Keep Current</label>
                                </div>
                                <div class='hide_button' style='display:none;'>
                                    <button type='submit' class='btn btn-primary' value='Continue' id='continue'>
                                        <i class='fas fa-chevron-right'></i>  " . text($btn_text) . "
                                    </button>
                                </div>
                            </form>
                        </div>
					</div>\r\n";
                        echo '<div class="row hideaway" style="display:none;">' . "\r\n";
                        echo '<div class="col-12">' . "\r\n";
                        echo '    <h4>Select New Theme: <h5>(scroll down to view all)</h5></h4>' . "\r\n";
                        echo '    <br />' . "\r\n";
                        $installer->displayThemesDivs();
                        echo "</div>";
                        break;

                    case 0:
                    default:
                        $top = <<<TOP
                            <h3 class="mb-3 border-bottom">Pre Install - Checking File and Directory Permissions</h3>
                                <div class="jumbotron p-5">
                                    <p>
                                        Welcome to OpenEMR. This utility will step you through the installation and configuration of OpenEMR for your practice.
                                    </p>
                                    <ul>
                                        <li>
                                            Before proceeding, be sure that you have a properly installed and configured MySQL server available, and a PHP configured webserver.
                                        </li>
                                        <li>
                                            <span class="text-highlight">Detailed installation instructions can be found in the <a href='Documentation/INSTALL' rel='noopener' target='_blank'><u>'INSTALL'</u></a> manual file.</span>
                                        </li>
                                        <li>
                                            If you are upgrading from a previous version, <strong>DO NOT</strong> use this script. Please read the <strong>'Upgrading'</strong> section found in the <a href='Documentation/INSTALL' rel='noopener' target='_blank'><u>'INSTALL'</u></a> manual file.
                                        </li>
                                    </ul>
TOP;
                                    echo $top;
                        if ($checkPermissions) {
                            echo "<p>We will now ensure correct file and directory permissions before starting installation:</p>\n";
                            echo "<p class='text-success m-0'>Ensuring following file is world-writable...</p>\n";
                            $errorWritable = 0;
                            foreach ($writableFileList as $tempFile) {
                                if (is_writable($tempFile)) {
                                        echo "<code class='ml-5'>" . text(realpath($tempFile)) . "</code> file is <span class='text-success font-weight-bold'>ready</span><br /><br />\n";
                                } else {
                                        echo "<p><span class='text-danger'>UNABLE</span> to open file '" . text(realpath($tempFile)) . "' for writing.<br />\n";
                                        echo "(configure file permissions; see below for further instructions)</p>\n";
                                        $errorWritable = 1;
                                }
                            }

                            if ($errorWritable) {
                                $site_id_esc = attr($site_id);
                                $check_file = <<<CHKFILE
                                            <p class="text-danger">You can't proceed until all above files are ready (world-writable).</p>
                                            <p>In linux, recommend changing file permissions with the <strong>'chmod 666 filename'</strong> command.</p>
                                            <p class='p-1 bg-danger text-white'>Fix above file permissions and then click the <strong>'Check Again'</strong> button to re-check files.</p>
                                            <br />
                                            <form method='post'>
                                                <input type='hidden' name='site' value='$site_id_esc' />
                                                <button type='submit' class='btn btn-primary' value='check again'>Check Again</button>
                                            </form>
CHKFILE;
                                echo $check_file . "\r\n";
                                break;
                            }

                            $errorWritable = 0;
                            foreach ($writableDirList as $tempDir) {
                                echo "<p class='text-success m-0'>Ensuring the <code>" . text(realpath($tempDir)) . "</code> directory and its subdirectories have proper permissions...</p>\n";
                                $errorWritable = recursive_writable_directory_test($tempDir);
                            }

                            if ($errorWritable) {
                                $site_id_esc = attr($site_id);
                                $check_directory = <<<CHKDIR
                                            <p class="text-danger">You can't proceed until all directories and subdirectories are ready.</p>
                                            <p>In linux, recommend changing owners of these directories to the web server. For example, in many linux OS's the web server user is 'apache', 'nobody', or 'www-data'. So if 'apache' were the web server user name, could use the command <strong>'chown -R apache:apache directory_name'</strong> command.</p>
                                            <p class='p-1 bg-warning'>Fix above directory permissions and then click the <strong>'Check Again'</strong> button to re-check directories.</p>
                                            <br />
                                            <form method='post'>
                                                <input type='hidden' name='site' value='$site_id_esc' />
                                                <button type='submit' value='check again'><b>Check Again</b></button>
                                            </form>
CHKDIR;
                                echo $check_directory . "\r\n";
                                break;
                            }

                            //RP_CHECK_LOGIC
                            $site_id_esc = attr($site_id);
                            $form = <<<FRM
                                        <p>All required files and directories have been verified.</p>
                                        <p class='mark'>Click <span class="font-weight-bold">Proceed to Step 1</span> to continue with a new installation.</p>
                                        <p class='p-1 bg-warning'>$caution: If you are upgrading from a previous version, <strong>DO NOT</strong> use this script. Please read the <strong>'Upgrading'</strong> section found in the <a href='Documentation/INSTALL' rel='noopener' target='_blank'><u>'INSTALL'</u></a> manual file.</p>
                                        <br />
                                        <form method='post'>
                                            <input name='state' type='hidden' value='1' />
                                            <input name='site' type='hidden' value='$site_id_esc' />
                                            <div class="form-group">
                                                <div class="col">
                                                    <button type='submit' class='btn btn-primary' value='Continue'>
                                                        <i class="fas fa-chevron-right"></i>  Proceed to Step 1
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
FRM;
                            echo $form . "\r\n";
                        } else {
                            $site_id_esc = attr($site_id);
                            $form = <<<FRM
                                        <br />
                                        <p class='p-1 bg-warning'>$caution: Permisssions checking has been disabled. All required files and directories have NOT been verified, please manually verify sites/$site_id_esc .</p>
                                        <p class='mark'>Click <b>Proceed to Step 1</b> to continue with a new installation.</p>
                                        <p class='p-1 bg-warning'>$caution: If you are upgrading from a previous version, <strong>DO NOT</strong> use this script. Please read the <strong>'Upgrading'</strong> section found in the <a href='Documentation/INSTALL' rel='noopener' target='_blank'><span style='text-decoration: underline;'>'INSTALL'</span></a> manual file.</p>
                                        <br />
                                        <form method='post'>
                                            <input name='state' type='hidden' value='1'>
                                            <input name='site' type='hidden' value='$site_id_esc'>
                                            <button type='submit' value='Continue'><b>Proceed to Step 1</b></button>
                                        </form>
FRM;
                            echo $form . "\r\n";                        }
                }
            }
                        $bot = <<<BOT
                                    </div>
                                </div>
                            </div>
BOT;
                        echo $bot . "\r\n";
            ?>


    </div><!--end of container div -->
    <?php $installer->setupHelpModal();?>
    <script>
        //jquery-ui tooltip
        $(function () {
            $('.icon-tooltip').prop( "title", "Click to see more information").tooltip({
                show: {
                    delay: 700,
                    duration: 0
                }
            });
            $('.enter-details-tooltip').prop( "title", "Additional help to fill out this form is available by hovering over labels of each box and clicking on the dark blue help ? icon that is revealed. On mobile devices tap once on the label to reveal the help icon and tap on the icon to show the help section").tooltip();
            $('.2fa-section-tooltip').prop( "title", "Two factor authentication prevents unauthorized access to openEMR thus improves security. It is optional. More information is available in the help file under Step 2 Database and OpenEMR Initial User Setup Details.").tooltip();


        });
    </script>
    <script>
        $(function () {
            $("input[type='radio']").click(function() {
                var radioValue = $("input[name='stylesheet']:checked").val();
                var imgPath = "public/images/stylesheets/";
                var currStyle = $("#current_theme_title").text();
                var currStyleTitle = currStyle;
                currStyle = currStyle.replace(/\b\w/g, l => l.toLowerCase());
                currStyle = currStyle.split(" ");
                currStyle = currStyle.join("_");
                currStyle = "style_" + currStyle + ".png";
                if (radioValue) {
                    var currThemeText = radioValue.split("_");
                    currThemeText = currThemeText.join(" ");
                    currThemeText = currThemeText.replace(/\b\w/g, l => l.toUpperCase());
                    var styleSelected = confirm("You have selected style  - " + currThemeText + "\n" + "Click OK to apply selection");
                    if (styleSelected) {
                        $("#current_theme").attr("src", imgPath + "style_" + radioValue + ".png");
                        $("#current_theme_title").text(currThemeText);
                        $("#new_theme").val("style_" + radioValue + ".css");
                    } else {
                        $("#current_theme").attr("src", imgPath + currStyle);
                        $("#current_theme_title").text(currStyleTitle);
                        $(this).prop("checked", false);
                    }
                }
            });
            $('.check').click(function() {
                $('.check').not(this).prop('checked', false);
                    if($('.check:checked').val() == 'show_theme'){
                        $(".hideaway").show();
                    } else if($('.check:checked').val() == 'keep_current'){
                        $(".hideaway").hide();
                    }

                    if($('.check').filter(':checked').length > 0) {
                        $(".hide_button").show();
                    } else {
                        $(".hide_button").hide();
                        $(".hideaway").hide();
                    }
            });
            $('.wait').removeClass('button-wait');

            $( "#create_db_button" ).hover(
                function() {
                    if (($('#iuserpass' ).val().length > 11 && $('#iuser' ).val().length > 11 ) || ($('#clone_database').prop('checked'))){

                        $("button").click(function(){
                           $(".oe-spinner").css("visibility", "visible");
                        });

                        $('.wait').click(function(){
                             $('.wait').addClass('button-wait');
                        });
                    }
                }
            );

            $("#step-4-btn").click(function(){
               $(".oe-spinner").css("visibility", "visible");
               $(this).addClass('button-wait');
            });
        });
    </script>
</body>
</html>
