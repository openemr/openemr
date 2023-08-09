<?php

/**
 * Fax SMS Module Member
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2023 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General public License 3
 */

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
