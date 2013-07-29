<?php
/**
 * library/parse_patient_xml.php Functions related to patient CCR/CCD/CCDA parsing.
 *
 * Functions related to patient CCR/CCD/CCDA parsing and insert/update to corresponding tables.
 *
 * Copyright (C) 2013 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Eldho Chacko <eldho@zhservices.com>
 * @author  Ajil P M <ajilpm@zhservices.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

function parseXmlStream($content,$field_mapping){
	$res = array();
	$xml = new DOMDocument;
	$xml->loadXML($content);
	$xpath = new DOMXpath($xml);
	$rootNamespace = $xml->lookupNamespaceUri($xml->namespaceURI);
	$xpath->registerNamespace('x',$rootNamespace);
	foreach($field_mapping as $skey=>$sval){
		$path = preg_replace("/\/([a-zA-Z])/","/x:$1",$skey);
		$elements = $xpath->query($path);
		if(!is_null($elements)){
			$ele_cnt = 1;
			foreach($elements as $element){
				foreach($sval as $field => $innerpath){
					$ipath = preg_replace(array("/^([a-zA-Z])/","/\/([a-zA-Z])/"),array("x:$1","/x:$1"),$innerpath);
					$val = $xpath->query($ipath, $element)->item(0)->textContent;
					if($val){
            $field_details = explode(':',$field);
						$res[$field_details[0]][$ele_cnt][$field_details[1]] = $val;
					}
				}
				$ele_cnt++;
			}
		}
	}
	return $res;
}

function insert_ccr_into_audit_data($var){
  $audit_master_id_to_delete = $var['audit_master_id_to_delete'];
  $approval_status = $var['approval_status'];
  $type = $var['type'];
  $ip_address = $var['ip_address'];
  $field_name_value_array = $var['field_name_value_array'];
  $entry_identification_array = $var['entry_identification_array'];
  if($audit_master_id_to_delete){
    $qry = "DELETE from audit_details WHERE audit_master_id=?";
    sqlStatement($qry,array($audit_master_id_to_delete));
    $qry = "DELETE from audit_master WHERE id=?";
    sqlStatement($qry,array($audit_master_id_to_delete));
  }
  $master_query = "INSERT INTO audit_master SET pid = ?,approval_status = ?,ip_address = ?,type = ?";
  $audit_master_id = sqlInsert($master_query,array(0,$approval_status,$ip_address,$type));
  $detail_query = "INSERT INTO `audit_details` (`table_name`, `field_name`, `field_value`, `audit_master_id`, `entry_identification`) VALUES ";
  $detail_query_array = '';
  foreach($field_name_value_array as $key=>$val){
    foreach($field_name_value_array[$key] as $cnt=>$field_details){
      foreach($field_details as $field_name=>$field_value){
        $detail_query .= "(? ,? ,? ,? ,?),";
        $detail_query_array[] = $key;
        $detail_query_array[] = trim($field_name);
        $detail_query_array[] = trim($field_value);
        $detail_query_array[] = $audit_master_id;
        $detail_query_array[] = trim($entry_identification_array[$key][$cnt]);
      }
    }
  }
  $detail_query = substr($detail_query, 0, -1);
  $detail_query = $detail_query.';';
  sqlInsert($detail_query,$detail_query_array);
}

function insert_patient($audit_master_id){
	$prow = sqlQuery("SELECT IFNULL(MAX(pid)+1,1) AS pid FROM patient_data");
	$pid = $prow['pid'];
	$res = sqlStatement("SELECT DISTINCT ad.table_name,entry_identification FROM audit_master as am,audit_details as ad WHERE am.id=ad.audit_master_id AND am.approval_status = '1' AND am.id=? AND am.type=11 ORDER BY ad.id",array($audit_master_id));
	$tablecnt = sqlNumRows($res);
	while($row = sqlFetchArray($res)){
		$resfield = sqlStatement("SELECT * FROM audit_details WHERE audit_master_id=? AND table_name=? AND entry_identification=?",array($audit_master_id,$row['table_name'],$row['entry_identification']));
		$table = $row['table_name'];
		$newdata = array();
		while($rowfield = sqlFetchArray($resfield)){
			if($table == 'patient_data'){
				if($rowfield['field_name'] == 'DOB'){
					$newdata['patient_data'][$rowfield['field_name']] = substr($rowfield['field_value'],0,10);
				}else{
					$newdata['patient_data'][$rowfield['field_name']] = $rowfield['field_value'];
				}
			}elseif($table == 'lists1'){
				$newdata['lists1'][$rowfield['field_name']] = $rowfield['field_value'];
			}elseif($table == 'lists2'){
				$newdata['lists2'][$rowfield['field_name']] = $rowfield['field_value'];
			}elseif($table == 'prescriptions'){
				$newdata['prescriptions'][$rowfield['field_name']] = $rowfield['field_value'];
			}elseif($table == 'immunizations'){
				$newdata['immunizations'][$rowfield['field_name']] = $rowfield['field_value'];
			}elseif($table == 'procedure_result'){
				$newdata['procedure_result'][$rowfield['field_name']] = $rowfield['field_value'];
			}elseif($table == 'procedure_type'){
				$newdata['procedure_type'][$rowfield['field_name']] = $rowfield['field_value'];
			}elseif($table == 'misc_address_book'){
				$newdata['misc_address_book'][$rowfield['field_name']] = $rowfield['field_value'];
			}elseif($table == 'documents'){
				$newdata['documents'][$rowfield['field_name']] = $rowfield['field_value'];
			}
		}
		if($table == 'patient_data'){
			updatePatientData($pid,$newdata['patient_data'],true);
		}elseif($table == 'lists1'){
			sqlInsert("INSERT INTO lists(".
				"pid,diagnosis,activity".
				") VALUES (".
				"'".add_escape_custom($pid)."',".
				"'".add_escape_custom($newdata['lists1']['diagnosis'])."',".
				"'".add_escape_custom($newdata['lists1']['activity'])."')"
			);
		}elseif($table == 'lists2'){
			sqlInsert("INSERT INTO lists(".
				"pid,date,type,title,diagnosis,reaction".
				") VALUES (".
				"'".add_escape_custom($pid)."',".
				"'".add_escape_custom($newdata['lists2']['date'])."',".
				"'".add_escape_custom($newdata['lists2']['type'])."',".
				"'".add_escape_custom($newdata['lists2']['title'])."',".
        "'".add_escape_custom($newdata['lists2']['diagnosis'])."',".
				"'".add_escape_custom($newdata['lists2']['reaction'])."')"
			);
		}elseif($table == 'prescriptions'){
			sqlInsert("INSERT INTO prescriptions(".
				"patient_id,date_added,active,drug,size,form,quantity".
				") VALUES (".
				"'".add_escape_custom($pid)."',".
				"'".add_escape_custom($newdata['prescriptions']['date_added'])."',".
				"'".add_escape_custom($newdata['prescriptions']['active'])."',".
				"'".add_escape_custom($newdata['prescriptions']['drug'])."',".
				"'".add_escape_custom($newdata['prescriptions']['size'])."',".
				"'".add_escape_custom($newdata['prescriptions']['form'])."',".
				"'".add_escape_custom($newdata['prescriptions']['quantity'])."')"
			);
		}elseif($table == 'immunizations'){
			sqlInsert("INSERT INTO immunizations(".
				"patient_id,administered_date,note".
				") VALUES (".
				"'".add_escape_custom($pid)."',".
				"'".add_escape_custom($newdata['immunizations']['administered_date'])."',".
				"'".add_escape_custom($newdata['immunizations']['note'])."')"
			);
		}elseif($table == 'procedure_result'){
			/*sqlInsert("INSERT INTO procedure_result(".
				"date,result,abnormal".
				") VALUES (".
				"'".add_escape_custom($newdata['procedure_result']['date'])."',".
				"'".add_escape_custom($newdata['procedure_result']['result'])."',".
				"'".add_escape_custom($newdata['procedure_result']['abnormal'])."')"
			);*/
		}elseif($table == 'procedure_type'){
			/*sqlInsert("INSERT INTO procedure_type(".
				"name".
				") VALUES (".
				"'".add_escape_custom($newdata['procedure_type']['name'])."')"
			);*/
		}elseif($table == 'misc_address_book'){
			sqlInsert("INSERT INTO misc_address_book(".
				"lname,fname,street,city,state,zip,phone".
				") VALUES (".
				"'".add_escape_custom($newdata['misc_address_book']['lname'])."',".
				"'".add_escape_custom($newdata['misc_address_book']['fname'])."',".
				"'".add_escape_custom($newdata['misc_address_book']['street'])."',".
				"'".add_escape_custom($newdata['misc_address_book']['city'])."',".
				"'".add_escape_custom($newdata['misc_address_book']['state'])."',".
				"'".add_escape_custom($newdata['misc_address_book']['zip'])."',".
				"'".add_escape_custom($newdata['misc_address_book']['phone'])."')"
			);
		}elseif($table == 'documents'){
			sqlQuery("UPDATE documents SET foreign_id = ? WHERE id =? ",array($pid,$newdata['documents']['id']));
		}
	}
	sqlQuery("UPDATE audit_master SET approval_status=2 WHERE id=?",array($audit_master_id));
}

