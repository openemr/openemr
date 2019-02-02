<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Header;

final class HeaderName
{
    /**
     * No public constructor.
     */
    private function __construct()
    {
    }

    /**
     * Filter the header name according to RFC 2822
     *
     * @see    http://www.rfc-base.org/txt/rfc-2822.txt (section 2.2)
     * @param  string $name
     * @return string
     */
    public static function filter($name)
    {
        $result = '';
        $tot    = strlen($name);
        for ($i = 0; $i < $tot; $i += 1) {
            $ord = ord($name[$i]);
            if ($ord > 32 && $ord < 127 && $ord !== 58) {
                $result .= $name[$i];
            }
        }
        return $result;
    }

    /**
     * Determine if the header name contains any invalid characters.
     *
     * @param string $name
     * @return bool
     */
    public static function isValid($name)
    {
        $tot = strlen($name);
        for ($i = 0; $i < $tot; $i += 1) {
            $ord = ord($name[$i]);
            if ($ord < 33 || $ord > 126 || $ord === 58) {
                return false;
            }
        }
        return true;
    }

    /**
     * Assert that the header name is valid.
     *
     * Raises an exception if invalid.
     *
     * @param string $name
     * @throws Exception\RuntimeException
     * @return void
     */
    public static function assertValid($name)
    {
        if (! self::isValid($name)) {
            throw new Exception\RuntimeException('Invalid header name detected');
        }
    }
}
