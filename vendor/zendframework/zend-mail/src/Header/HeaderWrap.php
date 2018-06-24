<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Header;

use Zend\Mail\Headers;
use Zend\Mime\Mime;

/**
 * Utility class used for creating wrapped or MIME-encoded versions of header
 * values.
 */
abstract class HeaderWrap
{
    /**
     * Wrap a long header line
     *
     * @param  string          $value
     * @param  HeaderInterface $header
     * @return string
     */
    public static function wrap($value, HeaderInterface $header)
    {
        if ($header instanceof UnstructuredInterface) {
            return static::wrapUnstructuredHeader($value, $header);
        } elseif ($header instanceof StructuredInterface) {
            return static::wrapStructuredHeader($value, $header);
        }
        return $value;
    }

    /**
     * Wrap an unstructured header line
     *
     * Wrap at 78 characters or before, based on whitespace.
     *
     * @param string          $value
     * @param HeaderInterface $header
     * @return string
     */
    protected static function wrapUnstructuredHeader($value, HeaderInterface $header)
    {
        $encoding = $header->getEncoding();
        if ($encoding == 'ASCII') {
            return wordwrap($value, 78, Headers::FOLDING);
        }
        return static::mimeEncodeValue($value, $encoding, 78);
    }

    /**
     * Wrap a structured header line
     *
     * @param  string              $value
     * @param  StructuredInterface $header
     * @return string
     */
    protected static function wrapStructuredHeader($value, StructuredInterface $header)
    {
        $delimiter = $header->getDelimiter();

        $length = strlen($value);
        $lines  = [];
        $temp   = '';
        for ($i = 0; $i < $length; $i++) {
            $temp .= $value[$i];
            if ($value[$i] == $delimiter) {
                $lines[] = $temp;
                $temp    = '';
            }
        }
        return implode(Headers::FOLDING, $lines);
    }

    /**
     * MIME-encode a value
     *
     * Performs quoted-printable encoding on a value, setting maximum
     * line-length to 998.
     *
     * @param  string $value
     * @param  string $encoding
     * @param  int    $lineLength maximum line-length, by default 998
     * @return string Returns the mime encode value without the last line ending
     */
    public static function mimeEncodeValue($value, $encoding, $lineLength = 998)
    {
        return Mime::encodeQuotedPrintableHeader($value, $encoding, $lineLength, Headers::EOL);
    }

    /**
     * MIME-decode a value
     *
     * Performs quoted-printable decoding on a value.
     *
     * @param  string $value
     * @return string Returns the mime encode value without the last line ending
     */
    public static function mimeDecodeValue($value)
    {
        // unfold first, because iconv_mime_decode is discarding "\n" with no apparent reason
        // making the resulting value no longer valid.

        // see https://tools.ietf.org/html/rfc2822#section-2.2.3 about unfolding
        $parts = explode(Headers::FOLDING, $value);
        $value = implode(' ', $parts);

        $decodedValue = iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');

        // imap (unlike iconv) can handle multibyte headers which are splitted across multiple line
        if (self::isNotDecoded($value, $decodedValue) && extension_loaded('imap')) {
            return array_reduce(
                imap_mime_header_decode(imap_utf8($value)),
                function ($accumulator, $headerPart) {
                    return $accumulator . $headerPart->text;
                },
                ''
            );
        }

        return $decodedValue;
    }

    private static function isNotDecoded($originalValue, $value)
    {
        return 0 === strpos($value, '=?')
            && strlen($value) - 2 === strpos($value, '?=')
            && false !== strpos($originalValue, $value);
    }

    /**
     * Test if is possible apply MIME-encoding
     *
     * @param string $value
     * @return bool
     */
    public static function canBeEncoded($value)
    {
        // avoid any wrapping by specifying line length long enough
        // "test" -> 4
        // "x-test: =?ISO-8859-1?B?dGVzdA==?=" -> 33
        //  8       +2          +3         +3  -> 16
        $charset = 'UTF-8';
        $lineLength = strlen($value) * 4 + strlen($charset) + 16;

        $preferences = [
            'scheme' => 'Q',
            'input-charset' => $charset,
            'output-charset' => $charset,
            'line-length' => $lineLength,
        ];

        $encoded = iconv_mime_encode('x-test', $value, $preferences);

        return (false !== $encoded);
    }
}
