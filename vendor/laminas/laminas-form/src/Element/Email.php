<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use Laminas\Filter\StringTrim;
use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Explode as ExplodeValidator;
use Laminas\Validator\Regex as RegexValidator;
use Laminas\Validator\ValidatorInterface;

class Email extends Element implements InputProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'email',
    ];

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var ValidatorInterface
     */
    protected $emailValidator;

    /**
     * Get primary validator
     *
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        if (null === $this->validator) {
            $emailValidator = $this->getEmailValidator();

            $multiple = isset($this->attributes['multiple']) ? $this->attributes['multiple'] : null;

            if (true === $multiple || 'multiple' === $multiple) {
                $this->validator = new ExplodeValidator([
                    'validator' => $emailValidator,
                ]);
            } else {
                $this->validator = $emailValidator;
            }
        }

        return $this->validator;
    }

    /**
     * Sets the primary validator to use for this element
     *
     * @param  ValidatorInterface $validator
     * @return $this
     */
    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * Get the email validator to use for multiple or single
     * email addresses.
     *
     * Note from the HTML5 Specs regarding the regex:
     *
     * "This requirement is a *willful* violation of RFC 5322, which
     * defines a syntax for e-mail addresses that is simultaneously
     * too strict (before the "@" character), too vague
     * (after the "@" character), and too lax (allowing comments,
     * whitespace characters, and quoted strings in manners
     * unfamiliar to most users) to be of practical use here."
     *
     * The default Regex validator is in use to match that of the
     * browser validation, but you are free to set a different
     * (more strict) email validator such as Laminas\Validator\Email
     * if you wish.
     *
     * @return ValidatorInterface
     */
    public function getEmailValidator()
    {
        if (null === $this->emailValidator) {
            $this->emailValidator = new RegexValidator(
                '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/'
            );
        }
        return $this->emailValidator;
    }

    /**
     * Sets the email validator to use for multiple or single
     * email addresses.
     *
     * @param  ValidatorInterface $validator
     * @return $this
     */
    public function setEmailValidator(ValidatorInterface $validator)
    {
        $this->emailValidator = $validator;
        return $this;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches an email validator.
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
                $this->getValidator(),
            ],
        ];
    }
}
