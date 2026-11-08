<?php

/**
 * BMI Category enum for classifying Body Mass Index values
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Forms;

enum BmiCategory: string
{
    // BMI lower bounds for category classification
    public const OBESITY_III_LOWER_BOUND = 42;
    public const OBESITY_II_LOWER_BOUND = 34;
    public const OBESITY_I_LOWER_BOUND = 30;
    public const OVERWEIGHT_LOWER_BOUND = 27;
    public const NORMAL_BORDERLINE_LOWER_BOUND = 25;
    public const NORMAL_LOWER_BOUND = 18.5;
    public const UNDERWEIGHT_LOWER_BOUND = 10;

    case ObesityIII = 'Obesity III';
    case ObesityII = 'Obesity II';
    case ObesityI = 'Obesity I';
    case Overweight = 'Overweight';
    case NormalBorderline = 'Normal BL';
    case Normal = 'Normal';
    case Underweight = 'Underweight';

    /**
     * Determine BMI category from a BMI value
     */
    public static function fromBmi(float $bmi): ?self
    {
        return match (true) {
            $bmi > self::OBESITY_III_LOWER_BOUND => self::ObesityIII,
            $bmi > self::OBESITY_II_LOWER_BOUND => self::ObesityII,
            $bmi > self::OBESITY_I_LOWER_BOUND => self::ObesityI,
            $bmi > self::OVERWEIGHT_LOWER_BOUND => self::Overweight,
            $bmi > self::NORMAL_BORDERLINE_LOWER_BOUND => self::NormalBorderline,
            $bmi > self::NORMAL_LOWER_BOUND => self::Normal,
            $bmi > self::UNDERWEIGHT_LOWER_BOUND => self::Underweight,
            default => null,
        };
    }

    /**
     * Create a BmiCategory from a stored string value
     */
    public static function tryFromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Get the translated display label
     */
    public function label(): string
    {
        return match ($this) {
            self::ObesityIII => xl('Obesity III'),
            self::ObesityII => xl('Obesity II'),
            self::ObesityI => xl('Obesity I'),
            self::Overweight => xl('Overweight'),
            self::NormalBorderline => xl('Normal BL'),
            self::Normal => xl('Normal'),
            self::Underweight => xl('Underweight'),
        };
    }
}
