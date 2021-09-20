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

require_once "../../../../globals.php";
require_once "../controller/Container.php";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\LifeMesh\Container;


if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$username = $_POST['username'];
$password = $_POST['password'];

$getcontainer = new Container();
$checkaccount = $getcontainer->getAppDispatch();
$url = 'accountCheck';
$accountisvalid = $checkaccount->apiRequest($username, $password, $url);

if ($accountisvalid) {
    $savecredentials = $getcontainer->getDatabase();
    $savecredentials->saveUserInformation($username, $password);
}

header('Location: accountsummary.php');




