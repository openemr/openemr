<?php
/**
 * Facility repository.
 *
 * Copyright (C) 2017 Matthew Vita <matthewvita48@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Matthew Vita <matthewvita48@gmail.com>
 * @link    http://www.open-emr.org
 */

namespace repositories;

use Doctrine\ORM\EntityRepository;

class FacilityRepository extends EntityRepository {
    private $logger;

    public function getById($id) {
        $result = $this->_em->getRepository($this->_entityName)->findOneBy(array("id" => $id));
        return $result;
    }

    /**
     * Currently OpenEMR has two approaches in identifying the "primary"
     * facility. This method combines both approaches.
     *
     * return the primary facility.
     */
    public function getMainFacility() {
        $queries = array();

        $queries[0]['criteria'] = array("primaryBusinessEntity" => true);
        $queries[0]['order'] = array();
        $queries[0]['limit'] = 1;
        $queries[0]['offset'] = null;

        $queries[1]['criteria'] = array();
        $queries[1]['order'] = array("id" => "ASC",
                                     "billingLocation" => "DESC",
                                     "acceptsAssignment" => "DESC");
        $queries[1]['limit'] = 1;
        $queries[1]['offset'] = null;

        $result = null;

        foreach($queries as $query) {
            $queryResult = $this->_em->getRepository($this->_entityName)->findBy(
                $query['criteria'],
                $query['order'],
                $query['limit'],
                $query['offset']
            );

            if (!empty($queryResult) && is_array($queryResult) && count($queryResult) === 1) {
                $result = $queryResult[0];
                break;
            }
        }

        return $result;
    }
}
