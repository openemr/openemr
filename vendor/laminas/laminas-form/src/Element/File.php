<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\Element;

use Laminas\Form\Element;
use Laminas\Form\ElementPrepareAwareInterface;
use Laminas\Form\FormInterface;
use Laminas\InputFilter\FileInput;
use Laminas\InputFilter\InputProviderInterface;

class File extends Element implements InputProviderInterface, ElementPrepareAwareInterface
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'file',
    ];

    /**
     * Prepare the form element (mostly used for rendering purposes)
     *
     * @param  FormInterface $form
     * @return mixed
     */
    public function prepareElement(FormInterface $form)
    {
        // Ensure the form is using correct enctype
        $form->setAttribute('enctype', 'multipart/form-data');
    }

    /**
     * Should return an array specification compatible with
     * {@link Laminas\InputFilter\Factory::createInput()}.
     *
     * @return array
     */
    public function getInputSpecification()
    {
        return [
            'type'     => FileInput::class,
            'name'     => $this->getName(),
            'required' => false,
        ];
    }
}
