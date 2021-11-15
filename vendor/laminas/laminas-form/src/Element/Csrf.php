<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element;
use Laminas\Form\ElementPrepareAwareInterface;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Csrf as CsrfValidator;
use Traversable;

use function array_merge;

class Csrf extends Element implements InputProviderInterface, ElementPrepareAwareInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'hidden',
    ];

    /**
     * @var array
     */
    protected $csrfValidatorOptions = [];

    /**
     * @var CsrfValidator
     */
    protected $csrfValidator;

    /**
     * Accepted options for Csrf:
     * - csrf_options: an array used in the Csrf
     *
     * @param array|Traversable $options
     * @return $this
     */
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($this->options['csrf_options'])) {
            $this->setCsrfValidatorOptions($this->options['csrf_options']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCsrfValidatorOptions()
    {
        return $this->csrfValidatorOptions;
    }

    /**
     * @param  array $options
     * @return $this
     */
    public function setCsrfValidatorOptions(array $options)
    {
        $this->csrfValidatorOptions = $options;
        return $this;
    }

    /**
     * Get CSRF validator
     *
     * @return CsrfValidator
     */
    public function getCsrfValidator()
    {
        if (null === $this->csrfValidator) {
            $csrfOptions = $this->getCsrfValidatorOptions();
            $csrfOptions = array_merge($csrfOptions, ['name' => $this->getName()]);
            $this->setCsrfValidator(new CsrfValidator($csrfOptions));
        }
        return $this->csrfValidator;
    }

    /**
     * @param  CsrfValidator $validator
     * @return $this
     */
    public function setCsrfValidator(CsrfValidator $validator)
    {
        $this->csrfValidator = $validator;
        return $this;
    }

    /**
     * Retrieve value
     *
     * Retrieves the hash from the validator
     *
     * @return string
     */
    public function getValue()
    {
        $validator = $this->getCsrfValidator();
        return $validator->getHash();
    }

    /**
     * Override: get attributes
     *
     * Seeds 'value' attribute with validator hash
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        $validator  = $this->getCsrfValidator();
        $attributes['value'] = $validator->getHash();
        return $attributes;
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
        return [
            'name' => $this->getName(),
            'required' => true,
            'filters' => [
                ['name' => StringTrim::class],
            ],
            'validators' => [
                $this->getCsrfValidator(),
            ],
        ];
    }

    /**
     * Prepare the form element
     */
    public function prepareElement(FormInterface $form)
    {
        $this->getCsrfValidator()->getHash(true);
    }
}
