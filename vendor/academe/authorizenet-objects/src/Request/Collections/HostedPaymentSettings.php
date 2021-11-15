<?php

namespace Academe\AuthorizeNet\Request\Collections;

/**
 *
 */

use Academe\AuthorizeNet\AbstractCollection;
use Academe\AuthorizeNet\Request\Model\HostedPaymentSetting;

class HostedPaymentSettings extends AbstractCollection
{
    protected function hasExpectedStrictType($item)
    {
        // Make sure the item is the correct type, and is not empty.
        return $item instanceof HostedPaymentSetting && $item->hasAny();
    }

    /**
     * The array of transaction settings needs to be wrapped by a single setting element.
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        return ['setting' => $data];
    }

    /**
     * Set a parameter on a setting.
     * Each setting has an array of one or more parameters, such as ReturnOptions with
     * has parameters showReceipt, url, urlText, cancelUrl and cancelUrlText.
     * Note that there is no validation on the parameter name passed to the setting.
     * Invalid parameter names will be reported by the gateway as an error.
     *
     * @param text $settingName The name of the setting
     * @param text $parameterName The name of the parameter within the setting
     * @param mixed $parameterValue The value of the parameter; usually boolean or string
     */
    public function setSettingParameter($settingName, $parameterName, $parameterValue)
    {
        // First find if the setting has already been added.
        foreach ($this as $key => $value) {
            if ($value->isSettingName($settingName)) {
                // Then replace it with a setting containing the new parameter value.
                $this[$key] = $value->withSettingParameter($parameterName, $parameterValue);

                return $this;
            }
        }

        // Did not find this setting in the collection, so create a new one with
        // just this parameter.
        $this->push(
            new HostedPaymentSetting(
                $settingName,
                [$parameterName => $parameterValue]
            )
        );

        return $this;
    }
}
