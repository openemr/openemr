<?php

/**
 *  package OpenEMR
 *  link    https://www.open-emr.org
 *  author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  Copyright (c) 2022.
 *  license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Billing;

use phpseclib\Net\SFTP;

class X12ClaimRepost
{
    public function __construct(
        $host,
        $username,
        $password,
        $port = 22
    ) {
        $connection = new SFTP($host, $port);
        if (false === $connection->login($username, $password)) {
            return 'failed';
        }
        return 'success';
    }
    //ToDo: fix this for more than one SFTP account. Right now, it will only select the first one in the table.
    public static function x12Url()
    {
        return sqlQuery('SELECT x12_sftp_host FROM x12_partners');
    }

    public static function x12Username()
    {
        return sqlQuery('SELECT x12_sftp_login FROM x12_partners');
    }
    public static function x12Password()
    {
        return sqlQuery('SELECT x12_sftp_pass FROM x12_partners');
    }

    public static function updateStatus(): void
    {
        sqlQuery("UPDATE `x12_remote_tracker` SET status = 'waiting', messages = NULL WHERE `status` = 'login-error'");
    }
}
