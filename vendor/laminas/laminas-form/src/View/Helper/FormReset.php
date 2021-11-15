<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;

class FormReset extends FormInput
{
    /**
     * Attributes valid for the input tag type="reset"
     *
     * @var array
     */
    protected $validTagAttributes = [
        'name'           => true,
        'autofocus'      => true,
        'disabled'       => true,
        'form'           => true,
        'type'           => true,
        'value'          => true,
    ];

    /**
     * Translatable attributes
     *
     * @var array
     */
    protected $translatableAttributes = [
        'value' => true,
    ];

    /**
     * Determine input type to use
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    protected function getType(ElementInterface $element)
    {
        return 'reset';
    }
}
