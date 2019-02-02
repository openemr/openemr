<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Transport;

/**
 * Stub class for backwards compatibility.
 *
 * Since PHP 7 adds "null" as a reserved keyword, we can no longer have a class
 * named that and retain PHP 7 compatibility. The original class has been
 * renamed to "InMemory", and this class is now an extension of it. It raises an
 * E_USER_DEPRECATED to warn users to migrate.
 *
 * @deprecated
 */
class Null extends InMemory
{
    public function __construct()
    {
        trigger_error(
            sprintf(
                'The class %s has been deprecated; please use %s\\InMemory',
                __CLASS__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );
    }
}
