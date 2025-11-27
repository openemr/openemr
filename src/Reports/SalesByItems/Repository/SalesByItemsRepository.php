<?php

/**
 * Repository for Sales by Item report data access
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Repository;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Reports\SalesByItems\Model\SalesItem;

class SalesByItemsRepository
{
    /**
     * Fetch billing items for the specified date range and filters
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return SalesItem[]
     */
    public function getBillingItems(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        $fromDateTime = $fromDate . ' 00:00:00';
        $toDateTime = $toDate . ' 23:59:59';

        $sqlBindArray = [];
        $query = "SELECT b.fee, b.pid, b.encounter, b.code_type, b.code, b.units, "
            . "b.code_text, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno, lo.title "
            . "FROM billing AS b "
            . "JOIN code_types AS ct ON ct.ct_key = b.code_type "
            . "JOIN form_encounter AS fe ON fe.pid = b.pid AND fe.encounter = b.encounter "
            . "LEFT JOIN codes AS c ON c.code_type = ct.ct_id AND c.code = b.code AND c.modifier = b.modifier "
            . "LEFT JOIN list_options AS lo ON lo.list_id = 'superbill' AND lo.option_id = c.superbill AND lo.activity = 1 "
            . "WHERE b.code_type != 'COPAY' AND b.activity = 1 AND b.fee != 0 AND "
            . "fe.date >= ? AND fe.date <= ?";

        $sqlBindArray[] = $fromDateTime;
        $sqlBindArray[] = $toDateTime;

        if ($facilityId) {
            $query .= " AND fe.facility_id = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId) {
            $query .= " AND fe.provider_id = ?";
            $sqlBindArray[] = $providerId;
        }

        $query .= " ORDER BY lo.title, b.code, fe.date, fe.id";

        $rows = QueryUtils::fetchRecords($query, $sqlBindArray);
        $items = [];

        foreach ($rows as $row) {
            $category = $row['title'] ?? 'None';
            $description = ($row['code'] ?? '') . ' ' . ($row['code_text'] ?? '');
            $invoiceNumber = $row['pid'] . '.' . $row['encounter'];
            $units = (int)($row['units'] ?? 1);

            $item = new SalesItem(
                (int)$row['pid'],
                (int)$row['encounter'],
                $category,
                trim($description),
                substr($row['date'], 0, 10),
                $units,
                (float)$row['fee'],
                $invoiceNumber,
                $row['invoice_refno'] ?? ''
            );

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Fetch drug sales for the specified date range and filters
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return SalesItem[]
     */
    public function getDrugSales(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        $fromDateTime = $fromDate . ' 00:00:00';
        $toDateTime = $toDate . ' 23:59:59';

        $sqlBindArray = [];
        $query = "SELECT s.sale_date, s.fee, s.quantity, s.pid, s.encounter, "
            . "d.name, fe.date, fe.facility_id, fe.provider_id, fe.invoice_refno "
            . "FROM drug_sales AS s "
            . "JOIN drugs AS d ON d.drug_id = s.drug_id "
            . "JOIN form_encounter AS fe ON "
            . "fe.pid = s.pid AND fe.encounter = s.encounter AND "
            . "fe.date >= ? AND fe.date <= ? "
            . "WHERE s.fee != 0";

        $sqlBindArray[] = $fromDateTime;
        $sqlBindArray[] = $toDateTime;

        if ($facilityId) {
            $query .= " AND fe.facility_id = ?";
            $sqlBindArray[] = $facilityId;
        }

        if ($providerId) {
            $query .= " AND fe.provider_id = ?";
            $sqlBindArray[] = $providerId;
        }

        $query .= " ORDER BY d.name, fe.date, fe.id";

        $rows = QueryUtils::fetchRecords($query, $sqlBindArray);
        $items = [];

        foreach ($rows as $row) {
            $category = 'Products';
            $description = $row['name'] ?? 'Unknown';
            $invoiceNumber = $row['pid'] . '.' . $row['encounter'];
            $quantity = (int)($row['quantity'] ?? 1);

            $item = new SalesItem(
                (int)$row['pid'],
                (int)$row['encounter'],
                $category,
                $description,
                substr($row['date'], 0, 10),
                $quantity,
                (float)$row['fee'],
                $invoiceNumber,
                $row['invoice_refno'] ?? ''
            );

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Fetch all sales items (billing + drug sales) for the specified criteria
     *
     * @param string $fromDate Date in YYYY-MM-DD format
     * @param string $toDate Date in YYYY-MM-DD format
     * @param int|null $facilityId Optional facility filter
     * @param int|null $providerId Optional provider filter
     * @return SalesItem[]
     */
    public function getAllSalesItems(
        string $fromDate,
        string $toDate,
        ?int $facilityId = null,
        ?int $providerId = null
    ): array {
        $billingItems = $this->getBillingItems($fromDate, $toDate, $facilityId, $providerId);
        $drugSales = $this->getDrugSales($fromDate, $toDate, $facilityId, $providerId);

        // Merge and sort by category, product, date
        $allItems = array_merge($billingItems, $drugSales);
        usort($allItems, function (SalesItem $a, SalesItem $b) {
            $catCompare = strcmp($a->getCategory(), $b->getCategory());
            if ($catCompare !== 0) {
                return $catCompare;
            }

            $descCompare = strcmp($a->getDescription(), $b->getDescription());
            if ($descCompare !== 0) {
                return $descCompare;
            }

            return strcmp($a->getTransactionDate(), $b->getTransactionDate());
        });

        return $allItems;
    }
}
