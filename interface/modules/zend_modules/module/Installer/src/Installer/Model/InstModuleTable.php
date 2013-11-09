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
// 		Author:   Jacob T.Paul <jacob@zhservices.com>
//							Vipin Kumar <vipink@zhservices.com>
// +------------------------------------------------------------------------------+

namespace Installer\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use \Application\Model\ApplicationTable;

class InstModuleTable
{
    protected $tableGateway;
		protected $applicationTable;
    public function __construct(TableGateway $tableGateway){
      $this->tableGateway = $tableGateway;
      $adapter = \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter();
      $this->adapter              = $adapter;
      $this->resultSetPrototype   = new ResultSet();
      $this->applicationTable	    = new ApplicationTable;
    }
    
    /**
     * Get All Modules Configuration Settings
     * 
     * @return type
     */
    public function getConfigSettings($id)
    {
        $sql    = "SELECT * FROM module_configuration
														WHERE module_id =?";
        $params = array($id);
        $result = $this->applicationTable->sqlQuery($sql, $params);
        return $result;
    }
		public function installSQL($dir)
		{
			$sqltext = $dir . "/table.sql";
    	if ($sqlarray = @file($sqltext)) {
				$sql = implode("", $sqlarray);
				$sqla = split(";", $sql);
				$this->getInstallerTable()->testingDir($dir);
				foreach ($sqla as $sqlq) {
					if (strlen($sqlq) > 5) {
						$query    = rtrim("$sqlq");
						$result = $this->applicationTable->sqlQuery($query);
					}
				}		    
				return true;
    	}	else
    	    return true;
		}
		
		/**
		 * Save Configuration Settings
		 *
		 */
		public function saveSettings($fieldName, $fieldValue, $moduleId)
		{
      /** Check the field exist */
      $sql = "SELECT * FROM module_configuration
                        WHERE field_name = ?
                        AND module_id = ?";
      $params = array(
                $fieldName,
                $moduleId,
              );
      $result = $this->applicationTable->sqlQuery($sql, $params);
      if ($result->count() > 0) {
        $sql = "UPDATE module_configuration SET field_value = ?
                                            WHERE module_id = ?
                                            AND field_name = ?";
        $params = array(
                    $fieldValue,
                    $moduleId,
                    $fieldName,
                  );
        $result = $this->applicationTable->sqlQuery($sql, $params);
      } else {
          $sql = "INSERT INTO module_configuration SET field_name = ?, field_value = ?, module_id = ?";
          $params = array(
                      $fieldName,
                      $fieldValue,
                      $moduleId,
                  );
          $result = $this->applicationTable->sqlQuery($sql, $params);
      }
		}
	
    /**
     * Get the list of modules as per the the params passed
     * @param string 	$state 	1/0	Installation status
     * @param int 		$limit	Limit
     * @param int 		$offset Offset
     * @return boolean|Ambigous <boolean, multitype:>
     */
    public function fetchAll($state="0", $limit="unlimited", $offset="0"){
    	$all 		= array();
    	$stateMod 	= "";
    	if($state != "")
    		$stateMod = " where mod_active like \"$state\"";
    	$sql = "select * from modules $stateMod  order by mod_ui_order asc";
    	if ($limit != "unlimited")
    		$sql .= " limit $limit, $offset";
    	$res = sqlStatement($sql);
    	if($res){
    		while($m = sqlFetchArray($res)){
    			$mod = new InstModule();
    			$mod -> exchangeArray($m);
        		array_push($all,$mod);
    		}
    	}
    	
    	return $all;   	
        
    }
    
