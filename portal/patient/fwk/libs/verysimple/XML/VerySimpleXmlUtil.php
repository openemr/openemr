<?php

/** @package    verysimple::XML */

/**
 * require supporting files
 */
require_once("ParseException.php");

/**
 * VerySimpleXmlUtil provides a collection of static methods that are useful when
 * dealing with XML
 *
 * @package verysimple::XML
 * @author VerySimple Inc.
 * @copyright 1997-2007 VerySimple, Inc.
 * @license http://www.gnu.org/licenses/lgpl.html LGPL
 * @version 3.2
 */
class VerySimpleXmlUtil
{
    // replacement variable for inner text and for attribute values
    static $reservedAttrib = array (
            "&",
            "\"",
            "'",
            "<",
            ">"
    );
    static $replacementsAttrib = array (
            "&amp;",
            "&quot;",
            "&apos;",
            "&lt;",
            "&gt;"
    );
    static $replacementsTempAttrib = array (
            "~amp;",
            "~quot;",
            "~apos;",
            "~lt;",
            "~gt;"
    );
    static $reservedText = array (
            "&",
            "<",
            ">"
    );
    static $replacementsText = array (
            "&amp;",
            "&lt;",
            "&gt;"
    );
    static $replacementsTempText = array (
            "~amp;",
            "~lt;",
            "~gt;"
    );

    /**
     * Parses the given XML using SimpleXMLElement, however traps PHP errors and
     * warnings and converts them to an Exception that you can catch.
     * Surround
     * this statement with try/catch and you can handle parsing exceptions instead
     * of allowing PHP to terminate or write errors to the browser
     *
     * @param string $xml
     *          to parse
     * @param string $emptyVal
     *          if $xml is empty, default to this value (ex "<xml/>")
     * @param bool $reAttempt
     *          if true then xml that cannot be parsed will be re-tried once with all non-ascii chars stripped
     *
     * @return SimpleXMLElement
     */
    static function SafeParse($xml, $emptyVal = null, $reAttempt = true)
    {
        if (! $xml) {
            $xml = $emptyVal;
            if (! $xml) {
                throw new Exception('Empty string could not be parsed as XML');
            }
        }

        // re-route error handling temporarily so we can convert PHP errors to an exception
        set_error_handler(array (
                "VerySimpleXmlUtil",
                "HandleParseException"
        ), E_ALL);

        try {
            $element = new SimpleXMLElement($xml);
        } catch (Exception $ex1) {
            // the xml could not be parsed, SimpleXMLElement is very picky about non-ascii characters
            // so if specified then re-try it with all non-ascii characters stripped
            if ($reAttempt) {
                try {
                    // $xml = @iconv('UTF-8', "ISO-8859-1//IGNORE", $xml); // this doesn't seem to work
                    $xml = preg_replace('/[[:^print:]]/', '?', $xml); // this is heavy-handed but works
                    $element = new SimpleXMLElement($xml);
                } catch (Exception $ex2) {
                    // re-throw the first exception so we don't confuse the error due to the second attempt
                    throw $ex1;
                }
            } else {
                throw $ex1;
            }
        }

        // reset error handling back to whatever it was
        restore_error_handler();

        return $element;
    }

    /**
     * Escapes special characters that will corrupt XML
     *
     * @param
     *          String to be escaped
     * @param
     *          bool (default false) true if you are escaping an attribute (ie <field attribute="" />)
     */
    static function Escape($str, $escapeQuotes = false)
    {
        if ($escapeQuotes) {
            $str = str_replace(VerySimpleXmlUtil::$replacementsAttrib, VerySimpleXmlUtil::$replacementsTempAttrib, $str);
            $str = str_replace(VerySimpleXmlUtil::$reservedAttrib, VerySimpleXmlUtil::$replacementsAttrib, $str);
            $str = str_replace(VerySimpleXmlUtil::$replacementsTempAttrib, VerySimpleXmlUtil::$replacementsAttrib, $str);
        } else {
            $str = str_replace(VerySimpleXmlUtil::$replacementsText, VerySimpleXmlUtil::$replacementsTempText, $str);
            $str = str_replace(VerySimpleXmlUtil::$reservedText, VerySimpleXmlUtil::$replacementsText, $str);
            $str = str_replace(VerySimpleXmlUtil::$replacementsTempText, VerySimpleXmlUtil::$replacementsText, $str);
        }

        return $str;
    }

    /**
     * UnEscapes special characters from XML that were Escaped
     *
     * @param
     *          string to be unescaped
     * @return string
     */
    static function UnEscape($str)
    {
        return str_replace(VerySimpleXmlUtil::$replacements, VerySimpleXmlUtil::$reserved, $str);
    }

    /**
     * converts a string containing xml into an array.
     * Note that if $recurse is false
     * This this will return a more simple structure but will only parse up to 3 levels
     *
     * @param
     *          string to be unescaped
     * @param
     *          bool (default false) true to recurse
     * @param string $emptyVal
     *          if $xml is empty, default to this value (ex "<xml/>")
     * @return array
     */
    static function ToArray($xmlstring, $recurse = false, $emptyVal = null)
    {
        $xmlstring = trim($xmlstring);

        if (! $xmlstring) {
            $xmlstring = $emptyVal;
            if (! $xmlstring) {
                throw new Exception('Empty string could not be parsed as XML');
            }
        }

        $xml = VerySimpleXmlUtil::SafeParse($xmlstring);
        $array = array ();

        if ($recurse) {
            VerySimpleXmlUtil::RecurseXmlObjToArr($xml, $array);
        } else {
            foreach ($xml as $key => $val) {
                $children = $val->children();

                if ($children) {
                    $grandchildren = $children->children();
                    if ($grandchildren) {
                        $array [$key] = $children;
                    } else {
                        $array [] = $val;
                    }
                } else {
                    $array [strval($key)] = strval($val);
                }
            }
        }

        return $array;
    }

