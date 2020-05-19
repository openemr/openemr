<?php

/**
 * Paytrace Payment Gateway
 * link    http://www.open-emr.org
 * author  Sherwin Gaddis <sherwingaddis@gmail.com>
 * Copyright (c) 2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 * license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\PaytraceGateway\Controllers;

/**
 * Class IndexController
 * @package OpenEMR\Modules\PaytraceGateway\Controllers
 */
class IndexController
{
    private $card;

    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function registerCard()
    {
        return "here!";
        /*
        return $GLOBALS['twig']->render('template/card_on_file.twig', [
            'title' => 'Initial Card Entry'
        ]);*/
    }


}
