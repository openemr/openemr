<?php

namespace OpenEMR\Cqm;

use FontLib\Table\DirectoryEntry;
use OpenEMR\Common\System\System;

class CqmServiceManager
{
    public static function makeCqmClient()
    {
        $servicePath = $GLOBALS['fileroot'] . DIRECTORY_SEPARATOR .
            'node_modules' . DIRECTORY_SEPARATOR .
            'oe-cqm-service' . DIRECTORY_SEPARATOR .
            'server.js';
        $client = new CqmClient(
            new System(),
            $servicePath,
            'http://localhost', // should be 127.0.0.1 loopback
            '6660' // ccda service runs on 6661
        );

        return $client;
    }
}
