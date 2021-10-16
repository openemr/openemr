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

require_once dirname(__FILE__, 6) . "/globals.php";
require_once dirname(__FILE__, 3) . "/controller/Container.php";

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

$createCheckout = new Container();

$email = $_POST['email'];

$checkout_session = $createCheckout->getAppDispatch()->getStripeUrl('createCheckoutSessionUrl', $email);

$checkout_session_url = json_decode($checkout_session);
$url = get_object_vars($checkout_session_url);

header("HTTP/1.1 303 See Other");
header("Location: " . $url['checkout_url'] );
