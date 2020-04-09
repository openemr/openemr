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
 * Get a patient's data.
 *
 * @param int    $pid   The PID of the patient
 */
// it needs to be escaped via whitelisting prior to using this function.


################END DRAFT#####################
function isPanelDB()
{
  /**
  * Purpose:
  * To check if the panels tables are created
  * Parameters:
  * IN = Nothing
  * OUT = true or false based on the status of the panels tables existence
  * Returns:
  * true: if the tables exist
  * False: if the tables are NOT exist
  */

  # test query
  $sql = "SELECT * FROM panel_category ";
  if (sqlQueryNoLogIgnoreError($sql))
  {
    return TRUE; // if the result is array
  } else {
    return FALSE; // if empty (the table does not exist)
  }
}

function getEnrollments($pid)
{
  /**
  * Purpose:
  * request the patient panle enrolment based on patient id
  * Parameters:
   * IN = patient id
  * OUT = the enrolmments list {id, patient_id, enrolment_date, discharge_date, status}
  * Returns:
  * if the tables exist return the enrolments data
  * if the tables  are not exist return -1
  */
  if (isPanelDB())
  {
    $sql = "SELECT * FROM panel_enrollment WHERE patient_id = " . $pid;
    return sqlStatement($sql);
  } else { return -1; }
}

function getPanel($panel_id)
{
  /**
  * Purpose:
  * request the panels information based on panel id
  * Parameters:
   * IN = panel_id
  * OUT = the panel {id, name, category_id}
  * Returns:
  * if the tables exist return the panel data
  * if the tables  are not exist return -1
  */
  if (isPanelDB())
  {

  $sql = "SELECT * FROM panel WHERE id=" . $panel_id;
  return sqlQuery($sql);
  } else { return -1; }
}


function getPanelCategory($category_id)
{
  /**
  * Purpose:
  * request the panel category information based on $category_id
  * Parameters:
   * IN = $category_id
  * OUT = the panel {id, name}
  * Returns:
  * if the tables exist return the category data
  * if the tables  are not exist return -1
  */
  if (isPanelDB())
  {
  // get  the category name
  $sql = "SELECT * FROM panel_category WHERE id=" . $category_id;
  return sqlQuery($sql);
  } else { return -1; }
}


function getPatientPanelsInfo($pid, $status = "active")
{
  /**
  * Purpose:
  * request the patient enrolment data with the category name and panel name
  * by joining the panel_enrollment, panel, and panel_category tables
  * Parameters:
   * IN = $pid, $status (by defulat the status is active)
  * OUT = {id, patient_id, panel_id, enrolment_date, discharge_date, status, panel, category}
  * Returns:
  * if the tables exist return the patient panels data
  * return -1 if the tables are not exist or if the patient is not enrolled in any panels yet
  */
  if (!isPanelDB()) {
    #if the panels is not activated print bellow line
    return -1;
  }else {
    #print the patient Panels
    if (mysqli_num_rows($resultSet) === 0) {
      return -1;
    }

    $sql = "SELECT e.*, p.name AS panel, c.name AS  category  ";
    $sql .= "FROM panel_enrollment AS  e ";
    $sql .= "JOIN panel AS p ON e.panel_id = p.id ";
    $sql .= "JOIN panel_category AS c ON p.category_id = c.id ";
    $sql .= "WHERE p.id = " . $pid . " ";
    $sql .= "AND e.status = \"" . $status . "\"";

    return sqlStatement($sql);
}
}
