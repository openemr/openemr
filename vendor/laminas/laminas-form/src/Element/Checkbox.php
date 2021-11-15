<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\InArray as InArrayValidator;
use Laminas\Validator\ValidatorInterface;
use Traversable;

class Checkbox extends Element implements InputProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'checkbox',
    ];

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var bool
     */
    protected $useHiddenElement = true;

    /**
     * @var string
     */
    protected $uncheckedValue = '0';

    /**
     * @var string
     */
    protected $checkedValue = '1';

    /**
     * Accepted options for MultiCheckbox:
     * - use_hidden_element: do we render hidden element?
     * - unchecked_value: value for checkbox when unchecked
     * - checked_value: value for checkbox when checked
     *
     * @param  array|Traversable $options
     * @return $this
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['use_hidden_element'])) {
            $this->setUseHiddenElement($this->options['use_hidden_element']);
        }

        if (isset($this->options['unchecked_value'])) {
            $this->setUncheckedValue($this->options['unchecked_value']);
        }

        if (isset($this->options['checked_value'])) {
            $this->setCheckedValue($this->options['checked_value']);
        }

        return $this;
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
     * Set the value to use when checkbox is unchecked
     *
     * @param $uncheckedValue
     * @return $this
     */
    public function setUncheckedValue($uncheckedValue)
    {
        $this->uncheckedValue = $uncheckedValue;
        return $this;
    }

    /**
     * Get the value to use when checkbox is unchecked
     *
     * @return string
     */
    public function getUncheckedValue()
    {
        return $this->uncheckedValue;
    }

    /**
     * Set the value to use when checkbox is checked
     *
     * @param $checkedValue
     * @return $this
     */
    public function setCheckedValue($checkedValue)
    {
        $this->checkedValue = $checkedValue;
        return $this;
    }

    /**
     * Get the value to use when checkbox is checked
     *
     * @return string
     */
    public function getCheckedValue()
    {
        return $this->checkedValue;
    }

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new InArrayValidator([
                'haystack' => [$this->checkedValue, $this->uncheckedValue],
                'strict'   => false,
            ]);
        }
        return $this->validator;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches the captcha as a validator.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        $spec = [
            'name' => $this->getName(),
            'required' => true,
        ];

        if ($validator = $this->getValidator()) {
            $spec['validators'] = [
                $validator,
            ];
        }

        return $spec;
    }

    /**
     * Checks if this checkbox is checked.
     *
     * @return bool
     */
    public function isChecked()
    {
        return $this->value === $this->getCheckedValue();
    }

    /**
     * Checks or unchecks the checkbox.
     *
     * @param bool $value The flag to set.
     * @return $this
     */
    public function setChecked($value)
    {
        $this->value = $value ? $this->getCheckedValue() : $this->getUncheckedValue();
        return $this;
    }

    /**
     * Checks or unchecks the checkbox.
     *
     * @param  mixed $value A boolean flag or string that is checked against the "checked value".
     * @return $this
     */
    public function setValue($value)
    {
        // Cast to strings because POST data comes in string form
        $checked = (string) $value === (string) $this->getCheckedValue();
        $this->value = $checked ? $this->getCheckedValue() : $this->getUncheckedValue();
        return $this;
    }
}
