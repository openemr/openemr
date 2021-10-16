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

require_once dirname(__FILE__, 5) .  "/globals.php";
require_once dirname(__FILE__, 2) . "/controller/Container.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Modules\LifeMesh\Database;

if (!CsrfUtils::verifyCsrfToken($_GET["token"], 'lifemesh')) {
    CsrfUtils::csrfNotVerified();
}

if (!AclMain::aclCheckCore('admin', 'manage_modules')) {
    echo xlt('Not Authorized');
    exit;
}

echo text((new Database())->removeAccountInfo());
