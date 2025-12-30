<?php

/**
 * MeasurementUtilsInterface.php
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

/**
 * Interface for unit measurement conversions.
 *
 * Implementations provide conversions between common units of measurement
 * used in healthcare (weight, length, temperature).
 */
interface MeasurementUtilsInterface
{
    /**
     * Convert kilograms to pounds.
     *
     * @param float $val Value in kilograms
     * @return string Formatted value in pounds
     */
    public function kgToLb(float $val): string;

    /**
     * Convert pounds to kilograms.
     *
     * @param float $val Value in pounds
     * @return string Formatted value in kilograms
     */
    public function lbToKg(float $val): string;

    /**
     * Convert centimeters to inches.
     *
     * @param float $val Value in centimeters
     * @return string Formatted value in inches
     */
    public function cmToInches(float $val): string;

    /**
     * Convert inches to centimeters.
     *
     * @param float $val Value in inches
     * @return string Formatted value in centimeters
     */
    public function inchesToCm(float $val): string;

    /**
     * Convert Fahrenheit to Celsius.
     *
     * @param float $val Value in Fahrenheit
     * @return string Formatted value in Celsius
     */
    public function fhToCelsius(float $val): string;

    /**
     * Convert Celsius to Fahrenheit.
     *
     * @param float $val Value in Celsius
     * @return string Formatted value in Fahrenheit
     */
    public function celsiusToFh(float $val): string;

    /**
     * Check if metric mode is enabled based on units_of_measurement setting.
     *
     * @return bool True if metric is the primary or only unit system
     */
    public function isMetric(): bool;

    /**
     * Format weight with appropriate unit label(s) based on units_of_measurement setting.
     *
     * @param float $lbs Value in pounds
     * @param bool $primaryOnly If true, only show primary unit (no secondary in parentheses)
     * @return string Formatted weight with unit label(s)
     */
    public function formatWeight(float $lbs, bool $primaryOnly = false): string;

    /**
     * Format length with appropriate unit label(s) based on units_of_measurement setting.
     *
     * @param float $inches Value in inches
     * @param bool $primaryOnly If true, only show primary unit (no secondary in parentheses)
     * @return string Formatted length with unit label(s)
     */
    public function formatLength(float $inches, bool $primaryOnly = false): string;

    /**
     * Format temperature with appropriate unit label(s) based on units_of_measurement setting.
     *
     * @param float $fahrenheit Value in Fahrenheit
     * @param bool $primaryOnly If true, only show primary unit (no secondary in parentheses)
     * @return string Formatted temperature with unit label(s)
     */
    public function formatTemperature(float $fahrenheit, bool $primaryOnly = false): string;
}
