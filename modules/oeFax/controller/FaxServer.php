<?php
/**
 * Fax Server SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
$ignoreAuth = 1;
require_once(__DIR__ . "/../../../interface/globals.php");

use OpenEMR\Common\Crypto\CryptoGen;

class FaxServer
{
    private $baseDir;
    private $crypto;
    private $authToken;

    public function __construct()
    {
        $this->baseDir = $GLOBALS['OE_SITE_DIR'] . DIRECTORY_SEPARATOR . "messageStore";
        $this->cacheDir = $GLOBALS['OE_SITE_DIR'] . '/documents/logs_and_misc/_cache';
        $this->serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
        $this->crypto = new CryptoGen();
        $this->getCredentials();
        $this->verifyRequest();
        $this->dispatchActions();
    }

    private function dispatchActions()
    {
        $action = $_GET['_FAX'];
        // allow only what we want
        $action .= 'Request';
        if ($action) {
            if (method_exists($this, $action)) {
                call_user_func(array($this, $action), array());
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(401);
        }

        exit;
    }

    private function getCredentials()
    {
        if (!file_exists($this->cacheDir . '/_credentials_twilio.php')) {
            http_response_code(404);
            exit;
        }
        $credentials = file_get_contents($this->cacheDir . '/_credentials_twilio.php');
        $credentials = json_decode($this->crypto->decryptStandard($credentials), true);
        $this->authToken = $credentials['password'];
        unset($credentials);

        return;
    }

    // verify actually from twilio
    private function verifyRequest()
    {
        $url = $this->serverUrl . $_SERVER['REQUEST_URI'];
        $me = $this->computeRequestSignature($url, $_POST);
        $them = $_SERVER["HTTP_X_TWILIO_SIGNATURE"];
        $agree = $me === $them;
        if ($agree) {
            return $agree;
        } else {
            error_log(errorLogEscape("Failed request verification me: " . $me . ' them: ' . $them));
            http_response_code(401);
            exit;
        }
    }

    private function computeRequestSignature($url, $data = array())
    {
        ksort($data);
        foreach ($data as $key => $value) {
            $url = $url . $key . $value;
        }
        // calculates the HMAC hash of the data with the key
        $hmac = hash_hmac("sha1", $url, $this->authToken, true);
        return base64_encode($hmac);
    }

    protected function faxCallbackRequest()
    {
        $file_path = $_POST['OriginalMediaUrl'];
        ['basename' => $basename, 'dirname' => $dirname] = pathinfo($file_path);
        $file = $this->baseDir . '/send/' . $basename;
        // they own it now so throw away.
        unlink($file);

        http_response_code(200);
        exit();
    }

    protected function receivedFaxRequest()
    {
        $dispose_uri = $GLOBALS['webroot'] . '/modules/oeFax/faxserver/receiveContent';
        $twimlResponse = new SimpleXMLElement("<Response></Response>");
        $receiveEl = $twimlResponse->addChild('Receive');
        $receiveEl->addAttribute('action', $dispose_uri);

        header('Content-type: text/xml');
        echo $twimlResponse->asXML();
        exit;
    }

    protected function receiveContentRequest()
    {
        // Throw away content. we'll manage on their server.
        $file = $_POST["MediaUrl"];
        header('Content-type: text/xml');
        http_response_code(200);
        echo '';
        exit();
    }
}
