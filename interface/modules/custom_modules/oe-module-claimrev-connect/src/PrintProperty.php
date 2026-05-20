<?php

/**
 *
 * @package OpenEMR
 * @link    https://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

class PrintProperty
{
    public static function displayProperty(string $title, mixed $propertyValue, string $qualifier = "", string $ending = "", string $style = ""): void
    {
        if (in_array($propertyValue, [null, '', false], true)) {
            return;
        }

        $valueStr = TypeCoerce::asString($propertyValue);
        if ($ending === '%' && (is_int($propertyValue) || is_float($propertyValue))) {
            $valueStr = TypeCoerce::asString($propertyValue * 100);
        }

        echo("<div class='row'>");
            echo("<div class='col'>");
                echo("<strong>");
                    echo text($title);
                echo("</strong>");
            echo("</div>");
            echo("<div class='col' style='" . attr($style)  . "' >");
        echo text($qualifier . $valueStr . $ending);
             echo("</div>");
        echo("</div>");
    }

    public static function displayDateProperty(string $title, mixed $propertyValue): void
    {
        // if the property value was "" then it used today's date. we don't want that!
        if (!is_string($propertyValue) || $propertyValue === '') {
            return;
        }

        $date = date_create($propertyValue);
        if ($date === false) {
            return;
        }
        $strDate = date_format($date, 'Y-m-d');
        self::displayProperty($title, $strDate);
    }

    /**
     * @param iterable<mixed, mixed>|null $validations
     */
    public static function printValidation(string $title, ?iterable $validations): void
    {
        if ($validations === null) {
            return;
        }
        echo("<div class='row'>");
            echo("<div class='col'>");
                echo("<div class='card'>");
                    echo("<div class='card-body'>");
                        echo("<h6>");
                            echo text($title);
                        echo("</h6>");
        foreach ($validations as $validation) {
            if (!is_object($validation)) {
                continue;
            }
            self::displayProperty("Is Valid Request", property_exists($validation, 'validRequestIndicator') ? $validation->validRequestIndicator : '');
            self::displayProperty("Reject Reason", property_exists($validation, 'rejectReasonCode') ? $validation->rejectReasonCode : '');
            self::displayProperty("Description", property_exists($validation, 'description') ? $validation->description : '');
            self::displayProperty("Follow-up Action", property_exists($validation, 'followUpActionCode') ? $validation->followUpActionCode : '');
        }

                    echo("</div>");


                echo("</div>");

            echo("</div>");
        echo("</div>");
    }
}
