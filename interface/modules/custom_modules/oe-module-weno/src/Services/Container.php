<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

/**
 * Class Container
 * @package OpenEMR\Rx\Weno
 */
class Container
{
    private $transmitProperties;
    private $logproperties;
    private $facilityproperties;
    private $wenopharmacyimport;

    public function __construct()
    {
        //do epic stuff here ...
    }

    /**
     * @return TransmitProperties
     */
    public function getTransmitProperties(): TransmitProperties
    {
        if ($this->transmitProperties === null) {
            $this->transmitProperties = new TransmitProperties();
        }
        return $this->transmitProperties;
    }

    /**
     * @return LogProperties
     */
    public function getLogproperties(): LogProperties
    {
        if ($this->logproperties === null) {
            $this->logproperties = new LogProperties();
        }
        return $this->logproperties;
    }

    /**
     * @return FacilityProperties
     */
    public function getFacilityproperties(): FacilityProperties
    {
        if ($this->facilityproperties === null) {
            $this->facilityproperties = new FacilityProperties();
        }
        return $this->facilityproperties;
    }
}
