<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

/**
 * InputFilter annotation
 *
 * Use this annotation to specify a specific input filter class to use with the
 * form. The value should be a string indicating the fully qualified class name
 * of the input filter to use.
 *
 * @Annotation
 */
class InputFilter extends AbstractArrayOrStringAnnotation
{
    /**
     * Retrieve the input filter class
     *
     * @return null|string
     */
    public function getInputFilter()
    {
        return $this->value;
    }
}
