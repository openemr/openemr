<?php

/**
 * Service to process billing items for Sales by Item report
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Services;

use OpenEMR\Reports\SalesByItems\Model\SalesItem;

class BillingItemService
{
    /**
     * Process a billing item and apply any transformations
     *
     * @param SalesItem $item The billing item to process
     * @return SalesItem The processed item
     */
    public function processItem(SalesItem $item): SalesItem
    {
        // Apply any billing-specific processing here
        // For now, return as-is since repository handles most transformation
        return $item;
    }

    /**
     * Process multiple billing items
     *
     * @param SalesItem[] $items Array of billing items
     * @return SalesItem[] Array of processed items
     */
    public function processItems(array $items): array
    {
        return array_map([$this, 'processItem'], $items);
    }

    /**
     * Get display description for billing item (remove code prefix if present)
     *
     * @param string $description The raw description
     * @return string The cleaned description
     */
    public function getDisplayDescription(string $description): string
    {
        // Match pattern like "XXX:" at the start and return everything after it
        if (preg_match('/^\S*?:(.+)$/', $description, $matches)) {
            return trim($matches[1]);
        }

        return $description;
    }

    /**
     * Extract code and text from description
     *
     * @param string $description The full description
     * @return array ['code' => string, 'text' => string]
     */
    public function parseCodeAndText(string $description): array
    {
        $parts = explode(' ', $description, 2);
        return [
            'code' => $parts[0] ?? '',
            'text' => $parts[1] ?? '',
        ];
    }

    /**
     * Validate billing item has required fields
     *
     * @param SalesItem $item The item to validate
     * @return bool True if valid
     */
    public function isValid(SalesItem $item): bool
    {
        return !empty($item->getCategory())
            && $item->getQuantity() > 0
            && $item->getAmount() > 0;
    }
}
