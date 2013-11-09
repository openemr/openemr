<?php
// +-----------------------------------------------------------------------------+
//OpenEMR - Open Source Electronic Medical Record
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
// Author:  Jacob T.Paul <jacob@zhservices.com>
//          Basil PT <basil@zhservices.com>  
//
// +------------------------------------------------------------------------------+

namespace Acl\Model;

use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use \Application\Model\ApplicationTable;

class AclTable extends AbstractTableGateway
{
    protected $table = 'acl';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Acl());
        $this->initialize();
    }

    public function fetchAll()
    {
        $resultSet = $this->select();
        return $resultSet;
    }
    
    public function aclSections($module_id){
        $obj    = new ApplicationTable;
        if($module_id != ''){
            $sql    = "SELECT * FROM module_acl_sections WHERE module_id = ?";
            $params = array($module_id);
        $result = $obj->sqlQuery($sql, $params);
        }else{
            $sql = "SELECT * FROM module_acl_sections ";
            $result = $obj->sqlQuery($sql);
        }
        return $result;  
    }
    public function aclUserGroupMapping(){
        $sql = "SELECT 
                    usr. id AS user_id,
                    garo.id AS aro_id,
                    garo.value AS username,
                    garo.name AS display_name,
                    gagp.id AS group_id,
                    gagp.name AS group_name,
                    gagp.value AS group_nick
                FROM
                    `gacl_aro` AS garo 
                        LEFT JOIN `gacl_groups_aro_map` AS gamp 
                            ON garo.id = gamp.aro_id 
                        LEFT JOIN `gacl_aro_groups` AS gagp
                            ON gagp.id = gamp.group_id
                        RIGHT JOIN `users_secure` usr 
                            ON usr. username =  garo.value
                WHERE
                    garo.section_value = ?";
        $params = array('users');
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,$params);
        return $result; 
    }
    public function getActiveModules(){
        $sql    = "SELECT * FROM modules";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql);
        return $result;
    }
    public function getGroups(){
        $sql    = "SELECT * FROM gacl_aro_groups WHERE parent_id > 0";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql);
        return $result;
    }
    public function getGroupAcl($module_id){
        $sql    = "SELECT * FROM module_acl_group_settings WHERE module_id = ? AND allowed =?";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($module_id,1));
        return $result;
    }
    public function deleteGroupACL($module_id,$section_id){
        $sql    = "DELETE FROM module_acl_group_settings WHERE module_id = ? AND section_id = ? ";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($module_id,$section_id));
    }
    public function deleteUserACL($module_id,$section_id){
        $sql    = "DELETE FROM module_acl_user_settings WHERE module_id = ? AND section_id = ? ";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($module_id,$section_id));
    }
    public function insertGroupACL($module_id,$group_id,$section_id,$allowed){
        $sql    = "INSERT INTO module_acl_group_settings (module_id,group_id,section_id,allowed) VALUES (?,?,?,?)";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($module_id,$group_id,$section_id,$allowed));
    }
    public function insertuserACL($module_id,$user_id,$section_id,$allowed){
        $sql    = "INSERT INTO module_acl_user_settings(module_id,user_id,section_id,allowed) VALUES (?,?,?,?)";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($module_id,$user_id,$section_id,$allowed));
    }
    public function getAclDataUsers($section_id){
        $sql    = " SELECT 
                        usr_settings.*,
                        aromap.group_id   
                    FROM
                        `module_acl_user_settings` AS usr_settings 
                        LEFT JOIN `users_secure` AS usr 
                          ON usr_settings.`user_id` = usr.id 
                        LEFT JOIN `gacl_aro` AS aro 
                          ON aro.value = usr.username 
                        LEFT JOIN `gacl_groups_aro_map` AS aromap 
                          ON aromap.aro_id = aro.id 
                    WHERE 
                       usr_settings.`section_id` = ? AND aro.section_value = ?";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($section_id,'users'));
        return $result;
    }
    public function getAclDataGroups($section_id){
        $sql    = "SELECT * FROM module_acl_group_settings WHERE section_id =?";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($section_id));
        return $result;
    }
    public function deleteModuleGroupACL($module_id){
        $sql    = "DELETE FROM module_acl_group_settings WHERE module_id =?";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($module_id));
    }
    public function getSectionsInsertId(){
        $sql    = "SELECT MAX(section_id) AS max_id FROM module_acl_sections";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql);
        $max_id = 0;
        foreach($result as $row){
            $max_id = $row['max_id'];
        }
        $max_id++;
        return $max_id;
    }
    public function saveACLSections($module_id,$parent_id,$section_identifier,$section_name,$section_id){
        $sql        = "INSERT INTO module_acl_sections(section_id,section_name,parent_section,section_identifier,module_id) VALUES(?,?,?,?,?)";
        $obj        = new ApplicationTable;
        $result     = $obj->sqlQuery($sql,array($section_id,$section_name,$parent_id,$section_identifier,$module_id));
    }
    public function getModuleSections($module_id){
        $sql    = "SELECT * FROM module_acl_sections WHERE module_id = ?";
        $obj    = new ApplicationTable;
        $result = $obj->sqlQuery($sql,array($module_id));
        return $result;
    }
    
}
