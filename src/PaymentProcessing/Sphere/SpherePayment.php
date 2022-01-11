<?php

/**
 * SpherePayment class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\PaymentProcessing\Sphere;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Services\UserService;

class SpherePayment
{
    /**
     * @var string
     */
    private $front;

    /**
     * @var int
     */
    private $patientIdCc;

    /**
     * @var string
     */
    private $serverSite;

    /**
     * @var string
     */
    private $mainUrl;

    /**
     * @var string
     */
    private $mainUrlRetail;

    /**
     * Constructor
     */
    public function __construct(string $front, int $patientIdCc)
    {
        // Set if front is 'clinic' (via clinic desk or via clinic phone) or 'patient' (via patient portal)
        $this->front = $front;

        // Set the patient pid
        $this->patientIdCc = $patientIdCc;

        // Set if in testing mode (or false for production mode)
        $testing = empty($GLOBALS['gateway_mode_production']);

        // Collect the correct trxcustid and trxcustid_licensekey and url
        $cryptoGen = new CryptoGen();
        if ($this->front == 'patient') {
            $frontSpecific = 'patient';
            $trxcustid = $cryptoGen->decryptStandard($GLOBALS['sphere_patientfront_trxcustid']);
            $trxcustidLicensekey = $cryptoGen->decryptStandard($GLOBALS['sphere_patientfront_trxcustid_licensekey']);
            if ($testing) {
                $url = Sphere::PATIENTFRONT_TESTING_URL;
            } else {
                $url = Sphere::PATIENTFRONT_PRODUCTION_URL;
            }
        } else { //$this->front == 'clinic'
            $frontSpecific = 'clinic-phone';
            $frontSpecificRetail = 'clinic-retail';
            $trxcustid = $cryptoGen->decryptStandard($GLOBALS['sphere_clinicfront_trxcustid']);
            $trxcustidLicensekey = $cryptoGen->decryptStandard($GLOBALS['sphere_clinicfront_trxcustid_licensekey']);
            $trxcustidRetail = $cryptoGen->decryptStandard($GLOBALS['sphere_clinicfront_retail_trxcustid']);
            $trxcustidRetailLicensekey = $cryptoGen->decryptStandard($GLOBALS['sphere_clinicfront_retail_trxcustid_licensekey']);
            if ($testing) {
                $url = Sphere::CLINICFRONT_TESTING_URL;
            } else {
                $url = Sphere::CLINICFRONT_PRODUCTION_URL;
            }
        }

        // Calculate the OpenEMR server
        $this->serverSite = $GLOBALS['site_addr_oath'] . $GLOBALS['web_root'];

        // Calculate the $mainUrl
        $this->mainUrl = $url . '?aggregators=' . urlencode(Sphere::AGGREGATOR_ID) . '&trxcustid=' . urlencode($trxcustid) . '&trxcustid_licensekey=' . urlencode($trxcustidLicensekey) . '&trxcustomfield[1]=' . urlencode($frontSpecific) . '&trxcustomfield[2]=' . urlencode($this->patientIdCc) . '&trxcustomfield[3]=' . urlencode(CsrfUtils::collectCsrfToken('sphere'));
        if ($this->front == 'clinic') {
            $this->mainUrlRetail = $url . '?aggregators=' . urlencode(Sphere::AGGREGATOR_ID) . '&trxcustid=' . urlencode($trxcustidRetail) . '&trxcustid_licensekey=' . urlencode($trxcustidRetailLicensekey) . '&trxcustomfield[1]=' . urlencode($frontSpecificRetail) . '&trxcustomfield[2]=' . urlencode($this->patientIdCc) . '&trxcustomfield[3]=' . urlencode(CsrfUtils::collectCsrfToken('sphere'));
        }

        if ($this->front == 'patient') {
            // Add specific parameters for patient front to the $mainUrl
            $this->mainUrl = $this->mainUrl . '&hide_ticket=y&cvc_help=y&avs=y&postal=y&fulladdress=y&show_email=y';
        } else { //$this->front == 'clinic'
            // Add specific parameters for clinic front (both phone and retail) to the $mainUrl
            $operatorEntry = (new UserService())->getCurrentlyLoggedInUser();
            $operatorName = $operatorEntry['fname'] . " " . $operatorEntry['lname'];
            $mainUrlEnd = '&hide_ticket=y&hide_trxoperator=y&trxoperator=' . urlencode($operatorName);
            $this->mainUrl = $this->mainUrl . $mainUrlEnd;
            $this->mainUrlRetail = $this->mainUrlRetail . $mainUrlEnd;
        }
    }

    /**
     * Return sphere code in javascript block.
     *
     * @return string
     */
    public function renderSphereJs(): string
    {
        if ($this->front == 'patient') {
            return '<script>' . $this->renderSphereJsPatientFront() . $this->renderSphereJsCore() . '</script>';
        } else { // $this->front == 'clinic'
            return '<script>' . $this->renderSphereJsClinicFront() . $this->renderSphereJsCore() . '</script>';
        }
    }

    /**
     * Return patient front specific sphere code in javascript block.
     *
     * @return string
     */
    private function renderSphereJsPatientFront(): string
    {
        return "
            function sphereSuccess(encData) {
                let oForm = document.forms['payment-form'];
                oForm.elements['mode'].value = 'Sphere';

                let inv_values = JSON.stringify(getFormObj('invoiceForm'));
                document.getElementById('invValues').value = inv_values;

                let hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'enc_data');
                hiddenInput.setAttribute('value', encData);
                oForm.appendChild(hiddenInput);

                // Submit payment to server
                fetch('./lib/paylib.php', {
                    method: 'POST',
                    body: new FormData(oForm)
                }).then(function(response) {
                    if (!response.ok) {
                        throw Error(response.statusText);
                    }
                    return response.text();
                }).then(function(data) {
                    if(data !== 'ok') {
                        throw data;
                    }
                    alert(chargeMsg);
                    window.location.reload(false);
                }).catch(function(error) {
                    alert(error)
                });
            }
            ";
    }

    /**
     * Return clinic front specific sphere code in javascript block.
     *
     * @return string
     */
    private function renderSphereJsClinicFront(): string
    {
        return "
            function sphereSuccess(transId) {
                document.getElementById('check_number').value = transId;
                alert(" . xlj('Successful payment') . ");
                document.querySelector(\"[name='form_save']\").click();
            }
            ";
    }

    /**
     * Return core sphere code in javascript block.
     *   Will employ the front and production status in the rendering.
     *
     * @return string
     */
    private function renderSphereJsCore(): string
    {
        return "
            function sphereNotSuccess(message) {
                alert(message);
            }
            function randomString(length, chars) {
                let result = '';
                for (let i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
                return result;
            }
            function paySphere(front) {
                let total = document.querySelector(\"[name='form_paytotal']\").value;
                let prepay = document.getElementById('form_prepayment').value;
                if (Number(total) < 1) {
                    if (Number(prepay) < 1) {
                        let error = " . xlj('Please enter a payment amount') . "
                        alert(error);
                        return false;
                    }
                    total = prepay;
                }
                document.getElementById('form_method').value = 'credit_card';
                // In case the javascript random generator breaks (ideally use the javascript value in case
                //  the user makes more than 1 payment from one screen, which would be very unusual to do).
                let backupTicket = " . js_escape(RandomGenUtils::createUniqueToken(12)) . ";
                let ticket = randomString(12, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
                if ((ticket == 0) || (ticket == '') || (ticket.length != 12)) {
                    error.log('Dynamic javascript ticket creation failed, so using backup ticket.');
                    ticket = backupTicket;
                }

                let responseUrl = " . js_escape($this->serverSite) . " + '/sphere/initial_response.php';
                let cancelUrl = " . js_escape($this->serverSite) . " + '/sphere/initial_response.php?cancel=cancel&ticket=' + encodeURIComponent(ticket) + '&front=' + encodeURIComponent(front) + '&patient_id_cc=' + " . js_escape($this->patientIdCc) . " + '&csrf_token=' + " . js_escape(CsrfUtils::collectCsrfToken('sphere')) .  ";
                let mainUrlEnd = '&amount=' + encodeURIComponent(total) + '&ticketno=' + encodeURIComponent(ticket) + '&response_url=' + encodeURIComponent(responseUrl) + '&is_redirect=y&show_cancelurl=' + encodeURIComponent(cancelUrl);
                let mainUrl = '';
                if ((front == 'patient') || (front == 'clinic-phone')) {
                    mainUrl = " . js_escape($this->mainUrl) . " + mainUrlEnd;
                } else { // front == 'clinic-retail'
                    mainUrl = " . js_escape($this->mainUrlRetail ?? '') . " + mainUrlEnd;
                }
                dlgopen(mainUrl, '_blank', 950, 650, '', 'Sphere Payment', {allowExternal: true});
            }
            ";
    }

    /**
     * Return sphere code in html block (basically is just a button).
     *
     * @return string
     */
    public static function renderSphereHtml(string $front): string
    {
        if ($front == 'patient') {
            return '<button type="button" class="btn btn-success btn-transmit mx-1" onclick="paySphere(\'patient\')">' . xlt("Credit Card Pay") . '</button>';
        } else { //$front == 'clinic'
            return '<button type="button" class="btn btn-success btn-transmit mx-1" onclick="paySphere(\'clinic-retail\')">' . xlt("Credit Card Pay in Person") . '</button>' .
                   '<button type="button" class="btn btn-success btn-transmit mx-1" onclick="paySphere(\'clinic-phone\')">' . xlt("Credit Card Pay over Phone") . '</button>';
        }
    }
}
