<?php

namespace Omnipay\AuthorizeNetApi\Message\HostedPage;

/**
 * Also known as "Authorize.Net Accept Hosted".
 */

use Academe\AuthorizeNet\Request\GetHostedPaymentPage;
use Academe\AuthorizeNet\Request\Collections\HostedPaymentSettings;
use Academe\AuthorizeNet\Request\Model\HostedPaymentSetting;

use Omnipay\AuthorizeNetApi\Message\AuthorizeRequest as ApiAuthorizeRequest;
use Omnipay\AuthorizeNetApi\Traits\HasHostedPageGatewayParams;

class AuthorizeRequest extends ApiAuthorizeRequest
{
    use HasHostedPageGatewayParams;

    /**
     * @var HostedPaymentSettings
     */
    protected $hostedPaymentSettings;

    /**
     * Wrap the transaction detail into a full request for an action on
     * the transaction.
     */
    protected function wrapTransaction($auth, $transaction)
    {
        $request = new GetHostedPaymentPage($auth, $transaction);

        // The Hosted Payment Page settings are at the request level, so
        // they cannot be set until we are wrapping the transaction into the request.
        // Set any individual parameters that map to standard Omnpay parameters first.

        if ($cancelUrl = $this->getCancelUrl()) {
            $this->setReturnOptionsCancelUrl($cancelUrl);
        }

        if ($returnUrl = $this->getReturnUrl()) {
            $this->setReturnOptionsUrl($returnUrl);
        }

        // Then add the settings to the outer requuest object.

        if ($settings = $this->getHostedPaymentSettings()) {
            $request = $request->withHostedPaymentSettings($settings);
        }

        return $request;
    }

    public function getData()
    {
        $transaction = parent::getData();

        return $transaction;
    }

    /**
     * Accept a transaction and sends it as a request.
     *
     * @param $data TransactionRequestInterface
     * @returns TransactionResponse
     */
    public function sendData($data)
    {
        $response_data = $this->sendTransaction($data);

        $response = new Response($this, $response_data);

        // The response needs to know whether we are in test mode or not,
        // so that it chooses the correct hosted page URL to redirect to.

        $response->setTestMode($this->getTestMode());

        return $response;
    }

    /**
     * Add all the hosted payment settings all at once.
     * @param array $value Name/value pairs for each setting.
     */
    public function setHostedPaymentSettings(array $value)
    {
        foreach ($value as $name => $value) {
            $this->setHostedPaymentSetting($name, $value);
        }
    }

    /**
     * @returns HostedPaymentSettings|null
     */
    public function getHostedPaymentSettings()
    {
        return $this->hostedPaymentSettings;
    }

    /**
     * @param mixed $name The Name of the setting,
     *   one of \Academe\AuthorizeNet\Request\Model\HostedPaymentSetting::SETTING_NAME_*
     * @param string|array $value The value of the setting.
     */
    public function setHostedPaymentSetting($name, $value)
    {
        // Initialise the collection if not intialised.
        if (empty($this->hostedPaymentSettings)) {
            $this->hostedPaymentSettings = new HostedPaymentSettings();
        }

        $this->hostedPaymentSettings->push(
            new HostedPaymentSetting($name, $value)
        );
    }

    /**
     * Set a named parameter on a named payment page setting.
     */
    public function setHostedPaymentSettingParameter($settingName, $parameterName, $parameterValue)
    {
        if ($settings = $this->getHostedPaymentSettings()) {
            // The settings collection already exists, so add to it.
            $settings->setSettingParameter($settingName, $parameterName, $parameterValue);
        } else {
            // No settings at all so far, so add this one to start.
            $this->setHostedPaymentSetting($settingName, [$parameterName => $parameterValue]);
        }
    }

    // The following parameters follow a consistent pattern that could be implemented
    // as a magic __call method, but aren't at this time (in case they need to go into
    // the gateway settings).
    // They exist to allow these settings to provided as scalar values, even though
    // they end up quite deep in the request strucure.
    // There are no equivalent getters for these setters. Do we need getters?

    /**
     * @param bool $value
     */
    public function setReturnOptionsShowReceipt($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_RETURN_OPTIONS,
            'showReceipt',
            (bool)$value
        );
    }

    /**
     * @param string $value
     */
    public function setReturnOptionsUrl($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_RETURN_OPTIONS,
            'url',
            $value
        );
    }

    /**
     * @param string $value
     */
    public function setReturnOptionsUrlText($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_RETURN_OPTIONS,
            'urlText',
            $value
        );
    }

    /**
     * @param string $value
     */
    public function setReturnOptionsCancelUrl($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_RETURN_OPTIONS,
            'cancelUrl',
            $value
        );
    }

    /**
     * @param string $value
     */
    public function setReturnOptionsCancelUrlText($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_RETURN_OPTIONS,
            'cancelUrlText',
            $value
        );
    }

    /**
     * @param string $value
     */
    public function setButtonOptionsText($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_BUTTON_OPTIONS,
            'text',
            $value
        );
    }

    /**
     * @param string $value
     */
    public function setStyleOptionsBgColor($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_STYLE_OPTIONS,
            'bgColor',
            $value
        );
    }

    /**
     * @param bool $value
     */
    public function setPaymentOptionsCardCodeRequired($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_PAYMENT_OPTIONS,
            'cardCodeRequired',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setPaymentOptionsShowCreditCard($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_PAYMENT_OPTIONS,
            'showCreditCard',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setPaymentOptionsShowBankAccount($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_PAYMENT_OPTIONS,
            'showBankAccount',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setSecurityOptionsCaptcha($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_SECURITY_OPTIONS,
            'captcha',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setShippingAddressOptionsShow($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_SHIPPING_ADDRESS_OPTIONS,
            'show',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setShippingAddressOptionsRequired($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_SHIPPING_ADDRESS_OPTIONS,
            'required',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setBillingAddressOptionsShow($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_BILLING_ADDRESS_OPTIONS,
            'show',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setBillingAddressOptionsRequired($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_BILLING_ADDRESS_OPTIONS,
            'required',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setCustomerOptionsShowEmail($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_CUSTOMER_OPTIONS,
            'showEmail',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setCustomerOptionsRequiredEmail($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_CUSTOMER_OPTIONS,
            'requiredEmail',
            (bool)$value
        );
    }

    /**
     * @param bool $value
     */
    public function setOrderOptionsShow($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_ORDER_OPTIONS,
            'show',
            (bool)$value
        );
    }

    /**
     * @param string $value
     */
    public function setOrderOptionsMerchantName($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_ORDER_OPTIONS,
            'merchantName',
            $value
        );
    }

    /**
     * Name is no typo - it just follows the same patterns as above.
     * @param string $value
     */
    public function setIFrameCommunicatorUrlUrl($value)
    {
        return $this->setHostedPaymentSettingParameter(
            HostedPaymentSetting::SETTING_NAME_FRAME_COMMUNICATOR_URL,
            'url',
            $value
        );
    }
}
