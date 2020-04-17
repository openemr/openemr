<?php
/**
 * panel.inc includes functions for manipulating panel information.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Wejdan Bagais <wbagais@gmail.com>
 * @copyright Copyright (c) 2020 Wejdan Bagais <wbagais@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

 /**
  * Purpose:
  * To check if the panels tables are created
  * @return boolean true or false based on the status of the panels tables existence
  * @return boolean true: if the tables exist
  * @return boolean False: if the tables are NOT exist
  */
function isPanelDB()
{
  # test query
  $sql = "SELECT * FROM panel_category ";
  if (sqlQueryNoLogIgnoreError($sql))
  {
    return TRUE; // if the result is array
  } else {
    return FALSE; // if empty (the table does not exist)
  }
}

/**
  * Purpose:
  * request the patient panle enrolment based on patient id
  * @param integer patient id
  * if the tables exist:
  * @return array the enrolmments list {id, patient_id, enrolment_date, discharge_date, status}
  * if the tables  do not exist:
  *@return integer return -1
  */
function getEnrollments($pid)
{
  if (isPanelDB())
  {
    $sql = "SELECT * FROM panel_enrollment WHERE patient_id = " . $pid;
    return sqlStatement($sql);
  } else { return -1; }
}

/**
  * Purpose:
  * request the panels information
  * if the tables exist
  * @return array the panel {id, name, category_id}
  * if the tables  do not exist
  * @return integer return -1
  */
function getAllPanels()
{
  if (isPanelDB())
  {

  $sql = "SELECT * FROM panel ";
  return sqlStatement($sql);
  } else { return -1; }
}

/**
  * Purpose:
  * request the panels information based on panel id
  * @param  integer panel_id
  * if the tables exist
  * @return array the panel {id, name, category_id}
  * if the tables  do not exist
  *@return integer return -1
  */
function getPanel($panel_id)
{
  if (isPanelDB())
  {

  $sql = "SELECT * FROM panel WHERE id=" . $panel_id;
  return sqlQuery($sql);
  } else { return -1; }
}

/**
  * Purpose:
  * request the panel category information based on $category_id
  * Parameters:
  * @param integer $category_id panal category id
  * if the tables exist:
  * @return array the panel category {id, name}
  * if the tables  are not exist
  *@return integer -1
  */
function getPanelCategory($category_id)
{
  if (isPanelDB())
  {
  // get  the category name
  $sql = "SELECT * FROM panel_category WHERE id=" . $category_id;
  return sqlQuery($sql);
  } else { return -1; }
}


/**
  * Purpose:
  * request the patient enrolment data with the category name and panel name
  * by joining the panel_enrollment, panel, and panel_category tables
  * @param integer $pid  patient id
  * @param string $status (by defulat the status is active)
  * if the tables exist:
  * @return array  patient panels data {id, patient_id, panel_id, enrolment_date, discharge_date, status, panel, category}
  * if the tables are not exist or if the patient is not enrolled in any panels yet:
  * @return integer return -1
  */
function getPatientPanelsInfo($pid, $status = "active")
{
  if (!isPanelDB()) {
    #if the panels is not activated print bellow line
    return -1;
  }
  else {
    #print the patient Panels
    if (mysqli_num_rows($resultSet) === 0) {
      return -1;
    }

    $sql = "SELECT e.*, p.name AS panel, c.name AS  category  ";
    $sql .= "FROM panel_enrollment AS  e ";
    $sql .= "JOIN panel AS p ON e.panel_id = p.id ";
    $sql .= "JOIN panel_category AS c ON p.category_id = c.id ";
    $sql .= "WHERE e.patient_id = " . $pid . " ";
    if ($status != "all"){
      $sql .= "AND e.status = \"" . $status . "\"";
    }
    $sql .= "ORDER BY e.status, category ";
    return sqlStatement($sql);
  }
}
/**
  * Purpose:
  * insert the patient into a new panel
  * @param  array  $enrollment      aray of panel_enrollment info
  * (patient_id, panel_id)
  * @return integer  Last id generated from the sql insert command
  */
function insertEnrolment($enrollment)
{
  #Check if the patient already enrolled and active
  $sql  = "SELECT id, status FROM panel_enrollment ";
  $sql .= "WHERE patient_id ='" .$enrollment['patient_id'] . "' ";
  $sql .= "AND panel_id ='" .$enrollment['panel_id'] . "' ";
  $sql .= 'AND status = "Active"';
  $resultSet = sqlQuery($sql);

  if (!isPanelDB()) {
    #if the panels is not activated print bellow line
    return -1;
    # less than 2 because the result is one even if the patient is not enrolled 
  } elseif(count($resultSet)<2){
      #insert the patient Panels
      $sql  = "INSERT INTO panel_enrollment ";
      $sql .= "(patient_id, panel_id, enrollment_date, status) ";
      $sql .= " VALUES ( ";
      $sql .= "'" .$enrollment['patient_id'] . "', ";
      $sql .= "'" .$enrollment['panel_id'] . "', ";
      $sql .= "'" . date('Y-m-d H:i:s') . "', ";
      $sql .=  "'" ."Active". "' ";
      $sql .= ")" ;
      $result = sqlInsert($sql);
      return $result;
  }
}
/**
  * Purpose:
  * insert the patient into a new panel
  * @param  array  $updates      aray of updated info
  * (patient_id, panel_id)
  * @return integer  Last id generated from the sql insert command
  */
function dischargePatient($updates){
  $sql = "UPDATE panel_enrollment SET ";
  $sql .= "status = '" . "Discharged". "', ";
  $sql .= "discharge_date='". date('Y-m-d H:i:s') . "' ";
  $sql .= "WHERE patient_id = '" . $updates['patient_id'] . "' ";
  $sql .= "AND panel_id = '" . $updates['panel_id'] . "' ";
  $sql .= "LIMIT 1";

  return sqlQuery($sql);
}
