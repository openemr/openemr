<?php

namespace PubNub;

class PubNubCborDecode
{
    const TYPE_MASK         = 0b11100000;
    const ADDITIONAL_MASK   = 0b00011111;

    const TYPE_UNSIGNED_INT = 0b00000000;
    const TYPE_NEGATIVE_INT = 0b00100000;
    const TYPE_BYTE_STRING  = 0b01000000;
    const TYPE_TEXT_STRING  = 0b01100000;
    const TYPE_ARRAY        = 0b10000000;
    const TYPE_HASHMAP      = 0b10100000;
    const TYPE_TAG          = 0b11000000;
    const TYPE_FLOAT        = 0b11100000;

    const ADDITIONAL_LENGTH_1B = 24;
    const ADDITIONAL_LENGTH_2B = 25;
    const ADDITIONAL_LENGTH_4B = 26;
    const ADDITIONAL_LENGTH_8B = 27;

    const ADDITIONAL_TYPE_INDEFINITE = 31;

    const INDEFINITE_BREAK = 0b11111111;

    private static $additionalLength = [
        self::ADDITIONAL_LENGTH_1B,
        self::ADDITIONAL_LENGTH_2B,
        self::ADDITIONAL_LENGTH_4B,
        self::ADDITIONAL_LENGTH_8B,
    ];

    private static $additionalLengthBytes = [
        self::ADDITIONAL_LENGTH_1B => 1,
        self::ADDITIONAL_LENGTH_2B => 2,
        self::ADDITIONAL_LENGTH_4B => 4,
        self::ADDITIONAL_LENGTH_8B => 8,
    ];

    const SIMPLE_VALUE_FALSE    = 'F4';
    const SIMPLE_VALUE_TRUE     = 'F5';
    const SIMPLE_VALUE_NULL     = 'F6';
    const SIMPLE_VALUE_UNDEF    = 'F7';

    private static $simpleValues = [
        self::SIMPLE_VALUE_FALSE => false,
        self::SIMPLE_VALUE_TRUE => true,
        self::SIMPLE_VALUE_NULL => null,
        self::SIMPLE_VALUE_UNDEF => null
    ];

    /**
     * Decode incoming hexadecimal string of data and outputing decoded values
     *
     * @param string $value
     *
     * @return mixed
     * @throws \Exception
     */
    public static function decode($value)
    {
        $value = self::sanitizeInput($value);
        $data = str_split($value, 2);
        return self::parseData($data);
    }

    private static function parseData(&$data)
    {
        $byte = array_shift($data);

        if (array_key_exists($byte, self::$simpleValues)) {
            return self::$simpleValues[$byte];
        }

        $bits = bindec(str_pad(base_convert($byte, 16, 2), 8, '0', STR_PAD_LEFT));


        $type = $bits & self::TYPE_MASK;
        $additional = $bits & self::ADDITIONAL_MASK;


        switch ($type) {
            case self::TYPE_NEGATIVE_INT:
            case self::TYPE_UNSIGNED_INT:
                if (in_array($additional, self::$additionalLength)) {
                    $value = hexdec(
                        self::getData($data, self::$additionalLengthBytes[$additional])
                    );
                } else {
                    $value = $additional;
                }
                if ($type === self::TYPE_NEGATIVE_INT) {
                    $value = -1 - $value;
                }
                return $value;
            case self::TYPE_FLOAT:
                if ($additional <= 23) {
                    return $additional;
                } elseif ($additional === self::ADDITIONAL_LENGTH_1B) {
                    return self::getData($data);
                } else {
                    return self::decodeFloat(
                        self::getData($data, self::$additionalLengthBytes[$additional]),
                        $additional
                    );
                }

            case self::TYPE_BYTE_STRING:
            case self::TYPE_TEXT_STRING:
                if (in_array($additional, self::$additionalLength)) {
                    $length = hexdec(self::getData($data, self::$additionalLengthBytes[$additional]));
                    $result =  hex2bin(self::getData($data, $length));
                } elseif ($additional == self::ADDITIONAL_TYPE_INDEFINITE) {
                    $result =  hex2bin(self::getIndefiniteData($data));
                } else {
                    $result = hex2bin(self::getData($data, $additional));
                }
                return $result;

            case self::TYPE_ARRAY:
                $result = [];
                if (in_array($additional, self::$additionalLength)) {
                    $length = hexdec(self::getData($data, self::$additionalLengthBytes[$additional]));
                } else {
                    $length = $additional;
                }

                for ($i = 0; $i < $length; $i++) {
                    $result[] = self::parseData($data);
                }
                return $result;

            case self::TYPE_HASHMAP:
                $result = [];
                if (in_array($additional, self::$additionalLength)) {
                    $length = hexdec(self::getData($data, self::$additionalLengthBytes[$additional]));
                } else {
                    $length = $additional;
                }

                for ($i = 0; $i < $length; $i++) {
                    $key = self::parseData($data);
                    $val = self::parseData($data);
                    $result[$key] = $val;
                }
                return $result;
            default:
                throw new \Exception(sprintf('Unsupported Type %b', $type));
        }
    }

    private static function decodeFloat($value, $precision)
    {
        $bytes = hexdec($value);
        switch ($precision) {
            case self::ADDITIONAL_LENGTH_2B:
                $sign = ($bytes & 0b1000000000000000) >> 15;
                $exp = ($bytes & 0b0111110000000000) >> 10;
                $mant = $bytes & 0b1111111111;
                if ($exp === 0) {
                    $result = (2 ** -14) * ($mant / 1024);
                } elseif ($exp === 0b11111) {
                    $result = INF;
                } else {
                    $result = (2 ** ($exp - 15)) * (1 + $mant / 1024);
                }
                return ($sign ? -1 : 1) * $result;

            case self::ADDITIONAL_LENGTH_4B:
                $sign = ($bytes >> 31) ? -1 : 1;
                $x = ($bytes & ((1 << 23) - 1)) + (1 << 23) * ($bytes >> 31 | 1);
                $exp = ($bytes >> 23 & 0xFF) - 127;
                return $x * pow(2, $exp - 23) * $sign;

            case self::ADDITIONAL_LENGTH_8B:
                $sign = ($bytes >> 63) ? -1 : 1;
                $exp = ($bytes >> 52) & 0x7ff;

                $mant = $bytes & 0xfffffffffffff;

                if (0 === $exp) {
                    $val = $mant * 2 ** (-(1022 + 52));
                } elseif (0b11111111111 !== $exp) {
                    $val = ($mant + (1 << 52)) * 2 ** ($exp - (1023 + 52));
                } else {
                    $val = 0 === $mant ? INF : NAN;
                }
                return $sign * $val;
        }
    }

    private static function getData(&$data, $bytes = 1)
    {
        $result = null;
        for ($i = 1; $i <= $bytes; $i++) {
            $result .= array_shift($data);
        }
        return (string)$result;
    }

    private static function getIndefiniteData(&$data)
    {
        $result = null;
        do {
            $byte = array_shift($data);
            if (hexdec($byte) == self::INDEFINITE_BREAK) {
                break;
            }
            $result .= $byte;
        } while (!empty($data));
        return (string)$result;
    }

    /**
     * Removes spaces, converts string to upper case and throws exception if input is not a valid heaxadecimal string
     *
     * @param string $value
     *
     * @return string
     */
    private static function sanitizeInput($value)
    {
        $value = strtoupper(str_replace(' ', '', $value));
        if (preg_match('/[^A-F0-9]/', $value)) {
            throw new \Exception('Invalid Input');
        }
        return $value;
    }
}