    /**
     * this will be used to register a module 
     * @param unknown_type $directory
     * @param unknown_type $rel_path
     * @param unknown_type $state
     * @param unknown_type $base
     * @return boolean
     */
    public function register($directory,$rel_path,$state=0, $base = "custom_modules" )
    {
    	/*$check = sqlQuery("select mod_active from modules where mod_directory='$directory'");*/
        $sql = "SELECT mod_active FROM modules WHERE mod_directory = ?";
        $params = array(
                   $directory,
                );
        $check = $this->applicationTable->sqlQuery($sql, $params);

        if ($check->count() == 0) {
    		$added = "";
    		$typeSet = "";
    		if($base != "custom_modules"){
    			$added = "module/";
    			$typeSet = "type=1,";
    		}
    		$lines = @file($GLOBALS['srcdir']."/../interface/modules/$base/$added$directory/info.txt");
    		if ($lines){
    			$name = $lines[0];
						}
						else
    			$name = $directory;
						
    		$uiname = ucwords(strtolower($directory));

                $sql = "INSERT INTO modules SET mod_name = ?,
                                                mod_active = ?, 
                                                mod_ui_name = ?, 
                                                mod_relative_link = ?,
                                                $typeSet 
                                                mod_directory = ?, 
                                                date=NOW()
                                                ";
                $params = array(
                   $name,
                   $state,
                   $uiname,
                   strtolower($rel_path),
                   mysql_escape_string($directory),
                );
                
                $result = $this->applicationTable->sqlQuery($sql, $params);
                $moduleInsertId = $result->getGeneratedValue();

    		/*$moduleInsertId = sqlInsert("insert into modules set
    				mod_name='$name',
    				mod_active='$state',
    				mod_ui_name= '$uiname',
    				mod_relative_link= '" . strtolower($rel_path) . "',".$typeSet."
				mod_directory='".mysql_escape_string($directory)."',
				date=NOW()
				");*/
		 
                if(file_exists($GLOBALS['srcdir']."/../interface/modules/$base/$added$directory/moduleSettings.php")){
                    $ModuleObject = 'modules_'.strtolower($directory);
                    $ModuleObjectTitle = 'Module '.ucwords($directory);
                    global $MODULESETTINGS;
                    include_once($GLOBALS['srcdir']."/../interface/modules/$base/$added$directory/moduleSettings.php");
                    foreach($MODULESETTINGS as $Settings=>$SettingsArray){
                        if($Settings=='ACL')
                                $SettingsVal =1;
                        elseif($Settings=='preferences')
                                $SettingsVal =2;
                        else
                                $SettingsVal =3;
                        $i = 0;
                        foreach($SettingsArray as $k=>$v){
                            if($SettingsVal==1){
                                    if($i==0)
                                            addObjectSectionAcl($ModuleObject, $ModuleObjectTitle);
                                    addObjectAcl($ModuleObject, $ModuleObjectTitle, $k, $v['menu_name']);
                                    $i++;
                            }
                            /*sqlStatement("INSERT INTO modules_settings VALUES (?,?,?,?,?)",array($moduleInsertId,$SettingsVal,$k,$v['menu_name'],$v['[path']));*/
                            $sql = "INSERT INTO modules_settings VALUES (?,?,?,?,?)";
                            $params = array($moduleInsertId,$SettingsVal,$k,$v['menu_name'],$v['[path']);
                            $result = $this->applicationTable->sqlQuery($sql, $params);
                        }
                    }
                }
                /*sqlStatement("INSERT INTO module_acl_sections VALUES (?,?,?,?)",array($moduleInsertId,$name,0,strtolower($directory)));*/
                $sql = "INSERT INTO module_acl_sections VALUES (?,?,?,?,?)";
                $params = array($moduleInsertId,$name,0,strtolower($directory),$moduleInsertId);
                $result = $this->applicationTable->sqlQuery($sql, $params);
                return $moduleInsertId;
    	}
    	return false;
    	
    }
    
    /**
     * get the list of all modules
     * @return multitype:
     */
    public function allModules(){
    	$sql    = "SELECT * FROM modules ORDER BY mod_ui_order ASC";
        $params = array();
        $result = $this->applicationTable->sqlQuery($sql, $params);
        return $result;
    }
    /**
     * get the list of all modules
     * @return multitype:
     */
    public function getInstalledModules(){
    	$all = array();
	
				$sql = "select * from modules where mod_active = ? order by mod_ui_order asc";
				$res =  $this->applicationTable->sqlQuery($sql,array("1"));
       
				if(count($res) > 0){
						foreach($res as $row)
	    {
		$mod = new InstModule();
								$mod -> exchangeArray($row);
		array_push($all,$mod);
	    }
	}	
    	return $all;    
    }
    
