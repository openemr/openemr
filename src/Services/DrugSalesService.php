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
use PHPStan\Parallel\Process;

class DrugSalesService extends BaseService
{
    const TABLE_NAME = 'drug_sales';

    public function __construct($table)
    {
        parent::__construct($table);
    }

    public function getUuidFields(): array
    {
        return ['uuid', 'patient_uuid', 'prescription_uuid','encounter_uuid'];
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
                    ds.sale_date,
                    ds.quantity,
                    ds.fee,
                    ds.billed,
                    ds.trans_type,
                    ds.notes,
                    ds.bill_date,
                    ds.selector,
                    d.name as drug_name,
                    d.ndc_number,
                    d.drug_code as rxnorm_code,
                    d.form as drug_form,
                    d.size as drug_size,
                    d.unit as drug_unit,
                    d.route as drug_route,
                    di.lot_number,
                    di.expiration,
                    p.prescription_uuid,
                    pd.patient_uuid,
                    fe.encounter_uuid,
                    pr.dosage,
                    pr.route as prescription_route,
                    pr.interval as prescription_interval,
                    pr.refills,
                    pr.note as prescription_note
                FROM drug_sales ds
                LEFT JOIN drugs d ON ds.drug_id = d.drug_id
                LEFT JOIN drug_inventory di ON ds.inventory_id = di.inventory_id
                LEFT JOIN (
                    SELECT
                        p.uuid as prescription_uuid,
                        p.id AS presc_id
                        FROM prescriptions p
                ) p ON ds.prescription_id = p.presc_id
                LEFT JOIN (
                    SELECT
                        pid AS patient_id,
                        uuid AS patient_uuid
                    FROM
                        patient_data
                ) pd ON ds.pid = pd.patient_id
                LEFT JOIN (
                    SELECT
                        encounter,
                        uuid AS encounter_uuid
                    FROM
                        form_encounter
                ) fe ON ds.encounter = fe.encounter
                LEFT JOIN prescriptions pr ON ds.prescription_id = pr.id
                WHERE ds.trans_type IN (1, 3, 4, 5)"; // Only include sales, returns, transfers, adjustments

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
}
