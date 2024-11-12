<?php

/*
 *   @package   OpenEMR
 *   @link      http://www.open-emr.org
 *
 *   @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 *   Copyright (c)  Juggernaut Systems Express
 *   @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 */

namespace OpenEMR\Reports\Productivity;


class DataQuery
{
    public mixed $fromDate;
    public mixed $toDate;

    public function __construct()
    {
        $this->fromDate = $_POST['fromDate'];
        $this->toDate = $_POST['toDate'];
    }

    private function mainDataQuery(): string
    {
        return "SELECT
    b.code_type,
    b.code,
    b.code_text,
    b.pid,
    fe.encounter,
    CONCAT(p.lname, ' ', p.fname) AS patient_name,  -- Adding patient first and last name
    CONCAT(u.lname, ' ', u.fname) AS provider,
    b.billed,
    b.payer_id,
    b.units,
    b.fee,
    b.bill_date,
    b.id,
    ins.name AS insurance_company_name,
    fe.date
FROM form_encounter AS fe
LEFT JOIN billing AS b
    ON b.pid = fe.pid
    AND b.encounter = fe.encounter
LEFT JOIN patient_data AS p
    ON p.pid = fe.pid  -- Joining patient_data to get patient names
LEFT JOIN insurance_companies AS ins
    ON b.payer_id = ins.id
LEFT JOIN code_types AS c
    ON c.ct_key = b.code_type
LEFT JOIN users AS u
    ON b.provider_id = u.id
LEFT JOIN ar_activity AS ar
    ON ar.encounter = b.encounter
    AND ar.pid = b.pid  -- Join with ar_activity using both encounter and pid
WHERE fe.pid IN (
    SELECT DISTINCT fe_inner.pid
    FROM form_encounter AS fe_inner
    WHERE fe_inner.date BETWEEN ? AND ?
)  -- Subquery to get the list of pids within the date range
AND fe.date BETWEEN ? AND ? AND b.billed = 1
GROUP BY b.code_type, b.code, b.code_text, b.pid, provider, b.billed, b.payer_id,
         b.units, b.fee, b.bill_date, b.id, ins.name, fe.date
ORDER BY b.pid
";
    }

    public function getReportData(): array
    {
        $buildData = $this->mainDataQuery();
        $rows = [];
        $data = sqlStatement($buildData, [$this->fromDate, $this->toDate, $this->fromDate, $this->toDate]);
        while ($row = sqlFetchArray($data)) {
            $rows[] = $row;
        }
        return $rows;
    }
}
