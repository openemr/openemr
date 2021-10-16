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

require_once dirname(__FILE__, 5) . "/globals.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\LifeMesh\Container;

if (!CsrfUtils::verifyCsrfToken($_GET["token"], 'lifemesh')) {
    CsrfUtils::csrfNotVerified();
}

if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
    echo xlt('Not Authorized');
    exit;
}

function credentials(): string
{
    $getaccountsummary = new Container();
    $getcredentals = sqlQuery("select `username`, `password` from `lifemesh_account`");
    $password = $getaccountsummary->getDatabase();
    $username = $getcredentals['username'];
    $pass = $password->cryptoGen->decryptStandard($getcredentals['password']);
    return base64_encode($username . ':' . $pass);
}

function cancelSubscription()
{
    $getaccountsummary = new Container();
    $encryptedaccountinfo = credentials();
    $docancelation = $getaccountsummary->getAppDispatch();
    return $docancelation->cancelSubscription($encryptedaccountinfo, 'cancelSubscription');
}

function resetPassword()
{
    $getaccountsummary = new Container();
    $encryptedaccountinfo = credentials();
    $doreset = $getaccountsummary->getAppDispatch();
    return $doreset->resetPassword($encryptedaccountinfo, 'resetPassword');
}

if ($_GET['acct'] == 'reset') {
    echo text(resetPassword());
}

if ($_GET['acct'] == 'cancel') {
    echo text(cancelSubscription());
}

