<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;

use function sprintf;

class FormTextarea extends AbstractHelper
{
    /**
     * Attributes valid for the input tag
     *
     * @var array
     */
    protected $validTagAttributes = [
        'autocomplete' => true,
        'autofocus'    => true,
        'cols'         => true,
        'dirname'      => true,
        'disabled'     => true,
        'form'         => true,
        'maxlength'    => true,
        'minlength'    => true,
        'name'         => true,
        'placeholder'  => true,
        'readonly'     => true,
        'required'     => true,
        'rows'         => true,
        'wrap'         => true,
    ];

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()}.
     *
     * @param  ElementInterface|null $element
     * @return string|FormTextarea
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (! $element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * Render a form <textarea> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        $name   = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes         = $element->getAttributes();
        $attributes['name'] = $name;
        $content            = (string) $element->getValue();
        $escapeHtml         = $this->getEscapeHtmlHelper();

        return sprintf(
            '<textarea %s>%s</textarea>',
            $this->createAttributesString($attributes),
            $escapeHtml($content)
        );
    }
}
