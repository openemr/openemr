<?php
// Temporary rest test interface until add a model
//$ignoreAuth = true;
require_once("../interface/globals.php");
require_once("./libs/controller/ClientAppController.php");
require 'vendor/autoload.php';

use OpenEMR\Core\Header;

// kick off app endpoints controller
$clientApp = new clientController();

echo "<script>var pid='" . $pid . "'</script>";
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
            let profile = 'Patient'; // @TODO get profile from view
            let actionUrl = '?action=' + req;
            return $.post(actionUrl, {'type': profile, 'pid': pid}).done(function (data) {
                $("#dashboard").empty().html(data);
            });
        };

    </script>
</head>
<body>
<nav class="navbar navbar-default navbar-static-top">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#nav-header-collapse">
                <span class="sr-only">Toggle</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">
                oeFHIR
            </a>
        </div>
        <div class="collapse navbar-collapse" id="nav-header-collapse">
            <form class="navbar-form navbar-left" method="GET" role="search">
                <div class="form-group">
                    <input type="text" name="q" class="form-control" placeholder="Search">
                </div>
                <button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-search"></i></button>
            </form>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown ">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        Activity
                        <span class="caret"></span></a>
                    <ul class="dropdown-menu" role="menu">
                        <li class="dropdown-header">Profiles</li>
                        <li class=""><a href="#">CCD</a></li>
                        <li class=""><a href="#">Care Plan</a></li>
                        <li class=""><a href="#">Episode</a></li>
                        <li class="divider"></li>
                        <li class="dropdown-header">Resources</li>
                        <li class=""><a href="#">Patient</a></li>
                        <li class=""><a href="#">Organization</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Server Login</a></li>
                    </ul>
                </li>
                <li><a href="https://fhirtest.uhn.ca" target="_blank">Visit Test Server</a></li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
</nav>
<div class="container-fluid main-container">
    <div class="col-md-2 sidebar">
        <ul class="nav nav-pills nav-stacked">
            <li class="active"><a href="#">Home</a></li>
            <li><a onclick="doPublish(event, 'create')" href="#">Publish</a></li>
            <li><a onclick="doPublish(event, 'read')" href="#">Read</a></li>
            <li><a onclick="doPublish(event, 'history')" href="#">Get History</a></li>
            <li><a onclick="alert('Not Implemented');return false;" href="#">Search</a></li>
            <li><a href="#"></a></li>
        </ul>
    </div>
    <div class="col-md-10 content">
        <div class="panel panel-default">
            <div class="panel-heading">
                Dashboard
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
