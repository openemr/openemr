<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Modules\WenoModule\Services\PharmacyService;

class WenoPharmaciesJson
{
    private CryptoGen $cryptoGen;
    private string $encrypted;

    final public function __construct(
        CryptoGen $cryptoGen
    ) {
        $this->cryptoGen = $cryptoGen;
        $job_j = $this->buildJson();
        $method = "aes-256-cbc";
        $key = substr(hash('sha256', $this->wenoEncryptionKey(), true), 0, 32);
        $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);//$this->provider->wenoChr();
        $this->encrypted = base64_encode(openssl_encrypt($job_j, $method, $key, OPENSSL_RAW_DATA, $iv));
    }

    public function storePharmacyDataJson()
    {
        $url = $this->wenoPharmacyDirectoryLink() . "?useremail=" . urlencode($this->providerEmail()) . "&data=" . urlencode($this->encrypted);
        $getWenoPharmaciesCsv = new DownloadWenoPharmacies();
        $storageLocation = $GLOBALS['OE_SITE_DIR'] . "/documents/logs_and_misc/weno/";
        return $getWenoPharmaciesCsv->retrieveDataFile($url, $storageLocation);
    }

    private function buildJson()
    {
        $checkWenoDb = new PharmacyService();
        $has_data = $checkWenoDb->checkWenoDb();
        $jobj = [
            "UserEmail" => $this->providerEmail(),
            "MD5Password" => $this->providerPassword(),
            "ExcludeNonWenoTest" => "N",
            "Daily" => 'N'
        ];

        if (date("l") != "Monday" && $has_data) {
            $jobj["Daily"] = "Y";
        }

        return text(json_encode($jobj));
    }
    private function providerEmail()
    {
        return $GLOBALS['weno_admin_username'];
    }

    private function providerPassword(): string
    {
        return md5($this->cryptoGen->decryptStandard($GLOBALS['weno_admin_password']));
    }
    private function wenoEncryptionKey()
    {
        return $this->cryptoGen->decryptStandard($GLOBALS['weno_encryption_key']);
    }

    private function wenoPharmacyDirectoryLink(): string
    {
        return "https://online.wenoexchange.com/en/EPCS/DownloadPharmacyDirectory";
    }

    public function checkBackgroundService(): string
    {
        $sql = "SELECT active FROM background_services WHERE name = 'WenoExchangePharmacies'";
        $activeStatus = sqlQuery($sql);
        if ($activeStatus['active'] == 0) {
            sqlStatement("UPDATE background_service SET `active` = 1,  WHERE name = 'WenoExchangePharmacies'");
            return "active";
        }
        return "live";
    }
}
