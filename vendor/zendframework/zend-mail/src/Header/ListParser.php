<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Header;

use function in_array;

/**
 * @internal
 */
class ListParser
{
    const CHAR_QUOTES = ['\'', '"'];
    const CHAR_DELIMS = [',', ';'];
    const CHAR_ESCAPE = '\\';

    /**
     * @param string $value
     * @param array $delims Delimiters allowed between values; parser will
     *     split on these, as long as they are not within quotes. Defaults
     *     to ListParser::CHAR_DELIMS.
     * @return array
     */
    public static function parse($value, array $delims = self::CHAR_DELIMS)
    {
        $values            = [];
        $length            = strlen($value);
        $currentValue      = '';
        $inEscape          = false;
        $inQuote           = false;
        $currentQuoteDelim = null;

        for ($i = 0; $i < $length; $i += 1) {
            $char = $value[$i];

            // If we are in an escape sequence, append the character and continue.
            if ($inEscape) {
                $currentValue .= $char;
                $inEscape = false;
                continue;
            }

            // If we are not in a quoted string, and have a delimiter, append
            // the current value to the list, and reset the current value.
            if (in_array($char, $delims, true) && ! $inQuote) {
                $values [] = $currentValue;
                $currentValue = '';
                continue;
            }

            // Append the character to the current value
            $currentValue .= $char;

            // Escape sequence discovered.
            if (self::CHAR_ESCAPE === $char) {
                $inEscape = true;
                continue;
            }

            // If the character is not a quote character, we are done
            // processing it.
            if (! in_array($char, self::CHAR_QUOTES)) {
                continue;
            }

            // If the character matches a previously matched quote delimiter,
            // we reset our quote status and the currently opened quote
            // delimiter.
            if ($char === $currentQuoteDelim) {
                $inQuote = false;
                $currentQuoteDelim = null;
                continue;
            }

            // Otherwise, we're starting a quoted string.
            $inQuote = true;
            $currentQuoteDelim = $char;
        }

        // If we reached the end of the string and still have a current value,
        // append it to the list (no delimiter was reached).
        if ('' !== $currentValue) {
            $values [] = $currentValue;
        }

        return $values;
    }
}
