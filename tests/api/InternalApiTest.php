<?php

/**
 * Testing script for the local/internal use of the api
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Enable this script via environment variable
if (!getenv('OPENEMR_ENABLE_INTERNAL_API_TEST')) {
    die('Set OPENEMR_ENABLE_INTERNAL_API_TEST=1 environment variable to enable this script');
}

$globalsBag = require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Http\HttpSessionFactory;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\Header;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\FHIR\R4\FHIRResource\FHIRBundle;
use OpenEMR\RestControllers\FHIR\Finder\FhirRouteFinder;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use OpenEMR\Services\FacilityService;
use OpenEMR\RestControllers\FacilityRestController;
use OpenEMR\RestControllers\Finder\StandardRouteFinder;

?>
<html>
<head>
    <?php Header::setupAssets('jquery'); ?>

    <script>
        function testAjaxApi() {
            $.ajax({
                type: 'GET',
                url: '../../apis/default/api/facility',
                dataType: 'json',
                headers: {
                    'apicsrftoken': <?php echo js_escape(CsrfUtils::collectCsrfToken('api')); ?>
                },
                success: function(thedata){
                    let thedataJSON = JSON.stringify(thedata);
                    $("#ajaxapi").html(thedataJSON);
                },
                error:function(){
                }
            });
        }

        function testFetchApi() {
            fetch('../../apis/default/api/facility', {
                credentials: 'same-origin',
                method: 'GET',
                headers: new Headers({
                    'apicsrftoken': <?php echo js_escape(CsrfUtils::collectCsrfToken('api')); ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                let dataJSON = JSON.stringify(data);
                document.getElementById('fetchapi').innerHTML = dataJSON;
            })
            .catch(error => console.error(error))
        }

        $(function () {
            testAjaxApi();
            testFetchApi();
        });
    </script>


</head>

<?php

// CALL the api via a local jquery ajax call
//  See above testAjaxApi() function for details.
echo "<b>local jquery ajax call:</b><br />";
echo "<div id='ajaxapi'></div>";
echo "<br /><br />";


// CALL the api via a local fetch call
//  See above testFetchApi() function for details.
echo "<b>local fetch call:</b><br />";
echo "<div id='fetchapi'></div>";
echo "<br /><br />";


// CALL the api via route handler
//  This allows same notation as the calls in the api (ie. '/api/facility'), but
//  is limited to get requests at this time.
$getParams = [];
try {
    $restRequest = HttpRestRequest::create('/api/facility', 'GET');
    $restRequest->setRequestUserRole("users");
    $sessionFactory = new HttpSessionFactory($restRequest, $globalsBag->get('webroot'), HttpSessionFactory::SESSION_TYPE_CORE);
    $sessionFactory->setUseExistingSessionBridge(true);
    $restRequest->setSession($sessionFactory->createSession());
    $getParams = $restRequest->getQueryParams();
    $kernel = new OEHttpKernel($globalsBag->get('kernel')->getEventDispatcher(), new ControllerResolver());
    $kernel->setSystemLogger(new SystemLogger());
    $dispatchHandler = new HttpRestRouteHandler($kernel);
    $routeFinder = new StandardRouteFinder($kernel);
    $routes = $routeFinder->find($restRequest);
    $dispatchRestRequest = $dispatchHandler->dispatch($routes, $restRequest);
    if (!$dispatchRestRequest->getAttribute("_controller")) {
        throw new \Exception("No controller found for the route.");
    }
    $controller = $dispatchRestRequest->getAttribute("_controller");
    $response = $controller();
    if (!$response instanceof \Psr\Http\Message\ResponseInterface) {
        throw new \Exception("Controller did not return a valid response.");
    }
    if ($response->getStatusCode() != 200) {
        throw new \Exception("Controller returned an error response with status code: " . $response->getStatusCode());
    }

    echo "<b>api via route handler call returning json:</b><br />";
    $contents = $response->getBody()->getContents();
    echo $contents;
} catch (\Exception $e) {
    echo "<b>api via route handler call returned error:</b><br />";
    echo "Error Message: " . $e->getMessage() . "<br />";
}
echo "<br /><br />";

// CALL the underlying service that is used by the api
echo "<b>service call:</b><br />";
echo json_encode((new FacilityService())->getAllFacility());
echo "<br /><br />";


// CALL the underlying controller that is used by the api
echo "<b>controller call:</b><br />";
$response = (new FacilityRestController())->getAll(HttpRestRequest::create('/api/facility', 'GET'));
if ($response->getStatusCode() != 200) {
    echo "Controller returned an error response with status code: " . $response->getStatusCode() . "<br />";
    echo "Error Message: " . $response->getReasonPhrase() . "<br />";
} else {
    echo $response->getBody()->getContents();
}
echo "<br /><br />";
?>
</html>
