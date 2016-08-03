<?php
/**
 * Created by PhpStorm.
 * User: drorgo
 * Date: 21/07/16
 * Time: 9:24 AM
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
        $sql="SELECT * FROM patient_data WHERE fname like ".$parameters['fname']." OR lname like ".$parameters['lname'] ." OR DOB like ".$parameters['DOB'];

        $rowset = $this->queryThis($sql);
        $results = array();
        foreach ($rowset as $row) {
            $results[] = $row;
        }

        return $results;
    }
}