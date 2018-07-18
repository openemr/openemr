<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Storage\Exception;

use Zend\Mail\Exception;

/**
 * Exception for Zend\Mail component.
 */
class OutOfBoundsException extends Exception\OutOfBoundsException implements ExceptionInterface
{
}
