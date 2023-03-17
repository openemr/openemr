<?php
/**
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 *
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2022 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */
namespace OpenEMR\Modules\ClaimRevConnector;

class PrintProperty 
{
    public static function DisplayProperty($title, $propertyValue, $style="" ,$qualifier = "", $ending = "")
    {
        if($propertyValue != '')
        {
            echo("<div class='row'>");
                echo("<div class='col'>");
                    echo("<strong>");
                        echo($title);
                    echo("</strong>");
                echo("</div>");
                echo("<div class='col' style='" . $style  . "' >");
                    if($ending == "%")
                    {
                        $propertyValue = $propertyValue * 100;
                    } 
                    echo($qualifier . $propertyValue . $ending); 
                 echo("</div>");
            echo("</div>");
        }
    }
    public static function DisplayDateProperty($title, $propertyValue)
    {
        //if the property value was "" then it used today's date.  we don't want that!
        if($propertyValue != '')
        {
            $date = date_create($propertyValue);
            $strDate = date_format($date, 'Y-m-d');
            PrintProperty::DisplayProperty($title,$strDate);
        }

    }
}

?>