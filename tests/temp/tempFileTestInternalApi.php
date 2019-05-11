<?php

require_once(dirname(__FILE__) . "/../../interface/globals.php");
?>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

    <script language="JavaScript">
        function testApi() {
            $.ajax({
                type: 'GET',
                url: '../../apis/api/facility',
                dataType: 'json',
                headers: {
                    'apicsrftoken': <?php echo js_escape($_SESSION['api_csrf_token']); ?>
                },
                success: function(thedata){
                    var thedataJSON = JSON.stringify(thedata);
                    $("#api").html(thedataJSON);
                },
                error:function(){
                }
            });
        }
        $(document).ready(function(){
            testApi();
        });
    </script>


</head>

<?php
// Looking into how to use the api locally via php calls (3 methods are shown below)


// CALL via ajax Rest
echo "<div id='api'></div>";
echo "<br><br>";

// CALL via route handler
use OpenEMR\Common\Http\HttpRestRouteHandler;

require_once(dirname(__FILE__) . "/../../_rest_config.php");
$gbl = RestConfig::GetInstance();
$gbl::setNotRestCall();
// return json
echo HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, '/api/facility', "GET", 'direct-json');
echo "<br><br>";
// return php array
echo print_r(HttpRestRouteHandler::dispatch($gbl::$ROUTE_MAP, '/api/facility', "GET", 'direct'));
echo "<br><br>";


// USE THE SERVICE WITHOUT REST CONTROLLER
use OpenEMR\Services\FacilityService;

echo json_encode((new FacilityService())->getAll());
echo "<br><br>";


// USE THE SERVICE WITH REST CONTROLLER
use OpenEMR\RestControllers\FacilityRestController;

echo json_encode((new FacilityRestController())->getAll());
echo "<br><br>";
?>
</html>