function createAuditArray($am_id,$table_name){
	if(strpos($table_name,',')){
		$tables = explode(',',$table_name);
		$arr = array($am_id);
		$table_qry = "";
		for($i=0;$i<count($tables);$i++){
			$table_qry .= "?,";
			array_unshift($arr,$tables[$i]);
		}
		$table_qry = substr($table_qry,0,-1);
		$query = sqlStatement("SELECT * FROM audit_master am LEFT JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name IN ($table_qry) 
		WHERE am.id = ? AND am.type = 11 AND am.approval_status = 1 ORDER BY ad.entry_identification,ad.field_name",$arr);
	}else{
		$query = sqlStatement("SELECT * FROM audit_master am LEFT JOIN audit_details ad ON ad.audit_master_id = am.id AND ad.table_name = ? 
			WHERE am.id = ? AND am.type = 11 AND am.approval_status = 1 ORDER BY ad.entry_identification,ad.field_name",array($table_name,$am_id));
	}
	$result = array();
	while($res = sqlFetchArray($query)){
		$result[$table_name][$res['entry_identification']][$res['field_name']] = $res['field_value'];
	}
	return $result;
}

function insertApprovedData($data){
  $patient_data_fields = '';
  $patient_data_values = array();
	foreach($data as $key=>$val){
		if(substr($key,-4) == '-sel'){
			if(is_array($val)){
				for($i=0;$i<count($val);$i++){
					if($val[$i] == 'insert'){
						if(substr($key,0,-4) == 'lists1'){
							if($_REQUEST['lists1-activity'][$i] == 'Active'){
								$activity = 1;
							}elseif($_REQUEST['lists1-activity'][$i] == 'Inactive'){
								$activity = 0;
							}
              $query = "INSERT INTO lists (pid,diagnosis,activity) VALUES (?,?,?)";
              sqlQuery($query,array($_REQUEST['pid'],$_REQUEST['lists1-diagnosis'][$i],$activity));
						}elseif(substr($key,0,-4) == 'lists2'){
              $query = "INSERT INTO lists (pid,date,type,title,diagnosis,reaction) VALUES (?,?,?,?,?,?)";
              sqlQuery($query,array($_REQUEST['pid'],$_REQUEST['lists2-date'][$i],$_REQUEST['lists2-type'][$i],$_REQUEST['lists2-title'][$i],$_REQUEST['lists2-diagnosis'][$i],$_REQUEST['lists2-reaction'][$i]));
						}elseif(substr($key,0,-4) == 'prescriptions'){
							if($_REQUEST['prescriptions-active'][$i] == 'Active'){
								$active = 1;
							}elseif($_REQUEST['prescriptions-active'][$i] == 'Inactive'){
								$active = 0;
							}
              $query = "INSERT INTO prescriptions (patient_id,date_added,active,drug,size,form,quantity) VALUES (?,?,?,?,?,?,?)";
              sqlQuery($query,array($_REQUEST['pid'],$_REQUEST['prescriptions-date_added'][$i],$active,$_REQUEST['prescriptions-drug'][$i],$_REQUEST['prescriptions-size'][$i],$_REQUEST['prescriptions-form'][$i],$_REQUEST['prescriptions-quantity'][$i]));
						}elseif(substr($key,0,-4) == 'immunizations'){
              $query = "INSERT INTO immunizations (patient_id,administered_date,note) VALUES (?,?,?)";
              sqlQuery($query,array($_REQUEST['pid'],$_REQUEST['immunizations-administered_date'][$i],$_REQUEST['immunizations-note'][$i]));
						}elseif(substr($key,0,-4) == 'procedure_result'){
              //$query = "INSERT INTO procedure_type (name) VALUES (?)";
              //sqlQuery($query,array($_REQUEST['procedure_type-name'][$i]));
              //$query = "INSERT INTO procedure_result (date,result,abnormal) VALUES (?,?,?)";
              //sqlQuery($query,array($_REQUEST['procedure_result-date'][$i],$active,$_REQUEST['procedure_result-abnormal'][$i]));
						}
					}elseif($val[$i] == 'update'){
						if(substr($key,0,-4) == 'lists1'){
							if($_REQUEST['lists1-activity'][$i] == 'Active'){
								$activity = 1;
							}elseif($_REQUEST['lists1-activity'][$i] == 'Inactive'){
								$activity = 0;
							}
              $query = "UPDATE lists SET diagnosis=?,activity=? WHERE pid=? AND diagnosis=?";
              sqlQuery($query,array($_REQUEST['lists1-diagnosis'][$i],$activity,$_REQUEST['pid'],$_REQUEST['lists1-old-diagnosis'][$i]));
						}
					}
				}
			}else{
				if(substr($key,0,12) == 'patient_data'){
					if($val == 'update'){
						$var_name = substr($key,0,-4);
						$field_name = substr($var_name,13);
            $patient_data_fields .= $field_name.'=?,';
            array_push($patient_data_values,$_REQUEST[$var_name]);
					}
				}
			}
		}
	}
	if(count($patient_data_values) > 0){
    array_push($patient_data_values,$_REQUEST['pid']);
    $patient_data_fields = substr($patient_data_fields,0,-1);
    $query = "UPDATE patient_data SET $patient_data_fields WHERE pid=?";
    sqlQuery($query,$patient_data_values);
	}
  sqlQuery("UPDATE documents SET foreign_id = ? WHERE id =? ",array($_REQUEST['pid'],$_REQUEST['doc_id']));
}

?>
