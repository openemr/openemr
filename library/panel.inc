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
  //if (sqlQueryNoLogIgnoreError(add_escape_custom($sql)))
  if (sqlQuery(add_escape_custom($sql)))
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
    $sql = "SELECT * FROM panel_enrollment WHERE patient_id = " . add_escape_custom($pid);
    return sqlStatement($sql);
  } else { return -1; }
}
/**
  * Purpose:
  * request the panels categories
  * if the tables exist
  * @return array the panel {id, name}
  * if the tables  do not exist
  * @return integer return -1
  */
function getAllPanelCategories()
{
  if (isPanelDB())
  {

  $sql = "SELECT * FROM panel_category ";
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
  * request the panels information
  * if the tables exist
  * @return array the panel {id, name, category_id}
  * if the tables  do not exist
  * @return integer return -1
  */
function getPanelsByCategory($category_id)
{
  if (isPanelDB())
  {

  $sql = "SELECT * FROM panel ";
  $sql .= "WHERE category_id= \"" .add_escape_custom($category_id) ."\"";
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
  *TODO change the name of the function to getPanelById
  */
function getPanel($panel_id)
{
  if (isPanelDB())
  {

  $sql = "SELECT * FROM panel WHERE id=" . add_escape_custom($panel_id);
  return sqlStatement($sql);
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
  *TODO change panel category to panel and panels to sub panel
  */
function getPanelCategory($category_id)
{
  if (isPanelDB())
  {
  // get  the category name
  $sql = "SELECT * FROM panel_category WHERE id=" . add_escape_custom($category_id);
  return sqlQuery($sql);
  } else { return -1; }
}

/**
  * Purpose:
  *
  * Parameters:
  * @param integer $patient ID panal category id
  * if the tables exist:
  * @return array the panel category
  * if the tables  are not exist
  *@return integer -1
  *TODO change panel category to panel and panels to sub panel
  */
function getPanelCategoryByPatient_id($pid, $status = "active")
{
  if (isPanelDB())
  {
  // get  the category name
  $sql = "SELECT DISTINCT c.name ";
  $sql .= " FROM panel_enrollment AS e  ";
  $sql .= "JOIN panel AS p ON e.panel_id = p.id  ";
  $sql .= "JOIN panel_category AS c ON p.category_id = c.id ";
  $sql .= "WHERE e.patient_id =\"" . add_escape_custom($pid) . "\" ";
  if ($status != "all"){
    $sql .= "AND e.status = \"" . add_escape_custom($status) . "\" ";
  }
  $sql .= "ORDER BY c.name ";
  return sqlStatement($sql);

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
function getPatientPanelsInfo($pid, $panelCategory = "None", $status = "active")
{
  if (!isPanelDB()) {
    #if the panels is not activated print bellow line
    return -1;
  }
  else {


    $sql = "SELECT e.*, p.name AS panel, c.name AS  category  ";
    $sql .= "FROM panel_enrollment AS  e ";
    $sql .= "JOIN panel AS p ON e.panel_id = p.id ";
    $sql .= "JOIN panel_category AS c ON p.category_id = c.id ";
    $sql .= "WHERE e.patient_id = \"" . add_escape_custom($pid) . "\" ";
    if ($status != "all"){
      $sql .= "AND e.status = \"" . add_escape_custom($status) . "\" ";
    }
    if ($panelCategory != "None"){
      $sql .= "AND c.name = \"" . add_escape_custom($panelCategory) . "\" ";
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
  foreach ($enrollment['panel_ids'] as $panel_id){
    #Check if the patient already enrolled and active
    $sql  = "SELECT id, status FROM panel_enrollment ";
    $sql .= "WHERE patient_id ='" .add_escape_custom($enrollment['patient_id']) . "' ";
    $sql .= "AND panel_id ='" . add_escape_custom($panel_id) . "' ";
    $sql .= 'AND status = "Active"';
    $resultSet = sqlQuery($sql);

    if (!isPanelDB()) {
      #if the panels is not activated print bellow line
      return -1;
      # less than 2 because the result is one even if the patient is not enrolled
    } elseif(count($resultSet)<2){
        #insert the patient Panels
        $sql  = "INSERT INTO panel_enrollment ";
        $sql .= "(patient_id, panel_id, enrollment_date, status, risk_stratification) ";
        $sql .= " VALUES ( ";
        $sql .= "'" . add_escape_custom($enrollment['patient_id']) . "', ";
        $sql .= "'" .add_escape_custom($panel_id) . "', ";
        $sql .= "'" . date('Y-m-d H:i:s') . "', ";
        $sql .=  "'" ."Active". "', ";
        $sql .= "'" .add_escape_custom($enrollment['risk_stratification']) . "' ";
        $sql .= ")" ;
        $result = sqlInsert($sql);
    }
}
}
/**
  * Purpose:
  * discharge the patient from a panel
  * @param  array  $updates      aray of updated info
  * (patient_id, panel_id)
  * @return integer  Last id generated from the sql insert command
  */
function dischargePatient($enrollment_id){
  $sql = "UPDATE panel_enrollment SET ";
  $sql .= "status = '" . "Discharged". "', ";
  $sql .= "discharge_date='". date('Y-m-d H:i:s') . "' ";
  $sql .= "WHERE id = '" . add_escape_custom($enrollment_id) . "' ";
  $sql .= "LIMIT 1";

  return sqlQuery($sql);
}
/**
  * Purpose:
  * return the list of the appointment for the selected panel and patient
  * @param  string  $panel_name panel name
  * @param  string $pid patient id number
  * @param  string $eventDates
  * if it next, it will return the next follow up appointment
  * if it all, it will return all the appointments
  * @return array   appointments list
  */
function getPanelAppointment($panel_name, $pid, $eventDates = 'next'){
  $sql  = "SELECT e.pc_eventDate, e.pc_startTime, e.pc_endTime, c.pc_catname, e.pc_apptstatus ";
  $sql .= "FROM openemr_postcalendar_events AS e, openemr_postcalendar_categories AS c ";
  $sql .= "WHERE  e.pc_catid = c.pc_catid ";
  $sql .= "AND LOWER(c.pc_catname) LIKE '%" . add_escape_custom($panel_name) . " %'  ";
  $sql .= "AND e.pc_pid = '" . add_escape_custom($pid) . "' ";
  if($eventDates == 'next'){
    $sql .= "AND e.pc_eventDate >= CURRENT_DATE ";
  }
  $sql .= "ORDER BY e.pc_eventDate ASC , e.pc_startTime ASC ";

  return sqlStatement($sql);
}

?>
