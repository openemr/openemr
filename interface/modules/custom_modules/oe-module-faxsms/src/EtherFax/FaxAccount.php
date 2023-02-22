<?php

namespace OpenEMR\Modules\FaxSMS\EtherFax;

/**
 * OpenEMR\Modules\FaxSMS\EtherFax\FaxAccount class.
 */
class FaxAccount
{
    public $Account;
    public $Name;
    public $Ports;
    public $Enabled;
    public $Features;
    public $AcceptedFormats;
    public $Numbers;
    public $Country;

    /**
     * @param $data
     * @return void
     */
    public function set($data)
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
