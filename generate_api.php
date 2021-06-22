<?php
require("vendor/autoload.php");
$openapi = \OpenApi\Generator::scan(['_rest_routes.inc.php']);
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();
