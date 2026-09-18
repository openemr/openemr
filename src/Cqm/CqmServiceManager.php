<?php

namespace OpenEMR\Cqm;

use OpenEMR\Common\System\System;
use OpenEMR\Core\OEGlobalsBag;

class CqmServiceManager
{
    public static function makeCqmClient(): CqmClient
    {
        $servicePath = OEGlobalsBag::getInstance()->getProjectDir()
            . '/ccdaservice/node_modules/oe-cqm-service/server.js';
        $client = new CqmClient(
            new System(),
            $servicePath,
            'http://127.0.0.1',
            '6660' // ccda service runs on 6661
        );

        return $client;
    }
}
