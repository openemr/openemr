<?php

/**
 * Base Service Class for MedEx API
 * All API services extend this base class
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    MedEx <support@MedExBank.com>
 * @copyright Copyright (c) 2018 MedEx <support@MedExBank.com>
 * @license   Proprietary - All Rights Reserved
 */

namespace MedExApi\Services;

use MedExApi\Client\HttpClient;
use MedExApi\MedEx;

class BaseService
{
    protected HttpClient $curl;
    protected MedEx $medEx;

    public function __construct(MedEx $medEx)
    {
        $this->medEx = $medEx;
        $this->curl = $this->medEx->curl;
    }
}
