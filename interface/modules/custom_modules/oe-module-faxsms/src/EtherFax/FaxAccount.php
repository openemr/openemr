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
