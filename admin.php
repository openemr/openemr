<?php
/**
 *
 * Multi Site Administration script.
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 * @author Rod Roark <rod@sunsetsystems.com>
 * @author Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
// Checks if the server's PHP version is compatible with OpenEMR:
require_once(dirname(__FILE__) . "/src/Common/Compatibility/Checker.php");
$response = OpenEMR\Common\Compatibility\Checker::checkPhpVersion();
if ($response !== true) {
    die(htmlspecialchars($response));
}

require_once "version.php";

// Please note that the plain sql is used over the Doctrine ORM for
// `version` table interactions because it cannot connect due to a
// lack of context (this code is ran outside of the OpenEMR context).

$webserver_root = dirname(__FILE__);
if (stripos(PHP_OS, 'WIN') === 0) {
    $webserver_root = str_replace("\\", "/", $webserver_root);
}

$OE_SITES_BASE = "$webserver_root/sites";

function sqlQuery($statement, $link)
{
    $row = mysqli_fetch_array(mysqli_query($link, $statement), MYSQLI_ASSOC);
    return $row;
}
?>
<html>
<head>
<title>OpenEMR Site Administration</title>
<link rel="stylesheet" href="public/assets/bootstrap/dist/css/bootstrap.min.css" type="text/css">
<link rel="stylesheet" href="public/assets/jquery-ui/jquery-ui.css" type="text/css">
<script type="text/javascript" src="public/assets/jquery/dist/jquery.min.js"></script>
<script type="text/javascript" src="public/assets/bootstrap/dist/js/bootstrap.min.js"></script>
<script type="text/javascript" src="public/assets/jquery-ui/jquery-ui.js"></script>
<link rel="stylesheet" href="public/assets/font-awesome/css/font-awesome.min.css" type="text/css">
<link rel="shortcut icon" href="public/images/favicon.ico" />
<style>
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
        background-color:  WHITESMOKE;
        padding:0 10px;
    }
    .oe-text-green {
        color: green;
    }
    button {
    font-weight:bold;
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
</head>
<body>
    <div class='container'>
        <div class="row">
            <div class="col-sm-12">
                <div class="page-header">
                    <h2>OpenEMR Multi Site Administration <a class="oe-pull-away oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="Click to view Help"><i class="fa fa-question-circle" aria-hidden="true"></i></a></h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class='table table-striped'>
                 <tr class='head'>
                  <td>Site ID</td>
                  <td>DB Name</td>
                  <td>Site Name</td>
                  <td>Version</td>
                  <td align='center'>Is Current</td>
                  <td align='center'>Log In</td>
                 </tr>
                <?php
                $dh = opendir($OE_SITES_BASE);
                if (!$dh) {
                    die("Cannot read directory '$OE_SITES_BASE'.");
                }

                $siteslist = array();

                while (false !== ($sfname = readdir($dh))) {
                    if (substr($sfname, 0, 1) == '.') {
                        continue;
                    }

                    if ($sfname == 'CVS') {
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
                ksort($siteslist);

                $encount = 0;
                foreach ($siteslist as $sfname) {
                    $sitedir = "$OE_SITES_BASE/$sfname";
                    $errmsg = '';
                    ++$encount;
                    $bgcolor = "#" . (($encount & 1) ? "ddddff" : "ffdddd");

                    echo " <tr class='detail' bgcolor='$bgcolor'>\n";

                  // Access the site's database.
                    include "$sitedir/sqlconf.php";

                    if ($config) {
                        $dbh = mysqli_connect("$host", "$login", "$pass", $dbase, $port);
                        if (!$dbh) {
                            $errmsg = "MySQL connect failed";
                        }
                    }

                    echo "  <td>$sfname</td>\n";
                    echo "  <td>$dbase</td>\n";

                    if (!$config) {
                        echo "  <td colspan='3'><a href='setup.php?site=$sfname'>Needs setup, click here to run it</a></td>\n";
                    } elseif ($errmsg) {
                        echo "  <td colspan='3' style='color:red'>$errmsg</td>\n";
                    } else {
                        // Get site name for display.
                        $row = sqlQuery("SELECT gl_value FROM globals WHERE gl_name = 'openemr_name' LIMIT 1", $dbh);
                        $openemr_name = $row ? $row['gl_value'] : '';

                        // Get version indicators from the database.
                        $row = sqlQuery("SHOW TABLES LIKE 'version'", $dbh);
                        if (empty($row)) {
                            $openemr_version = 'Unknown';
                            $database_version = 0;
                        } else {
                            $row = sqlQuery("SELECT * FROM version LIMIT 1", $dbh);
                            $database_patch_txt = "";
                            if (!(empty($row['v_realpatch'])) && $row['v_realpatch'] != 0) {
                                $database_patch_txt = " (" . $row['v_realpatch'] .")";
                            }

                            $openemr_version = $row['v_major'] . "." . $row['v_minor'] . "." .
                            $row['v_patch'] . $row['v_tag'] . $database_patch_txt;
                            $database_version = 0 + $row['v_database'];
                            $database_acl = 0 + $row['v_acl'];
                            $database_patch = 0 + $row['v_realpatch'];
                        }

                        // Display relevant columns.
                        echo "  <td>$openemr_name</td>\n";
                        echo "  <td>$openemr_version</td>\n";
                        if ($v_database != $database_version) {
                            echo "  <td align='center'><a href='sql_upgrade.php?site=$sfname'>Upgrade Database</a></td>\n";
                        } elseif (($v_acl > $database_acl)) {
                            echo "  <td align='center'><a href='acl_upgrade.php?site=$sfname'>Upgrade Access Controls</a></td>\n";
                        } elseif (($v_realpatch != $database_patch)) {
                            echo "  <td align='center'><a href='sql_patch.php?site=$sfname'>Patch Database</a></td>\n";
                        } else {
                            echo "  <td align='center'><i class='fa fa-check fa-lg oe-text-green' aria-hidden='true' ></i></a></td>\n";
                        }
                        if (($v_database == $database_version) && ($v_acl <= $database_acl) && ($v_realpatch == $database_patch)) {
                            echo "  <td align='center'><a href='interface/login/login.php?site=$sfname' title =' Login to site $sfname' ><i class='fa fa-sign-in fa-lg' aria-hidden='true'></i></a></td>\n";
                        } else {
                            echo "  <td align='center'><i class='fa fa-ban fa-lg button-wait' aria-hidden='true'></i></td>\n";
                        }
                    }

                    echo " </tr>\n";

                    if ($config && $dbh !== false) {
                        mysqli_close($dbh);
                    }
                }
                ?>
                </table>
                <form method='post' action='setup.php'>
                     <button type='submit' name='form_submit' value='Add New Site'><b>Add New Site</b></button>
                </form>
            </div>
        </div>
    </div><!--end of container div-->
    <div class="row">
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content  oe-modal-content" style="height:700px">
                        <div class="modal-header clearfix">
                            <button type="button" class="close" data-dismiss="modal" aria-label=Close>
                            <span aria-hidden="true" style="color:#000000; font-size:1.5em;">×</span></button>
                        </div>
                        <div class="modal-body" style="height:80%;">
                            <iframe src="" id="targetiframe" style="height:100%; width:100%; overflow-x: hidden; border:none"
                            allowtransparency="true"></iframe>
                        </div>
                        <div class="modal-footer" style="margin-top:0px;">
                           <button class="btn btn-link btn-cancel oe-pull-away" data-dismiss="modal" type="button">Close</button>
                           <!--<button class="btn btn-default btn-print oe-pull-away" data-dismiss="modal" id="print-help-href" type="button">Print</button>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(function() {
                $('#help-href').click (function(){
                    document.getElementById('targetiframe').src = "Documentation/help_files/openemr_multisite_admin_help.php";
                })
            });
            $(function() {
                $('#print-help-href').click (function(){
                    $("#targetiframe").get(0).contentWindow.print();
                })
            });
            // Jquery draggable
            $('.modal-dialog').draggable({
                    handle: ".modal-header, .modal-footer"
            });
           $( ".modal-content" ).resizable({
                aspectRatio: true,
                minHeight: 300,
                minWidth: 300
            });
        </script>
</body>
</html>
