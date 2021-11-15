<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Annotation;

use Laminas\Filter\Boolean as BooleanFilter;

use function is_bool;

/**
 * Required annotation
 *
 * Use this annotation to specify the value of the "required" flag for a given
 * input. Since the flag defaults to "true", this will typically be used to
 * "unset" the flag (e.g., "@Annotation\Required(false)"). Any boolean value
 * understood by \Laminas\Filter\Boolean is allowed as the content.
 *
 * @Annotation
 */
class Required
{
    /**
     * @var bool
     */
    protected $required = true;

    /**
     * Receive and process the contents of an annotation
     *
     * @param  array $data
     */
    public function __construct(array $data)
    {
        if (! isset($data['value'])) {
            $data['value'] = false;
        }

        $required = $data['value'];

        if (! is_bool($required)) {
            $filter   = new BooleanFilter();
            $required = $filter->filter($required);
        }

        $this->required = $required;
    }

    /**
     * Get value of required flag
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }
}
