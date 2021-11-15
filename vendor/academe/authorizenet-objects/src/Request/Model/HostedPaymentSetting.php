<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 * A Hosted Payment Page setting contains an array of one or
 * more parameters.
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;

class HostedPaymentSetting extends AbstractModel
{
    /**
     * @var Names of settings for the hosted payment page, with a common prefix removed
     */
    const SETTING_NAME_RETURN_OPTIONS           = 'ReturnOptions';
    const SETTING_NAME_BUTTON_OPTIONS           = 'ButtonOptions';
    const SETTING_NAME_STYLE_OPTIONS            = 'StyleOptions';
    const SETTING_NAME_PAYMENT_OPTIONS          = 'PaymentOptions';
    const SETTING_NAME_SECURITY_OPTIONS         = 'SecurityOptions';
    const SETTING_NAME_SHIPPING_ADDRESS_OPTIONS = 'ShippingAddressOptions';
    const SETTING_NAME_BILLING_ADDRESS_OPTIONS  = 'BillingAddressOptions';
    const SETTING_NAME_CUSTOMER_OPTIONS         = 'CustomerOptions';
    const SETTING_NAME_ORDER_OPTIONS            = 'OrderOptions';
    const SETTING_NAME_FRAME_COMMUNICATOR_URL   = 'FrameCommunicatorUrl';

    /**
     * @var Name of the setting with a common prefix removed
     */
    protected $settingName;

    /**
     * @var array Each setting will be stored as an array of parameters.
     * Examples and a specification can be found here:
     * https://developer.authorize.net/api/reference/features/accept_hosted.html
     */
    protected $settingParameters;

    /**
     * @var The prefix each setting name will have when sent to the gateway.
     */
    protected $settingNamePrefix = 'hostedPayment';

    /**
     * @param string $settingName Name of the setting, one of static::SETTING_NAME_*
     * @param mixed $settingParameters Value as array or JSON-encoded string
     */
    public function __construct(
        $settingName,
        $settingParameters = []
    ) {
        parent::__construct();

        $this->setSettingName($settingName);
        $this->setSettingParameters($settingParameters);
    }

    /**
     * Serialize this object.
     */
    public function jsonSerialize()
    {
        $data = [];

        $data['settingName'] = $this->settingNamePrefix . $this->getSettingName();

        // The documentation describes the value as a "JSON object", but it's
        // really a JSON *string*, which means it is double-encoded.

        $data['settingValue'] = json_encode($this->getSettingParameters());

        return $data;
    }

    public function hasAny()
    {
        return true;
    }

    protected function setSettingName($value)
    {
        // If the prefix is present, then remove it for now.
        if (strpos($value, $this->settingNamePrefix) === 0) {
            $value = substr($value, strlen($this->settingNamePrefix));
        }

        // Support camelCase for those that may want to use it.
        $value = ucfirst($value);

        $this->assertValueSettingName($value);

        $this->settingName = $value;
    }

    /**
     * Set all the settign parameters in one go.
     * @param arry|string $value Array of parameters or array as a JSON string.
     */
    protected function setSettingParameters($value)
    {
        // If a JSON string has been passed in, then expand it to an array to
        // facilitate further manipulation.

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $this->settingParameters = $value;
    }

    /**
     * Set a single parameter.
     */
    protected function setSettingParameter($name, $value)
    {
        $this->settingParameters[$name] = $value;
        return $this;
    }

    /**
     * Clone with a single parameter.
     */
    public function withSettingParameter($name, $value)
    {
        $clone = clone $this;
        return $clone->setSettingParameter($name, $value);
    }

    public function isSettingName($settingName)
    {
        // If the prefix is present, then remove it for now.
        if (strpos(lcfirst($settingName), $this->settingNamePrefix) === 0) {
            $settingName = substr($settingName, strlen($this->settingNamePrefix));
        }

        return (ucfirst($settingName) === $this->getSettingName());
    }
}
