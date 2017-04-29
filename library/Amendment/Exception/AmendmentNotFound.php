<?php
/**
 * Created by PhpStorm.
 * User: rdown
 * Date: 4/28/17
 * Time: 20:35
 */

namespace OpenEMR\Amendment\Exception;

use \Exception as E;

class AmendmentNotFound extends E
{

    public function __construct()
    {
        parent::__construct();
    }
}
