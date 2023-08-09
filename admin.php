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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="public/assets/bootstrap/dist/css/bootstrap.min.css">
    <script src="public/assets/jquery/dist/jquery.min.js"></script>
    <script src="public/assets/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="public/assets/@fortawesome/fontawesome-free/css/all.min.css">
    <link rel="shortcut icon" href="public/images/favicon.ico" />
</head>
<body>
    <div class='container mt-3'>
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>OpenEMR Multi Site Administration</h2>
                    <a class="text-secondary" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href">
                        <i class="fa fa-question-circle fa-lg" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="Click to view Help"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <div class="table-responsive">
                    <table class='table table-striped text-center'>
                        <tr>
                            <th>Site ID</th>
                            <th>DB Name</th>
                            <th>Site Name</th>
                            <th>Version</th>
                            <th>Is Current</th>
                            <th>Log In</th>
                            <th>Patient Portal</th>
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

                            echo " <tr>\n";

                        // Access the site's database.
                            include "$sitedir/sqlconf.php";

                            if ($config) {
                                $dbh = mysqli_connect("$host", "$login", "$pass", $dbase, $port);
                                if (!$dbh) {
                                    $errmsg = "MySQL connect failed";
                                }
                            }

                            echo "  <td>" . htmlspecialchars($sfname, ENT_NOQUOTES) . "</td>\n";
                            echo "  <td>" . htmlspecialchars($dbase, ENT_NOQUOTES) . "</td>\n";

                            if (!$config) {
                                echo "  <td colspan='3'><a href='setup.php?site=" . htmlspecialchars(urlencode($sfname), ENT_QUOTES) . "' class='text-decoration-none'>Needs setup, click here to run it</a></td>\n";
                            } elseif ($errmsg) {
                                echo "  <td colspan='3' class='text-danger'>" . htmlspecialchars($errmsg, ENT_NOQUOTES) . "</td>\n";
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
                                        $database_patch_txt = " (" . $row['v_realpatch'] . ")";
                                    }

                                    $openemr_version = $row['v_major'] . "." . $row['v_minor'] . "." .
                                    $row['v_patch'] . $row['v_tag'] . $database_patch_txt;
                                    $database_version = 0 + $row['v_database'];
                                    $database_acl = 0 + $row['v_acl'];
                                    $database_patch = 0 + $row['v_realpatch'];
                                }

                                // Display relevant columns.
                                echo "  <td>" . htmlspecialchars($openemr_name, ENT_NOQUOTES) . "</td>\n";
                                echo "  <td>" . htmlspecialchars($openemr_version, ENT_NOQUOTES) . "</td>\n";
                                if ($v_database != $database_version) {
                                    echo "  <td><a href='sql_upgrade.php?site=" . htmlspecialchars(urlencode($sfname), ENT_QUOTES) . "' class='text-decoration-none'>Upgrade Database</a></td>\n";
                                } elseif (($v_acl > $database_acl)) {
                                    echo "  <td><a href='acl_upgrade.php?site=" . htmlspecialchars(urlencode($sfname), ENT_QUOTES) . "' class='text-decoration-none'>Upgrade Access Controls</a></td>\n";
                                } elseif (($v_realpatch != $database_patch)) {
                                    echo "  <td><a href='sql_patch.php?site=" . htmlspecialchars(urlencode($sfname), ENT_QUOTES) . "' class='text-decoration-none'>Patch Database</a></td>\n";
                                } else {
                                    echo "  <td><i class='fa fa-check fa-lg text-success' aria-hidden='true' ></i></a></td>\n";
                                }
                                if (($v_database == $database_version) && ($v_acl <= $database_acl) && ($v_realpatch == $database_patch)) {
                                    echo "  <td><a href='interface/login/login.php?site=" . htmlspecialchars(urlencode($sfname), ENT_QUOTES) . "' class='text-decoration-none'><i class='fa fa-sign-in-alt fa-lg' aria-hidden='true' data-toggle='tooltip' data-placement='top' title ='Login to site " . htmlspecialchars($sfname, ENT_QUOTES) . "'></i></a></td>\n";
                                    echo "  <td><a href='portal/index.php?site=" . htmlspecialchars(urlencode($sfname), ENT_QUOTES) . "' class='text-decoration-none'><i class='fa fa-sign-in-alt fa-lg' aria-hidden='true' data-toggle='tooltip' data-placement='top' title ='Login to site " . htmlspecialchars($sfname, ENT_QUOTES) . "'></i></a></td>\n";
                                } else {
                                    echo "  <td><i class='fa fa-ban fa-lg text-secondary' aria-hidden='true'></i></td>\n";
                                    echo "  <td><i class='fa fa-ban fa-lg text-secondary' aria-hidden='true'></i></td>\n";
                                }
                            }

                            echo " </tr>\n";

                            if ($config && $dbh !== false) {
                                mysqli_close($dbh);
                            }
                        }
                        ?>
                    </table>
                </div>
                <form method='post' action='setup.php'>
                    <button type='submit' class='btn btn-primary font-weight-bold' name='form_submit' value='Add New Site'>Add New Site</button>
                </form>
            </div>
        </div>
    </div><!--end of container div-->

    <div class="row">
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content" style="height:700px">
                    <div class="modal-header clearfix">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="color:var(--black); font-size:1.5em;">×</span>
                        </button>
                    </div>
                        <div class="modal-body" style="height:80%;">
                            <iframe src="" id="targetiframe" class="h-100 w-100" style="overflow-x: hidden; border:none"
                                allowtransparency="true"></iframe>
                        </div>
                    <div class="modal-footer mt-0">
                        <button class="btn btn-secondary" data-dismiss="modal" type="button">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function () {
            $('#help-href').click (function() {
                document.getElementById('targetiframe').src = "Documentation/help_files/openemr_multisite_admin_help.php";
            });
        });
        $(function () {
            $('#print-help-href').click (function(){
                $("#targetiframe").get(0).contentWindow.print();
            });
        });
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
        // Jquery draggable
        $(".modal-dialog").addClass('drag-action');
        $(".modal-content").addClass('resize-action');
    </script>
</body>
</html>
