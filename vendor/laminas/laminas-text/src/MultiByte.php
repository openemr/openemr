<?php

/**
 * @see       https://github.com/laminas/laminas-text for the canonical source repository
 * @copyright https://github.com/laminas/laminas-text/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-text/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Text;

use Laminas\Stdlib\StringUtils;

/**
 * Contains multibyte safe string methods
 */
class MultiByte
{
    /**
     * Word wrap
     *
     * @param  string  $string
     * @param  int $width
     * @param  string  $break
     * @param  bool $cut
     * @param  string  $charset
     * @throws Exception\InvalidArgumentException
     * @return string
     * @deprecated Please use Laminas\Stdlib\StringUtils instead
     */
    public static function wordWrap($string, $width = 75, $break = "\n", $cut = false, $charset = 'utf-8')
    {
        trigger_error(sprintf(
            "This method is deprecated, please use '%s' instead",
            'Laminas\Stdlib\StringUtils::getWrapper(<charset>)->wordWrap'
        ), E_USER_DEPRECATED);

        try {
            return StringUtils::getWrapper($charset)->wordWrap($string, $width, $break, $cut);
        } catch (\Laminas\Stdlib\Exception\InvalidArgumentException $e) {
            throw new Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * String padding
     *
     * @param  string  $input
     * @param  int $padLength
     * @param  string  $padString
     * @param  int $padType
     * @param  string  $charset
     * @return string
     * @deprecated Please use Laminas\Stdlib\StringUtils instead
     */
    public static function strPad($input, $padLength, $padString = ' ', $padType = STR_PAD_RIGHT, $charset = 'utf-8')
    {
        trigger_error(sprintf(
            "This method is deprecated, please use '%s' instead",
            'Laminas\Stdlib\StringUtils::getWrapper(<charset>)->strPad'
        ), E_USER_DEPRECATED);

        return StringUtils::getWrapper($charset)->strPad($input, $padLength, $padString, $padType);
    }
}
