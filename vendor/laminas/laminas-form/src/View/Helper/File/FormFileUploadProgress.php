<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper\File;

use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\FormInput;

use function sprintf;
use function uniqid;

/**
 * A view helper to render the hidden input with a UploadProgress id
 * for file uploads progress tracking.
 */
class FormFileUploadProgress extends FormInput
{
    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string
     */
    public function __invoke(ElementInterface $element = null)
    {
        return $this->renderHiddenId();
    }

    /**
     * Render a hidden form <input> element with the progress id
     *
     * @return string
     */
    public function renderHiddenId()
    {
        $attributes = [
            'id'    => 'progress_key',
            'name'  => $this->getName(),
            'type'  => 'hidden',
            'value' => $this->getValue(),
        ];

        return sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $this->getInlineClosingBracket()
        );
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return 'UPLOAD_IDENTIFIER';
    }

    /**
     * @return string
     */
    protected function getValue()
    {
        return uniqid();
    }
}
