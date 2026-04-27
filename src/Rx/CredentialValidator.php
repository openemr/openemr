<?php

/**
 * Utility class for validating Ensora eRx credentials and detecting authentication failures.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Sherwin Gaddis <sherwingaddis@gmail.com>
 * @copyright Copyright (c) 2025 Sherwin Gaddis <sherwingaddis@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Rx;

class CredentialValidator
{
    private const REQUIRED_FIELDS = [
        'erx_account_partner_name' => 'Partner Name',
        'erx_account_name' => 'Account Name',
        'erx_account_password' => 'Account Password',
    ];

    private const AUTH_FAILURE_INDICATORS = [
        'authentication',
        'unauthorized',
        'invalid credentials',
        'access denied',
    ];

    /**
     * Check if all required Ensora eRx credentials are configured.
     *
     * @param array<mixed> $globals Globals configuration array (e.g. $GLOBALS)
     */
    public static function hasRequiredCredentials(array $globals): bool
    {
        return count(self::getMissingCredentials($globals)) === 0;
    }

    /**
     * Check if an XML response indicates an authentication failure.
     *
     * Parses the response and matches indicators against the text of error
     * elements only — not the whole document — so successful responses that
     * happen to contain words like "credential" in element names don't trigger
     * a false positive.
     */
    public static function isAuthenticationError(string $xml): bool
    {
        if ($xml === '') {
            return false;
        }

        $previousUseErrors = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        if ($document === false) {
            return false;
        }

        foreach (self::extractErrorMessages($document) as $message) {
            $lower = strtolower($message);
            foreach (self::AUTH_FAILURE_INDICATORS as $indicator) {
                if (str_contains($lower, $indicator)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get missing credential field labels keyed by globals key.
     *
     * @param array<mixed> $globals Globals configuration array (e.g. $GLOBALS)
     * @return array<string, string>
     */
    public static function getMissingCredentials(array $globals): array
    {
        return array_filter(
            self::REQUIRED_FIELDS,
            static fn(string $field): bool => !isset($globals[$field]) || $globals[$field] === '',
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Yield the text content of any element whose name suggests it carries
     * an error message (Error, ErrorMessage, Description, Message, etc.).
     *
     * @return iterable<string>
     */
    private static function extractErrorMessages(\SimpleXMLElement $element): iterable
    {
        $name = strtolower($element->getName());
        if (str_contains($name, 'error') || str_contains($name, 'message') || str_contains($name, 'description')) {
            $text = trim((string) $element);
            if ($text !== '') {
                yield $text;
            }
        }

        foreach ($element->children() as $child) {
            yield from self::extractErrorMessages($child);
        }
    }
}
