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
 * AllowEmpty annotation
 *
 * Presence of this annotation is a hint that the associated
 * \Laminas\InputFilter\Input should enable the allowEmpty flag.
 *
 * @Annotation
 * @deprecated 2.4.8 Use `@Validator({"name":"NotEmpty"})` instead.
 */
class AllowEmpty
{
    /**
     * @var bool
     */
    protected $allowEmpty = true;

    /**
     * Receive and process the contents of an annotation
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (! isset($data['value'])) {
            $data['value'] = false;
        }

        $allowEmpty = $data['value'];

        if (! is_bool($allowEmpty)) {
            $filter   = new BooleanFilter();
            $allowEmpty = $filter->filter($allowEmpty);
        }

        $this->allowEmpty = $allowEmpty;
    }

    /**
     * Get value of required flag
     *
     * @return bool
     */
    public function getAllowEmpty()
    {
        return $this->allowEmpty;
    }
}
