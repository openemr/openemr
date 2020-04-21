<?php

/* +-----------------------------------------------------------------------------+
* Copyright 2016 matrix israel
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 3
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see
* http://www.gnu.org/licenses/licenses.html#GPL
*    @author  Dror Golan <drorgo@matrix.co.il>
* +------------------------------------------------------------------------------+
 *
 */

namespace Patientvalidation\Model;

use Laminas\Db\Sql\Expression;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Predicate;
use Application\Model\ApplicationTable;
use Laminas\Db\Adapter\Adapter;

class PatientDataTable
{

    protected $tableGateway;
    protected $adapter;


    /**
     * PatientTable constructor.
     * @param TableGateway $tableGateway
     */
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $adapter = \Laminas\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
        $this->adapter = $adapter;
    }









    /**
     * @param array $parameters
     * @return array
     */
    public function getPatients(array $parameters)
    {
        //You can use this function to write whatever rules that you need from the DB
        //$sql="SELECT * FROM patient_data WHERE fname like ".$parameters['fname']." OR lname like ".$parameters['lname'] ." OR DOB like ".$parameters['DOB'];


        $obj    = new ApplicationTable();
        $sql    = " SELECT * FROM patient_data WHERE fname like  ? OR lname like ? OR DOB like ?  OR pubpid = ?";
        $params = array($parameters['fname'],$parameters['lname'],$parameters['DOB'],isset($parameters['pubpid']) ? $parameters['pubpid'] : '');
        $rowset = $obj->zQuery($sql, $params);


        $results = array();
        foreach ($rowset as $row) {
            $results[] = $row;
        }

        return $results;
    }
}
