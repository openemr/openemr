<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;

class Setting extends AbstractModel
{
    protected $settingName;
    protected $settingValue;

    public function __construct(
        $settingName,
        $settingValue
    ) {
        parent::__construct();

        $this->setSettingName($settingName);
        $this->setSettingValue($settingValue);
    }

    public function jsonSerialize()
    {
        $data = [];

        $data['settingName'] = $this->getSettingName();
        $data['settingValue'] = $this->getSettingValue();

        return $data;
    }

    public function hasAny()
    {
        return true;
    }

    protected function setSettingName($value)
    {
        $this->settingName = $value;
    }

    protected function setSettingValue($value)
    {
        $this->settingValue = $value;
    }
}
