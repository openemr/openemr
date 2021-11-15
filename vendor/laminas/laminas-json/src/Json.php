<?php

/**
 * @see       https://github.com/laminas/laminas-json for the canonical source repository
 * @copyright https://github.com/laminas/laminas-json/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-json/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Json;

use Laminas\Json\Exception\RuntimeException;
use SplQueue;

/**
 * Class for encoding to and decoding from JSON.
 */
class Json
{
    /**
     * How objects should be encoded: as arrays or as stdClass.
     *
     * TYPE_ARRAY is 1, which also conveniently evaluates to a boolean true
     * value, allowing it to be used with ext/json's functions.
     */
    const TYPE_ARRAY  = 1;
    const TYPE_OBJECT = 0;

    /**
     * Whether or not to use the built-in PHP functions.
     *
     * @var bool
     */
    public static $useBuiltinEncoderDecoder = false;

    /**
     * Decodes the given $encodedValue string from JSON.
     *
     * Uses json_decode() from ext/json if available.
     *
     * @param string $encodedValue Encoded in JSON format
     * @param int $objectDecodeType Optional; flag indicating how to decode
     *     objects. See {@link Decoder::decode()} for details.
     * @return mixed
     * @throws RuntimeException
     */
    public static function decode($encodedValue, $objectDecodeType = self::TYPE_OBJECT)
    {
        $encodedValue = (string) $encodedValue;
        if (function_exists('json_decode') && static::$useBuiltinEncoderDecoder !== true) {
            return self::decodeViaPhpBuiltIn($encodedValue, $objectDecodeType);
        }

        return Decoder::decode($encodedValue, $objectDecodeType);
    }

    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * Encodes using ext/json's json_encode() if available.
     *
     * NOTE: Object should not contain cycles; the JSON format
     * does not allow object reference.
     *
     * NOTE: Only public variables will be encoded
     *
     * NOTE: Encoding native javascript expressions are possible using Laminas\Json\Expr.
     *       You can enable this by setting $options['enableJsonExprFinder'] = true
     *
     * @see Laminas\Json\Expr
     *
     * @param  mixed $valueToEncode
     * @param  bool $cycleCheck Optional; whether or not to check for object recursion; off by default
     * @param  array $options Additional options used during encoding
     * @return string JSON encoded object
     */
    public static function encode($valueToEncode, $cycleCheck = false, array $options = [])
    {
        if (is_object($valueToEncode)) {
            if (method_exists($valueToEncode, 'toJson')) {
                return $valueToEncode->toJson();
            }

            if (method_exists($valueToEncode, 'toArray')) {
                return static::encode($valueToEncode->toArray(), $cycleCheck, $options);
            }
        }

        // Pre-process and replace javascript expressions with placeholders
        $javascriptExpressions = new SplQueue();
        if (isset($options['enableJsonExprFinder'])
           && $options['enableJsonExprFinder'] == true
        ) {
            $valueToEncode = static::recursiveJsonExprFinder($valueToEncode, $javascriptExpressions);
        }

        // Encoding
        $prettyPrint = (isset($options['prettyPrint']) && ($options['prettyPrint'] === true));
        $encodedResult = self::encodeValue($valueToEncode, $cycleCheck, $options, $prettyPrint);

        // Post-process to revert back any Laminas\Json\Expr instances.
        $encodedResult = self::injectJavascriptExpressions($encodedResult, $javascriptExpressions);

        return $encodedResult;
    }

