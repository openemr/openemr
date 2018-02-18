<?php
/**
 * Provider publish fhir UI
 * (Temporary rest test interface until add a model)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

require_once("../interface/globals.php");
require_once("./libs/controller/ClientAppController.php");
require 'vendor/autoload.php';

use OpenEMR\Core\Header;

// kick off app endpoints controller
$clientApp = new clientController();

echo "<script>var pid='" . attr($pid) . "'</script>";
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(['opener']); ?>
    <script>
        $(document).ready(function () {

        });

        function doPublish(e, req) {
            top.restoreSession();
            e.preventDefault();
            e.stopPropagation();
            let wait = '<i class="fa fa-cog fa-spin fa-4x"></i>';
            let profile = getSelResource();
            if (profile === 'Encounter' && req === 'create') {
                req = req + 'EncounterAll';
            }
            let actionUrl = '?action=' + req;
            let id = pid; // eventually will be other live id's
            $("#dashboard").empty().html(wait);
            return $.post(actionUrl, {'type': profile, 'pid': pid, oeid: id}).done(function (data) {
                $("#dashboard").empty().html('<pre>' + data + '</pre>');
            });
        };

        function getSelResource() {
            return $('#resource option:selected').val()
        }

    </script>
</head>
<body>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#nav-header-collapse">
                <span class="sr-only"><?php echo xlt('Toggle'); ?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">
                <?php echo xlt('oeFHIR'); ?>
            </a>
        </div>
        <div class="collapse navbar-collapse" id="nav-header-collapse">
            <form class="navbar-form navbar-left" method="GET" role="search">
                <div class="form-group">
                    <input type="text" name="q" class="form-control" placeholder="<?php echo xla('Search'); ?>">
                </div>
                <button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-search"></i></button>
            </form>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown ">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <?php echo xlt('Activity'); ?>
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-header"><?php echo xlt('Profiles'); ?></li>
                        <li class=""><a href="#"><?php echo xlt('CCD'); ?></a></li>
                        <li class=""><a href="#"><?php echo xlt('Care Plan'); ?></a></li>
                        <li class=""><a href="#"><?php echo xlt('Episode'); ?></a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header"><?php echo xlt('Resources'); ?></li>
                        <li class=""><a href="#"><?php echo xlt('Patient'); ?></a></li>
                        <li class=""><a href="#"><?php echo xlt('Organization'); ?></a></li>
                        <li class="divider"></li>
                        <li><a href="#"><?php echo xlt('Server Login'); ?></a></li>
                    </ul>
                </li>
                <li><a href="https://fhirtest.uhn.ca" target="_blank"><?php echo xlt('Visit Test Server'); ?></a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
<div class="container-fluid main-container">
    <div class="row">
        <form>
            <div class="col-md-2 content form-group">
                <div class="input-group input-group-sm">
                    <label for="enc"><?php echo xlt('Resource (select one)') ?></label>
                    <select class="form-control" id="resource">
                        <option value="Patient" selected><?php echo xlt('Current Patient') ?></option>
                        <option value="Encounter"><?php echo xlt('All Encounters') ?></option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <div class="col-md-2 sidebar">
        <ul class="nav nav-pills nav-stacked">
            <li class="active"><a href="#"><?php echo xlt('Home'); ?></a></li>
            <li><a onclick="doPublish(event, 'create')" href="#"><?php echo xlt('Publish'); ?></a></li>
            <li><a onclick="doPublish(event, 'read')" href="#"><?php echo xlt('Read'); ?></a></li>
            <li><a onclick="doPublish(event, 'history')" href="#"><?php echo xlt('Get History'); ?></a></li>
            <li><a onclick="doPublish(event, 'search')" href="#"><?php echo xlt('Search'); ?></a></li>
            <li><a href="#"></a></li>
        </ul>
    </div>

    <div class="col-md-10 content">
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php echo xlt('Dashboard'); ?>
            </div>
            <div id="dashboard" class="panel-body">
            </div>
        </div>
    </div>
    <footer class="pull-left footer">
        <p class="col-md-12">
        <hr class="divider">
        </p>
    </footer>
</div>

</body>
</html>
