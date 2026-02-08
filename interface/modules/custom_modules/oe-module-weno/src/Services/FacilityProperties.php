<?php

/**
 *  @package OpenEMR
 *  @link    http://www.open-emr.org
 *  @author  Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @copyright Copyright (c) 2020 Sherwin Gaddis <sherwingaddis@gmail.com>
 *  @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Modules\WenoModule\Services;

class FacilityProperties
{
    public $facilityupdates;

    public function __construct()
    {
        //do epic stuff!!
    }

    /**
     * @return array
     */
    public function getFacilities()
    {
        $sql = "select id, name, street, city, weno_id from facility";
        $list = sqlStatement($sql);
        $facilities_list = [];
        while ($row = sqlFetchArray($list)) {
            $facilities_list[] = $row;
        }
        return $facilities_list;
    }

    /**
     * @return
     */
    public function updateFacilityNumber()
    {
        $locations = $this->facilityupdates;
        foreach ($locations as $location) {
            sqlQuery("update facility set weno_id = ? where id = ?", [$location[1], $location[0]]);
        }
    }
}
