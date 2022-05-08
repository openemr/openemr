<?php

namespace OpenEMR\Services\Cda;

use DOMElement;
use ErrorException;

/**
 * Helpers for work with LibXML and DOM.
 *
 * @author  Miloslav Hůla (https://github.com/milo)
 */
class SchematronHelpers
{
    /** @var array */
    private static $handleXmlErrors = array();


    /**
     * Enable LibXML internal error handling.
     *
     * @param bool  clear existing errors
     */
    public static function handleXmlErrors($clear = true)
    {
        self::$handleXmlErrors[] = libxml_use_internal_errors(true);
        $clear && libxml_clear_errors();
    }


    /**
     * Fetch all LibXML errors.
     *
     * @param bool
     * @return NULL|ErrorException  all errors chained in exceptions
     */
    public static function fetchXmlErrors($restoreHandling = true)
    {
        $e = null;
        foreach (array_reverse(libxml_get_errors()) as $error) {
            $e = new ErrorException(trim($error->message), $error->code, $error->level, $error->file, $error->line, $e);
        }
        libxml_clear_errors();
        $restoreHandling && self::restoreErrorHandling();
        return $e;
    }


    /**
     * Restore LibXML internal error handling previously enabled by self::handleXmlErrors()
     */
    public static function restoreErrorHandling()
    {
        libxml_use_internal_errors(array_pop(self::$handleXmlErrors));
    }


    /**
     * Returns value of element attribute.
     *
     * @param DOMElement
     * @param string  attribute name
     * @param mixed  default value if attribude does not exist
     * @return mixed
     * @throws SchematronException  when attribute does not exist and default value is not specified
     */
    public static function getAttribute(DOMElement $element, $name)
    {
        if ($element->hasAttribute($name)) {
            return $element->getAttribute($name);
        } elseif (count($args = func_get_args()) > 2) {
            return $args[2];
        }

        throw new SchematronException("Missing required attribute '$name' for element <$element->nodeName> on line {$element->getLineNo()}.");
    }
}
