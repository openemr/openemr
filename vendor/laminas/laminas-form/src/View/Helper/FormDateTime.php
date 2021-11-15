<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper;

use Laminas\Form\ElementInterface;

class FormDateTime extends FormInput
{
    /**
     * Attributes valid for the input tag type="datetime"
     *
     * @var array
     */
    protected $validTagAttributes = [
        'name'           => true,
        'autocomplete'   => true,
        'autofocus'      => true,
        'disabled'       => true,
        'form'           => true,
        'list'           => true,
        'max'            => true,
        'min'            => true,
        'readonly'       => true,
        'required'       => true,
        'step'           => true,
        'type'           => true,
        'value'          => true,
    ];

    /**
     * Determine input type to use
     *
     * @param  ElementInterface $element
     * @return string
     */
    protected function getType(ElementInterface $element)
    {
        return 'datetime';
    }
}
