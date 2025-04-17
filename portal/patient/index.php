<?php

/** @package    Patient Portal
 *
 * From phreeze package
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 *
 */

//require_once ("./../verify_session.php");
/* GlobalConfig object contains all configuration information for the app */
require_once("_global_config.php");
require_once("_app_config.php");
require_once("_machine_config.php"); // This include auth any framework calls

if (!GlobalConfig::$CONNECTION_SETTING) {
    throw new Exception('GlobalConfig::$CONNECTION_SETTING is not configured.  Are you missing _machine_config.php?');
}

/* require framework libs */
require_once("verysimple/Phreeze/Dispatcher.php");

// the global config is used for all dependency injection
$gc = GlobalConfig::GetInstance();

try {
    if (!empty($_SESSION['register'])) {
        // Need to bootstrap for registration
        $GLOBALS['bootstrap_register'] = true;
    } else {
        $GLOBALS['bootstrap_register'] = false;
    }
    if (isset($_SESSION['pid']) && (isset($_SESSION['patient_portal_onsite_two']))) {
        // Need to bootstrap all requests to only allow the pid in $_SESSION['pid']
        //  and to only allow access to api calls applicable to that pid (or patientId).
        // Also need to collect the id of the patient to verify the correct id is used
        //  in the uri check in GenericRouter.php .
        $GLOBALS['bootstrap_pid'] = $_SESSION['pid'];
        $sqlCollectPatientId = sqlQuery("SELECT `id` FROM `patient_data` WHERE `pid` = ?", [$GLOBALS['bootstrap_pid']]);
        $GLOBALS['bootstrap_uri_id'] = $sqlCollectPatientId['id'];
        if (
            (!empty($_POST['pid']) && ($_POST['pid'] != $GLOBALS['bootstrap_pid'])) ||
            (!empty($_GET['pid']) && ($_GET['pid'] != $GLOBALS['bootstrap_pid'])) ||
            (!empty($_REQUEST['pid']) && ($_REQUEST['pid'] != $GLOBALS['bootstrap_pid'])) ||
            (!empty($_POST['patientId']) && ($_POST['patientId'] != $GLOBALS['bootstrap_pid'])) ||
            (!empty($_GET['patientId']) && ($_GET['patientId'] != $GLOBALS['bootstrap_pid'])) ||
            (!empty($_REQUEST['patientId']) && ($_REQUEST['patientId'] != $GLOBALS['bootstrap_pid']))
        ) {
            // Unauthorized use
            $error = 'Unauthorized';
            throw new Exception($error);
        }
    }
    Dispatcher::Dispatch(
        $gc->GetPhreezer(),
        $gc->GetRenderEngine(),
        '',
        $gc->GetContext(),
        $gc->GetRouter()
    );
} catch (exception $ex) {
    // This is the global error handler which will be called in the event of
    // uncaught errors.  If the endpoint appears to be an API request then
    // render it as JSON, otherwise attempt to render a friendly HTML page

    $url = RequestUtil::GetCurrentURL();
    $isApiRequest = (strpos($url, 'api/') !== false);

    if ($isApiRequest) {
        $result = new stdClass();
        $result->success = false;
        $result->message = $ex->getMessage();
        $result->data = $ex->getTraceAsString();

        @header('HTTP/1.1 401 Unauthorized');
        echo json_encode($result);
    } else {
        $gc->GetRenderEngine()->assign("message", $ex->getMessage());
        $gc->GetRenderEngine()->assign("stacktrace", $ex->getTraceAsString());
        $gc->GetRenderEngine()->assign("code", $ex->getCode());

        try {
            $gc->GetRenderEngine()->display("DefaultErrorFatal.tpl");
        } catch (Exception $ex2) {
            // this means there is an error with the template, in which case we can't display it nicely
            echo "<style>* { font-family: verdana, arial, helvetica, sans-serif; }</style>\n";
            echo "<h1>Fatal Error:</h1>\n";
            echo '<h3>' . htmlentities($ex->getMessage()) . "</h3>\n";
            echo "<h4>Original Stack Trace:</h4>\n";
            echo '<textarea wrap="off" style="height: 200px; width: 100%;">' . htmlentities($ex->getTraceAsString()) . '</textarea>';
            echo "<h4>In addition to the above error, the default error template could not be displayed:</h4>\n";
            echo '<textarea wrap="off" style="height: 200px; width: 100%;">' . htmlentities($ex2->getMessage()) . "\n\n" . htmlentities($ex2->getTraceAsString()) . '</textarea>';
        }
    }
}
