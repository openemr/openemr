<?php
/** **************************************************************************
 *	WMTCASE.CLASS.PHP
 *	This file contains a print class for use with any print form
 *
 *  NOTES:
 *  1) __CONSTRUCT - always uses the ID to retrieve data 
 *  2) GET - uses alternate selectors to find and return associated object
 * 
 *  @version 1.0
 *  @copyright Williams Medical Technologies, Inc.
 *  @author Rich Genandt <rgenandt@gmail.com>
 * 
 *************************************************************************** */

/** 
 * Provides a partial representation of the patient data record This object
 * does NOT include all of the fields associated with the core patient data
 * record and should NOT be used for database updates.  It is intended only
 * for retrieval of partial patient information primarily for display 
 * purposes (reports for example).
 *
 */

if(!isset($GLOBALS['srcdir'])) include_once('../../globals.php');

if(!class_exists('wmtCase')) {

class wmtCase {

    public function __construct() {
    }

    // Fetch Alert logs by param
    public static function fetchAlertLogsByParam($data = array(), $limit = '') {

        $whereParam = array();
        $bind = array();

        foreach ($data as $dk => $dItem) {
            if(!empty($dItem)) {
                $whereParam[] = $dk . " = ? ";  
                $bind[] = $dItem;
            }
        }

        $whereStr = "";
        if(!empty($whereParam)) {
            $whereStr = " WHERE " . implode(" AND ", $whereParam);
        }

        $sql = "SELECT fl.*, u.username as user_name FROM form_value_logs As fl LEFT JOIN users As u ON u.id = fl.username ".$whereStr." ORDER BY date DESC ";

        if(!empty($limit)) {
            $sql .= ' LIMIT '.$limit;
        }

        $lres=sqlStatement($sql, $bind);
        $result = array();

        while ($lrow = sqlFetchArray($lres)) {
            $result[] = $lrow;
        }
        return $result;
    }

    public static function fetchCaseAlertLogs($case_id, $limit = '') {
        $sql = "SELECT fl.*, u.username as user_name  FROM case_form_value_logs As fl LEFT JOIN users As u ON u.id = fl.user WHERE fl.case_id = ? ORDER BY created_date DESC ";

        if(!empty($limit)) {
            $sql .= ' LIMIT '.$limit;
        }

        $lres=sqlStatement($sql, array($case_id));
        $result = array();

        while ($lrow = sqlFetchArray($lres)) {
            $result[] = $lrow;
        }
        return $result;
    }

    // Get Insurance Companies Data
    public static function getInsuranceCompaniesData($id, $pid) {
        $query = "SELECT ic.* FROM insurance_data AS ins LEFT JOIN insurance_companies AS ic ON ins.`provider` = ic.`id` WHERE ins.`id` = ? AND ins.`pid` = ? ";

        $fres = sqlStatement($query, array($id, $pid));
        $row = sqlFetchArray($fres);

        return $row;
    }

    public static function getExtraInfoOfLPC($lpcData = array()) {
        $lpctextData = array();

        if(!empty($lpcData)) {
            $lpctextData[] = $lpcData['lname'].', '.$lpcData['fname'];

            if(!empty($lpcData['organization'])) {
                $lpctextData[] = $lpcData['organization'];
            }

            if(!empty($lpcData['email'])) {
                $lpctextData[] = $lpcData['email'];
            }

            $lpc_addr_data = array();
            if(!empty($lpcData['street'])) {
                $lpc_addr_data[] = $lpcData['street'];
            }

            if(!empty($lpcData['streetb'])) {
                $lpc_addr_data[] = $lpcData['streetb'];
            }

            if(!empty($lpcData['city'])) {
                $lpc_addr_data[] = $lpcData['city'];
            }

            if(!empty($lpcData['state'])) {
                $lpc_addr_data[] = $lpcData['state'];
            }

            if(!empty($lpcData['zip'])) {
                $lpc_addr_data[] = $lpcData['zip'];
            }

            if(!empty($lpc_addr_data)) {
                $lpctextData[] = implode(", ", $lpc_addr_data);
            }
        }

        if(!empty($lpctextData)) {
            $lpctextData = implode(", ", $lpctextData);
        } else {
            $lpctextData = "";
        }

        return $lpctextData;
    }

    public static function getUsersBy($thisField, $special_title='', $whereCon = array(), $display_extra = '', $allow_empty=true) {
        $whereStr = '';
        if(!empty($whereCon)) {
            if(is_array($whereCon)) {
                foreach ($whereCon as $wck => $wItem) {
                    if(is_array($wItem)) {
                        $whereStr .= "AND " . $wck . " IN('" . implode("','", $wItem) . "') ";
                    } else {
                        $whereStr .= "AND " . $wck . " = '" . $wItem . "' ";
                    }
                }
            } else {
                $whereStr = $whereCon;
            }
        }
        $sql = "SELECT id, lname, fname, mname, specialty";
        if($display_extra) { $sql .= ", $display_extra"; }
        $sql .= " FROM users WHERE active=1 AND (lname != '' AND fname != '') ".
            " $whereStr ORDER BY lname";
            
        $rlist= sqlStatementNoLog($sql);

        if($allow_empty) {
            echo "<option value=''";
            if(!$thisField) echo " selected='selected'";
                echo ">&nbsp;</option>";
            }
        if($special_title) {
            echo "<option value='-1'";
        if($thisField == -1) echo " selected='selected'";
            echo ">$special_title</option>";
        }
        while ($rrow= sqlFetchArray($rlist)) {
            echo "<option value='" . $rrow['id'] . "'";
            if($thisField == $rrow['id']) echo " selected='selected'";
            echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
                if($display_extra) {
                    $keys = explode(',',$display_extra);
                    foreach($keys as $extra) {
                        $extra = trim($extra);
                        if($extra) { echo ' - '.$rrow[$extra]; }
                    }
                }
            echo "</option>";
        }
    }

    public static function referringSelect($thisField, $special_title='', $specialty='', $abook_type = array(), $display_extra = '', $allow_empty=true, $extInfo = false) {
        if($specialty) {
            $specialty = "AND UPPER(specialty) LIKE UPPER('%$specialty%')";
        }

        if(!empty($abook_type)) {
            $abook_type = "AND abook_type IN ('". implode("','", $abook_type) ."')";
        }

      $sql = "SELECT *";
        if($display_extra) { $sql .= ", $display_extra"; }
        $sql .= " FROM users WHERE active=1 ".
            " $specialty $abook_type ORDER BY lname";
      $rlist= sqlStatementNoLog($sql);
        if($allow_empty) {
        echo "<option value=''";
        if(!$thisField) echo " selected='selected'";
        echo ">&nbsp;</option>";
        }
        if($special_title) {
        echo "<option value='-1'";
        if($thisField == -1) echo " selected='selected'";
        echo ">$special_title</option>";
        }
        while ($rrow= sqlFetchArray($rlist)) {
            $extInfoTxt = '';

            if($extInfo === true) {
                $extInfoTxt = self::getExtraInfoOfLPC($rrow);
                if(!empty($extInfoTxt)) {
                    $extInfoTxt = base64_encode($extInfoTxt);
                    $extInfoTxt = "data-extinfo='$extInfoTxt'";
                }
            }

            echo "<option $extInfoTxt value='" . $rrow['id'] . "'";
            if($thisField == $rrow['id']) echo " selected='selected'";
            echo ">" . $rrow['lname'].', '.$rrow['fname'].' '.$rrow['mname'];
                if($display_extra) {
                    $keys = explode(',',$display_extra);
                    foreach($keys as $extra) {
                        $extra = trim($extra);
                        if($extra) { echo ' - '.$rrow[$extra]; }
                    }
                }
            echo "</option>";
         }
    }

    // Check Is Insurance is liable
    public static function checkIsLiableForPiCase($ids, $pid) {
        $types = array('20', '21', '16');

        $status = false;
        foreach ($ids as $i => $id) {
            if(isset($id) && $id != 0) {
                $ins_data = self::getInsuranceCompaniesData($id, $pid);

                if(isset($ins_data) && !empty($ins_data)) {
                    if(in_array($ins_data['ins_type_code'], $types)) {
                        $status = true;
                    }
                }
            }
        }

        return $status;
    }

    // Check Is Insurance is liable
    public static function isInsLiableForPiCase($pid) {
        global $dt, $field_prefix;

        $ids = array();
        for ($i=1; $i <= 3 ; $i++) { 
            $ids[] = $dt['ins_data_id'.$i];
        }

        return self::checkIsLiableForPiCase($ids, $pid);
    }

    // Get PI Case Manager Data
    public static function getPICaseManagerData($case_id, $field_val = '') {
        $resultItem = array();
        $binds = array();
        $whereStr = '';

        if(!empty($case_id)) {
            $binds = array($case_id);

            if(!empty($field_val)) {
                $whereStr .= ' AND field_name = ? ';
                $binds[] = $field_val;
            }

            $result = sqlStatement("SELECT * FROM vh_pi_case_management_details WHERE case_id = ? $whereStr ", $binds);

            while ($row = sqlFetchArray($result)) {
                $resultItem[] = $row;
            }
        }

        return $resultItem;
    }  

    // Get & Prepare form data
    public static function piCaseManagerFormData($case_id, $field_prefix = '') {
        $resultItem = array();

        if(!empty($case_id)) {
            $caseManageData = self::getPICaseManagerData($case_id);
            foreach ($caseManageData as $rk => $row) {
                if(isset($row['field_name'])) {
                    $field_name = "tmp_" . $field_prefix . $row['field_name'];
                    $field_value = isset($row['field_value']) ? $row['field_value'] : "";

                    if(!isset($resultItem[$field_name])) {
                        $resultItem[$field_name] = array();
                    }

                    if($row['field_name'] == "case_manager") {
                        $resultItem[$field_name] = $field_value;
                    } else {
                        $resultItem[$field_name][] = $field_value;
                    }
                }
            }   
        }

        return $resultItem;
    }

    public static function manageInsData($pid) {
        global $dt, $field_prefix;

        $ids = array();
        for ($i=1; $i <= 3 ; $i++) { 
            $ids[] = $dt['ins_data_id'.$i];
        }

        return self::checkIsLiableForPiCase($ids, $pid);
    }

    /* Case Save */

    public static function addScRcData($id, $value) {
        if(!empty($id)) {
            $sql = "UPDATE `form_cases` SET `sc_referring_id` = ? WHERE `id` = ?";
            sqlStatement($sql, array($value, $id));
        }
    }

    public static function updateRecentDate($id) {
        if(!empty($id)) {
            $logDate = self::fetchCaseAlertMaxDAte($id);
            $createdTime = isset($logDate['created_date']) ? $logDate['created_date'] : '';
            $updatedTime = isset($logDate['recent_date']) ? $logDate['recent_date'] : '';

            if(!empty($createdTime) && !empty($updatedTime)) {
                sqlStatement("UPDATE `form_cases` SET `bc_created_time` = ?, `bc_update_time` = ? WHERE `id` = ?", array($createdTime, $updatedTime, $id));
            }
        }
    }

    public static function fetchCaseAlertMaxDAte($case_id) {
        $sql = "SELECT min(fl.created_date) as created_date, max(fl.created_date) as recent_date FROM case_form_value_logs As fl WHERE fl.case_id = ? ";

        $result=sqlQuery($sql, array($case_id));
        return $result;
    }

    public static function generateRehabLog($case_id = '', $data = array(), $field_prefix = '') {
        if(!empty($case_id)) {
            $fieldList = array('rehab_field_1', 'rehab_field_2');
            $caseManagerData = self::piCaseManagerFormData($case_id, '');
            $oldFieldValue = array();
            $newFieldValue = array();

            if(isset($caseManagerData['tmp_rehab_field_1']) && isset($caseManagerData['tmp_rehab_field_2'])) {
                $oldR1Field = $caseManagerData['tmp_rehab_field_1'];
                $oldR2Field = $caseManagerData['tmp_rehab_field_2'];

                for ($old_i=0; $old_i < count($oldR1Field); $old_i++) { 
                    if(empty($oldR1Field[$old_i]) || empty($oldR2Field[$old_i])) {
                        continue;
                    }
                    $oldFieldValue[] = $oldR1Field[$old_i] ."-". $oldR2Field[$old_i];
                }
            }

            if(isset($data['rehab_field_1']) && isset($data['rehab_field_2'])) {
                $newR1Field = $data['rehab_field_1'];
                $newR2Field = $data['rehab_field_2'];

                for ($new_i=0; $new_i < count($newR1Field); $new_i++) {
                    if(empty($newR1Field[$new_i]) || empty($newR2Field[$new_i])) {
                        continue;
                    }
                    $newFieldValue[] = $newR1Field[$new_i] ."-". $newR2Field[$new_i];
                }
            }

            $diffValArray1 = array_diff($newFieldValue, $oldFieldValue);
            $diffValArray2 = array_diff($oldFieldValue, $newFieldValue);

            $isNeedToUpdate = false;
            if($diffValArray1 !== $diffValArray2) {
                $isNeedToUpdate = true;
            }

            if($isNeedToUpdate === true) {
                return array(
                    'old_value' => implode(", ", $oldFieldValue),
                    'new_value' => implode(", ", $newFieldValue)
                );
            }
        }

        return false;
    }

    public static function savePICaseManagmentDetails($case_id = '', $data = array()) {

        if(!empty($case_id) && !empty($data)) {
            foreach ($data as $dk => $dItem) {
                if(!empty($dk)) {

                    //Delete Record
                    sqlStatement("DELETE FROM `vh_pi_case_management_details` WHERE case_id = ? AND field_name = ? ", array($case_id, $dk));

                    if(is_array($dItem)) {
                        foreach ($dItem as $diK => $dsItem) {
                            if(!empty($dsItem)) {
                                //Insert Items
                                $insertSql = "INSERT INTO `vh_pi_case_management_details` (case_id, field_name, field_index, field_value) VALUES (?, ?, ?, ?) ";
                                sqlInsert($insertSql, array(
                                    $case_id,
                                    $dk,
                                    $diK,
                                    $dsItem
                                ));
                            }
                        }
                    } else {

                        //Insert Items
                        $insertSql = "INSERT INTO `vh_pi_case_management_details` (case_id, field_name, field_index, field_value) VALUES (?, ?, ?, ?) ";
                        sqlInsert($insertSql, array(
                            $case_id,
                            $dk,
                            0,
                            $dItem
                        ));
                    }
                }   
            }
        }
    }

    public static function logFormFieldValues($data = array()) {
        if(!empty($data)) {
            extract($data);

            $sql = "INSERT INTO `form_value_logs` ( field_id, form_name, form_id, new_value, old_value, pid, username ) VALUES (?, ?, ?, ?, ?, ?, ?) ";
            return sqlInsert($sql, array(
                $field_id,
                $form_name,
                $form_id,
                $new_value,
                $old_value,
                $pid,
                $username
            ));
        }

        return false;
    }

    public static function getArrayValDeff($array1, $array2) {
        $diff = array_filter($array1, 
          function ($val) use (&$array2) { 
            $key = array_search($val, $array2);
            if ( $key === false ) return true;
            unset($array2[$key]);
            return false;
          }
        );

        return $diff;
    }

    public static function getAbookData($id = array()) {
        $resultItem = array();

        if(!empty($id)) {
            $idStr = implode("','", $id); 

            $result = sqlStatement("SELECT * from users u where id IN ('$idStr') ");
            while ($row = sqlFetchArray($result)) {
                $resultItem['id_'.$row['id']] = $row;
            }
        }

        return $resultItem;
    }

    /* End*/
}

}