<?php

/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c ) 2020.. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\OePrescriptions;

use OpenEMR\Common\Database\Connector;
use OpenEMR\Entities\Prescriptions;

class OePrescriptions
{
    public function __construct()
    {
        //one day we will do epic stuff here
    }

    public function listPrescriptions($pid)
    {
        return $GLOBALS['twig']->render(
            'prescription/list.twig',
            [
                $prescriptions = '',
                'tabtitle' => xl('Prescription List'),
                'pagetitle' => xl('Prescription List'),
                'prescriptions' => $prescriptions
            ]
        );
    }
}
