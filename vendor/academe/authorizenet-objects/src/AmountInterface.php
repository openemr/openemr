<?php

namespace Academe\AuthorizeNet;

/**
 * Amount interface, for carrying an amount and its currency.
 */

interface AmountInterface
{
    /**
     * Return the amount, always in decimal major units.
     * For example, return "1.20" for amount 1.2 USD.
     *
     * @return string The amount string, major and minor decimal units
     */
    public function getFormatted();

    /**
     * Return the currency ISO code.
     * For example "GBP" for UK Pound (£).
     *
     * @return string The ISO 4217 three-character currency code
     */
    public function getCurrencyCode();
}
