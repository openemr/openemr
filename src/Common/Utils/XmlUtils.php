<?php

/**
 * XmlUtils - Centralized XML parsing utilities.
 *
 * All XML parsing in OpenEMR should go through this class to ensure that:
 * 1. LIBXML_NONET is always applied (prevents XXE / SSRF via external DTDs/entities).
 * 2. Parse failures are handled consistently instead of silently producing a `false`.
 * 3. Future hardening (e.g. LIBXML_DTDATTR, switch to DOMDocument) can be applied
 *    in one place rather than across every call site.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Craig Allen <craigrallen@gmail.com>
 * @copyright Copyright (c) 2026 Craig Allen <craigrallen@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

use SimpleXMLElement;

class XmlUtils
{
    /**
     * Default libxml flags applied to every parse call.
     *
     * - LIBXML_NONET disables network access during parsing, preventing XXE-based
     *   server-side request forgery via external entities or DTDs.
     * - LIBXML_NOERROR suppresses libxml error output during parsing; when
     *   internal error handling is enabled, errors may still accumulate and be
     *   retrieved via libxml_get_errors().
     */
    private const DEFAULT_FLAGS = LIBXML_NONET | LIBXML_NOERROR;

    /**
     * Parse an XML string and return a SimpleXMLElement.
     *
     * Combines LIBXML_NONET with any caller-supplied flags, so callers that need
     * LIBXML_NOCDATA or LIBXML_NOEMPTYTAG can pass those without re-specifying LIBXML_NONET.
     *
     * @param string $xml        The XML string to parse.
     * @param int    $extraFlags Additional libxml flags (e.g. LIBXML_NOCDATA). LIBXML_NONET
     *                           is always applied regardless of this argument.
     * @param string $class      SimpleXMLElement subclass to instantiate (rarely needed).
     *
     * @return SimpleXMLElement
     *
     * @throws XmlParseException If parsing fails, with the first libxml error message included.
     */
    public static function loadString(
        string $xml,
        int $extraFlags = 0,
        string $class = SimpleXMLElement::class
    ): SimpleXMLElement {
        // Always enforce LIBXML_NONET; merge caller's extra flags.
        $flags = self::DEFAULT_FLAGS | $extraFlags;

        $previousErrorHandling = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            $result = simplexml_load_string($xml, $class, $flags);
            $errors = libxml_get_errors();
            libxml_clear_errors();
        } finally {
            libxml_use_internal_errors($previousErrorHandling);
        }

        if ($result === false) {
            $firstError = $errors !== [] ? trim($errors[0]->message) : 'unknown error';
            throw new XmlParseException(
                sprintf('Failed to parse XML: %s', $firstError)
            );
        }

        return $result;
    }

    /**
     * Like loadString() but returns null on failure instead of throwing.
     *
     * Useful in contexts where invalid XML is expected (e.g. user-supplied input that
     * may or may not be XML) and the caller wants to handle the failure gracefully.
     *
     * @param string $xml
     * @param int    $extraFlags
     * @param string $class
     *
     * @return SimpleXMLElement|null
     */
    public static function tryLoadString(
        string $xml,
        int $extraFlags = 0,
        string $class = SimpleXMLElement::class
    ): ?SimpleXMLElement {
        try {
            return self::loadString($xml, $extraFlags, $class);
        } catch (XmlParseException) {
            return null;
        }
    }
}
