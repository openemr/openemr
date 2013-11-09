<?php
//    +-----------------------------------------------------------------------------+ 
//    OpenEMR - Open Source Electronic Medical Record
//    Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
//
//    This program is free software: you can redistribute it and/or modify
//    it under the terms of the GNU Affero General Public License as
//    published by the Free Software Foundation, either version 3 of the
//    License, or (at your option) any later version.
//
//    This program is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU Affero General Public License for more details.
//
//    You should have received a copy of the GNU Affero General Public License
//    along with this program.  If not, see <http://www.gnu.org/licenses/>.
//    Author:   Remesh Babu S <remesh@zhservices.com>
//
// +------------------------------------------------------------------------------+
namespace Application\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;


class ApplicationTable extends AbstractTableGateway
{
    protected $table = 'application';
    protected $adapter;
    /**
     * 
     * @param \Zend\Db\Adapter\Adapter $adapter
     */
    public function __construct()
    {
      $adapter = \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
      $this->adapter = $adapter;
      $this->resultSetPrototype = new ResultSet();
      $this->resultSetPrototype->setArrayObjectPrototype(new Application());
      $this->initialize();
    }
     
    public function sqlQuery($sql, $params = '')
    {
        $statement = $this->adapter->query($sql);
        $return = $statement->execute($params);

        $count = count($params);
        $arr = array();
        foreach($params as $val) {
          array_push($arr, "'" . $val . "'");
        }
        $logSQL = preg_replace(array_fill(0, $count, "/\?/"), $arr, $sql, 1);
        $this->log($logSQL);
        return $return;
    }
    
    /**
     * Log all DB Transactions
     * Usege in other model
     * @example use \Application\Model\ApplicationTable 
     * @example $obj = new ApplicationTable() create an object
     * @example $obj->log($params) call log function
     * @param arry $params
     */
    public function log($logSQL)
    {
      $sql        = "INSERT INTO log SET date = ? ,user = ?, groupname = ?, comments = ?";
      $dt         = date('Y-m-d  H:i:s');
      $user       = $_SESSION['authUser'];
      $group      = $_SESSION['authGroup'];
      $params     = array($dt, $user, $group, $logSQL);
      $statement  = $this->adapter->query($sql);
      $return     = $statement->execute($params);
      return true;
    }
    
    /**
     * Checks ACL
     * @param String $user_id Auth user Id
     * $param String $section_identifier ACL Section id
     * @return boolean
     */
    public function aclcheck($user_id,$section_identifier){
        $sql_user_acl   = " SELECT 
                                COUNT(allowed) AS count 
                            FROM
                                module_acl_user_settings AS usr_settings 
                                LEFT JOIN module_acl_sections AS acl_sections 
                                    ON usr_settings.section_id = acl_sections.`section_id` 
                            WHERE 
                                acl_sections.section_identifier = ? AND usr_settings.user_id = ? AND usr_settings.allowed = ?";
        $sql_group_acl  = " SELECT 
                                COUNT(allowed) AS count 
                            FROM
                                module_acl_group_settings AS group_settings 
                                LEFT JOIN module_acl_sections AS  acl_sections
                                  ON group_settings.section_id = acl_sections.section_id
                            WHERE
                                acl_sections.`section_identifier` = ? AND group_settings.group_id IN (?) AND group_settings.allowed = ?";
        $sql_user_group = " SELECT 
                                gagp.id AS group_id
                            FROM
                                gacl_aro AS garo 
                                LEFT JOIN `gacl_groups_aro_map` AS gamp 
                                    ON garo.id = gamp.aro_id 
                                LEFT JOIN `gacl_aro_groups` AS gagp
                                    ON gagp.id = gamp.group_id
                                RIGHT JOIN `users_secure` usr 
                                    ON usr. username =  garo.value
                            WHERE
                                garo.section_value = ? AND usr. id = ?";
                                
        $res_groups     = $this->sqlQuery($sql_user_group,array('users',$user_id));
        $groups = array();
        foreach($res_groups as $row){
          array_push($groups,$row['group_id']);
        }
        $groups_str = implode(",",$groups);
        
        $count_user_denied      = 0;
        $count_user_allowed     = 0;
        $count_group_denied     = 0;
        $count_group_allowed    = 0;
        
        $res_user_denied    = $this->sqlQuery($sql_user_acl,array($section_identifier,$user_id,0));
        foreach($res_user_denied as $row){
            $count_user_denied  = $row['count'];
        }
        
        $res_user_allowed   = $this->sqlQuery($sql_user_acl,array($section_identifier,$user_id,1));
        foreach($res_user_allowed as $row){
            $count_user_allowed  = $row['count'];
        }
        
        $res_group_denied   = $this->sqlQuery($sql_group_acl,array($section_identifier,$groups_str,0));
        foreach($res_group_denied as $row){
            $count_group_denied  = $row['count'];
        }
        
        $res_group_allowed  = $this->sqlQuery($sql_group_acl,array($section_identifier,$groups_str,1));
        foreach($res_group_allowed as $row){
            $count_group_allowed  = $row['count'];
        }

        if($count_user_denied > 0)
            return false;
        elseif($count_user_allowed > 0)
            return true;
        elseif($count_group_denied > 0)
            return false;
        elseif($count_group_allowed > 0)
            return true;
        else
            return false;
    }
    
}