    /**
     * This is method RecurseXmlObjToArr
     *
     * @param mixed $obj
     *          This is a description
     * @param mixed $arr
     *          This is a description
     * @return mixed This is the return value description
     *
     */
    static function RecurseXmlObjToArr($obj, &$arr)
    {
        $children = $obj->children();
        foreach ($children as $elementName => $node) {
            $nextIdx = count($arr);
            $arr [$nextIdx] = array ();
            $arr [$nextIdx] ['@name'] = strtolower((string) $elementName);
            $arr [$nextIdx] ['@attributes'] = array ();
            $attributes = $node->attributes();
            foreach ($attributes as $attributeName => $attributeValue) {
                $attribName = strtolower(trim((string) $attributeName));
                $attribVal = trim((string) $attributeValue);
                $arr [$nextIdx] ['@attributes'] [$attribName] = $attribVal;
            }

            $text = (string) $node;
            $text = trim($text);
            if (strlen($text) > 0) {
                $arr [$nextIdx] ['@text'] = $text;
            }

            $arr [$nextIdx] ['@children'] = array ();
            VerySimpleXmlUtil::RecurseXmlObjToArr($node, $arr [$nextIdx] ['@children']);
        }

        return;
    }

    /**
     * Recurses value and serializes it as an XML string
     *
     * @param variant $var
     *          object, array or value to convert
     * @param string $root
     *          name of the root node (optional)
     * @return string XML
     */
    static function ToXML($var, $root = "")
    {
        $xml = "";

        if (is_object($var)) {
            // object have properties that we recurse
            $name = strlen($root) > 0 && is_numeric($root) == false ? $root : get_class($var);
            $xml .= "<" . $name . ">\n";

            $props = get_object_vars($var);
            foreach (array_keys($props) as $key) {
                $xml .= VerySimpleXmlUtil::ToXML($props [$key], $key);
            }

            $xml .= "</" . $name . ">\n";
        } elseif (is_array($var)) {
            $name = strlen($root) > 0 ? (is_numeric($root) ? "Array_" . $root : $root) : "Array";
            $xml .= "<" . $name . ">\n";

            foreach (array_keys($var) as $key) {
                $xml .= VerySimpleXmlUtil::ToXML($var [$key], $key);
            }

            $xml .= "</" . $name . ">\n";
        } else {
            $name = strlen($root) > 0 ? (is_numeric($root) ? "Value_" . $root : $root) : "Value";
            $xml .= "<" . $name . ">" . VerySimpleXmlUtil::Escape($var) . "</" . $name . ">\n";
        }

        return $xml;
    }

    /**
     * For a node that is known to have inner text and no other child nodes,
     * this returns the inner text and advances the reader curser to the next
     * element.
     * If there is no text, the curser is not advanced
     *
     * @param
     *          XMLReader
     * @param string $default
     *          (optional) if the value is blank
     * @return string
     */
    static function GetInnerText(XMLReader $xml, $default = "")
    {
        if ($xml->isEmptyElement == false && $xml->read() && $xml->nodeType == XMLReader::TEXT) {
            return $xml->value;
        } else {
            return $default;
        }
    }

    /**
     * Given an XMLReader, returns an object that can be inspected using print_r
     *
     * @param
     *          XMLReader
     * @return object
     */
    static function ConvertReader(XMLReader $xml)
    {
        $node_types = array (
                0 => "XMLReader::NONE",
                1 => "XMLReader::ELEMENT",
                2 => "XMLReader::ATTRIBUTE",
                3 => "XMLReader::TEXT",
                4 => "XMLReader::CDATA",
                5 => "XMLReader::ENTITY_REF",
                6 => "XMLReader::ENTITY",
                7 => "XMLReader::PI",
                8 => "XMLReader::COMMENT",
                9 => "XMLReader::DOC",
                10 => "XMLReader::DOC_TYPE",
                11 => "XMLReader::DOC_FRAGMENT",
                12 => "XMLReader::NOTATION",
                13 => "XMLReader::WHITESPACE",
                14 => "XMLReader::SIGNIFICANT_WHITESPACE",
                15 => "XMLReader::END_ELEMENT",
                16 => "XMLReader::END_ENTITY",
                17 => "XMLReader::XML_DECLARATION"
        );

        $obj;
        $obj->attributeCount = $xml->attributeCount;
        $obj->baseURI = $xml->baseURI;
        $obj->depth = $xml->depth;
        $obj->hasAttributes = ($xml->hasAttributes ? 'TRUE' : 'FALSE');
        $obj->hasValue = ($xml->hasValue ? 'TRUE' : 'FALSE');
        $obj->isDefault = ($xml->isDefault ? 'TRUE' : 'FALSE');
        $obj->isEmptyElement = (@$xml->isEmptyElement ? 'TRUE' : 'FALSE');
        $obj->localName = $xml->localName;
        $obj->name = $xml->name;
        $obj->namespaceURI = $xml->namespaceURI;
        $obj->nodeType = $xml->nodeType;
        $obj->nodeTypeDescription = $node_types [$xml->nodeType];
        $obj->prefix = $xml->prefix;
        $obj->value = $xml->value;
        $obj->xmlLang = $xml->xmlLang;

        return $obj;
    }

    /**
     * Handler for catching ParseException errors
     */
    public static function HandleParseException($code, $string, $file, $line, $context)
    {
        throw new ParseException($string, $code);
    }
}
