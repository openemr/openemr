<?php

/*
 *
 * @package     OpenEMR Telehealth Module
 * @link        https://lifemesh.ai/telehealth/
 *
 * @author      Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright   Copyright (c) 2021 Lifemesh Corp <telehealth@lifemesh.ai>
 * @license     GNU General Public License 3
 *
 */

require_once "../../../../globals.php";
require_once "../controller/Container.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\LifeMesh\Container;

if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token"], 'lifemesh')) {
    CsrfUtils::csrfNotVerified();
}

if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
    echo xlt('Not Authorized');
    exit;
}

$username = $_POST['username'];
$password = $_POST['password'];

$getcontainer = new Container();
$checkaccount = $getcontainer->getAppDispatch();
$url = 'accountCheck';
$accountisvalid = $checkaccount->apiRequest($username, $password, $url);

if (($checkaccount->getStatus() === 200 && $accountisvalid === true) ||
    ($checkaccount->getStatus() === 261 && $accountisvalid === false)) {
    // Pass when valid with active subscription (status is 200 and accountisvalid is true) or
    //  without active subscription (status is 261 and accountisvalid is false)
    $savecredentials = $getcontainer->getDatabase();
    $savecredentials->saveUserInformation($username, $password);
} else {
    echo text($checkaccount->getStatusMessage());
    exit;
}

header('Location: accountsummary.php');




