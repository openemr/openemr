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

use Symfony\Component\HTTPFoundation\Request;
use Symfony\Component\HTTPFoundation\Respone;
use OpenEMR\Pharmacy\Service\Import;

$loadPhar = new Import();
$request = Request::createFromGlobals();
$saved = $loadPhar->importPharmacies($request->request->get('city', ''), $request->request->get('state', ''));

$response = new Response($saved, Response::HTTP_OK, ['content-type' => 'application/json']);
$response->send();
