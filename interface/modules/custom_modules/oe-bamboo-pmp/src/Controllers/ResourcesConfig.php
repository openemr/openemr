<?php

/*
 *
 *   package   OpenEMR
 *   link      http://www.open-emr.org
 *   author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)
 *   All rights reserved
 */

namespace Juggernaut\Module\Bamboo\Controllers;

use OpenEMR\Common\Crypto\CryptoGen;

class ResourcesConfig
{
    public function storeConnectionData($data)
    {
        $cryptoGen = new CryptoGen();
        $password = $cryptoGen->encryptStandard($data['password'], null, 'database');
        $username = $data['username'];

        return sqlStatement("INSERT INTO `module_bamboo_credentials` (`id`, `username`, `password`, `date`) VALUES (?, ?, ?, NOW())", [null, $username, $password]);
    }

    public function getConnectionData()
    {
        return sqlQuery("SELECT * FROM `module_bamboo_credentials`");
    }
}
