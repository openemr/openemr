<?php

/**
 * Class ImportPharmacies
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2019 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Pharmacy\Services;

use OpenEMR\Common\Database\QueryUtils;
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
     * @return int Number of pharmacies imported
     */
    public function importPharmacies(string $city, string $state): int
    {

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

        $pharmacyObj = json_decode((string) $body, true, 512, 0);
        $i = 0;
        foreach (is_array($pharmacyObj) ? $pharmacyObj : [] as $value) {
            foreach (is_array($value) ? $value : [] as $show) {
                if (!is_array($show)) {
                    continue;
                }
                $record = NppesPharmacy::fromResult($show);
                if ($record === null) {
                    continue;
                }
                /*********************Skip duplicates*******************/
                if (self::entryCheck($record->npi) === true) {
                    continue;
                }

                $pharmacy = new Pharmacy();
                $pharmacy->set_id();
                $pharmacy->set_name($record->name);
                $pharmacy->set_ncpdp($record->ncpdp);
                $pharmacy->set_npi($record->npi);
                $pharmacy->set_address_line1($record->addressLine1);
                $pharmacy->set_city($record->city);
                $pharmacy->set_state($record->state);
                $pharmacy->set_zip($record->zip);
                // Pharmacy::set_number() takes a non-nullable string, so only
                // set a number the registry actually holds: about a fifth of
                // results carry no fax.
                if ($record->fax !== null) {
                    $pharmacy->set_fax($record->fax);
                }
                if ($record->phone !== null) {
                    $pharmacy->set_phone($record->phone);
                }
                $pharmacy->persist();
                ++$i;
            }
        }
        $response = $i;
        return $response;
    }

    /**
     * Look to see if the pharmacy is in the database already.
     */
    private function entryCheck(string $npi): bool
    {
        $sql = "SELECT count(*) AS num FROM pharmacies WHERE npi = ?";
        $count = QueryUtils::fetchSingleValue($sql, 'num', [$npi]);
        return is_numeric($count) && (int) $count > 0;
    }
}
