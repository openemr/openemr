<?php

/**
 * interface/modules/zend_modules/module/Application/src/Application/Model/SendtoTable.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    BASIL PT <basil@zhservices.com>
 * @copyright Copyright (c) 2014 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace Application\Model;

use Laminas\Db\TableGateway\AbstractTableGateway;
use Application\Model\ApplicationTable;
use Laminas\Db\Adapter\Driver\Pdo\Result;

class SendtoTable extends AbstractTableGateway
{
    /*
    * getFacility
    * @return array facility
    *
    **/
    public function getFacility()
    {
        $appTable   = new ApplicationTable();
        $sql        = "SELECT * FROM facility ORDER BY name";
        $result     = $appTable->zQuery($sql);
        return $result;
    }


    /*
    * getUsers
    * @param String $type
    * @return array
    *
    **/
    public function getUsers($type)
    {
        $appTable   = new ApplicationTable();
        $sql        = "SELECT * FROM users WHERE abook_type = ?";
        $result     = $appTable->zQuery($sql, array($type));
        return $result;
    }


    /*
    * getFaxRecievers
    * @return array fax reciever types
    *
    **/
    public function getFaxRecievers()
    {
        $appTable   = new ApplicationTable();
        $sql        = "SELECT option_id, title FROM list_options WHERE list_id = 'abook_type'";
        $result     = $appTable->zQuery($sql, array($formId));
        return $result;
    }

    /*
    * CCDA component list
    *
    * @param    $type
    * @return   $components     Array of CCDA components
    **/
    public function getCCDAComponents($type)
    {
        $components = array();
        $query      = "select * from ccda_components where ccda_type = ?";
        $appTable   = new ApplicationTable();
        $result     = $appTable->zQuery($query, array($type));

        foreach ($result as $row) {
            $components[$row['ccda_components_field']] = $row['ccda_components_name'];
        }

        return $components;
    }
}
