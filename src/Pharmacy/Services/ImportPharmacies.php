<?php

/**
 * Class ImportPharmacies
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Pharmacy\Services;

use Address;
use OpenEMR\Common\Http\oeHttp;
use Pharmacy;

/**
 * @package ImportPharmacies
 * This class extends the Pharmacy class to import pharmacies listed with CMS.
 * It can be adapted to work in other countries if a similar API is available.
 * There is a duplication check using the NPI number. If the NPI number exist in the table. The entry is skipped.
 * However, if the pharmacy gets a new NPI number, there could be two entries with the same address and different
 * NPI numbers. I have discovered that some times erroneous entries can be retrieved.
 */
class ImportPharmacies
{
    /**
     * @param $city
     * @param $state
     * @return string
     */
    public function importPharmacies($city, $state)
    {
        $address = new Address();

        $query = [
            'number' => '',
            'enumeration_type' => '',
            'taxonomy_description' => 'pharmacy',
            'first_name' => '',
            'last_name' => '',
            'organization_name'  => '',
            'address_purpose' => '',
            'city' => $city,
            'state' => $state,
            'postal_code' => '',
            'country_code' => '',
            'limit' => '100',
            'skip' => '',
            'version' => '2.1',
        ];

        /**
         * The function call was changed in PR#3172 from oeHttp::get() to oeHttpRequest::getCurlOptions()
         * with the 'ECDHE-RSA-AES256-GCM-SHA384' cipher passed to curl in order to handle an issue in
         * 5.0.2 (1) with OpenSSL 1.1.1c and 1.1.1d where attempting to import the pharmacies from
         * https://npiregistry.cms.hhs.gov/api/ results in the error:
         *   PHP Fatal error: Uncaught GuzzleHttp\Exception\ConnectException: cURL error 35:
         *   error:141A318A:SSL routines:tls_process_ske_dhe:dh key too small
         *   (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)
         * The latest versions of OpenSSL have deprecated the use of the 512-bit Diffie–Hellman key that is
         * apparently still used by the CMS server.  Once CMS updates their encryption it may be possible to
         * revert this back to the original call.
         */
         $response = oeHttp::getCurlOptions(
             'https://npiregistry.cms.hhs.gov/api/',
             $query,
             [CURLOPT_SSL_CIPHER_LIST => 'ECDHE-RSA-AES256-GCM-SHA384']
         );

        $body = $response->body(); // already should be json.

        $pharmacyObj = json_decode($body, true, 512, 0);
        $i = 0;
        foreach ($pharmacyObj as $obj => $value) {
            foreach ($value as $key => $show) {
                /*********************Skip duplicates*******************/
                $npi = $show['number'];
                if (self::entryCheck($npi) === true) {
                    continue;
                }
               /*************Check Zip Code Length**********************/
                $zipCode = $show['addresses'][0]['postal_code'];
                if (strlen($zipCode) > 5) {
                    $zip = substr($zipCode, 0, -4);
                }
                /******************************************************/
                $identifiers = $show['identifiers'];
                $ncpdp = self::findNcpdp($identifiers);

                $pharmacy = new Pharmacy();
                $pharmacy->set_id();
                $pharmacy->set_name($show['basic']['name']);
                $pharmacy->set_ncpdp($ncpdp);
                $pharmacy->set_npi($show['number']);
                $pharmacy->set_address_line1($show['addresses'][0]['address_1']);
                $pharmacy->set_city($show['addresses'][0]['city']);
                $pharmacy->set_state($show['addresses'][0]['state']);
                $pharmacy->set_zip($zip);
                $pharmacy->set_fax($show['addresses'][0]['fax_number']);
                $pharmacy->set_phone($show['addresses'][0]['telephone_number']);
                $pharmacy->persist();
                ++$i;
            }
        }
        $response = $i;
        return $response;
    }

    /**
     * @param $identifiers
     * @return mixed
     */
    private function findNcpdp($identifiers)
    {
        foreach ($identifiers as $identifier => $value) {
            if ($value['desc'] == 'Other') {
                return $value['identifier'];
            }
        }
        return null;
    }

    /**
     * Look to see if the pharmacy is in the database already.
     * @param $npi
     * @return bool
     *
     */
    private function entryCheck($npi)
    {
        $sql = "SELECT count(*) AS num FROM pharmacies WHERE npi = ?";
        $query = sqlQuery($sql, [$npi]);
        if ($query['num'] > 0) {
            return true;
        } else {
            return false;
        }
    }
}
