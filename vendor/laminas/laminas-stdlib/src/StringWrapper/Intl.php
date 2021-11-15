<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib\StringWrapper;

use Laminas\Stdlib\Exception;

use function extension_loaded;
use function grapheme_strlen;
use function grapheme_strpos;
use function grapheme_substr;

class Intl extends AbstractStringWrapper
{
    /**
     * List of supported character sets (upper case)
     *
     * @var string[]
     */
    protected static $encodings = ['UTF-8'];

    /**
     * Get a list of supported character encodings
     *
     * @return string[]
     */
    public static function getSupportedEncodings()
    {
        return static::$encodings;
    }

    /**
     * Constructor
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct()
    {
        if (! extension_loaded('intl')) {
            throw new Exception\ExtensionNotLoadedException(
                'PHP extension "intl" is required for this wrapper'
            );
        }
    }

    /**
     * Returns the length of the given string
     *
     * @param string $str
     * @return int|false
     */
    public function strlen($str)
    {
        return grapheme_strlen($str);
    }

    /**
     * Returns the portion of string specified by the start and length parameters
     *
     * @param string   $str
     * @param int      $offset
     * @param int|null $length
     * @return string|false
     */
    public function substr($str, $offset = 0, $length = null)
    {
        // Due fix of PHP #62759 The third argument returns an empty string if is 0 or null.
        if ($length !== null) {
            return grapheme_substr($str, $offset, $length);
        }

        return grapheme_substr($str, $offset);
    }

    /**
     * Find the position of the first occurrence of a substring in a string
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     * @return int|false
     */
    public function strpos($haystack, $needle, $offset = 0)
    {
        return grapheme_strpos($haystack, $needle, $offset);
    }
}
