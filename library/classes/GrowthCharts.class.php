<?php

/**
 * Handles the growth chart plotting and percentiles, includes charts for patients with Down Syndrome.
 * This file replaces growth_stats, cdc_growth_stats, who_growth_stats.

 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Daniel Pflieger <daniel@growlingflea.com> <daniel@mi-squared.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Daniel Pflieger <daniel@groowlingflea.com><daniel@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 *
 *
 */

use OpenEMR\Common\ORDataObject\ORDataObject;

$GLOBALS['cdc_data_tables'] = array(
    "bmi" => "cdc_bmi_age",
    "weight" => "cdc_weight_age",
    "height" => "cdc_stature_age"
);



class GrowthCharts extends ORDataObject
{
    const COL_AGE_MOS = 'agemos';
    const COL_SEX = 'sex';
    const COL_HEIGHT = 'height';
    const TBL_WHO_WEIGHT_LENGTH_MALE = 'who_wfh_boys_percentiles_expanded_tables_birth_to_5yrs';
    const TBL_WHO_WEIGHT_LENGTH_FEMALE = 'who_wfh_girls_percentiles_expanded_tables_birth_to_5yrs';
    const COL_LENGTH = 'length';
    const COL_AGE = 'age';

    public static function stringSexNumber($sex): int
    {
        if ($sex === 'Male') {
            $sex = 1;
        }
        if ($sex === 'Female') {
            $sex = 2;
        }
        return $sex;
    }


    public static function cdcAgePercentile($x, $age, $sex, $stat, $hasDS = false): string
    {

        $sex = self::stringSexNumber($sex);
        if (isset($GLOBALS['cdc_data_tables'][$stat])) {
            $table = $GLOBALS['cdc_data_tables'][$stat];
        } else {
            return "Unknown Stat";
        }

        $difference = "(?-" . self::COL_AGE_MOS .  ")";
        $delta = "ABS" . $difference;
        $parameters = array();
        $sql_get_lms = "SELECT " . self::COL_AGE_MOS .  ",L,M,S," . $delta . " as delta";
        array_push($parameters, $age);

        $sql_get_lms .= " FROM " . $table;
        $sql_get_lms .= " WHERE IF(" . self::COL_AGE_MOS .  "=0,?=0,-0.5<=" . $difference . " AND " . $difference . " <0.5) ";
        array_push($parameters, $age); // Only use 0 value if it is AT birth

        $sql_get_lms .= " AND " . self::COL_SEX . "=? ";
        array_push($parameters, $sex);

        $sql_get_lms .= " ORDER BY " . $delta . " ASC LIMIT 1";
        array_push($parameters, $age);

        $lms = sqlQuery($sql_get_lms, $parameters);
         // If we can't lookup the proper parameters to use, return null
        if ($lms === false) {
            return 0;
        }

        $z = self::xToZLms($x, $lms['L'], $lms['M'], $lms['S']);
        return 100 * ( self::cdf($z));
    }


    public static function cdcWeightHeight($weight, $height, $sex, $hasDS = false)
    {
        $parameters = array();
        $sex = self::stringSexNumber($sex);
        $table = "cdc_weight_height";
        $difference = "(?-" . self::COL_SEX . ")";
        $delta = "ABS" . $difference;

        $sql_get_lms = " SELECT " . self::COL_SEX . ",L,M,S," . $delta . " as delta";
        array_push($parameters, $height);
        $sql_get_lms .= " FROM " . $table;
        $sql_get_lms .= " WHERE -0.5<=" . $difference . " AND " . $difference . "<0.5 ";
        array_push($parameters, $height, $height); // Only use 0 value if it is AT birth
        $sql_get_lms .= "  AND " . self::COL_SEX . "=? ";
        array_push($parameters, $sex);
        $sql_get_lms .= " ORDER BY " . $delta . " ASC LIMIT 1";
        array_push($parameters, $height);

        $lms = sqlQuery($sql_get_lms, $parameters);
        // If we can't lookup the proper parameters to use, return null
        if ($lms === false) {
            return 0;
        }

        $z = self::xToZLms($weight, $lms['L'], $lms['M'], $lms['S']);

        return 100 * (self::cdf($z));
    }

