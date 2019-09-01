<?php

namespace OpenEMR\Pharmacy\Service;

use OpenEMR\Common\Http\oeHttp;
use Pharmacy;
use Address;

/**
 * Class Import
 * @package Import
 */
class Import extends Pharmacy
{

    public function importPharmacies($city, $state)
    {
        $pharmacy = new Pharmacy();
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
            'limit' => '5',
            'skip' => '',
            'version' => '2.1',
        ];
        $response = oeHttp::get('https://npiregistry.cms.hhs.gov/api/', $query);

        $body = $response->body(); // already should be json.

        $pharmacyObj = json_decode($body, true, 512, 0);
        $i=0;
        foreach ($pharmacyObj as $obj => $value) {
            foreach ($value as $show) {
                $pharmacy->set_id("");
                $pharmacy->set_name($show['basic']['name']);
                $pharmacy->set_ncpdp($show['identifiers']['identifer']);
                $pharmacy->set_npi($show['number']);
                $pharmacy->set_address_line1($show['addresses'][0]['address_1']);
                $pharmacy->set_city($show['addresses'][0]['city']);
                $pharmacy->set_state($show['addresses'][0]['state']);
                $pharmacy->set_zip(substr($show['addresses'][0]['postal_code'], 0, -4));
                $pharmacy->set_fax($show['addresses'][0]['fax_number']);
                $pharmacy->set_phone($show['addresses'][0]['telephone_number']);
                $pharmacy->persist();
                ++$i;
            }
        }
        return "{%i} Pharmacies imported ";
    }

    public function getForeignId()
    {
        $sql = "SELECT MAX(id) FROM pharmacies";
        $this->id = sqlQuery($sql);
        return $this->id;
    }

    public function getAddressId()
    {
        $sql = "SELECT MAX(id) FROM addresses";
        $this->id = sqlQuery($sql);
        return $this->id;
    }
}
