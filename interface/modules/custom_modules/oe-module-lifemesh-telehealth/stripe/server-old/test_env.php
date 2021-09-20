<?php

/*
 *
 * @package      OpenEMR
 * @link               https://www.open-emr.org
 *
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

$ignoreAuth = true;
// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;

require_once "../../../../../globals.php";
require_once "../../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$envar = $dotenv->load();

echo "<pre>";
//var_dump($dotenv);

$v = print_r($envar{"STRIPE_SECRET_KEY"}, true);

echo "end testing " . $v;
echo "<br>";
var_dump($_ENV);

