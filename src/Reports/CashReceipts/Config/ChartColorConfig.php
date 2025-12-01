<?php

/**
 * ChartColorConfig - Centralized configuration for chart colors
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Reports\CashReceipts\Config;

/**
 * Centralized configuration for chart colors
 * Uses a colorblind-friendly palette (Okabe-Ito palette + extensions)
 * Can be extended or overridden for theming
 */
class ChartColorConfig
{
    /**
     * Colorblind-friendly palette (Okabe-Ito palette + extensions)
     * These colors are accessible for people with color vision deficiency
     * and work well with both light and dark themes
     *
     * @var array
     */
    private static array $colors = [
        '#E69F00', // Orange
        '#56B4E9', // Sky Blue
        '#009E73', // Bluish Green
        '#F0E442', // Yellow
        '#0072B2', // Blue
        '#D55E00', // Vermillion
        '#CC79A7', // Reddish Purple
        '#999999', // Gray
        '#44AA99', // Teal
        '#882255', // Wine
        '#DDCC77', // Sand
        '#117733', // Green
    ];

    /**
     * Named color presets for common chart scenarios
     *
     * @var array
     */
    private static array $presets = [
        'revenue' => [
            '#E69F00', // Orange
            '#56B4E9', // Sky Blue
            '#009E73', // Bluish Green
        ],
        'cashflow' => [
            '#E69F00', // Orange - Total
            '#56B4E9', // Sky Blue - Professional
            '#009E73', // Bluish Green - Clinic
        ],
        'procedures' => [
            '#D55E00', // Vermillion
            '#CC79A7', // Reddish Purple
            '#0072B2', // Blue
            '#F0E442', // Yellow
        ],
    ];

    /**
     * Get the full color palette
     *
     * @return array Array of hex color codes
     */
    public static function getColors(): array
    {
        return self::$colors;
    }

    /**
     * Get a specific color by index
     *
     * @param int $index Index in the color palette
     * @return string Hex color code
     */
    public static function getColor(int $index): string
    {
        return self::$colors[$index % count(self::$colors)];
    }

    /**
     * Get a preset color palette for a specific chart type
     *
     * @param string $presetName Name of the preset (e.g., 'revenue', 'cashflow', 'procedures')
     * @return array Array of hex color codes for the preset
     */
    public static function getPreset(string $presetName): array
    {
        return self::$presets[$presetName] ?? self::$colors;
    }

    /**
     * Get multiple colors by count
     *
     * @param int $count Number of colors to return
     * @return array Array of hex color codes
     */
    public static function getColorsByCount(int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = self::getColor($i);
        }
        return $result;
    }

    /**
     * Set custom colors (useful for theming)
     * Note: This should be called early in the application initialization
     *
     * @param array $colors Array of hex color codes
     * @return void
     */
    public static function setColors(array $colors): void
    {
        if (!empty($colors)) {
            self::$colors = $colors;
        }
    }

    /**
     * Set custom preset
     *
     * @param string $presetName Name of the preset
     * @param array $colors Array of hex color codes for the preset
     * @return void
     */
    public static function setPreset(string $presetName, array $colors): void
    {
        if (!empty($colors)) {
            self::$presets[$presetName] = $colors;
        }
    }
}
