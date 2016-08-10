<?php

/* +-----------------------------------------------------------------------------+
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
use Zend\Db\Sql\Expression;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Predicate;
use \Application\Model\ApplicationTable;
use Zend\Db\Adapter\Adapter;
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
        $adapter = \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
        $this->adapter = $adapter;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        $rsArray=array();
        $rs= $this->tableGateway->select();
        foreach($rs as $r) {
            $rsArray[]=$r;
        }
        return $rsArray;
    }


    /**
     * @param $sql
     * @return query for the sql statement
     */
    public function queryThis($sql){

        $rowset = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $returnedArray=$rowset->toArray();
        return $returnedArray;

    }



    /**
     * update table
     * @param $set
     * @param $where
     * @return int (num of affected rows)
     */
    public function updatePatient($set, $where)
    {
        $rowupdute = $this->tableGateway->update($set, $where);
        return $rowupdute;
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function getPatients(array $parameters)
    {
        //You can use this function to write whatever rules that you need from the DB
        //$sql="SELECT * FROM patient_data WHERE fname like ".$parameters['fname']." OR lname like ".$parameters['lname'] ." OR DOB like ".$parameters['DOB'];

        $obj    = new ApplicationTable;
        $sql    = " SELECT * FROM patient_data WHERE fname like  ? OR lname like ? OR DOB like ? ";
        $params = array($parameters['fname'],$parameters['lname'],$parameters['DOB']);
        $rowset = $obj->zQuery($sql, $params);



       // $rowset = $this->queryThis($sql);
        $results = array();
        foreach ($rowset as $row) {
            $results[] = $row;
        }

        return $results;
    }
}