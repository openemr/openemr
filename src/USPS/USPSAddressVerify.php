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
    protected $addresses = array();
  /**
   * Perform the API call to verify the address
   * @return string
   */
    public function verify()
    {
        return $this->doRequest();
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
        $packageId = $id !== null ? $id : ((count($this->addresses) + 1));
        $this->addresses['Address'][] = array_merge(array('@attributes' => array('ID' => $packageId)), $data->getAddressInfo());
    }
}
