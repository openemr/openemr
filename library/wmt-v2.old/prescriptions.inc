<?php
require_once("{$GLOBALS['srcdir']}/sql.inc");
// In this case the id is the patient id
// May need to add filtering by date at some point
//function getPrescriptionbyPatient ($this_pid, $cols = "*")
//{
//  $sql = "SELECT $cols FROM prescriptions WHERE patient_id='$this_pid' AND active='1' ORDER BY date_added DESC";
//  $res = sqlStatement($sql);
//  for($iter =0;$row = sqlFetchArray($res);$iter++)
//  $all[$iter] = $row;
//  return $all;
//}

function getAllPrescriptionsbyPatientOld ($this_pid, $cols = "*")
{
  // $sql="SELECT id FROM patient_data WHERE pid='$this_pid'";
  // $res=sqlStatement($sql);
  // $pat=sqlFetchArray($res);
  // $this_id=$pat['id'];
  $sql = "SELECT $cols FROM prescriptions WHERE patient_id='$this_pid' ORDER BY date_added DESC";
  $res = sqlStatement($sql);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

?>
