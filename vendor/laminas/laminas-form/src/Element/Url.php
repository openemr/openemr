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
use Laminas\Validator\Uri as UriValidator;
use Laminas\Validator\ValidatorInterface;

class Url extends Element implements InputProviderInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'url',
    ];

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        if (null === $this->validator) {
            $this->validator = new UriValidator([
                'allowAbsolute' => true,
                'allowRelative' => false,
            ]);
        }
        return $this->validator;
    }

    /**
     * Provide default input rules for this element
     *
     * Attaches an uri validator.
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
