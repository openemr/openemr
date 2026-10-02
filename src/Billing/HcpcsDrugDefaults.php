<?php

/**
 * NDC and default units for a HCPCS code, taken from the inventory drug related to it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing;

use OpenEMR\Common\Database\QueryUtils;

final readonly class HcpcsDrugDefaults
{
    /**
     * NDC units of measure for a claim's drug quantity (837P CTP05-1).
     */
    public const NDC_UOM_CHOICES = [
        'ML' => 'ML',
        'GR' => 'Grams',
        'ME' => 'Milligrams',
        'F2' => 'I.U.',
        'UN' => 'Units',
    ];

    /**
     * @param string $ndcInfo Fee sheet NDC value: "N4<ndc>   <uom><quantity>" when the drug has an
     *                        NDC unit and quantity, the bare NDC when it has only a number, or ''.
     * @param ?int   $units   Default units for the service line, or null to use the code's default.
     */
    public function __construct(
        public string $ndcInfo,
        public ?int $units,
    ) {
    }

    /**
     * Find the active inventory drug related to a HCPCS code. When several drugs are related
     * to the code, one with stock on hand wins, then the first by name.
     */
    public static function forCode(string $code): ?self
    {
        $rows = QueryUtils::fetchRecords(
            "SELECT d.ndc_number, d.billing_units, d.ndc_uom, d.ndc_quantity FROM drugs AS d " .
            "WHERE d.active = 1 AND FIND_IN_SET(?, REPLACE(d.related_code, ';', ',')) > 0 " .
            "ORDER BY (SELECT COALESCE(SUM(di.on_hand), 0) FROM drug_inventory AS di " .
            "WHERE di.drug_id = d.drug_id AND di.destroy_date IS NULL) > 0 DESC, d.name, d.drug_id " .
            "LIMIT 1",
            ['HCPCS:' . $code]
        );
        $row = $rows[0] ?? null;
        return is_array($row) ? self::fromDrugRow($row) : null;
    }

    /**
     * @param array<mixed> $row A drugs row with ndc_number, billing_units, ndc_uom and ndc_quantity.
     */
    public static function fromDrugRow(array $row): self
    {
        $ndc = is_string($row['ndc_number'] ?? null) ? trim($row['ndc_number']) : '';
        $uom = is_string($row['ndc_uom'] ?? null) ? $row['ndc_uom'] : '';
        $quantity = $row['ndc_quantity'] ?? null;

        $ndcInfo = $ndc;
        if ($ndc !== '' && array_key_exists($uom, self::NDC_UOM_CHOICES) && is_numeric($quantity) && $quantity > 0) {
            $ndcInfo = 'N4' . $ndc . '   ' . $uom . self::formatQuantity((float) $quantity);
        }

        $units = $row['billing_units'] ?? null;
        return new self($ndcInfo, is_numeric($units) && (int) $units > 0 ? (int) $units : null);
    }

    private static function formatQuantity(float $quantity): string
    {
        // decimal(10,3) comes back as "1.000"; the claim wants "1".
        return rtrim(rtrim(number_format($quantity, 3, '.', ''), '0'), '.');
    }
}
