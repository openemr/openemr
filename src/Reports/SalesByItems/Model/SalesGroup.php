<?php

/**
 * Sales Group model for grouped sales data (category/product)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\SalesByItems\Model;

class SalesGroup
{
    private string $category;
    private string $product;
    private int $quantity = 0;
    private float $total = 0.0;
    /** @var SalesItem[] */
    private array $items = [];

    public function __construct(string $category, string $product)
    {
        $this->category = $category;
        $this->product = $product;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getProduct(): string
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @return SalesItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function addItem(SalesItem $item): void
    {
        $this->items[] = $item;
        $this->quantity += $item->getQuantity();
        $this->total += $item->getAmount();
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    public function addToQuantity(int $qty): void
    {
        $this->quantity += $qty;
    }

    public function addToTotal(float $amount): void
    {
        $this->total += $amount;
    }
}
