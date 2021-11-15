<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form;

use Laminas\InputFilter\InputFilterProviderInterface;
use Traversable;

class InputFilterProviderFieldset extends Fieldset implements InputFilterProviderInterface
{
    /**
     * Holds the specification which will be returned by getInputFilterSpecification
     *
     * @var array|Traversable
     */
    protected $filterSpec = [];

    /**
     * @return array|Traversable
     */
    public function getInputFilterSpecification()
    {
        return $this->filterSpec;
    }

    /**
     * @param array|Traversable $filterSpec
     */
    public function setInputFilterSpecification($filterSpec)
    {
        $this->filterSpec = $filterSpec;
    }

    /**
     * Set options for a fieldset. Accepted options are:
     * - input_filter_spec: specification to be returned by getInputFilterSpecification
     *
     * @param  array|Traversable $options
     * @return $this
     * @throws Exception\InvalidArgumentException
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['input_filter_spec'])) {
            $this->setInputFilterSpecification($options['input_filter_spec']);
        }

        return $this;
    }
}
