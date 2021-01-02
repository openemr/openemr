<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Rx\Weno\Container;

$container = new Container();
$log_review = $container->getLogproperties();
$wenoProperties = $container->getTransmitproperties();
$logurlparam = $log_review->logReview();
$provider_info = $wenoProperties->getProviderEmail();

if ($logurlparam == 'error') {
    echo xlt("Cipher failure check encryption key");
    exit;
}

$url = "https://test.wenoexchange.com/en/EPCS/RxLog?useremail=";

$urlOut = $url . $provider_info['email'] . "&data=" . urlencode($logurlparam);
header("Location: " . $urlOut);