    /**
     * Discover and replace javascript expressions with temporary placeholders.
     *
     * Check each value to determine if it is a Laminas\Json\Expr; if so, replace the value with
     * a magic key and add the javascript expression to the queue.
     *
     * NOTE this method is recursive.
     *
     * NOTE: This method is used internally by the encode method.
     *
     * @see encode
     * @param mixed $value a string - object property to be encoded
     * @param SplQueue $javascriptExpressions
     * @param null|string|int $currentKey
     * @return mixed
     */
    protected static function recursiveJsonExprFinder(
        $value,
        SplQueue $javascriptExpressions,
        $currentKey = null
    ) {
        if ($value instanceof Expr) {
            // TODO: Optimize with ascii keys, if performance is bad
            $magicKey = "____" . $currentKey . "_" . (count($javascriptExpressions));

            $javascriptExpressions->enqueue([
                // If currentKey is integer, encodeUnicodeString call is not required.
                'magicKey' => (is_int($currentKey)) ? $magicKey : Encoder::encodeUnicodeString($magicKey),
                'value'    => $value,
            ]);

            return $magicKey;
        }

        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = static::recursiveJsonExprFinder($value[$k], $javascriptExpressions, $k);
            }
            return $value;
        }

        if (is_object($value)) {
            foreach ($value as $k => $v) {
                $value->$k = static::recursiveJsonExprFinder($value->$k, $javascriptExpressions, $k);
            }
            return $value;
        }

        return $value;
    }

    /**
     * Pretty-print JSON string
     *
     * Use 'indent' option to select indentation string; by default, four
     * spaces are used.
     *
     * @param string $json Original JSON string
     * @param array $options Encoding options
     * @return string
     */
    public static function prettyPrint($json, array $options = [])
    {
        $indentString = isset($options['indent']) ? $options['indent'] : '    ';

        $json = trim($json);
        $length = strlen($json);
        $stack = [];

        $result = '';
        $inLiteral = false;

        for ($i = 0; $i < $length; ++$i) {
            switch ($json[$i]) {
                case '{':
                case '[':
                    if ($inLiteral) {
                        break;
                    }

                    $stack[] = $json[$i];

                    $result .= $json[$i];
                    while (isset($json[$i + 1]) && preg_match('/\s/', $json[$i + 1])) {
                        ++$i;
                    }
                    if (isset($json[$i + 1]) && $json[$i + 1] !== '}' && $json[$i + 1] !== ']') {
                        $result .= "\n" . str_repeat($indentString, count($stack));
                    }

                    continue 2;
                case '}':
                case ']':
                    if ($inLiteral) {
                        break;
                    }

                    $last = end($stack);
                    if (($last === '{' && $json[$i] === '}')
                        || ($last === '[' && $json[$i] === ']')
                    ) {
                        array_pop($stack);
                    }

                    $result .= $json[$i];
                    while (isset($json[$i + 1]) && preg_match('/\s/', $json[$i + 1])) {
                        ++$i;
                    }
                    if (isset($json[$i + 1]) && ($json[$i + 1] === '}' || $json[$i + 1] === ']')) {
                        $result .= "\n" . str_repeat($indentString, count($stack) - 1);
                    }

                    continue 2;
                case '"':
                    $result .= '"';

                    if (! $inLiteral) {
                        $inLiteral = true;
                    } else {
                        $backslashes = 0;
                        $n = $i;
                        while ($json[--$n] === '\\') {
                            ++$backslashes;
                        }

                        if (($backslashes % 2) === 0) {
                            $inLiteral = false;

                            while (isset($json[$i + 1]) && preg_match('/\s/', $json[$i + 1])) {
                                ++$i;
                            }

                            if (isset($json[$i + 1]) && ($json[$i + 1] === '}' || $json[$i + 1] === ']')) {
                                $result .= "\n" . str_repeat($indentString, count($stack) - 1);
                            }
                        }
                    }
                    continue 2;
                case ':':
                    if (! $inLiteral) {
                        $result .= ': ';
                        continue 2;
                    }
                    break;
                case ',':
                    if (! $inLiteral) {
                        $result .= ',' . "\n" . str_repeat($indentString, count($stack));
                        continue 2;
                    }
                    break;
                default:
                    if (! $inLiteral && preg_match('/\s/', $json[$i])) {
                        continue 2;
                    }
                    break;
            }

            $result .= $json[$i];

            if ($inLiteral) {
                continue;
            }

            while (isset($json[$i + 1]) && preg_match('/\s/', $json[$i + 1])) {
                ++$i;
            }

            if (isset($json[$i + 1]) && ($json[$i + 1] === '}' || $json[$i + 1] === ']')) {
                $result .= "\n" . str_repeat($indentString, count($stack) - 1);
            }
        }

        return $result;
    }

    /**
     * Decode a value using the PHP built-in json_decode function.
     *
     * @param string $encodedValue
     * @param int $objectDecodeType
     * @return mixed
     * @throws RuntimeException
     */
    private static function decodeViaPhpBuiltIn($encodedValue, $objectDecodeType)
    {
        $decoded = json_decode($encodedValue, (bool) $objectDecodeType);

        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return $decoded;
            case JSON_ERROR_DEPTH:
                throw new RuntimeException('Decoding failed: Maximum stack depth exceeded');
            case JSON_ERROR_CTRL_CHAR:
                throw new RuntimeException('Decoding failed: Unexpected control character found');
            case JSON_ERROR_SYNTAX:
                throw new RuntimeException('Decoding failed: Syntax error');
            default:
                throw new RuntimeException('Decoding failed');
        }
    }

    /**
     * Encode a value to JSON.
     *
     * Intermediary step between injecting JavaScript expressions.
     *
     * Delegates to either the PHP built-in json_encode operation, or the
     * Encoder component, based on availability of the built-in and/or whether
     * or not the component encoder is requested.
     *
     * @param mixed $valueToEncode
     * @param bool $cycleCheck
     * @param array $options
     * @param bool $prettyPrint
     * @return string
     */
    private static function encodeValue($valueToEncode, $cycleCheck, array $options, $prettyPrint)
    {
        if (function_exists('json_encode') && static::$useBuiltinEncoderDecoder !== true) {
            return self::encodeViaPhpBuiltIn($valueToEncode, $prettyPrint);
        }

        return self::encodeViaEncoder($valueToEncode, $cycleCheck, $options, $prettyPrint);
    }

    /**
     * Encode a value to JSON using the PHP built-in json_encode function.
     *
     * Uses the encoding options:
     *
     * - JSON_HEX_TAG
     * - JSON_HEX_APOS
     * - JSON_HEX_QUOT
     * - JSON_HEX_AMP
     *
     * If $prettyPrint is boolean true, also uses JSON_PRETTY_PRINT.
     *
     * @param mixed $valueToEncode
     * @param bool $prettyPrint
     * @return string|false Boolean false return value if json_encode is not
     *     available, or the $useBuiltinEncoderDecoder flag is enabled.
     */
    private static function encodeViaPhpBuiltIn($valueToEncode, $prettyPrint = false)
    {
        if (! function_exists('json_encode') || static::$useBuiltinEncoderDecoder === true) {
            return false;
        }

        $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

        if ($prettyPrint) {
            $encodeOptions |= JSON_PRETTY_PRINT;
        }

        return json_encode($valueToEncode, $encodeOptions);
    }

    /**
     * Encode a value to JSON using the Encoder class.
     *
     * Passes the value, cycle check flag, and options to Encoder::encode().
     *
     * Once the result is returned, determines if pretty printing is required,
     * and, if so, returns the result of that operation, otherwise returning
     * the encoded value.
     *
     * @param mixed $valueToEncode
     * @param bool $cycleCheck
     * @param array $options
     * @param bool $prettyPrint
     * @return string
     */
    private static function encodeViaEncoder($valueToEncode, $cycleCheck, array $options, $prettyPrint)
    {
        $encodedResult = Encoder::encode($valueToEncode, $cycleCheck, $options);

        if ($prettyPrint) {
            return self::prettyPrint($encodedResult, ['indent' => '    ']);
        }

        return $encodedResult;
    }

    /**
     * Inject javascript expressions into the encoded value.
     *
     * Loops through each, substituting the "magicKey" of each with its
     * associated value.
     *
     * @param string $encodedValue
     * @param SplQueue $javascriptExpressions
     * @return string
     */
    private static function injectJavascriptExpressions($encodedValue, SplQueue $javascriptExpressions)
    {
        foreach ($javascriptExpressions as $expression) {
            $encodedValue = str_replace(
                sprintf('"%s"', $expression['magicKey']),
                $expression['value'],
                (string) $encodedValue
            );
        }

        return $encodedValue;
    }
}
