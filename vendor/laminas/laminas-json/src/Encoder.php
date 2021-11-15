<?php

/**
 * @see       https://github.com/laminas/laminas-json for the canonical source repository
 * @copyright https://github.com/laminas/laminas-json/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-json/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Json;

use Iterator;
use IteratorAggregate;
use JsonSerializable;
use Laminas\Json\Exception\InvalidArgumentException;
use Laminas\Json\Exception\RecursionException;
use ReflectionClass;

/**
 * Encode PHP constructs to JSON.
 */
class Encoder
{
    /**
     * Whether or not to check for possible cycling.
     *
     * @var bool
     */
    protected $cycleCheck;

    /**
     * Additional options used during encoding.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Array of visited objects; used to prevent cycling.
     *
     * @var array
     */
    protected $visited = [];

    /**
     * @param bool $cycleCheck Whether or not to check for recursion when encoding.
     * @param array $options Additional options used during encoding.
     */
    protected function __construct($cycleCheck = false, array $options = [])
    {
        $this->cycleCheck = $cycleCheck;
        $this->options = $options;
    }

    /**
     * Use the JSON encoding scheme for the value specified.
     *
     * @param mixed $value The value to be encoded.
     * @param bool $cycleCheck Whether or not to check for possible object recursion when encoding.
     * @param array $options Additional options used during encoding.
     * @return string The encoded value.
     */
    public static function encode($value, $cycleCheck = false, array $options = [])
    {
        $encoder = new static($cycleCheck, $options);

        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        return $encoder->encodeValue($value);
    }

    /**
     * Encode a value to JSON.
     *
     * Recursive method which determines the type of value to be encoded
     * and then dispatches to the appropriate method.
     *
     * $values are either
     * - objects (returns from {@link encodeObject()})
     * - arrays (returns from {@link encodeArray()})
     * - scalars (returns from {@link encodeDatum()})
     *
     * @param $value mixed The value to be encoded.
     * @return string Encoded value.
     */
    protected function encodeValue(&$value)
    {
        if (is_object($value)) {
            return $this->encodeObject($value);
        }

        if (is_array($value)) {
            return $this->encodeArray($value);
        }

        return $this->encodeDatum($value);
    }

    /**
     * Encode an object to JSON by encoding each of the public properties.
     *
     * A special property is added to the JSON object called '__className' that
     * contains the classname of $value; this can be used by consumers of the
     * resulting JSON to cast to the specific class.
     *
     * @param $value object
     * @return string
     * @throws RecursionException If recursive checks are enabled and the
     *     object has been serialized previously.
     */
    protected function encodeObject(&$value)
    {
        if ($this->cycleCheck) {
            if ($this->wasVisited($value)) {
                if (! isset($this->options['silenceCyclicalExceptions'])
                    || $this->options['silenceCyclicalExceptions'] !== true
                ) {
                    throw new RecursionException(sprintf(
                        'Cycles not supported in JSON encoding; cycle introduced by class "%s"',
                        get_class($value)
                    ));
                }

                return '"* RECURSION (' . str_replace('\\', '\\\\', get_class($value)) . ') *"';
            }

            $this->visited[] = $value;
        }

        $props = '';

        if (method_exists($value, 'toJson')) {
            $props = ',' . preg_replace("/^\{(.*)\}$/", "\\1", $value->toJson());
        } else {
            if ($value instanceof IteratorAggregate) {
                $propCollection = $value->getIterator();
            } elseif ($value instanceof Iterator) {
                $propCollection = $value;
            } else {
                $propCollection = get_object_vars($value);
            }

            foreach ($propCollection as $name => $propValue) {
                if (! isset($propValue)) {
                    continue;
                }

                $props .= ','
                    . $this->encodeValue($name)
                    . ':'
                    . $this->encodeValue($propValue);
            }
        }

        $className = get_class($value);
        return '{"__className":'
            . $this->encodeString($className)
            . $props . '}';
    }

    /**
     * Determine if an object has been serialized already.
     *
     * @param mixed $value
     * @return bool
     */
    protected function wasVisited(&$value)
    {
        if (in_array($value, $this->visited, true)) {
            return true;
        }

        return false;
    }

    /**
     * JSON encode an array value.
     *
     * Recursively encodes each value of an array and returns a JSON encoded
     * array string.
     *
     * Arrays are defined as integer-indexed arrays starting at index 0, where
     * the last index is (count($array) -1); any deviation from that is
     * considered an associative array, and will be passed to
     * {@link encodeAssociativeArray()}.
     *
     * @param $array array
     * @return string
     */
    protected function encodeArray($array)
    {
        // Check for associative array
        if (! empty($array) && (array_keys($array) !== range(0, count($array) - 1))) {
            // Associative array
            return $this->encodeAssociativeArray($array);
        }

        // Indexed array
        $tmpArray = [];
        $result   = '[';
        $length   = count($array);

        for ($i = 0; $i < $length; $i++) {
            $tmpArray[] = $this->encodeValue($array[$i]);
        }

        $result .= implode(',', $tmpArray);
        $result .= ']';

        return $result;
    }

