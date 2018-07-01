<?php
/**
 * @see       https://github.com/zendframework/zend-loader for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-loader/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Loader\Exception;

require_once __DIR__ . '/ExceptionInterface.php';

class BadMethodCallException extends \BadMethodCallException implements
    ExceptionInterface
{
}
