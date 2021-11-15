<?php

/**
 * @see       https://github.com/laminas/laminas-stdlib for the canonical source repository
 * @copyright https://github.com/laminas/laminas-stdlib/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-stdlib/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Stdlib\StringWrapper;

use Laminas\Stdlib\Exception;

use function array_map;
use function array_search;
use function extension_loaded;
use function mb_convert_encoding;
use function mb_list_encodings;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;

class MbString extends AbstractStringWrapper
{
    /**
     * List of supported character sets (upper case)
     *
     * @var null|string[]
     * @link http://php.net/manual/mbstring.supported-encodings.php
     */
    protected static $encodings = null;

    /**
     * Get a list of supported character encodings
     *
     * @return string[]
     */
    public static function getSupportedEncodings()
    {
        if (static::$encodings === null) {
            static::$encodings = array_map('strtoupper', mb_list_encodings());

            // FIXME: Converting € (UTF-8) to ISO-8859-16 gives a wrong result
            $indexIso885916 = array_search('ISO-8859-16', static::$encodings, true);
            if ($indexIso885916 !== false) {
                unset(static::$encodings[$indexIso885916]);
            }
        }

        return static::$encodings;
    }

    /**
     * Constructor
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct()
    {
        if (! extension_loaded('mbstring')) {
            throw new Exception\ExtensionNotLoadedException(
                'PHP extension "mbstring" is required for this wrapper'
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
        return mb_strlen($str, $this->getEncoding());
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
        return mb_substr($str, $offset, $length, $this->getEncoding());
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
        return mb_strpos($haystack, $needle, $offset, $this->getEncoding());
    }

    /**
     * Convert a string from defined encoding to the defined convert encoding
     *
     * @param string  $str
     * @param bool $reverse
     * @return string|false
     */
    public function convert($str, $reverse = false)
    {
        $encoding        = $this->getEncoding();
        $convertEncoding = $this->getConvertEncoding();

        if ($convertEncoding === null) {
            throw new Exception\LogicException(
                'No convert encoding defined'
            );
        }

        if ($encoding === $convertEncoding) {
            return $str;
        }

        $fromEncoding = $reverse ? $convertEncoding : $encoding;
        $toEncoding   = $reverse ? $encoding : $convertEncoding;
        return mb_convert_encoding($str, $toEncoding, $fromEncoding);
    }
}