    /**
     * Encode an associative array to JSON.
     *
     * JSON does not have a concept of associative arrays; as such, we encode
     * them to objects.
     *
     * @param array $array Array to encode.
     * @return string
     */
    protected function encodeAssociativeArray($array)
    {
        $tmpArray = [];
        $result   = '{';

        foreach ($array as $key => $value) {
            $tmpArray[] = sprintf(
                '%s:%s',
                $this->encodeString((string) $key),
                $this->encodeValue($value)
            );
        }

        $result .= implode(',', $tmpArray);
        $result .= '}';
        return $result;
    }

    /**
     * JSON encode a scalar data type (string, number, boolean, null).
     *
     * If value type is not a string, number, boolean, or null, the string
     * 'null' is returned.
     *
     * @param mixed $value
     * @return string
     */
    protected function encodeDatum($value)
    {
        if (is_int($value) || is_float($value)) {
            return str_replace(',', '.', (string) $value);
        }

        if (is_string($value)) {
            return $this->encodeString($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return 'null';
    }

    /**
     * JSON encode a string value by escaping characters as necessary.
     *
     * @param string $string
     * @return string
     */
    protected function encodeString($string)
    {
        // @codingStandardsIgnoreStart
        // Escape these characters with a backslash or unicode escape:
        // " \ / \n \r \t \b \f
        $search  = ['\\', "\n", "\t", "\r", "\b", "\f", '"', '\'', '&', '<', '>', '/'];
        $replace = ['\\\\', '\\n', '\\t', '\\r', '\\b', '\\f', '\\u0022', '\\u0027', '\\u0026', '\\u003C', '\\u003E', '\\/'];
        $string  = str_replace($search, $replace, $string);
        // @codingStandardsIgnoreEnd

        // Escape certain ASCII characters:
        // 0x08 => \b
        // 0x0c => \f
        $string = str_replace([chr(0x08), chr(0x0C)], ['\b', '\f'], $string);
        $string = self::encodeUnicodeString($string);

        return '"' . $string . '"';
    }

    /**
     * Encode the constants associated with the ReflectionClass parameter.
     *
     * The encoding format is based on the class2 format.
     *
     * @param ReflectionClass $class
     * @return string Encoded constant block in class2 format
     */
    private static function encodeConstants(ReflectionClass $class)
    {
        $result    = "constants:{";
        $constants = $class->getConstants();

        if (empty($constants)) {
            return $result . '}';
        }

        $tmpArray = [];
        foreach ($constants as $key => $value) {
            $tmpArray[] = sprintf('%s: %s', $key, self::encode($value));
        }

        $result .= implode(', ', $tmpArray);

        return $result . "}";
    }

    /**
     * Encode the public methods of the ReflectionClass in the class2 format
     *
     * @param ReflectionClass $class
     * @return string Encoded method fragment.
     */
    private static function encodeMethods(ReflectionClass $class)
    {
        $result  = 'methods:{';
        $started = false;

        foreach ($class->getMethods() as $method) {
            if (! $method->isPublic() || ! $method->isUserDefined()) {
                continue;
            }

            if ($started) {
                $result .= ',';
            }
            $started = true;

            $result .= sprintf('%s:function(', $method->getName());

            if ('__construct' === $method->getName()) {
                $result .= '){}';
                continue;
            }

            $argsStarted = false;
            $argNames    = "var argNames=[";

            foreach ($method->getParameters() as $param) {
                if ($argsStarted) {
                    $result .= ',';
                }

                $result .= $param->getName();

                if ($argsStarted) {
                    $argNames .= ',';
                }

                $argNames .= sprintf('"%s"', $param->getName());
                $argsStarted = true;
            }
            $argNames .= "];";

            $result .= "){"
                . $argNames
                . 'var result = ZAjaxEngine.invokeRemoteMethod('
                . "this, '"
                . $method->getName()
                . "',argNames,arguments);"
                . 'return(result);}';
        }

        return $result . "}";
    }

    /**
     * Encode the public properties of the ReflectionClass in the class2 format.
     *
     * @param ReflectionClass $class
     * @return string Encode properties list
     *
     */
    private static function encodeVariables(ReflectionClass $class)
    {
        $propValues = get_class_vars($class->getName());
        $result     = "variables:{";
        $tmpArray   = [];

        foreach ($class->getProperties() as $prop) {
            if (! $prop->isPublic()) {
                continue;
            }

            $name = $prop->getName();
            $tmpArray[] = sprintf('%s:%s', $name, self::encode($propValues[$name]));
        }

        $result .= implode(',', $tmpArray);

        return $result . "}";
    }

    /**
     * Encodes the given $className into the class2 model of encoding PHP classes into JavaScript class2 classes.
     *
     * NOTE: Currently only public methods and variables are proxied onto the
     * client machine
     *
     * @param $className string The name of the class, the class must be
     *     instantiable using a null constructor.
     * @param $package string Optional package name appended to JavaScript
     *     proxy class name.
     * @return string The class2 (JavaScript) encoding of the class.
     * @throws InvalidArgumentException
     */
    public static function encodeClass($className, $package = '')
    {
        $class = new ReflectionClass($className);
        if (! $class->isInstantiable()) {
            throw new InvalidArgumentException(sprintf(
                '"%s" must be instantiable',
                $className
            ));
        }

        return sprintf(
            'Class.create(\'%s%s\',{%s,%s,%s});',
            $package,
            $className,
            self::encodeConstants($class),
            self::encodeMethods($class),
            self::encodeVariables($class)
        );
    }

    /**
     * Encode several classes at once.
     *
     * Returns JSON encoded classes, using {@link encodeClass()}.
     *
     * @param string[] $classNames
     * @param string $package
     * @return string
     */
    public static function encodeClasses(array $classNames, $package = '')
    {
        $result = '';
        foreach ($classNames as $className) {
            $result .= static::encodeClass($className, $package);
        }

        return $result;
    }

    /**
     * Encode Unicode Characters to \u0000 ASCII syntax.
     *
     * This algorithm was originally developed for the Solar Framework by Paul
     * M. Jones.
     *
     * @link   http://solarphp.com/
     * @link   https://github.com/solarphp/core/blob/master/Solar/Json.php
     * @param  string $value
     * @return string
     */
    public static function encodeUnicodeString($value)
    {
        $strlenVar = strlen($value);
        $ascii     = "";

        // Iterate over every character in the string, escaping with a slash or
        // encoding to UTF-8 where necessary.
        for ($i = 0; $i < $strlenVar; $i++) {
            $ordVarC = ord($value[$i]);

            switch (true) {
                case (($ordVarC >= 0x20) && ($ordVarC <= 0x7F)):
                    // characters U-00000000 - U-0000007F (same as ASCII)
                    $ascii .= $value[$i];
                    break;

                case (($ordVarC & 0xE0) == 0xC0):
                    // characters U-00000080 - U-000007FF, mask 110XXXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack('C*', $ordVarC, ord($value[$i + 1]));
                    $i += 1;
                    $utf16 = self::utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ordVarC & 0xF0) == 0xE0):
                    // characters U-00000800 - U-0000FFFF, mask 1110XXXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack(
                        'C*',
                        $ordVarC,
                        ord($value[$i + 1]),
                        ord($value[$i + 2])
                    );
                    $i += 2;
                    $utf16 = self::utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ordVarC & 0xF8) == 0xF0):
                    // characters U-00010000 - U-001FFFFF, mask 11110XXX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack(
                        'C*',
                        $ordVarC,
                        ord($value[$i + 1]),
                        ord($value[$i + 2]),
                        ord($value[$i + 3])
                    );
                    $i += 3;
                    $utf16 = self::utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ordVarC & 0xFC) == 0xF8):
                    // characters U-00200000 - U-03FFFFFF, mask 111110XX
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack(
                        'C*',
                        $ordVarC,
                        ord($value[$i + 1]),
                        ord($value[$i + 2]),
                        ord($value[$i + 3]),
                        ord($value[$i + 4])
                    );
                    $i += 4;
                    $utf16 = self::utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;

                case (($ordVarC & 0xFE) == 0xFC):
                    // characters U-04000000 - U-7FFFFFFF, mask 1111110X
                    // see http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                    $char = pack(
                        'C*',
                        $ordVarC,
                        ord($value[$i + 1]),
                        ord($value[$i + 2]),
                        ord($value[$i + 3]),
                        ord($value[$i + 4]),
                        ord($value[$i + 5])
                    );
                    $i += 5;
                    $utf16 = self::utf82utf16($char);
                    $ascii .= sprintf('\u%04s', bin2hex($utf16));
                    break;
            }
        }

        return $ascii;
    }

    /**
     * Convert a string from one UTF-8 char to one UTF-16 char.
     *
     * Normally should be handled by mb_convert_encoding, but provides a slower
     * PHP-only method for installations that lack the multibyte string
     * extension.
     *
     * This method is from the Solar Framework by Paul M. Jones.
     *
     * @link http://solarphp.com
     * @param string $utf8 UTF-8 character
     * @return string UTF-16 character
     */
    protected static function utf82utf16($utf8)
    {
        // Check for mb extension otherwise do by hand.
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($utf8, 'UTF-16', 'UTF-8');
        }

        switch (strlen($utf8)) {
            case 1:
                // This case should never be reached, because we are in ASCII range;
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return $utf8;

            case 2:
                // Return a UTF-16 character from a 2-byte UTF-8 char;
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr(0x07 & (ord($utf8[0]) >> 2)) . chr((0xC0 & (ord($utf8[0]) << 6)) | (0x3F & ord($utf8[1])));

            case 3:
                // Return a UTF-16 character from a 3-byte UTF-8 char;
                // see: http://www.cl.cam.ac.uk/~mgk25/unicode.html#utf-8
                return chr((0xF0 & (ord($utf8[0]) << 4))
                    | (0x0F & (ord($utf8[1]) >> 2))) . chr((0xC0 & (ord($utf8[1]) << 6))
                    | (0x7F & ord($utf8[2])));
        }

        // ignoring UTF-32 for now, sorry
        return '';
    }
}
