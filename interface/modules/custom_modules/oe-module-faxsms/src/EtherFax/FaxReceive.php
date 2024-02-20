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
 * OpenEMR\Modules\FaxSMS\EtherFax\FaxReceive class.
 */
class FaxReceive
{
    public $FaxResult;
    public $JobId;
    public $CalledNumber;
    public $CallingNumber;
    public $RemoteId;
    public $PagesReceived;
    public $ConnectTime;
    public $ConnectSpeed;
    public $ReceivedOn;
    public $FaxImage;
    public $AnalyzeFormResult;
    public $DocumentParams;

    /**
     * Default constructor.
     */
    public function __construct()
    {
        $this->FaxResult = 0;
        $this->PagesReceived = 0;
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
