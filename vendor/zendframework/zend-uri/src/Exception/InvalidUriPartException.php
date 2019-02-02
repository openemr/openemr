<?php
/**
 * @see       https://github.com/zendframework/zend-uri for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-uri/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Uri\Exception;

class InvalidUriPartException extends InvalidArgumentException
{
    /**
     * Part-specific error codes
     *
     * @var int
     */
    const INVALID_SCHEME    = 1;
    const INVALID_USER      = 2;
    const INVALID_PASSWORD  = 4;
    const INVALID_USERINFO  = 6;
    const INVALID_HOSTNAME  = 8;
    const INVALID_PORT      = 16;
    const INVALID_AUTHORITY = 30;
    const INVALID_PATH      = 32;
    const INVALID_QUERY     = 64;
    const INVALID_FRAGMENT  = 128;
}
