<?php

/*
 * Verify valid address with USPS Web API
 * originally under MIT License
 * https://packagist.org/packages/binarydata/usps-php-api
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Vincent Gabriel
 * @author    stephen waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2012 Vincent Gabriel
 * @copyright Copyright (c) 2022 stephen waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\USPS;

use OpenEMR\USPS\USPSBase;
use OpenEMR\USPS\USPSAddress;

/**
 * USPS Address Verify Class
 * used to verify an address is valid
 * @since 1.0
 * @author Vincent Gabriel
 */
class USPSAddressVerify extends USPSBase
{
  /**
   * @var string - the api version used for this type of call
   */
    protected $apiVersion = 'Verify';
  /**
   * @var array - list of all addresses added so far
   */
    protected $addresses = [];
  /**
   * Perform the API call to verify the address
   * @return string
   */
    public function verify()
    {
        if ($this->useV3) {
            return $this->verifyV3();
        }
        return $this->doRequest();
    }

  /**
   * Verify address using v3 API
   * @return string
   */
    protected function verifyV3()
    {
        // v3 only does one address at a time
        if (empty($this->addresses['Address'])) {
            $this->setErrorMessage('No address to verify');
            return '';
        }

        $address = $this->addresses['Address'][0];

        $params = [];

        if (isset($address['FirmName'])) {
            $params['firm'] = $address['FirmName'];
        }
        if (isset($address['Address1'])) {
            $params['secondaryAddress'] = $address['Address1'];
        }
        if (isset($address['Address2'])) {
            $params['streetAddress'] = $address['Address2'];
        }
        if (isset($address['City'])) {
            $params['city'] = $address['City'];
        }
        if (isset($address['State'])) {
            $params['state'] = $address['State'];
        }
        if (isset($address['Zip5'])) {
            $params['ZIPCode'] = $address['Zip5'];
        }
        if (isset($address['Zip4'])) {
            $params['ZIPPlus4'] = $address['Zip4'];
        }

        return $this->doRequestV3('/address', $params);
    }
  /**
   * returns array of all addresses added so far
   * @return array
   */
    public function getPostFields()
    {
        return $this->addresses;
    }
  /**
   * Add Address to the stack
   * @param USPSAddress object $data
   * @param string $id the address unique id
   * @return void
   */
    public function addAddress(USPSAddress $data, $id = null)
    {
        $packageId = $id ?? count($this->addresses) + 1;
        $this->addresses['Address'][] = array_merge(['@attributes' => ['ID' => $packageId]], $data->getAddressInfo());
    }
}
