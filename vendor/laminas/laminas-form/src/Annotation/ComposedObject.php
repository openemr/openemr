<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

use function is_array;

/**
 * ComposedObject annotation
 *
 * Use this annotation to specify another object with annotations to parse
 * which you can then add to the form as a fieldset. The value should be a
 * string indicating the fully qualified class name of the composed object
 * to use.
 *
 * @Annotation
 */
class ComposedObject extends AbstractArrayOrStringAnnotation
{
    /**
     * Retrieve the composed object classname
     *
     * @return null|string
     */
    public function getComposedObject()
    {
        if (is_array($this->value)) {
            return $this->value['target_object'];
        }
        return $this->value;
    }

    /**
     * Is this composed object a collection or not
     *
     * @return bool
     */
    public function isCollection()
    {
        return is_array($this->value) && isset($this->value['is_collection']) && $this->value['is_collection'];
    }

    /**
     * Retrieve the options for the composed object
     *
     * @return array
     */
    public function getOptions()
    {
        return is_array($this->value) && isset($this->value['options']) ? $this->value['options'] : [];
    }
}
