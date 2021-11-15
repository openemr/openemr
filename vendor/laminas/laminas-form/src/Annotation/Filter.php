<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

/**
 * Filter annotation
 *
 * Expects an associative array defining the filter.  Typically, this includes
 * the "name" with an associated string value indicating the filter name or
 * class, and optionally an "options" key with an object/associative array value
 * of options to pass to the filter constructor.
 *
 * This annotation may be specified multiple times; filters will be added
 * to the filter chain in the order specified.
 *
 * @Annotation
 */
class Filter extends AbstractArrayAnnotation
{
    /**
     * Retrieve the filter specification
     *
     * @return null|array
     */
    public function getFilter()
    {
        return $this->value;
    }
}
