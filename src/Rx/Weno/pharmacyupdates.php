<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Weno Pharmacy Directory Download
// on $url for live data use live.wenoexchange.com instead
require_once("../../../interface/globals.php");

use OpenEMR\Rx\Weno\TransmitProperties;
$setting = new TransmitProperties();

$url = "https://cert.wenoexchange.com/wenox/GetListResponse.aspx?PharmacyDirectory=yes&EZUser=1";

// provide your Weno Online Partner ID and password MD5 hash

$weno_email = "future_feature@open_emr.org";
$pass_hash = md5("iy]5ak4k:");
$out = fopen("PharmacyDirectory.zip", 'wb');
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FILE, $out);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERPWD, "{$weno_email}:{$pass_hash}");
curl_exec($ch);
curl_close($ch);
fclose($out);

