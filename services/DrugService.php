<?php
/**
 *  DrugService
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

namespace OpenEMR\Services;

class DrugService
{
   /**
    * Default constructor
    */
   public function __construct()
   {
   }

  public function deleteDrug($drug_id)
  {
    sqlStatement("DELETE FROM drug_inventory WHERE drug_id = ?", array($drug_id));
    sqlStatement("DELETE FROM drug_templates WHERE drug_id = ?", array($drug_id));
    sqlStatement("DELETE FROM drugs WHERE drug_id = ?", array($drug_id));
    sqlStatement("DELETE FROM prices WHERE pr_id = ? AND pr_selector != ''", array($drug_id));
  }

  public Function updateDrug($data, $drug_id)
  {
    $sql  = " UPDATE drugs SET";
    $sql .= " name='" . $data["name"] . "',";
    $sql .= " ndc_number='" . $data["ndc_number"] . "',";
    $sql .= " on_order='" . $data["on_order"] . "',";
    $sql .= " reorder_point='" . $data["reorder_point"] . "',";
    $sql .= " max_level='" . $data["max_level"] . "',";
    $sql .= " form='" . $data["form"] . "',";
    $sql .= " size='" . $data["size"] . "',";
    $sql .= " unit='" . $data["unit"] . "',";
    $sql .= " route='" . $data["route"] . "',";
    $sql .= " cyp_factor='" . $data["cyp_factor"] . "',";
    $sql .= " related_code='" . $data["related_code"] . "',";
    $sql .= " allow_multiple='" . $data["allow_multiple"] . "',";
    $sql .= " allow_combining='" . $data["allow_combining"] . "',";
    $sql .= " active='" . $data["active"] . "'";
    $sql .= " WHERE drug_id='" . $drug_id . "'";
    sqlStatement("DELETE FROM drug_templates WHERE drug_id = ?", array($drug_id));
    return sqlStatement($sql);
  }

  public Function insertDrug($data)
  {
    $sql  = " INSERT INTO drugs SET";
    $sql .= " name='" . $data["name"] . "',";
    $sql .= " ndc_number='" . $data["ndc_number"] . "',";
    $sql .= " on_order='" . $data["on_order"] . "',";
    $sql .= " reorder_point='" . $data["reorder_point"] . "',";
    $sql .= " max_level='" . $data["max_level"] . "',";
    $sql .= " form='" . $data["form"] . "',";
    $sql .= " size='" . $data["size"] . "',";
    $sql .= " unit='" . $data["unit"] . "',";
    $sql .= " route='" . $data["route"] . "',";
    $sql .= " cyp_factor='" . $data["cyp_factor"] . "',";
    $sql .= " related_code='" . $data["related_code"] . "',";
    $sql .= " allow_multiple='" . $data["allow_multiple"] . "',";
    $sql .= " allow_combining='" . $data["allow_combining"] . "',";
    $sql .= " active='" . $data["active"] . "'";
    return sqlInsert($sql);
  }

  public Function insertTemplate($drug_id, $selector, $dosage, $period, $quantity, $refills, $taxrates)
  {
    sqlInsert(
        "INSERT INTO drug_templates ( " .
        "drug_id, selector, dosage, period, quantity, refills, taxrates " .
        ") VALUES ( ?, ?, ?, ?, ?, ?, ? )",
        array($drug_id, $selector, $dosage, $period, $quantity, $refills, $taxrates)
    );
  }
  
  public Function deletePrices($drug_id) 
  {
    sqlStatement("DELETE FROM prices WHERE pr_id = ? AND pr_selector != ''", array($drug_id));
  }

}
?>

