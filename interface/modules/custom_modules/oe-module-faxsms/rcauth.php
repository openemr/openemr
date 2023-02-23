<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth = 1;
$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");

use OpenEMR\Common\Crypto\CryptoGen;
use RingCentral\SDK\SDK;

$url = $_SESSION['url'];
$callbackUrl = $_SESSION['redirect_uri'];

function processCode(): void
{
    $vendor = '_ringcentral';
    $cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
    $authUser = 0;
    $credentials = sqlQuery("SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", array($authUser, $vendor))['credentials'];
    if (empty($credentials)) {
        echo xlt("Applications credentials were not found. Please setup account.");
        die('Credential Error');
    }
    $cryptoGen = new CryptoGen();
    $credentials = json_decode($cryptoGen->decryptStandard($credentials), true, 512, JSON_THROW_ON_ERROR);
    $serverUrl = !$credentials['production'] ? "https://platform.devtest.ringcentral.com" : "https://platform.ringcentral.com";

    $callbackUrl = $credentials['redirect_url'];
    $rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $serverUrl, 'OpenEMR', '1.0.0');
    $platform = $rcsdk->platform();

    $qs = $platform->parseAuthRedirectUrl($_SERVER['QUERY_STRING']);
    $qs["redirectUri"] = $callbackUrl;

    // log in
    $apiResponse = $platform->login($qs);
    $_SESSION['sessionAccessToken'] = $apiResponse->text();

    // archive authentication data for future reauths.
    $file = $cacheDir . DIRECTORY_SEPARATOR . 'platform.json';
    $content = json_encode($platform->auth()->data(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    $cryptoGen = new CryptoGen();
    $content = $cryptoGen->encryptStandard($content);
    file_put_contents($file, $content);
}

if (isset($_GET['code'])) {
    processCode();
    exit;
}
?>
<script>
    const tokenUrl = '<?php echo $url; ?>';
    const redirectUrl = '<?php echo $callbackUrl; ?>';
    const config = {
        authUri: tokenUrl,
        redirectUri: redirectUrl,
    };
    const OAuthCode = function (config) {
        this.config = config;
        this.loginPopup = function () {
            console.log("URL: " + tokenUrl);
            this.loginPopupUri(this.config['authUri'], this.config['redirectUri']);
        };
        this.loginPopupUri = function (authUri, redirectUrl) {
            win = window.open(authUri, 'auth2F', 'width=800, height=600');
            var pollOAuth = window.setInterval(function () {
                if (win.closed) {
                    window.clearInterval(pollOAuth);
                }
                try {
                    console.log(win.document.URL);
                    if (win.document.URL.indexOf(redirectUrl) !== -1) {
                        window.clearInterval(pollOAuth);
                        win.close();
                    }
                } catch (e) {
                    //console.log(e);
                }
            }, 300);
        }
    };
    const oauth = new OAuthCode(config);

    window.addEventListener("load", function () {
        if (tokenUrl) {
            top.restoreSession();
            oauth.loginPopup();
        }
    });
</script>