    public static function bmiPctToStatus($pct): string
    {
        if ($pct < 5) {
            return "Underweight";
        } else if ($pct < 85) {
            return "Healthy weight";
        } else if ($pct < 95) {
            return "Overweight";
        } else {
            return "Obese";
        }
    }



    public static function whoWeightHeight($weight, $height, $sex): string
    {
        $sex = strtolower($sex);
        if ($sex === 'male' || $sex == 'boys' || $sex == 1) {
            $table = self::TBL_WHO_WEIGHT_LENGTH_MALE;
        } else if ($sex == 'female' || $sex == 'girls' || $sex == 2) {
            $table = self::TBL_WHO_WEIGHT_LENGTH_FEMALE;
        } else {
            return 0;
        }

        $difference = " (?-Height)";
        $delta = "ABS" . $difference;
        $parameters = array();
        $sql_get_lms = "SELECT Height, L, M, S," . $delta . " as delta";
        array_push($parameters, $height);
        $sql_get_lms .= " FROM " . $table;
        $sql_get_lms .= " WHERE -0.25<= " . $difference . " AND " . $difference . "<0.25 ";
        array_push($parameters, $height, $height);
        $sql_get_lms .= " ORDER BY " . $delta . " ASC LIMIT 1";
        array_push($parameters, $height);

        $lms = sqlQuery($sql_get_lms, $parameters);
        // If we can't lookup the proper parameters to use, return null
        if ($lms === false) {
            return 0;
        }
        $z = self::xToZLms($weight, $lms['L'], $lms['M'], $lms['S']);
        return 100 * ( self::cdf($z));
    }

    private static function whoGetTableName($stat)
    {
        $table = "who_";
        if ($stat === 'bmi') {
            $table .= 'bfa_';
            $ageColumn = "Age";
        } else if ($stat === 'head') {
            $table .= 'hcfa_';
            $ageColumn = "Age";
        } else if ($stat === 'weight') {
            $table .= 'wfa_';
            $ageColumn = "Age";
            //current WHO percentile sheet uses 'Day' instead of 'Age'
        } else if ($stat === 'height') {
            $table .= 'lhfa_';
            $ageColumn = "Day";
        } else {
            return false;
        }
        return array('table' => $table, 'column' => $ageColumn);
    }

    public static function whoZScore($value, $age, $sex, $stat = '')
    {
        // get the table

        $table = self::whoGetTableName($stat)['table'];
        $column = "Day"; //more WHO inconsistency in naming convention. Ugh!
        $table .= $sex . "_zscore_expanded_tables_birth_to_5yrs";

        //get S0, s1
        $query = sqlQuery("Select * from $table where $column = ?", array($age));

        //calculate Z-score
        $z = ($value - $query['SD0'] ) / $query['SD1'];
        $query['Zscore'] = $z;
        return $query;
    }

    public static function whoAgePercentile($x, $age, $sex, $stat)
    {
        //Thank you WHO for providing one report that does not follow a naming convention.
        $ageColumn = '';

        $table = self::whoGetTableName($stat)['table'];
        $ageColumn = self::whoGetTableName($stat)['column'];
        $table .= $sex . "_percentiles_expanded_tables_birth_to_5yrs";
        $difference = "(?-" . $ageColumn . ")";
        $delta = "ABS" . $difference;
        $parameters = array();
        $sql_get_lms = "SELECT " . $ageColumn . ", L, M, S," . $delta . " as delta";
        array_push($parameters, $age);
        $sql_get_lms .= " FROM " . $table;
        $sql_get_lms .= " WHERE -0.5<=" . $difference . " AND " . $difference . "<0.5 ";
        array_push($parameters, $age, $age);
        $sql_get_lms .= " ORDER BY " . $delta . " ASC LIMIT 1";
        array_push($parameters, $age);

        $lms = sqlQuery($sql_get_lms, $parameters);
        error_log($stat . ":" . $x . ":" . $lms['L'] . ":" . $lms['M'] . ":" . $lms['S']);
        // If we can't lookup the proper parameters to use, return null
        if ($lms === false) {
            error_log("No LMS value!:" . $age);
            return 0;
        }

        $z = self::xToZLms($x, $lms['L'], $lms['M'], $lms['S']);
        return 100 * ( self::cdf($z));
    }

