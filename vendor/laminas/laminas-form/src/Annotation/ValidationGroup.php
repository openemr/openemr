<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

/**
 * ValidationGroup annotation
 *
 * Allows passing validation group to the form
 *
 * The value should be an associative array.
 *
 * @Annotation
 * @copyright  Copyright (c) 2005-2015 Laminas (https://www.zend.com)
 * @license    https://getlaminas.org/license/new-bsd     New BSD License
 */
class ValidationGroup extends AbstractArrayAnnotation
{
    /**
     * Retrieve the options
     *
     * @return null|array
     */
    public function getValidationGroup()
    {
        return $this->value;
    }
}
