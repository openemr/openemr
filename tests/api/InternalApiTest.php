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

require_once(__DIR__ . "/../../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

?>
<html>
<head>
    <?php Header::setupAssets('jquery'); ?>

    <script>
        function testAjaxApi() {
            $.ajax({
                type: 'GET',
                url: '../../apis/api/facility',
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
            fetch('../../apis/api/facility', {
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
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;

require_once(__DIR__ . "/../../_rest_config.php");
$gbl = RestConfig::GetInstance();
$gbl::setNotRestCall();
$restRequest = new HttpRestRequest($gbl, $_SERVER);
$restRequest->setRequestMethod("GET");
$restRequest->setRequestPath("/api/facility");
$restRequest->setIsLocalApi(true);
$restRequest->setApiType("oemr");
// below will return as json
echo "<b>api via route handler call returning json:</b><br />";
echo HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, $restRequest, 'direct-json');
echo "<br /><br />";

// below will return as php array
echo "<b>api via route handler call returning php array:</b><br />";
echo print_r(HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, $restRequest, 'direct'));
echo "<br /><br />";


// CALL the underlying service that is used by the api
use OpenEMR\Services\FacilityService;

echo "<b>service call:</b><br />";
echo json_encode((new FacilityService())->getAllFacility());
echo "<br /><br />";


// CALL the underlying controller that is used by the api
use OpenEMR\RestControllers\FacilityRestController;

echo "<b>controller call:</b><br />";
echo json_encode((new FacilityRestController())->getAll());
echo "<br /><br />";
?>
</html>
