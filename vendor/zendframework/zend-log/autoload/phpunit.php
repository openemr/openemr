<?php
/**
 * @see       https://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2017 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-log/blob/master/LICENSE.md New BSD License
 */

/**
 * This is required due to the fact that we extend the LoggerInterfaceTest from psr/log, which
 * is still using non-namespaced versions of PHPUnit.
 */
if (! class_exists(\PHPUnit_Framework_TestCase::class)) {
    class_alias(\PHPUnit\Framework\TestCase::class, \PHPUnit_Framework_TestCase::class, true);
}
