<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

/**
 * ErrorMessage annotation
 *
 * Allows providing an error message to seed the Input specification for a
 * given element. The content should be a string.
 *
 * @Annotation
 */
class ErrorMessage extends AbstractStringAnnotation
{
    /**
     * Retrieve the message
     *
     * @return null|string
     */
    public function getMessage()
    {
        return $this->value;
    }
}
