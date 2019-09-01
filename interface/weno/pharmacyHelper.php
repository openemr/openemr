<?php
/**
 * pharmacyHelper
 * This helper is to bridge information from the admin page ajax
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace pharmacyHelper;

require_once('../globals.php');


use OpenEMR\Pharmacy\Service\Import;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$loadPhar = new Import();

$request = Request::createFromGlobals();
$content = $request->getContent();

$input = explode("-", $content);
$city = $input[0];
$state = $input[1];

//file_put_contents("api.txt", $city . "  " . $state);

$saved = $loadPhar->importPharmacies($city, $state);

$response = new Response($saved, Response::HTTP_OK, ['content-type' => 'application/json']);
$response->send();
