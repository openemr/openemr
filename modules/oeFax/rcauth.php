<?php
/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2018-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
$ignoreAuth = 1;
require_once(__DIR__ . "/../../interface/globals.php");

if (empty($_SESSION['url'])) {
    http_response_code(401);
    exit();
}

use OpenEMR\Common\Crypto\CryptoGen;
use RingCentral\SDK\SDK;

$url = $_SESSION['url'];
$callbackUrl = $_SESSION['redirect_uri'];

function processCode()
{
    $cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
    $credentials = file_get_contents($cacheDir . '/_credentials.php');
    $cryptoGen = new CryptoGen();
    $credentials = json_decode($cryptoGen->decryptStandard($credentials), true);
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
    $content = json_encode($platform->auth()->data(), JSON_PRETTY_PRINT);
    $cryptoGen = new CryptoGen();
    $content = $cryptoGen->encryptStandard($content);
    file_put_contents($file, $content);
}

if (isset($_GET['code'])) {
    processCode();
    exit();
}
?>
<script>
    var tokenUrl = '<?php echo $url; ?>';
    var redirectUrl = '<?php echo $callbackUrl; ?>';
    var config = {
        authUri: tokenUrl,
        redirectUri: redirectUrl,
    };
    var OAuthCode = function (config) {
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
                        location.reload();
                    }
                } catch (e) {
                    //console.log(e);
                }
            }, 300);
        }
    };
    var oauth = new OAuthCode(config);

    window.addEventListener("load", function () {
        if (tokenUrl) {
            oauth.loginPopup();
        }
    });
</script>
