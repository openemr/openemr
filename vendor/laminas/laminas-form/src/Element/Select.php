<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use Laminas\Form\Element;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception\InvalidArgumentException;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Explode as ExplodeValidator;
use Laminas\Validator\InArray as InArrayValidator;
use Laminas\Validator\ValidatorInterface;
use Traversable;

use function array_key_exists;
use function is_array;

class Select extends Element implements InputProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'select',
    ];

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var bool
     */
    protected $disableInArrayValidator = false;

    /**
     * Create an empty option (option with label but no value). If set to null, no option is created
     *
     * @var bool
     */
    protected $emptyOption = null;

    /**
     * @var array
     */
    protected $valueOptions = [];

    /**
     * @var bool
     */
    protected $useHiddenElement = false;

    /**
     * @var string
     */
    protected $unselectedValue = '';

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

        // Update InArrayValidator validator haystack
        if (null !== $this->validator) {
            if ($this->validator instanceof InArrayValidator) {
                $validator = $this->validator;
            }
            if ($this->validator instanceof ExplodeValidator
                && $this->validator->getValidator() instanceof InArrayValidator
            ) {
                $validator = $this->validator->getValidator();
            }
            if (! empty($validator)) {
                $validator->setHaystack($this->getValueOptionsValues());
            }
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
     * - empty_option: should an empty option be prepended to the options ?
     *
     * @param  array|Traversable $options
     * @return Select|ElementInterface
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

        if (isset($this->options['empty_option'])) {
            $this->setEmptyOption($this->options['empty_option']);
        }

        if (isset($this->options['disable_inarray_validator'])) {
            $this->setDisableInArrayValidator($this->options['disable_inarray_validator']);
        }

        if (isset($this->options['use_hidden_element'])) {
            $this->setUseHiddenElement($this->options['use_hidden_element']);
        }

        if (isset($this->options['unselected_value'])) {
            $this->setUnselectedValue($this->options['unselected_value']);
        }

        return $this;
    }

    /**
     * Set a single element attribute
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Select|ElementInterface
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
     * @param bool $disableOption
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
     * Set the string for an empty option (can be empty string). If set to null, no option will be added
     *
     * @param  string|null $emptyOption
     * @return $this
     */
    public function setEmptyOption($emptyOption)
    {
        $this->emptyOption = $emptyOption;
        return $this;
    }

    /**
     * Return the string for the empty option (null if none)
     *
     * @return string|null
     */
    public function getEmptyOption()
    {
        return $this->emptyOption;
    }

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator && ! $this->disableInArrayValidator()) {
            $validator = new InArrayValidator([
                'haystack' => $this->getValueOptionsValues(),
                'strict'   => false,
            ]);

            if ($this->isMultiple()) {
                $validator = new ExplodeValidator([
                    'validator'      => $validator,
                    'valueDelimiter' => null, // skip explode if only one value
                ]);
            }

            $this->validator = $validator;
        }
        return $this->validator;
    }

    /**
     * Do we render hidden element?
     *
     * @param  bool $useHiddenElement
     * @return $this
     */
    public function setUseHiddenElement($useHiddenElement)
    {
        $this->useHiddenElement = (bool) $useHiddenElement;
        return $this;
    }

    /**
     * Do we render hidden element?
     *
     * @return bool
     */
    public function useHiddenElement()
    {
        return $this->useHiddenElement;
    }

    /**
     * Set the value if the select is not selected
     *
     * @param string $unselectedValue
     * @return $this
     */
    public function setUnselectedValue($unselectedValue)
    {
        $this->unselectedValue = (string) $unselectedValue;
        return $this;
    }

    /**
     * Get the value when the select is not selected
     *
     * @return string
     */
    public function getUnselectedValue()
    {
        return $this->unselectedValue;
    }

    /**
     * Provide default input rules for this element
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $spec = [
            'name' => $this->getName(),
            'required' => true,
        ];

        if ($this->useHiddenElement() && $this->isMultiple()) {
            $unselectedValue = $this->getUnselectedValue();

            $spec['allow_empty'] = true;
            $spec['continue_if_empty'] = true;
            $spec['filters'] = [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => static function ($value) use ($unselectedValue) {
                            if ($value === $unselectedValue) {
                                $value = [];
                            }
                            return $value;
                        },
                    ],
                ],
            ];
        }

        if ($validator = $this->getValidator()) {
            $spec['validators'] = [
                $validator,
            ];
        }

        return $spec;
    }

    /**
     * Get only the values from the options attribute
     *
     * @return array
     */
    protected function getValueOptionsValues()
    {
        $values  = [];
        $options = $this->getValueOptions();
        foreach ($options as $key => $optionSpec) {
            if (is_array($optionSpec) && array_key_exists('options', $optionSpec)) {
                foreach ($optionSpec['options'] as $nestedKey => $nestedOptionSpec) {
                    $values[] = $this->getOptionValue($nestedKey, $nestedOptionSpec);
                }
                continue;
            }

            $values[] = $this->getOptionValue($key, $optionSpec);
        }
        return $values;
    }

    protected function getOptionValue($key, $optionSpec)
    {
        return is_array($optionSpec) ? $optionSpec['value'] : $key;
    }

    /**
     * Element has the multiple attribute
     *
     * @return bool
     */
    public function isMultiple()
    {
        return isset($this->attributes['multiple'])
            && ($this->attributes['multiple'] === true || $this->attributes['multiple'] === 'multiple');
    }
}
