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

use OpenEMR\Common\Logging\EventAuditLogger;
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
        $fileLocal = dirname(__DIR__, 2) . '/sites/default/documents/edi/' . $bat_filename;
        $sftpServer = $GLOBALS['ch_sftp']; //this can be IP address or URL
        $sftpUsername = $GLOBALS['ch_sftp_username'];
        $sftpPassword = $cryptoGen->decryptStandard($GLOBALS['ch_sftp_pwd']);
        $inboundremotedir = $GLOBALS['ch_sftp_dir'];
        $sftpPort = 22;

        $ch = new SFTP($sftpServer, $sftpPort);
        if (!$ch->login($sftpUsername, $sftpPassword)) {
            return sftp_status('Login error', $sftpServer . ":" . $sftpPort);
        }

        try {
            if (file_exists($fileLocal)) {
                $ch->put($inboundremotedir . '/' . $bat_filename, $fileLocal, SFTP::SOURCE_LOCAL_FILE);
            } else {
                die('file does not exist!');//I was thinking do this to stop the billing with a hard error. This should never happen
            }
        } catch (Exception $e) {
            $response = 'Caught exception: ' . $e->getMessage();
            return $response;
        }

        return "Success";
    }
}
