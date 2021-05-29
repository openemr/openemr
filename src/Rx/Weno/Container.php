<?php

/*
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2021 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Rx\Weno;

/**
 * Class Container
 * @package OpenEMR\Rx\Weno
 */
class Container
{
    private $transmitproperties;
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
    public function getTransmitproperties(): TransmitProperties
    {
        if ($this->transmitproperties === null) {
            $this->transmitproperties = new TransmitProperties();
        }
        return $this->transmitproperties;
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
