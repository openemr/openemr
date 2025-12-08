<?php

/*
 * DrugSalesService.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Stephen Nielson <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Services\Search\FhirSearchWhereClauseBuilder;
use OpenEMR\Validators\ProcessingResult;
use PHPMailer\PHPMailer\PHPMailer;
use Exception;
use InvalidArgumentException;

class DrugSalesService extends BaseService
{
    const TABLE_NAME = 'drug_sales';

    public function __construct()
    {
        parent::__construct(self::TABLE_NAME);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'patient_uuid', 'prescription_uuid','encounter_uuid', 'dispenser_uuid'];
    }

    /**
     * @param $search
     * @param $isAndCondition
     * @return ProcessingResult
     */
    public function search($search, $isAndCondition = true): ProcessingResult
    {
        $sql = "SELECT
                    ds.uuid,
                    ds.sale_id,
                    ds.drug_id,
                    ds.inventory_id,
                    ds.prescription_id,
                    ds.pid as patient_id,
                    ds.encounter,
                    ds.user,
                    dispenser.dispenser_uuid,
                    ds.sale_date,
                    ds.quantity,
                    ds.fee,
                    ds.billed,
                    ds.trans_type,
                    ds.notes,
                    ds.bill_date,
                    ds.selector,
                    d.drug_name,
                    d.ndc_number,
                    d.rxnorm_code,
                    d.drug_form,
                    d.drug_size,
                    d.drug_unit,
                    d.drug_route,
                    di.lot_number,
                    di.expiration,
                    pd.patient_uuid,
                    fe.encounter_uuid,
                    pr.prescription_uuid,
                    pr.dosage,
                    pr.prescription_drug_size,
                    pr.drug_dosage_instructions,
                    pr.prescription_route,
                    pr.prescription_interval,
                    pr.refills,
                    pr.prescription_note
                    ,routes_list.route_id
                    ,routes_list.route_title
                    ,routes_list.route_codes

                    ,units_list.unit_id
                    ,units_list.unit_title
                    ,units_list.unit_codes

                    ,units_list.unit_id
                    ,units_list.unit_title
                    ,units_list.unit_codes

                    ,intervals_list.interval_id
                    ,intervals_list.interval_title
                    ,intervals_list.interval_notes
                    ,intervals_list.interval_codes
                    ,drug_forms.drug_form_title
                    ,drug_forms.drug_form_codes
                FROM drug_sales ds
                LEFT JOIN (
                    SELECT
                        drug_id,
                        uuid AS drug_uuid,
                        name as drug_name,
                        ndc_number,
                        drug_code as rxnorm_code,
                        form as drug_form,
                        size as drug_size,
                        unit as drug_unit,
                        route as drug_route
                    FROM
                        drugs
                ) d ON ds.drug_id = d.drug_id
                LEFT JOIN drug_inventory di ON ds.inventory_id = di.inventory_id
                LEFT JOIN (
                    SELECT
                        uuid as prescription_uuid,
                        id AS presc_id,
                        dosage,
                        drug_dosage_instructions,
                        size AS prescription_drug_size,
                        route as prescription_route,
                        `interval` as prescription_interval,
                        refills,
                        unit AS prescription_unit,
                        note as prescription_note
                        FROM prescriptions
                ) pr ON ds.prescription_id = pr.presc_id
                LEFT JOIN (
                    SELECT
                        pid AS patient_id,
                        uuid AS patient_uuid
                    FROM
                        patient_data
                ) pd ON ds.pid = pd.patient_id
                LEFT JOIN (
                    SELECT
                        id AS dispenser_user_id
                        ,uuid AS dispenser_uuid
                        ,username AS dispenser_username
                    FROM
                        users
                    WHERE
                        npi IS NOT NULL AND npi != ''
                ) dispenser ON ds.user = dispenser.dispenser_username
                LEFT JOIN (
                    SELECT
                        encounter,
                        uuid AS encounter_uuid
                    FROM
                        form_encounter
                ) fe ON ds.encounter = fe.encounter
                LEFT JOIN
                (
                  SELECT
                    option_id AS route_id
                    ,title AS route_title
                    ,codes AS route_codes
                  FROM list_options
                  WHERE list_id='drug_route'
                ) routes_list ON routes_list.route_id = pr.prescription_route
                LEFT JOIN
                (
                  SELECT
                    option_id AS interval_id
                    ,title AS interval_title
                    ,codes AS interval_codes
                    ,notes AS interval_notes
                  FROM list_options
                  WHERE list_id='drug_interval'
                ) intervals_list ON intervals_list.interval_id = pr.prescription_interval
                LEFT JOIN
                (
                  SELECT
                    option_id AS unit_id
                    ,title AS unit_title
                    ,codes AS unit_codes
                  FROM list_options
                  WHERE list_id='drug_units'
                ) units_list ON units_list.unit_id = pr.prescription_unit
                LEFT JOIN
                (
                  SELECT
                    option_id AS drug_form_id
                    ,title AS drug_form_title
                    ,codes AS drug_form_codes
                  FROM list_options
                  WHERE list_id='drug_form'
                ) drug_forms ON drug_forms.drug_form_id = d.drug_form"; // Only include sales, returns, transfers, adjustments

        $whereClause = FhirSearchWhereClauseBuilder::build($search);
        $sql .= $whereClause->getFragment();
        $sqlBindArray = $whereClause->getBoundValues();

        $sql .= " ORDER BY ds.sale_date DESC";

        $statementResults = QueryUtils::sqlStatementThrowException($sql, $sqlBindArray);
        $processingResult = new ProcessingResult();
        while ($row = QueryUtils::fetchArrayFromResultSet($statementResults)) {
            $processingResult->addData($this->createResultRecordFromDatabaseResult($row));
        }

        return $processingResult;
    }

    public function sellDrug(
        $drug_id,
        $quantity,
        $fee,
        $patient_id = 0,
        $encounter_id = 0,
        $prescription_id = 0,
        $sale_date = '',
        $user = '',
        $default_warehouse = '',
        $testonly = false,
        &$expiredlots = null,
        $pricelevel = '',
        $selector = ''
    ) {

        if (empty($patient_id)) {
            $patient_id   = $GLOBALS['pid'];
        }

        if (empty($sale_date)) {
            $sale_date    = date('Y-m-d');
        }

        if (empty($user)) {
            $user         = $_SESSION['authUser'];
            $userId       = $_SESSION['authUserID'];
        } else {
            $userService = new UserService();
            $userRecord = $userService->getUserByUsername($user);
            if (empty($userRecord)) {
                throw new InvalidArgumentException(xl('The specified user does not exist') . ": " . $user);
            }
            $userId = $userRecord['id'];
        }

        // error_log("quantity = '$quantity'"); // debugging

        // Sanity check.
        if (!$testonly) {
            if (empty($encounter_id) || $encounter_id == 0) {
                throw new InvalidArgumentException(xl('No active encounter selected. To dispense medication, you must first select or create a valid encounter for this patient. Please return to the patient dashboard, select or create an encounter, then try again.'));
            }
            $tmp = QueryUtils::fetchRecords(
                "SELECT count(*) AS count from form_encounter WHERE pid = ? AND encounter = ?",
                [$patient_id, $encounter_id]
            );
            // TODO: Better error handling.
            if (empty($tmp[0]['count'])) {
                // credit to cburnicki and his PR here https://github.com/openemr/openemr/pull/8598/files
                // This is a system error - the encounter ID exists but doesn't match the patient
                $this->getLogger()->error("Drug dispensing error: Encounter does not exist or does not belong to patient", ['patient_id' => $patient_id, 'encounter_id' => $encounter_id]);
                throw new Exception(xl('System error: The selected encounter does not exist or does not belong to this patient. This may indicate a data integrity issue. Please contact support if this problem persists.'));
            }
        }

        if (empty($default_warehouse)) {
            // Get the default warehouse, if any, for the user.
            $rowuser = QueryUtils::fetchRecords("SELECT default_warehouse FROM users WHERE username = ?", [$user]);
            $default_warehouse = $rowuser[0]['default_warehouse'];
        }

        // Get relevant options for this product.
        $rowdrug = QueryUtils::fetchRecords("SELECT allow_combining, reorder_point, name, dispensable " .
            "FROM drugs WHERE drug_id = ?", [$drug_id])[0];
        $allow_combining = $rowdrug['allow_combining'];
        $dispensable     = $rowdrug['dispensable'];

        if (!$dispensable) {
            // Non-dispensable is a much simpler case and does not touch inventory.
            if ($testonly) {
                return true;
            }

            $uuid = UuidRegistry::getRegistryForTable(self::TABLE_NAME)->createUuid();
            $sale_id = QueryUtils::sqlInsert(
                "INSERT INTO drug_sales ( " .
                "uuid, drug_id, inventory_id, prescription_id, pid, encounter, user, " .
                "sale_date, quantity, fee, created_by, updated_by ) VALUES ( " .
                "?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$uuid, $drug_id, $prescription_id, $patient_id, $encounter_id, $user, $sale_date, $quantity, $fee, $userId, $userId]
            );
            return $sale_id;
        }

        // Combining is never allowed for prescriptions and will not work with
        // dispense_drug.php.
        if ($prescription_id) {
            $allow_combining = 0;
        }

        $rows = [];
        // $firstrow = false;
        $qty_left = $quantity;
        $bad_lot_list = '';
        $total_on_hand = 0;
        $gotexpired = false;

        // If the user has a default warehouse, sort those lots first.
        $orderby = ($default_warehouse === '') ?
            "" : "di.warehouse_id != '$default_warehouse', ";
        $orderby .= "lo.seq, di.expiration, di.lot_number, di.inventory_id";

        // Retrieve lots in order of expiration date within warehouse preference.
        $query = "SELECT di.*, lo.option_id, lo.seq " .
            "FROM drug_inventory AS di " .
            "LEFT JOIN list_options AS lo ON lo.list_id = 'warehouse' AND " .
            "lo.option_id = di.warehouse_id AND lo.activity = 1 " .
            "WHERE " .
            "di.drug_id = ? AND di.destroy_date IS NULL AND di.on_hand != 0 ";
        $sqlarr = [$drug_id];
        if ($GLOBALS['SELL_FROM_ONE_WAREHOUSE'] && $default_warehouse) {
            $query .= "AND di.warehouse_id = ? ";
            $sqlarr[] = $default_warehouse;
        }

        $query .= "ORDER BY $orderby";
        $res = QueryUtils::sqlStatementThrowException($query, $sqlarr);

        // First pass.  Pick out lots to be used in filling this order, figure out
        // if there is enough quantity on hand and check for lots to be destroyed.
        while ($row = QueryUtils::fetchArrayFromResultSet($res)) {
            if ($row['warehouse_id'] != $default_warehouse) {
                // Warehouses with seq > 99 are not available.
                $seq = empty($row['seq']) ? 0 : $row['seq'] + 0;
                if ($seq > 99) {
                    continue;
                }
            }

            $on_hand = $row['on_hand'];
            $expired = (!empty($row['expiration']) && $row['expiration'] <= $sale_date);
            if ($expired || $on_hand < $quantity) {
                $tmp = $row['lot_number'];
                if (! $tmp) {
                    $tmp = '[missing lot number]';
                }

                if ($bad_lot_list) {
                    $bad_lot_list .= ', ';
                }

                $bad_lot_list .= $tmp;
            }

            if ($expired) {
                $gotexpired = true;
                continue;
            }

            /*****************************************************************
            // Note the first row in case total quantity is insufficient and we are
            // allowed to go negative.
            if (!$firstrow) $firstrow = $row;
             *****************************************************************/

            $total_on_hand += $on_hand;

            if ($on_hand > 0 && $qty_left > 0 && ($allow_combining || $on_hand >= $qty_left)) {
                $rows[] = $row;
                $qty_left -= $on_hand;
            }
        }

        if ($expiredlots !== null) {
            $expiredlots = $gotexpired;
        }

        if ($testonly) {
            // Just testing inventory, so return true if OK, false if insufficient.
            // $qty_left, if positive, is the amount requested that could not be allocated.
            return $qty_left <= 0;
        }

        if ($bad_lot_list) {
            $this->send_drug_email(
                "Possible lot destruction needed",
                "The following lot(s) are expired or were too small to fill the " .
                "order for patient $patient_id: $bad_lot_list\n"
            );
        }

        /*******************************************************************
        if (empty($firstrow)) return 0; // no suitable lots exist
        // This can happen when combining is not allowed.  We will use the
        // first row and take it negative.
        if (empty($rows)) {
        $rows[] = $firstrow;
        $qty_left -= $firstrow['on_hand'];
        }
         *******************************************************************/

        // The above was an experiment in permitting a negative lot quantity.
        // We decided that was a bad idea, so now we just error out if there
        // is not enough on hand.
        if ($qty_left > 0) {
            return 0;
        }

        $sale_id = 0;
        $qty_final = $quantity; // remaining unallocated quantity
        $fee_final = $fee;      // remaining unallocated fee

        // Second pass.  Update the database.
        foreach ($rows as $row) {
            $inventory_id = $row['inventory_id'];

            /*****************************************************************
            $thisqty = $row['on_hand'];
            if ($qty_left > 0) {
            $thisqty += $qty_left;
            $qty_left = 0;
            }
            else if ($thisqty > $qty_final) {
            $thisqty = $qty_final;
            }
             *****************************************************************/
            $thisqty = min($qty_final, $row['on_hand']);

            $qty_final -= $thisqty;

            // Compute the proportional fee for this line item.  For the last line
            // item take the remaining unallocated fee to avoid round-off error.
            $thisfee = $qty_final ? sprintf('%0.2f', $fee * $thisqty / $quantity) : sprintf('%0.2f', $fee_final);

            $fee_final -= $thisfee;

            // Update inventory and create the sale line item.
            QueryUtils::sqlStatementThrowException("UPDATE drug_inventory SET " .
                "on_hand = on_hand - ? " .
                "WHERE inventory_id = ?", [$thisqty,$inventory_id]);
            $uuid = UuidRegistry::getRegistryForTable(self::TABLE_NAME)->createUuid();
            $sale_id = QueryUtils::sqlInsert(
                "INSERT INTO drug_sales ( " .
                "uuid, drug_id, inventory_id, prescription_id, pid, encounter, user, sale_date, quantity, fee, pricelevel, selector, created_by, updated_by ) " .
                "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$uuid, $drug_id, $inventory_id, $prescription_id, $patient_id, $encounter_id, $user,
                    $sale_date,
                    $thisqty,
                    $thisfee,
                    $pricelevel,
                    $selector,
                    $userId,
                    $userId]
            );

            // If this sale exhausted the lot then auto-destroy it if that is wanted.
            if ($row['on_hand'] == $thisqty && !empty($GLOBALS['gbl_auto_destroy_lots'])) {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE drug_inventory SET " .
                    "destroy_date = ?, destroy_method = ?, destroy_witness = ?, destroy_notes = ? "  .
                    "WHERE drug_id = ? AND inventory_id = ?",
                    [$sale_date, xl('Automatic from sale'), $user, "sale_id = $sale_id",
                        $drug_id,
                        $inventory_id]
                );
            }
        }

        /*******************************************************************
        // If appropriate, generate email to notify that re-order is due.
        if (($total_on_hand - $quantity) <= $rowdrug['reorder_point']) {
        send_drug_email("Product re-order required",
        "Product '" . $rowdrug['name'] . "' has reached its reorder point.\n");
        }
        // TBD: If the above is un-commented, fix it to handle the case of
        // $GLOBALS['gbl_min_max_months'] being true.
         *******************************************************************/

        // If combining is allowed then $sale_id will be just the last inserted ID,
        // and it serves only to indicate that everything worked.  Otherwise there
        // can be only one inserted row and this is its ID.
        return $sale_id;
    }

    public function send_drug_email($subject, $body): void
    {
        $recipient = $GLOBALS['practice_return_email_path'];
        if (empty($recipient)) {
            return;
        }

        $mail = new PHPMailer();
        $mail->From = $recipient;
        $mail->FromName = 'In-House Pharmacy';
        $mail->isMail();
        $mail->Host = "localhost";
        $mail->Mailer = "mail";
        $mail->Body = $body;
        $mail->Subject = $subject;
        $mail->AddAddress($recipient);
        if (!$mail->Send()) {
            $this->getLogger()->error("There has been a mail error sending to " . $recipient .
                    " " . $mail->ErrorInfo);
        }
    }
}
