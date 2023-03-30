<?php

/**
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

    namespace OpenEMR\Modules\ClaimRevConnector;

class ClaimSearchModel
{
    public $patientFirstName = "";
    public $patientLastName = "";
    public $receivedDateStart;
    public $receivedDateEnd;
    public $serviceDateStart;
    public $serviceDateEnd;

    public function __construct()
    {
    }
}