    /**
     * @param int $id
     * @param string $cols
     * @return Ambigous <boolean, unknown>
     */
    function getRegistryEntry ( $id, $cols = "" )
    {
    	$adapter 	= $this->adapter;
        $sql 		= new Sql($adapter);
	
	if($cols <> ""){
	    $colsArr	= explode(",",$cols);
	}
       
        $select = $sql->select();
	$where	= array('mod_id' => $id);
        $select->from("modules")
		->columns($colsArr)
		->where($where);
	
	$selectString 	= $sql->getSqlStringForSqlObject($select);
	
	//LOGGING QUERIES
	$parameter 	= array(
			    'query' 	=> $selectString,
			    'type'    	=> 1, // 1- for log to table ; 0 - for log file
			 );
        $obj = new ApplicationTable;
        $obj->log($parameter);

        $results 	= $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);
	
	$resultSet 	= new ResultSet();
	$resultSet->initialize($results);	
	$resArr		= $resultSet->toArray();
	$rslt 		= $resArr[0];
    	
    	$mod = new InstModule();
    	$mod -> exchangeArray($rslt);   
    	
	return $mod;
    }
	
    /**
     * Function to enable/disable a module
     * @param int 		$id		Module PK
     * @param string 	$mod	Status
     */
    function updateRegistered ( $id, $mod ) {
        if($mod == "mod_active=1"){
						$resp	= $this->checkDependencyOnEnable($id);
						
            if($resp['status'] == 'success' && $resp['code'] == '1') {
                $sql = "UPDATE modules SET mod_active = ?, 
                                            date = ? 
                                       WHERE mod_id = ?";
                $params = array(
                            1,
                            date('Y-m-d H:i:s'),
                            $id,
                         );
                $results   = $this->applicationTable->sqlQuery($sql, $params);
                
                /*$adapter = $this->adapter;
                $sql = new Sql($adapter);
                $update = $sql->update("modules");
                $fields	= array(
                                'mod_active' => "1",
                                'date' => date('Y-m-d H:i:s'));
                $where	= array('mod_id' => $id);
                $update->set($fields);
                $update->where($where);
                $selectString = $sql->getSqlStringForSqlObject($update);
                //LOGGING QUERIES
                $parameter 	= array(
                                                'query' 	=> $selectString,
                                                'type'    	=> 1, // 1- for log to table ; 0 - for log file
                                 );
                $obj = new ApplicationTable;
                $obj->log($parameter);
                $results = $adapter->query($selectString, $adapter::QUERY_MODE_EXECUTE);*/
            }
        }
        else if($mod == "mod_active=0"){
            $resp	= $this->checkDependencyOnDisable($id);	    
            if($resp['status'] == 'success' && $resp['code'] == '1') {
                $sql = "UPDATE modules SET mod_active = ?, 
                                            date = ? 
                                       WHERE mod_id = ?";
                $params = array(
                   0,
                   date('Y-m-d H:i:s'),
                   $id,
                );
                $results   = $this->applicationTable->sqlQuery($sql, $params);                
            }	 
        }
        else{
            /*$resp = sqlInsert("update modules set $mod,date=NOW() where mod_id=?",array($id));*/
            $sql = "UPDATE modules SET $mod, 
                                            date=NOW() 
                                       WHERE mod_id = ?";
                $params = array(
                   $id,
                );
                $resp   = $this->applicationTable->sqlQuery($sql, $params);
        }
	return $resp;
    }
    
    /**
     * Function to get ACL objects for module
     * @param int 		$mod_id		Module PK
     */
    public function getSettings($type,$mod_id){
      if($type=='ACL')
        $type = 1;
      elseif($type=='Hooks')
        $type = 3;
      else
        $type = 2;
      $all = array();
    	$sql = "SELECT ms.*,mod_directory FROM modules_settings AS ms LEFT OUTER JOIN modules AS m ON ms.mod_id=m.mod_id WHERE m.mod_id=? AND fld_type=?";
    	$res = sqlStatement($sql,array($mod_id,$type));
    	if($res){
    		while($m = sqlFetchArray($res)){
		    $mod = new InstModule();
		    $mod -> exchangeArray($m);
		    array_push($all,$mod);
    		}
    	}
    	return $all;
    }
    /**
     * Function to get Oemr User Group
     */
    public function getOemrUserGroup(){
      $all = array();
    	$sql = "SELECT * FROM gacl_aro_groups AS gag LEFT OUTER JOIN gacl_groups_aro_map AS ggam ON gag.id=ggam.group_id
      WHERE parent_id<>0 AND group_id IS NOT NULL GROUP BY id ";
    	$res = sqlStatement($sql);
    	if($res){
    		while($m = sqlFetchArray($res)){
		    $mod = new InstModule();
		    $mod -> exchangeArray($m);
		    array_push($all,$mod);
    		}
    	}
    	return $all;
    }
    /**
     * Function to get Oemr User Group and Aro Map
     */
    public function getOemrUserGroupAroMap(){
      $all = array();
    	$sql = "SELECT group_id,u.id AS id,CONCAT_WS(' ',CONCAT_WS(',',u.lname,u.fname),u.mname) AS user,u.username FROM gacl_aro_groups gag
      LEFT OUTER JOIN gacl_groups_aro_map AS ggam ON gag.id=ggam.group_id LEFT OUTER JOIN gacl_aro AS ga ON ggam.aro_id=ga.id
      LEFT OUTER JOIN users AS u ON u.username=ga.value WHERE group_id IS NOT NULL ORDER BY gag.id";
    	$res = sqlStatement($sql);
    	if($res){
    		while($m = sqlFetchArray($res)){
		    $all[$m['group_id']][$m['id']] = $m['user'];
    		}
    	}
    	return $all;
    }
    /**
     * Function to get Active Users
     */
    public function getActiveUsers(){
      $all = array();
    	$sql = "SELECT id,username,CONCAT_WS(' ',fname,mname,lname) AS USER FROM users WHERE active=1 AND username IS NOT NULL AND username<>''";
    	$res = sqlStatement($sql);
    	if($res){
    		while($m = sqlFetchArray($res)){
		    $all[$m['username']] = $m['USER'];
    		}
    	}
    	return $all;
    }
    public function getTabSettings($mod_id){
      $all = array();
    	$sql = "SELECT fld_type,COUNT(*) AS cnt  FROM modules_settings WHERE mod_id=? GROUP BY fld_type ORDER BY fld_type ";
    	$res = sqlStatement($sql,array($mod_id));
    	if($res){
    		while($m = sqlFetchArray($res)){
		    $all[$m['fld_type']] = $m['cnt'];
    		}
    	}
    	return $all;
    }
    /**
     *Function To Get Active ACL for this Module
     */
    public function getActiveACL($mod_id){
      $arr = array();
      $Section = sqlQuery("SELECT mod_directory FROM modules WHERE mod_id=?",array($mod_id));
      $aco = "modules_".$Section['mod_directory'];
      $MapRes = sqlStatement("SELECT * FROM gacl_aco_map WHERE section_value=?",array($aco));
      while($MapRow = sqlFetchArray($MapRes)){
        $aroRes = sqlStatement("SELECT acl_id,value,CONCAT_WS(' ',fname,mname,lname) AS user FROM gacl_aro_map LEFT OUTER JOIN users ON
                               value=username WHERE active=1 AND acl_id=?",array($MapRow['acl_id']));
        $i=0;
        while($aroRow = sqlFetchArray($aroRes)){
          $arr[$MapRow['value']][$i]['acl_id']  = $aroRow['acl_id'];
          $arr[$MapRow['value']][$i]['value']   = $aroRow['value'];
          $arr[$MapRow['value']][$i]['user']    = $aroRow['user'];
          $i++;
        }
      }
      return $arr;
    }
    /**
     *Function To Get Saved Hooks For this Module
     */
    public function getActiveHooks($mod_id){
      $all = array();
				$sql		= "SELECT msh.*,ms.menu_name FROM modules_hooks_settings AS msh LEFT OUTER JOIN modules_settings AS ms ON
                               obj_name=enabled_hooks AND ms.mod_id=msh.mod_id LEFT OUTER JOIN modules AS m ON msh.mod_id=m.mod_id 
										WHERE fld_type = ? AND mod_active = ? AND msh.mod_id = ? ";
				$res		= $this->applicationTable->sqlQuery($sql,array("3","1",$mod_id));
				foreach($res as $row)
				{
        $mod = new InstModule();
						$mod -> exchangeArray($row);
		    array_push($all,$mod);        
      }
      return $all;
    }
    /**
     * Function to Save Configurations
     */
    public function SaveConfigurations($post){
	foreach($post as $aco=>$acoArray){
	    $Arr 	= explode("_-_-_",$aco);
	    $acoSection = $Arr[0];
	    $acoValue 	= $Arr[1];
	    foreach($acoArray as $aroKey=>$aro){
		$ACLARR = sqlQuery("SELECT acl_id FROM gacl_aco_map WHERE section_value=? AND value=?",array($acoSection,$acoValue));
		if($ACLARR['acl_id']){
		    $aclSeq = $ACLARR['acl_id'];
		}
		else{
		    sqlStatement("UPDATE gacl_acl_seq SET id=LAST_INSERT_ID(id+1)");
		    $aclSeqArr 	= sqlQuery("SELECT id FROM gacl_acl_seq");
		    $aclSeq 	= $aclSeqArr['id'];
		    sqlStatement("INSERT INTO gacl_acl (id,section_value,allow,enabled,return_value,note)
				    VALUES(?,?,1,1,?,?)",array($aclSeq,'user','',''));
		    sqlStatement("INSERT INTO gacl_aco_map (acl_id,section_value,value) VALUES (?,?,?)",array($aclSeq,$acoSection,$acoValue));
		}
		sqlStatement("INSERT INTO gacl_aro_map (acl_id,section_value,value) VALUES (?,?,?)",array($aclSeq,'users',$aro));
	    }
	}
    }
        
      
    /**
     * Function to get Status of a Hook
     */
    public function getHookStatus($modId,$hookId,$hangerId){
				if($modId && $hookId && $hangerId){						
						$res	= $this->applicationTable->sqlQuery("select * FROM modules_hooks_settings WHERE mod_id = ? AND enabled_hooks = ? AND attached_to = ? ",array($modId,$hookId,$hangerId));
						foreach($res as $row)
						{
								$modArr	= $row;
						}
       
	if($modArr['mod_id'] <> ""){
	    return "1";
	}
	else{
	    return "0";
	}
      }
    }
    
    /**
     * Function to Delete ACL
     */
    public function DeleteAcl($post){
      if($post['aclID'] && $post['user']){
        sqlStatement("DELETE FROM gacl_aro_map WHERE acl_id=? AND value=?",array($post['aclID'],$post['user']));
      }
    }
		
    /**
     * Function to Delete Hooks
     */
    public function saveHooks($modId,$hookId,$hangerId){
			if($modId){						
				$this->applicationTable->sqlQuery("INSERT INTO modules_hooks_settings(mod_id, enabled_hooks, attached_to) VALUES (?,?,?) ",array($modId,$hookId,$hangerId));			
			}
    }
		
		/**
		 * Save Module Hook settings
		 */
		public function saveModuleHookSettings($modId,$hook)
		{
			$sql = "INSERT INTO modules_settings SET mod_id = ?,
																								fld_type = 3,
																								obj_name = ?,
																								menu_name = ?,
																								path = ?";
			$params = array(
										$modId,
										$hook['name'],
										$hook['title'],
										$hook['path'],
									);
			$this->applicationTable->sqlQuery($sql, $params);
		}
  
    /**
     * Function to Delete Hooks
     */
    public function DeleteHooks($post){
			if($post['hooksID']){						
				$this->applicationTable->sqlQuery("DELETE FROM modules_hooks_settings WHERE id = ? ",array($post['hooksID']));			
			}
    }
    
		/**
     * Function to Delete Module Hooks
     */
    public function deleteModuleHooks($modId){
			if($modId){
				//DELETE MODULE HOOKS							
				$this->applicationTable->sqlQuery("DELETE FROM modules_hooks_settings WHERE mod_id = ? ",array($modId));
					
			}
    }
    
    public function checkDependencyOnEnable($mod_id)
    {
			$retArray	= array();
			$modDirectory	= $this->getModuleDirectory($mod_id);
			if($modDirectory){
				//GET DEPENDED MODULES OF A MODULE HOOKS FROM A FUNCTION IN ITS MODEL CONFIGURATION CLASS
				$depModules	= $this->getDependedModulesByDirectoryName($modDirectory);
				$requiredModules	= array();
				if(count($depModules) > 0){
					foreach($depModules as $depModule){
						if($depModule <> ""){																						
							$res	= $this->getModuleStatusByDirectoryName($moduleDir);																								
							if($res <> "Enabled"){
								$requiredModules[]	= $depModule;
							}	
						}						
					}			
				}
		
				if(count($requiredModules) > 0) {
					$retArray['status']	= "failure";
					$retArray['code']	= "200";
					$retArray['value']	= $requiredModules;
				} else {
						$retArray['status']	= "success";
						$retArray['code']	= "1";
						$retArray['value']	= "";
				}
	    } else {
				$retArray['status']	= "failure";
				$retArray['code']	= "400";
				$retArray['value']	= "Module Directory not found";
			}
			return $retArray;
    }
    
    
    public function checkDependencyOnDisable($mod_id)
    {
			$retArray	= array();
			$depFlag	= "0";
			$modArray	= $this->getInstalledModules();
	
			//GET MODULE DIRECTORY OF DISABLING MODULE
			$modDirectory	= $this->getModuleDirectory($mod_id);
			$usedModArr	= array();
			if(count($modArray) > 0){
				//LOOP THROUGH INSTALLED MODULES
				foreach($modArray as $module) {
					if($module->modId <> ""){
						//GET MODULE DEPENDED MODULES
						$InstalledmodDirectory	= $this->getModuleDirectory($module->modId);
						$depModArr	= $this->getDependencyModulesDir($module->modId);
						if(count($depModArr) > 0){
							//LOOP THROUGH DEPENDENCY MODULES
							//CHECK IF THE DISABLING MODULE IS BEING DEPENDED BY OTHER INSTALLED MODULES
							foreach($depModArr as $depModule) {
								if($modDirectory == $depModule){
									$depFlag	= "1";
									//break(2);
									$usedModArr[] = $InstalledmodDirectory;
								}
							}		
						}
					}
				}
			}
			if($depFlag == "0"){
					$retArray['status']	= "success";
					$retArray['code']	= "1";
					$retArray['value']	= "";
			} else {
				$usedModArr		= array_unique($usedModArr);
				$multiple = "";
				if(count($usedModArr) > 1) {
					$multiple	= "s";
				}
				$usedModules	= implode(",",$usedModArr);
				$retArray['status']	= "failure";
				$retArray['code']	= "200";
				$retArray['value']	= "Dependency Problem : This module is being used by ".$usedModules." module".$multiple;
			}
			return $retArray;
    }
    
    public function getDependencyModules($mod_id)
    {
			$reader = new Ini();
			$modDirname	= $this->getModuleDirectory($mod_id);
			if($modDirname <> ""){			
				$depModuleStatusArr	= array();
				//GET DEPENDED MODULES OF A MODULE HOOKS FROM A FUNCTION IN ITS MODEL CONFIGURATION CLASS
				$depModulesArr	= $this->getDependedModulesByDirectoryName($modDirname);
				$ret_str="";
				if(count($depModulesArr)>0){
					$count = 0;
					foreach($depModulesArr as $modDir){
						if($count > 0){
							$ret_str.= ", ";
						}
						$ret_str.= trim($modDir)."(".$this->getModuleStatusByDirectoryName($modDir).")";
						$count++;
					}			
				}		
			}		
			return $ret_str;		
    }
    
    public function getDependencyModulesDir($mod_id)
    {
			$depModulesArr	= array();
			$modDirectory 	= $this->getModuleDirectory($mod_id);
			if($modDirectory){			
					//GET DEPENDED MODULES OF A MODULE HOOKS FROM A FUNCTION IN ITS MODEL CONFIGURATION CLASS
					$depModulesArr	= $this->getDependedModulesByDirectoryName($modDirectory);							 
			}		
			return $depModulesArr;		
    }
    
    public function getModuleStatusByDirectoryName($moduleDir)
    {
				$res	= $this->applicationTable->sqlQuery("select mod_active,mod_directory from modules where mod_directory = ? ",array(trim($moduleDir)));
				foreach($res as $row) {
						$check	= $row;
				}
	
			if((count($check) > 0)&& is_array($check)){
				if($check['mod_active'] == "1"){
					return "Enabled";
				} else {
					return "Disabled";
				}		
			} else {
				return "Missing";
			}
    }
    
    public function getHangers()
    {
			return array(
					'reports' 	=> "Reports",
					'encounter' => "Encounter",
					'demographics' => "Demographics",
				);
		}
    
    public function getModuleDirectory($mod_id)
    {
			$moduleName	= "";
			if($mod_id <> ""){	
				$res	= $this->applicationTable->sqlQuery("SELECT mod_directory FROM modules WHERE mod_id = ? ",array($mod_id));
				foreach($res as $row) {
					$modArr	= $row;
				}
				if($modArr['mod_directory'] <> ""){			
					$moduleName = $modArr['mod_directory'];
				}		
				return $moduleName;
			}
    }
    
    public function checkModuleHookExists($mod_id,$hookId)
    {  
			$res	= $this->applicationTable->sqlQuery("SELECT obj_name FROM modules_settings WHERE mod_id = ? AND fld_type = ? AND obj_name = ? ",array($mod_id,"3",$hookId));
			foreach($res as $row){
					$modArr	= $row;
			}			
				if($modArr['obj_name'] <> ""){
				return "1";
			} else {
				return "0";
			}
    }
		
		//GET MODULE HOOKS FROM A FUNCTION IN CONFIGURATION MODEL CLASS
		public function getModuleHooks($moduleDirectory)
		{	
			$phpObjCode 	= str_replace('[module_name]', $moduleDirectory, '$objHooks  = new \[module_name]\Model\Configuration();');
			$className		= str_replace('[module_name]', $moduleDirectory, '\[module_name]\Model\Configuration');
			if(class_exists($className)){
					eval($phpObjCode);
			}
			$hooksArr	= array();
			if($objHooks){
				//$obj	= new \Lab\Model\Configuration();
				$hooksArr	= $objHooks->getHookConfig();
			}
			return $hooksArr;
		}
		
		
		//GET MODULE ACL SECTIONS FROM A FUNCTION IN CONFIGURATION MODEL CLASS
		public function getModuleAclSections($moduleDirectory)
		{	
				$phpObjCode 	= str_replace('[module_name]', $moduleDirectory, '$objHooks  = new \[module_name]\Model\Configuration();');
				$className		= str_replace('[module_name]', $moduleDirectory, '\[module_name]\Model\Configuration');
				
				if(class_exists($className)){
						eval($phpObjCode);
				}
				
				$aclArray	= array();
				if($objHooks){
						$aclArray	= $objHooks->getAclConfig();
				}
				return $aclArray;
		}
		
		public function insertAclSections($acl_data,$mod_dir,$module_id){
				$obj    = new ApplicationTable;
				foreach($acl_data as $acl){
						$identifier = $acl['section_id'];
						$name				= $acl['section_name'];
						$parent			= $acl['parent_section'];
						
						$sql_parent = "SELECT section_id FROM module_acl_sections WHERE section_identifier =?";
						$result = $obj->sqlQuery($sql_parent,array($parent));
						$parent_id = 0;
						foreach($result as $row){
								$parent_id = $row['section_id'];
						}
						$sql_max_id = "SELECT MAX(section_id) as max_id FROM module_acl_sections";
						$result = $obj->sqlQuery($sql_max_id);
						$section_id = 0;
						foreach($result as $row){
								$section_id = $row['max_id'];
						}
						$section_id++;
						$sql_if_exists = "SELECT COUNT(*) as count FROM module_acl_sections WHERE section_identifier = ? AND parent_section =?";
						$result = $obj->sqlQuery($sql_if_exists,array($identifier,$parent_id));
						$exists = 0;
						foreach($result as $row){
								if($row['count'] > 0) $exists =1;
						}
						if($exists) continue;
						$sql_insert = "INSERT INTO module_acl_sections (`section_id`,`section_name`,`parent_section`,`section_identifier`,`module_id`) VALUES(?,?,?,?,?)";
						$obj->sqlQuery($sql_insert,array($section_id,$name,$parent_id,$identifier,$module_id));
				}
				
				$sql = "SELECT COUNT(mod_id) AS count FROM modules_settings WHERE mod_id = ? AND fld_type = 1";
				$result = $obj->sqlQuery($sql,array($module_id));
				$exists = 0;
				foreach($result as $row){
						if($row['count'] > 0) $exists =1;
				}
				if(!$exists){
						$sql = "INSERT INTO modules_settings(`mod_id`,`fld_type`,`obj_name`,`menu_name`) VALUES(?,?,?,?)";
						$result = $obj->sqlQuery($sql,array($module_id,1,$mod_dir,$mod_dir));
				}
		}
		public function deleteACLSections($module_id){
				$obj    = new ApplicationTable;
				$sql 		= "DELETE FROM module_acl_sections WHERE module_id =? AND parent_section <> 0";
				$obj->sqlQuery($sql,array($module_id));
				
				$sqsl		= "DELETE FROM modules_settings WHERE mod_id =? AND fld_type = 1";
				$obj->sqlQuery($sql,array($module_id));
		}
		
		//GET DEPENDED MODULES OF A MODULE FROM A FUNCTION IN CONFIGURATION MODEL CLASS
		public function getDependedModulesByDirectoryName($moduleDirectory)
		{	
				$phpObjCode 	= str_replace('[module_name]', $moduleDirectory, '$objHooks  = new \[module_name]\Model\Configuration();');
				$className		= str_replace('[module_name]', $moduleDirectory, '\[module_name]\Model\Configuration');
				
				if(class_exists($className)){
						eval($phpObjCode);
				}
				
				$retArr	= array();
				if($objHooks){
						$retArr	= $objHooks->getDependedModulesConfig();
				}
				return $retArr;
		}
		
		/**
     * Function to Save Module Hooks
     */
		public function saveModuleHooks($modId,$hookId,$hookTitle,$hookPath){				
				if($modId){
						$this->applicationTable->sqlQuery("INSERT INTO modules_settings(mod_id, fld_type, obj_name, menu_name, path) VALUES (?,?,?,?,?) ",array($modId,"3",$hookId,$hookTitle,$hookPath));			
				}
    }
		
		/**
     * Function to Save Module Hooks
     * 
     */
		public function deleteModuleHookSettings($modId){
				if($modId){						
						$this->applicationTable->sqlQuery("DELETE FROM modules_settings WHERE mod_id = ? AND fld_type = ?",array($modId,"3"));			
				}
    }
}
?>
