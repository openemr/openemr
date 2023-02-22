<?php

namespace OpenEMR\Modules\FaxSMS\EtherFax;

/**
 * OpenEMR\Modules\FaxSMS\EtherFax\FaxStatus class.
 */
class FaxStatus
{
    public $FaxResult;
    public $State;
    public $JobId;
    public $PagesDelivered;
    public $ConnectTime;
    public $ConnectSpeed;
    public $Tag;
    public $CompletedOn;
    public int $Result;
    public $Message;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->FaxResult = 0;
        $this->State = FaxState::Idle;
        $this->PagesDelivered = 0;
        $this->ConnectTime = 0;
        $this->ConnectSpeed = 0;
    }

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
