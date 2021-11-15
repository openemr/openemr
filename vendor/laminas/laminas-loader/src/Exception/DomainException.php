<?php

/**
 * @see       https://github.com/laminas/laminas-loader for the canonical source repository
 * @copyright https://github.com/laminas/laminas-loader/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-loader/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Loader\Exception;

require_once __DIR__ . '/ExceptionInterface.php';

class DomainException extends \DomainException implements ExceptionInterface
{
}
