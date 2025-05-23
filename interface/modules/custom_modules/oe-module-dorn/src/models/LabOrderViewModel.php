<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022-2025 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 namespace OpenEMR\Modules\Dorn\models;

class LabOrderViewModel
{
    public $labGuid;
    public $labAccountNumber = "";
    public $orderNumber = "";
    public $patientId = "";
    public $hl7Base64 = "";

    public function __construct()
    {
    }
}
