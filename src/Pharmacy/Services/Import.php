<?php

namespace OpenEMR\Pharmacy\Service;

use Address;
use OpenEMR\Common\Http\oeHttp;
use Pharmacy;


/**
 * Class Import
 * @package Import
 * This class extends the Pharmacy class to import pharmacies listed with CMS.
 * It can be adapted to work in other countries if a similar API is available.
 * There is a duplication check using the NPI number. If the NPI number exist in the table. The entry is skipped.
 * However, if the pharmacy gets a new NPI number, there could be two entries with the same address and different
 * NPI numbers. I have discovered that some times erroneous entries can be retrieved.
 */
class Import extends Pharmacy
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
        $response = oeHttp::get('https://npiregistry.cms.hhs.gov/api/', $query);

        $body = $response->body(); // already should be json.

        $pharmacyObj = json_decode($body, true, 512, 0);
        $i=0;
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
                    $pharmacy = new Pharmacy();
                    $pharmacy->set_id();
                    $pharmacy->set_name($show['basic']['name']);
                    $pharmacy->set_ncpdp($show['identifiers'][0]['identifer']);
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
     * get the NCPDP number from the sub array of idenifiers.
     * @param $identifier
     */
    private function findNcpdp($identifier)
    {
        $val = "Other";
        foreach ($identifier as $element) {
            if ($element['key'] == $val) {
            }
        }

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
