<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 */

$ignoreAuth = 1;
$sessionAllowWrite = true;
require_once(__DIR__ . "/../../../globals.php");
use OpenEMR\Common\Crypto\CryptoGen;
use RingCentral\SDK\SDK;
$url = $_SESSION['url'];
$callbackUrl = $_SESSION['redirect_uri'];
function processCode()
{
    $vendor = '_ringcentral';
    $cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
    $authUser = 0;
    try {
        $credentialsRow = sqlQuery("SELECT * FROM `module_faxsms_credentials` WHERE `auth_user` = ? AND `vendor` = ?", array($authUser, $vendor));
        if (!$credentialsRow) {
            throw new Exception("Applications credentials were not found. Please setup account.");
        }

        $credentials = $credentialsRow['credentials'];
        $cryptoGen = new CryptoGen();
        $credentials = json_decode($cryptoGen->decryptStandard($credentials), true, 512, JSON_THROW_ON_ERROR);
        $serverUrl = !$credentials['production'] ? "https://platform.devtest.ringcentral.com" : "https://platform.ringcentral.com";
        $callbackUrl = $credentials['redirect_url'];
        $rcsdk = new SDK($credentials['appKey'], $credentials['appSecret'], $serverUrl, 'OpenEMR', '1.0.0');
        $platform = $rcsdk->platform();
        $qs = $platform->parseAuthRedirectUrl($_SERVER['QUERY_STRING']);
        $qs["redirectUri"] = $callbackUrl;
    // Log in
        $apiResponse = $platform->login($qs);
        $_SESSION['sessionAccessToken'] = $apiResponse->text();
    // Archive authentication data for future reauths.
        $file = $cacheDir . DIRECTORY_SEPARATOR . 'platform.json';
        $content = json_encode($platform->auth()->data(), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $encryptedContent = $cryptoGen->encryptStandard($content);
        file_put_contents($file, $encryptedContent);
    } catch (Exception $e) {
        echo xlt($e->getMessage());
        die('Credential Error');
    }
}

if (isset($_GET['code'])) {
    processCode();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OAuth Login</title>
</head>
<body>
    <div class="container">Test Test test test</div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            alert("here");
            const tokenUrl = '<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>';
            const redirectUrl = '<?php echo htmlspecialchars($callbackUrl, ENT_QUOTES, 'UTF-8'); ?>';

            if (tokenUrl) {
                top.restoreSession();
                const oauth = new OAuthCode({
                    authUri: tokenUrl,
                    redirectUri: redirectUrl,
                });
                oauth.loginPopup();
            }
        });

        class OAuthCode {
            constructor(config) {
                this.config = config;
            }

            loginPopup() {
                console.log("URL: " + this.config.authUri);
                this.loginPopupUri(this.config.authUri, this.config.redirectUri);
            }

            loginPopupUri(authUri, redirectUri) {
                const win = window.open(authUri, 'auth2F', 'width=800, height=600');
                const pollOAuth = window.setInterval(() => {
                    if (win.closed) {
                        window.clearInterval(pollOAuth);
                    }
                    try {
                        if (win.document.URL.indexOf(redirectUri) !== -1) {
                            window.clearInterval(pollOAuth);
                            win.close();
                        }
                    } catch (e) {
                        console.log(e);
                    }
                }, 300);
            }
        }
    </script>
</body>
</html>
