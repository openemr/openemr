<?php

/**
 * Provider publish fhir UI
 * (Temporary rest test interface until add a model)
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../interface/globals.php");
require_once("./libs/controller/ClientAppController.php");

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
        function doPublish(e, req) {
            top.restoreSession();
            e.preventDefault();
            e.stopPropagation();
            let api = $("#apiCheck").is(":checked") ? 1 : 0;
            let wait = '<i class="fa fa-cog fa-spin fa-4x"></i>';
            let profile = getSelResource();
            if (profile === 'Encounter' && req === 'create') {
                req = req + 'EncounterAll';
            }
            let actionUrl = '?action=' + req;
            let id = pid; // eventually will be other live id's
            $("#dashboard").empty().html(wait);
            return $.post(actionUrl, {'type': profile, 'pid': pid, oeid: id, api: api}).done(function (data) {
                $("#dashboard").empty().html('<pre>' + data + '</pre>');
            }).fail(function (data) {
                $("#dashboard").empty().html('<pre>' + data.statusText + '</pre>');
            })
        };

        function getSelResource() {
            return $('#resource option:selected').val()
        }

    </script>
</head>
<body>
    <nav class="navbar navbar-expand-sm navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <?php echo xlt('oeFHIR'); ?>
            </a>
            <button type="button" class="navbar-toggler mr-auto" data-toggle="collapse"
                data-target="#nav-header-collapse">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="nav-header-collapse">
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
                    <li><a href="https://fhirtest.uhn.ca" rel="noopener" target="_blank"><?php echo xlt('Visit Test Server'); ?></a></li>
                </ul>
            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
    <div class="container-fluid main-container">
        <div class="row">
            <form>
                <div class="content form-group my-2">
                    <div class="input-group input-group-sm mx-1">
                        <label for="resource"><?php echo xlt('Resource (select one)') ?></label>
                        <select class="form-control mx-1" id="resource">
                            <option value="Patient" selected><?php echo xlt('Current Patient') ?></option>
                            <option value="Encounter"><?php echo xlt('All Encounters') ?></option>
                        </select>
                        <div class="custom-control custom-switch ml-2">
                            <input type="checkbox" class="custom-control-input" id="apiCheck" checked>
                            <label class="custom-control-label" for="apiCheck"><?php echo xlt('Use OpenEMR API'); ?></label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="row">
            <div class="col-2 bg-light ml-0">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item active"><a href="#"><?php echo xlt('Home'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" onclick="doPublish(event, 'create')" href="#"><?php echo xlt('Publish'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" onclick="doPublish(event, 'read')" href="#"><?php echo xlt('Read'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" onclick="doPublish(event, 'history')" href="#"><?php echo xlt('Get History'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" onclick="doPublish(event, 'search')" href="#"><?php echo xlt('Search'); ?></a></li>
                    <li class="nav-item"><a class="nav-link" href="#"></a></li>
                </ul>
            </div>

            <div class="col-10 content">
                <div class="card panel-default">
                    <div class="card-heading">
                        <?php echo xlt('Dashboard'); ?>
                    </div>
                    <div id="dashboard" class="card-body">
                    </div>
                </div>
            </div>
        </div>
        <footer class="float-left footer">
            <p class="col-md-12">
            <hr class="divider">
            </p>
        </footer>
    </div>

</body>
</html>
