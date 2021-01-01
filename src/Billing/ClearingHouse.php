<?php

/**
 *  @package   OpenEMR
 *  @link      http://www.open-emr.org
 *  @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c )2020. Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

namespace OpenEMR\Billing;

use phpseclib\Net\SFTP;
use OpenEMR\Common\Crypto;

/**
 * Class ClearingHouse
 * @package OpenEMR\Billing
 */
class ClearingHouse
{
    /**
     * @param $bat_filename
     * @return string
     */
    public function sendBilling($bat_filename)
    {
        $cryptoGen = new Crypto\CryptoGen();
        $fileLocal = $GLOBALS['OE_SITE_DIR'] . '/documents/edi/' . $bat_filename;
        $sftpServer = $GLOBALS['ch_sftp']; //this can be IP address or URL
        $sftpUsername = $GLOBALS['ch_sftp_username'];
        $sftpPassword = $cryptoGen->decryptStandard($GLOBALS['ch_sftp_pwd']);
        $inboundremotedir = $GLOBALS['ch_sftp_dir'];
        $sftpPort = 22;

        $ch = new SFTP($sftpServer, $sftpPort);
        $ch->setTimeout(20);
        if (!$ch->login($sftpUsername, $sftpPassword)) {
            return sftp_status('Login error', $sftpServer . ":" . $sftpPort);
        }

        try {
            if (file_exists($fileLocal)) {
                $ch->put($inboundremotedir . '/' . $bat_filename, $fileLocal, SFTP::SOURCE_LOCAL_FILE);
            }
        } catch (Exception $e) {
            $response = 'Caught exception: ' . $e->getMessage();
            return $response;
        }
        return "Success";
    }
}
