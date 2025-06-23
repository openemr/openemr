<?php

/**
 * @package    OpenEMR
 * @link       http://www.open-emr.org
 * @author     Sherwin Gaddis <sherwingaddis@gmail.com>
 * @author     Jerry Padgett <sjpadgett@gmail.com>
 * @copyright  Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright  Copyright (c) 2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license    https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__DIR__, 4) . "/globals.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Core\Header;
use OpenEMR\Modules\WenoModule\Services\LogProperties;
use OpenEMR\Modules\WenoModule\Services\TransmitProperties;
use OpenEMR\Modules\WenoModule\Services\WenoLogService;

/*
 * access control is on Weno side based on the user login
 */
if (!AclMain::aclCheckCore('patients', 'rx')) {
    echo TransmitProperties::styleErrors(xlt('Prescriptions Review Not Authorized'));
    exit;
}

$log_properties = new LogProperties();
$logurlparam = $log_properties->logReview();
$provider_info = $log_properties->getProviderEmail();

if ($logurlparam == 'error') {
    echo TransmitProperties::styleErrors(xlt("Cipher failure check encryption key"));
    exit;
}

$url = "https://online.wenoexchange.com/en/EPCS/RxLog?useremail=";

$urlOut = $url . urlencode($provider_info['email'] ?? '') . "&data=" . urlencode($logurlparam);
$wenoLog = new WenoLogService();
$wenoLog->insertWenoLog("Online Weno RxLog", "Requested", $urlOut);

?>

<?php Header::setupHeader(); ?>
<title><?php echo xlt("Weno RxLog") ?></title>
<div class="mt-3 container-xl">
    <iframe id="wenoIframe-rx"
        title="Weno Rx"
        width="100%"
        height="100%"
        src="<?php echo $urlOut; ?>">
    </iframe>
</div>
