<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use Laminas\Validator\InArray as InArrayValidator;
use Laminas\Validator\ValidatorInterface;

class Radio extends MultiCheckbox
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'radio',
    ];

    /**
     * Get validator
     *
     * @return ValidatorInterface
     */
    protected function getValidator()
    {
        if (null === $this->validator && ! $this->disableInArrayValidator()) {
            $this->validator = new InArrayValidator([
                'haystack'  => $this->getValueOptionsValues(),
                'strict'    => false,
            ]);
        }
        return $this->validator;
    }
}
