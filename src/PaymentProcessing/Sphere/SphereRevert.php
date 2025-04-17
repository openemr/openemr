<?php

/**
 * SphereRevert class.
 *  Will handle void and credits
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PaymentProcessing\Sphere;

use Exception;
use GuzzleHttp\Client;
use OpenEMR\Common\Auth\AuthGlobal;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;

class SphereRevert
{
    /**
     * @var string
     */
    private $front;

    /**
     * @var string
     */
    private $custid;

    /**
     * @var string
     */
    private $custpass;

    /**
     * @var AuthGlobal
     */
    private $authGlobalPin;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $returnUrl;

    /**
     * @var CryptoGen
     */
    private $cryptoGen;

    /**
     * @var SystemLogger
     */
    private $logger;

    public function __construct(string $front)
    {
        $this->logger = new SystemLogger();

        if (($front != 'patient') && ($front != 'clinic-phone') && ($front != 'clinic-retail')) {
            $this->logger->error("SphereRevert getToken front needs to be patient or clinic-phone or clinic-retail. Exiting.");
            exit;
        }
        $this->front = $front;

        $this->authGlobalPin = new AuthGlobal('sphere_credit_void_confirm_pin');

        $this->cryptoGen = new CryptoGen();
        if ($front == 'patient') {
            $this->custid = $this->cryptoGen->decryptStandard($GLOBALS['sphere_patientfront_trxcustid']);
            $this->custpass = $this->cryptoGen->decryptStandard($GLOBALS['sphere_ecomm_tc_link_pass']);
        } elseif ($front == 'clinic-retail') {
            $this->custid = $this->cryptoGen->decryptStandard($GLOBALS['sphere_clinicfront_retail_trxcustid']);
            $this->custpass = $this->cryptoGen->decryptStandard($GLOBALS['sphere_retail_tc_link_pass']);
        } else { // $front == 'clinic-phone'
            $this->custid = $this->cryptoGen->decryptStandard($GLOBALS['sphere_clinicfront_trxcustid']);
            $this->custpass = $this->cryptoGen->decryptStandard($GLOBALS['sphere_moto_tc_link_pass']);
        }

        $this->client = new Client(['base_uri' => Sphere::TRUSTEE_API_URL]);

        // Calculate the OpenEMR server returnUrl
        $this->returnUrl = $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'] . '/sphere/initial_response.php';
    }

    /**
     * Get token
     * @throws Exception
     */
    public function getToken(string $action, string $transid, string $confirmPinInput, string $uuidTx): string
    {
        if (($action != 'void') && ($action != 'credit')) {
            $this->logger->error("SphereRevert getToken action needs to be void or credit. Exiting.");
            exit;
        }

        // Note added the sphere_credit_void_confirm_pin in OpenEMR so can not initiate a credit
        //  or void in OpenEMR without knowing this value (ie. the user will enter it in when
        //  request the credit/void, which is then compared to the value (an encrypted hash) set
        //  in globals).
        if (!$this->authGlobalPin->globalVerify($confirmPinInput)) {
            throw new Exception(xl('Incorrect confirmation PIN'));
        }

        // Note that the returnurl needs to match the returnUrl that is created in processSphereRevert() js function, so
        //  if modify this, also need to modify there
        $response = $this->client->request('POST', 'token.php', [
            'form_params' => [
                'aggregators' => 1,
                'aggregator1' => Sphere::AGGREGATOR_ID,
                'custid' => $this->custid,
                'password' => $this->custpass,
                'returnurl' => $this->returnUrl . "?action=" . urlencode($action) . "&front=" . urlencode($this->front) . "&uuid_tx=" . urlencode($uuidTx) . "&revert=1&csrf_token=" . urlencode(CsrfUtils::collectCsrfToken("sphere_revert")),
                'action' => $action,
                'transid' => $transid
            ]
        ]);
        $token = $response->getBody();

        // for testing of token, need to trim off the = at end if it exists
        $trimmedToken = trim($token, '=');
        if ((strlen($trimmedToken) == 43) && (ctype_alnum($trimmedToken))) {
            // success, so return token
            return $token;
        } else {
            // not successful, so return error message
            throw new Exception($token);
        }
    }

    /**
     * Check querystring hash to ensure the return url from Sphere is authentic
     */
    public function checkQuerystringHash(string $hash, string $querystring): bool
    {
        if (empty($hash) || empty($querystring)) {
            return false;
        }

        //  (remove hash key/value at end of querystring before calculating hash)
        $processQuerystring = substr($querystring, 0, strpos($querystring, "&hash="));
        if (hash_equals($hash, hash_hmac('sha1', $processQuerystring, $this->custpass))) {
            return true;
        }

        return false;
    }

    /**
     * Complete transaction
     */
    public function completeTransaction(string $token): array
    {
        $response = $this->client->request('POST', 'complete.php', [
            'form_params' => [
                'token' => $token,
                'password' => $this->custpass
            ]
        ]);

        $complete = $response->getBody();

        $data = [];
        $lines = explode("\n", $complete);
        foreach ($lines as $line) {
            if (!empty($line)) {
                list($param, $val) = explode('=', $line, 2);
                $data[$param] = $val;
            }
        }
        return $data;
    }

    /**
     * Return revert sphere code in javascript block.
     *
     * @return string
     */
    public static function renderRevertSphereJs(): string
    {
        return '
            function revertSphere(action, front, transId, uuidTx) {
                dlgopen("", "", 300, 250, false, xl("Enter PIN code"), {
                    buttons: [{
                        text: xl("Proceed"),
                        close: true,
                        style: "btn-sm btn-primary",
                        click: doRevertSphere,
                    }, {
                        text: xl("Cancel"),
                        close: true,
                        style: "btn-sm btn-secondary"
                    }],
                    type: "Confirm",
                    html: \'<div class="input-group"> \
                                <input type="password" id="pinCode" class="form-control" placeholder="\' + jsAttr(xl(\'PIN Code\')) + \'"> \
                                <input type="hidden" id="actionRevert" value="\' + jsAttr(action) + \'"> \
                                <input type="hidden" id="frontRevert" value="\' + jsAttr(front) + \'"> \
                                <input type="hidden" id="transIdRevert" value="\' + jsAttr(transId) + \'"> \
                                <input type="hidden" id="uuidTx" value="\' + jsAttr(uuidTx) + \'"> \
                           </div>\'
                });
            }

            const doRevertSphere = function() {
                let pinCode = document.getElementById("pinCode").value;
                document.getElementById("pinCode").value = "";
                let action = document.getElementById("actionRevert").value;
                document.getElementById("actionRevert").value = "";
                let front = document.getElementById("frontRevert").value;
                document.getElementById("frontRevert").value = "";
                let transId = document.getElementById("transIdRevert").value;
                document.getElementById("transIdRevert").value = "";
                let uuidTx = document.getElementById("uuidTx").value;
                document.getElementById("uuidTx").value = "";

                if ((pinCode == "") || (action == "") || (front == "") || (transId == "") || (uuidTx == "")) {
                    alert(' . xlj("Missing information, so aborting credit/void.") . ');
                    return;
                }

                let request = new FormData;
                request.append("pin_code", pinCode);
                request.append("action", action);
                request.append("front", front);
                request.append("trans_id", transId);
                request.append("uuid_tx", uuidTx);
                request.append("csrf_token", ' . js_escape(CsrfUtils::collectCsrfToken("sphere_revert_token")) . ');

                top.restoreSession();
                fetch(top.webroot_url + "/sphere/token.php", {
                    method: "POST",
                    credentials: "same-origin",
                    body: request
                }).then((response) => {
                    if (response.status !== 200) {
                        console.log("Token request failed. Status Code: " + response.status);
                        return;
                    }
                    return response.json();
                }).then((data) => {
                    if (data["success"]) {
                        processSphereRevert(data["success"], front, action, transId, uuidTx);
                    } else if (data["error"]) {
                        alert(' . xlj("Error") . ' + ": " + data["error"]);
                        document.getElementById("form_refresh").value = "true";
                        document.getElementById("theform").submit();
                    } else {
                        alert(' . xlj("Unknown Error") . ');
                    }
                }).catch(function(error) {
                    console.log("Request failed", error);
                });
            }

            function processSphereRevert(token, front, action, transId, uuidTx) {
                // Note that the returnUrl needs to match the returnurl that is created in getToken() function, so
                //  if modify this, also need to modify there
                let returnUrl = ' . js_escape($GLOBALS["site_addr_oath"] . $GLOBALS["web_root"]) . ' + "/sphere/initial_response.php?action=" + encodeURIComponent(action) + "&front=" + encodeURIComponent(front) + "&uuid_tx=" + encodeURIComponent(uuidTx) + "&revert=1&csrf_token=" + ' . js_url(CsrfUtils::collectCsrfToken("sphere_revert")) . ';
                let mainUrl = ' . js_escape(Sphere::TRUSTEE_API_URL) . ' + "payment.php?aggregators=1&aggregator1=" + ' . js_escape(Sphere::AGGREGATOR_ID) . ' + "&transid=" + encodeURIComponent(transId) + "&token=" + encodeURIComponent(token) + "&action=" + encodeURIComponent(action) + "&returnurl=" + encodeURIComponent(returnUrl);
                dlgopen(mainUrl, "_blank", 950, 650, "", "Sphere " + action, {allowExternal: true});
            }

            function sphereRevertSuccess(message) {
                alert(message);
                document.getElementById("form_refresh").value = "true";
                document.getElementById("theform").submit();
            }

            function sphereRevertNotSuccess(message) {
                alert(message);
                document.getElementById("form_refresh").value = "true";
                document.getElementById("theform").submit();
            }
            ';
    }

    /**
     * Return sphere button for void.
     */
    public static function renderSphereVoidButton(string $front, string $transactionId, string $uuid): string
    {
        return '<button type="button" class="btn btn-sm btn-danger ml-1" onclick="revertSphere(\'void\', ' . attr_js($front) . ', ' . attr_js($transactionId) . ', ' . attr_js($uuid) . ')">' . xlt("Void Charge") . '</button>';
    }

    /**
     * Return sphere button for credit.
     */
    public static function renderSphereCreditButton(string $front, string $transactionId, string $uuid): string
    {
        return '<button type="button" class="btn btn-sm btn-danger ml-1" onclick="revertSphere(\'credit\', ' . attr_js($front) . ', ' . attr_js($transactionId) . ', ' . attr_js($uuid) . ')">' . xlt("Credit Charge") . '</button>';
    }
}
