<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

/**
 * Stub class for backwards compatibility.
 *
 * Since PHP 7 adds "null" as a reserved keyword, we can no longer have a class
 * named that and retain PHP 7 compatibility. The original class has been
 * renamed to "ToNull", and this class is now an extension of it. It raises an
 * E_USER_DEPRECATED to warn users to migrate.
 *
 * @deprecated
 */
class Null extends ToNull
{
    /**
     * {@inheritdoc}
     */
    public function __construct($typeOrOptions = null)
    {
        trigger_error(
            sprintf(
                'The class %s has been deprecated; please use %s\\ToNull',
                __CLASS__,
                __NAMESPACE__
            ),
            E_USER_DEPRECATED
        );

        parent::__construct($typeOrOptions);
    }
}
