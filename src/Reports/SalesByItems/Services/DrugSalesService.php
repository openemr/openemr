<?php

/**
 * Service to process drug sales for Sales by Item report
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Services;

use OpenEMR\Reports\SalesByItems\Model\SalesItem;

class DrugSalesService
{
    /**
     * Process a drug sales item and apply any transformations
     *
     * @param SalesItem $item The drug sales item to process
     * @return SalesItem The processed item
     */
    public function processItem(SalesItem $item): SalesItem
    {
        // Apply any drug-specific processing here
        // For now, return as-is since repository handles most transformation
        return $item;
    }

    /**
     * Process multiple drug sales items
     *
     * @param SalesItem[] $items Array of drug sales items
     * @return SalesItem[] Array of processed items
     */
    public function processItems(array $items): array
    {
        return array_map([$this, 'processItem'], $items);
    }

    /**
     * Get category for drug sales (always 'Products')
     *
     * @return string The category
     */
    public function getCategory(): string
    {
        return 'Products';
    }

    /**
     * Get display description for drug sales
     *
     * @param string $description The raw description (drug name)
     * @return string The cleaned description
     */
    public function getDisplayDescription(string $description): string
    {
        // For drugs, remove any prefixes similar to codes
        if (preg_match('/^\S*?:(.+)$/', $description, $matches)) {
            return trim($matches[1]);
        }

        return $description;
    }

    /**
     * Validate drug sales item has required fields
     *
     * @param SalesItem $item The item to validate
     * @return bool True if valid
     */
    public function isValid(SalesItem $item): bool
    {
        return $item->getCategory() === 'Products'
            && !empty($item->getDescription())
            && $item->getQuantity() > 0
            && $item->getAmount() > 0;
    }

    /**
     * Format drug quantity display (may include units)
     *
     * @param int $quantity The quantity
     * @return string The formatted quantity
     */
    public function formatQuantity(int $quantity): string
    {
        return (string)$quantity;
    }
}