    public static function getCdcStats($age, $sex, $weight, $height, $bmi, $hasDS = false): array
    {
        $retval = array();
        $retval['BMI_pct'] = self::cdcAgePercentile($bmi, $age, $sex, 'bmi', $hasDS);
        $retval['weight_height_pct'] = self::cdcWeightHeight($weight, $height, $sex, $hasDS);
        $retval['weight_pct'] = self::cdcAgePercentile($weight, $age, $sex, 'weight', $hasDS);
        $retval['height_pct'] = self::cdcAgePercentile($height, $age, $sex, 'height', $hasDS);
        return $retval;
    }

    public static function getWhoStats($ageinDays, $sex, $weight, $height, $head): array
    {
        $retval = array();
        $age = ceil($ageinDays);
        if (strtolower($sex) === 'male') {
            $sex = 'boys';
        } else {
            $sex = 'girls';
        }

        $retval['head_pct'] = self::whoAgePercentile($head, $age, $sex, 'head');
        $retval['head_Z'] = self::whoZscore($head, $age, $sex, 'head');
        $retval['weight_height_pct'] = self::whoWeightHeight($weight, $height, $sex);
        $retval['weight_pct'] = self::whoAgePercentile($weight, $age, $sex, 'weight');
        $retval['weight_Z'] = self::whoZscore($weight, $age, $sex, 'weight');
        $retval['height_pct'] = self::whoAgePercentile($height, $age, $sex, 'height');
        $retval['height_Z'] = self::whoZscore($height, $age, $sex, 'height');
        $bmi = number_format(($weight) / pow(($height * .01), 2), 2) ;
        $retval['bmi_pct'] = self::whoAgePercentile($bmi, $age, $sex, 'bmi');
        $retval['bmi_Z'] = self::whoZscore($bmi, $age, $sex, 'bmi');

        return $retval;
    }

    public static function erf($x): float
    {
        $t = 1 / (1 + (0.5) * abs($x));
        $tau = $t * exp(- ($x * $x) - 1.26551223 +
                $t * (1.00002368 +                                // $t
                    $t * (0.37409196 +                            // $t^2
                        $t * (0.09678418 +                        // $t^3
                            $t * (-0.18628806 +                   // $t^4
                                $t * (0.27886807 +                // $t^5
                                    $t * (-1.13520398 +           // $t^6
                                        $t * (1.48851587 +        // $t^7
                                            $t * (-0.82215223 +   // $t^8
                                                $t * (0.17087227))) ))) )))); // $t^9 (9 close parens to close the polynomial
 //close parenthesis for exp
        if ($x >= 0) {
            return 1 - $tau;
        } else {
            return $tau - 1;
        }
    }
    /**
     * The cumulative distribution function computed in terms of erf
     *
     * @param type $n
     * @return type
     */
    public static function cdf($n): float
    {

        return (1 + self::erf($n / sqrt(2))) / 2;
    }

    public static function xToZLms($x, $l, $m, $s): float
    {
        $x = floatval($x);
        $l = floatval($l);
        $m = floatval($m);

        if ($l == 0) {
            return log($x / $m) / $s;
        } else {
            return (pow(($x / $m), $l) - 1) / ($l * $s);
        }
    }

    public static function getMeasurementType($measurement)
    {

        if (
            $measurement == 'weight' ||
            $measurement == 'height' ||
            $measurement == 'head_circ'
        ) {
            return 'measurement';
        } else if (
            $measurement == 'bps' ||
            $measurement == 'bpd' ||
            $measurement == 'temperature' ||
            $measurement == 'respiration'
        ) {
            return 'vitals';
        } else if (
            $measurement == 'oxygen_saturation' ||
            $measurement == 'pulse'
        ) {
            return 'pulse';
        } else if (
            $measurement == 'Age' ||
            $measurement == 'Age in Days' ||
            $measurement == 'date'
        ) {
            return 'summary';
        } else if (
            $measurement == 'BMI' ||
            $measurement == 'BMI_status' ||
            $measurement == 'waist_circ'
        ) {
            return 'measurement_2';
        }
        echo $measurement;
    }
}
