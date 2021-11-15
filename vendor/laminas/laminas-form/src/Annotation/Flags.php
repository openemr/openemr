<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

/**
 * Flags annotation
 *
 * Allows passing flags to the form factory. These flags are used to indicate
 * metadata, and typically the priority (order) in which an element will be
 * included.
 *
 * The value should be an associative array.
 *
 * @Annotation
 */
class Flags extends AbstractArrayAnnotation
{
    /**
     * Retrieve the flags
     *
     * @return null|array
     */
    public function getFlags()
    {
        return $this->value;
    }
}
