<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use Laminas\Form\ElementInterface;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\Validator\Explode as ExplodeValidator;
use Laminas\Validator\InArray as InArrayValidator;
use Laminas\Validator\ValidatorInterface;
use Traversable;

use function is_array;

class MultiCheckbox extends Checkbox
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'multi_checkbox',
    ];

    /**
     * @var bool
     */
    protected $disableInArrayValidator = false;

    /**
     * @var bool
     */
    protected $useHiddenElement = false;

    /**
     * @var string
     */
    protected $uncheckedValue = '';

    /**
     * @var array
     */
    protected $valueOptions = [];

    /**
     * @return array
     */
    public function getValueOptions()
    {
        return $this->valueOptions;
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function setValueOptions(array $options)
    {
        $this->valueOptions = $options;

        // Update Explode validator haystack
        if ($this->validator instanceof ExplodeValidator) {
            $validator = $this->validator->getValidator();
            $validator->setHaystack($this->getValueOptionsValues());
        }

        return $this;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function unsetValueOption($key)
    {
        if (isset($this->valueOptions[$key])) {
            unset($this->valueOptions[$key]);
        }

        return $this;
    }

    /**
     * Set options for an element. Accepted options are:
     * - label: label to associate with the element
     * - label_attributes: attributes to use when the label is rendered
     * - value_options: list of values and labels for the select options
     *
     * @param  array|Traversable $options
     * @return MultiCheckbox|ElementInterface
     * @throws InvalidArgumentException
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['value_options'])) {
            $this->setValueOptions($this->options['value_options']);
        }
        // Alias for 'value_options'
        if (isset($this->options['options'])) {
            $this->setValueOptions($this->options['options']);
        }
        if (isset($this->options['disable_inarray_validator'])) {
            $this->setDisableInArrayValidator($this->options['disable_inarray_validator']);
        }

        return $this;
    }

    /**
     * Set a single element attribute
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        // Do not include the options in the list of attributes
        // TODO: Deprecate this
        if ($key === 'options') {
            $this->setValueOptions($value);
            return $this;
        }
        return parent::setAttribute($key, $value);
    }

    /**
     * Set the flag to allow for disabling the automatic addition of an InArray validator.
     *
     * @param  bool $disableOption
     * @return $this
     */
    public function setDisableInArrayValidator($disableOption)
    {
        $this->disableInArrayValidator = (bool) $disableOption;
        return $this;
    }

    /**
     * Get the disable in array validator flag.
     *
     * @return bool
     */
    public function disableInArrayValidator()
    {
        return $this->disableInArrayValidator;
    }

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator && ! $this->disableInArrayValidator()) {
            $inArrayValidator = new InArrayValidator([
                'haystack'  => $this->getValueOptionsValues(),
                'strict'    => false,
            ]);
            $this->validator = new ExplodeValidator([
                'validator'      => $inArrayValidator,
                'valueDelimiter' => null, // skip explode if only one value
            ]);
        }
        return $this->validator;
    }

    /**
     * Get only the values from the options attribute
     *
     * @return array
     */
    protected function getValueOptionsValues()
    {
        $values = [];
        $options = $this->getValueOptions();
        foreach ($options as $key => $optionSpec) {
            $value = is_array($optionSpec) ? $optionSpec['value'] : $key;
            $values[] = $value;
        }
        if ($this->useHiddenElement()) {
            $values[] = $this->getUncheckedValue();
        }
        return $values;
    }

    /**
     * Sets the value that should be selected.
     *
     * @param  mixed $value The value to set.
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
