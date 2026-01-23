<?php

/**
 * RainforestPayment class.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Firehed
 * @copyright Copyright (c) 2026 TBD <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\RainforestPayment;

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Modules\RainforestPayment\Rainforest\Rainforest;

class RainforestPayment
{
    /**
     * @var OEGlobalsBag
     */
    private $globalsBag;

    /**
     * Constructor
     */
    public function __construct(private readonly string $front, private readonly int $patientIdCc)
    {
        $this->globalsBag = OEGlobalsBag::getInstance();
    }

    /**
     * Return rainforest code in javascript block.
     *
     * @return string
     */
    public function renderRainforestJs(): string
    {
        if ($this->front == 'patient') {
            return '<script>' . $this->renderRainforestJsPatientFront() . '</script>';
        } else { // $this->front == 'clinic'
            return '<script>' . $this->renderRainforestJsClinicFront() . '</script>';
        }
    }

    /**
     * Return patient front specific rainforest code in javascript block.
     *
     * @return string
     */
    private function renderRainforestJsPatientFront(): string
    {
        $webroot = $this->globalsBag->getString('webroot');
        $modulePath = Bootstrap::MODULE_INSTALLATION_PATH;
        
        return "
            function rainforestSuccess(payinId) {
                let oForm = document.forms['payment-form'] || document.forms['invoiceForm'];
                if (!oForm) {
                    console.error('Payment form not found');
                    return;
                }

                let inv_values = JSON.stringify(getFormObj('invoiceForm'));
                let hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'payin_id');
                hiddenInput.setAttribute('value', payinId);
                oForm.appendChild(hiddenInput);

                let invValuesInput = document.createElement('input');
                invValuesInput.setAttribute('type', 'hidden');
                invValuesInput.setAttribute('name', 'invValues');
                invValuesInput.setAttribute('value', inv_values);
                oForm.appendChild(invValuesInput);

                // Submit payment to server
                fetch('{$webroot}/portal/lib/paylib.php', {
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

            function rainforestNotSuccess(message) {
                alert(message);
            }

            function payRainforest(front) {
                let total = document.querySelector(\"[name='form_paytotal']\").value;
                let prepay = document.getElementById('form_prepayment')?.value || '0';
                if (Number(total) < 1) {
                    if (Number(prepay) < 1) {
                        let error = " . xlj('Please enter a payment amount') . ";
                        alert(error);
                        return false;
                    }
                    total = prepay;
                }

                // Get encounter data from form
                const amountFields = document.querySelectorAll('input.amount_field, input[name^=\"form_upay\"]');
                const encounters = [];
                amountFields.forEach((field) => {
                    const data = field.dataset;
                    const value = field.value;
                    if (value && parseFloat(value) > 0) {
                        encounters.push({
                            id: data.encounterId || field.name.match(/\\[(\\d+)\\]/)?.[1] || '',
                            code: data.code || '',
                            codeType: data.codeType || '',
                            value: value,
                        });
                    }
                });

                const patientId = document.getElementById('hidden_patient_code')?.value || 
                                 document.querySelector('[name=\"form_pid\"]')?.value || 
                                 " . js_escape((string)$this->patientIdCc) . ";
                const dollars = total;
                const currency = 'USD';
                
                let data = {
                    dollars,
                    currency,
                    encounters,
                    patientId,
                };

                $.ajax({
                    contentType: 'application/json; charset=utf-8',
                    data: JSON.stringify(data),
                    dataType: 'json',
                    success: function(responseData) {
                        const component = document.createElement('rainforest-payment');
                        component.setAttribute('session-key', responseData.session_key);
                        component.setAttribute('payin-config-id', responseData.payin_config_id);
                        
                        const cardElement = document.getElementById('card-element');
                        if (cardElement) {
                            // Clear any existing component
                            cardElement.innerHTML = '';
                            cardElement.appendChild(component);
                        } else {
                            console.error('Card element not found');
                            return;
                        }

                        component.addEventListener('approved', function (event) {
                            console.debug(event);
                            console.debug(event.detail);
                            if (event.detail && event.detail[0] && event.detail[0].data) {
                                const payinId = event.detail[0].data.payin_id;
                                rainforestSuccess(payinId);
                            } else {
                                rainforestNotSuccess('Payment approved but no payin_id received');
                            }
                        });

                        component.addEventListener('declined', function (event) {
                            console.debug(event);
                            rainforestNotSuccess('Payment was declined. Please try again or use a different payment method.');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error creating payment component:', error);
                        rainforestNotSuccess('Failed to initialize payment. Please try again.');
                    },
                    type: 'POST',
                    url: '{$webroot}{$modulePath}/public/payment_component.php',
                });
            }
            ";
    }

    /**
     * Return clinic front specific rainforest code in javascript block.
     *
     * @return string
     */
    private function renderRainforestJsClinicFront(): string
    {
        return "
            function rainforestSuccess(payinId) {
                document.getElementById('check_number').value = payinId;
                alert(" . xlj('Successful payment') . ");
                document.querySelector(\"[name='form_save']\").click();
            }
            ";
    }

    /**
     * Return rainforest code in html block (basically is just a button).
     *
     * @return string
     */
    public static function renderRainforestHtml(string $front): string
    {
        if ($front == 'patient') {
            return '<button type="button" class="btn btn-primary" onclick="payRainforest(\'patient\')">' . xlt("Pay with Rainforest") . '</button>';
        } else { //$front == 'clinic'
            return '<button type="button" class="btn btn-primary" onclick="payRainforest(\'clinic\')">' . xlt("Pay with Rainforest") . '</button>';
        }
    }
}
