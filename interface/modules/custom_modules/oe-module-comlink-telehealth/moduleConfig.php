<?php

/**
 *
 *  package   OpenEMR
 *  link      http://www.open-emr.org
 *  author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  author    Jerry Padgett <sjpadgett@gmail.com>
 *  copyright Copyright (c )2021. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 *  license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

require_once dirname(__FILE__, 4) . '/globals.php';

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use Comlink\OpenEMR\Modules\TeleHealthModule\TelehealthGlobalConfig;

$module_config = 1;

if (!empty($_GET['setup']) ?? null) {
    /**
     * Example expected data structure for received data.
     * $args = ['ctsiVBData' => [
     * 'registrationUri' => 'https://sginsts.comlinktelehealth.io:28749/CTSIVB/',
     * 'videoApiUri' => 'https://sginsts.comlinktelehealth.io:22528',
     * 'ctsiOrgUid' => 'OPEN5xxxxx',
     * 'ctsiOrgPwd' => 'DD71C92B-xxxx-xx',
     * 'ctsiOrgId' => 'OPENEM2xxxx']];
     *
     * */
    if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $content = trim(file_get_contents("php://input"));
    $credentials = json_decode($content, true);
    $cryptoGen = new CryptoGen();
    $items[TelehealthGlobalConfig::COMLINK_VIDEO_REGISTRATION_API] = $credentials['registrationUri'] ?? '';
    $items[TelehealthGlobalConfig::COMLINK_VIDEO_TELEHEALTH_API] = $credentials['videoApiUri'] ?? '';
    $items[TelehealthGlobalConfig::COMLINK_VIDEO_API_USER_ID] = $credentials['ctsiOrgUid'] ?? '';
    $items[TelehealthGlobalConfig::COMLINK_VIDEO_API_USER_PASSWORD] = isset($credentials['ctsiOrgPwd']) ? $cryptoGen->encryptStandard(trim($credentials['ctsiOrgPwd'])) : '';
    $items[TelehealthGlobalConfig::COMLINK_VIDEO_TELEHEALTH_CMS_ID] = $credentials['ctsiOrgId'] ?? '';
    $items[TelehealthGlobalConfig::COMLINK_TELEHEALTH_PAYMENT_SUBSCRIPTION_ID] = $credentials['paypal_subscription_id'] ?? '';
    // Save to globals table.
    foreach ($items as $key => $credential) {
        sqlQuery(
            "INSERT INTO `globals` (`gl_name`,`gl_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `gl_name` = ?, `gl_value` = ?",
            array($key, $credential, $key, $credential)
        );
    }

    echo xlt("Credentials save is successful.");
    exit;
}
?>
<html>
<head>
    <script>
        // Process received data from origin.
        function receiveMessage(e) {
            let remote = "https://credentials.affordablecustomehr.com";
            // ensure we accept data of known origin.
            // TODO Remember! if application moves domain to change here!
            if (e.origin !== remote) {
                alert("Invalid source!");
                return false;
            }
            // fix twig single quote escaping of passed in object.
            let prepared = e.data.replace(/&quot;/ig, '"');
            let url = 'moduleConfig.php?setup=1&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
            fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    "Content-Type": "application/json"
                },
                body: prepared
            }).then((response) => {
                if (response.status !== 200) {
                    console.log('Save credentials failed. Status Code: ' + response.status);
                    return;
                }
                return response.text();
            }).then(response => {
                alert(response);
                // Back to landing page.
                window.location.replace("./moduleConfig.php");
            });
        }
        // Our posted message event from distant origin.
        window.onload = function () {
            window.addEventListener('message', receiveMessage);
        }
    </script>
    <title></title>
</head>
<body>
    <!-- Need a frame to work with cross-origin -->
    <iframe class="h-100 w-100" id="setup-frame" src="welcome.php" width="100%" height="100%" style="border: none;"></iframe>
</body>
</html>
