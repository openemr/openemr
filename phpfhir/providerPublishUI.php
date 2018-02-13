<?php
// Temporary rest test interface until add a model
$ignoreAuth = true;
require_once("../interface/globals.php");
require 'vendor/autoload.php';

use oeFHIR\oeFHIRHttpClient;
use oeFHIR\oeFHIRResource;
use OpenEMR\Core\Header;

// @TODO add model to presist
//
//$pid = $_SESSION['pid'];
if (!isset($_SESSION['pid'])) {
    $pid = $_REQUEST['patient_id'];
    $_SESSION['pid'] = $pid;
} else {
    $pid = $_SESSION['pid'];
}

class clientController
{
    private $_request, $_response, $_query, $_post, $_server, $_cookies, $_session;
    protected $_currentAction, $_defaultModel;

    const ACTION_POSTFIX = 'Action';
    const ACTION_DEFAULT = 'indexAction';

    public function __construct()
    {
        $this->_request = &$_REQUEST;
        $this->_query = &$_GET;
        $this->_post = &$_POST;
        $this->_server = &$_SERVER;
        $this->_cookies = &$_COOKIE;
        $this->_session = &$_SESSION;
        $this->init();
    }

    public function init()
    {
        $this->dispatchActions();
        $this->render();
    }

    public function dispatchActions()
    {
        $action = $this->getQuery('action');
        if ($action && $action .= self::ACTION_POSTFIX) {
            if (method_exists($this, $action)) {
                $this->setResponse(
                    call_user_func(array($this, $action), array())
                );
            } else {
                $this->setHeader("HTTP/1.0 404 Not Found");
            }
        } else {
            $this->setResponse(
                call_user_func(array($this, self::ACTION_DEFAULT), array())
            );
        }

        return $this->_response;
    }

    public function render()
    {
        if ($this->_response) {
            if (is_scalar($this->_response)) {
                echo $this->_response;
            } else {
                throw new \Exception('Response content must be scalar');
            }

            exit;
        }
    }

    public function getRequest($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_request[$param]) ?
                $this->_request[$param] : $default;
        }

        return $this->_request;
    }

    public function getQuery($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_query[$param]) ?
                $this->_query[$param] : $default;
        }

        return $this->_query;
    }

    public function getPost($param = null, $default = null)
    {
        if ($param) {
            return isset($this->_post[$param]) ?
                $this->_post[$param] : $default;
        }

        return $this->_post;
    }

    public function indexAction()
    {
        return null;
    }

    public function setResponse($content)
    {
        $this->_response = $content;
    }

    public function setHeader($params)
    {
        if (!headers_sent()) {
            if (is_scalar($params)) {
                header($params);
            } else {
                foreach ($params as $key => $value) {
                    header(sprintf('%s: %s', $key, $value));
                }
            }
        }

        return $this;
    }

    // @TODO dev for other type id's - not sure how that works prob extensions.
    public function createAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = 'oe-' . $pid;
        $rs = new oeFHIRResource();
        $r = $rs->createPatientResource($pid);
        $pt = $client->sendResource($type, $id, $r);
        //$this->setHeader(array('Content-Type' => 'application/json')); // not scalar when set
        return $pt;
    }

    public function historyAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = 'oe-' . $pid;
        $pt = $client->requestResource($type, $id, 'history');
        return $pt;
    }

    public function readAction()
    {
        $pid = $this->getRequest(pid);
        $type = $this->getRequest(type);
        $client = new oeFHIRHttpClient();
        $id = 'oe-' . $pid;
        $pt = $client->requestResource($type, $id, ''); // gets latest version.
        return $pt;
    }
}

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
