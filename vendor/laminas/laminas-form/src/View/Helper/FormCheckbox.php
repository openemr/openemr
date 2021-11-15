<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View\Helper;

use Laminas\Form\Element\Checkbox as CheckboxElement;
use Laminas\Form\ElementInterface;
use Laminas\Form\Exception;

use function sprintf;

class FormCheckbox extends FormInput
{
    /**
     * Render a form <input> element from the provided $element
     *
     * @param  ElementInterface $element
     * @throws Exception\InvalidArgumentException
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (! $element instanceof CheckboxElement) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Laminas\Form\Element\Checkbox',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if (empty($name) && $name !== 0) {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $attributes            = $element->getAttributes();
        $attributes['name']    = $name;
        $attributes['type']    = $this->getInputType();
        $attributes['value']   = $element->getCheckedValue();
        $closingBracket        = $this->getInlineClosingBracket();

        if ($element->isChecked()) {
            $attributes['checked'] = 'checked';
        }

        $rendered = sprintf(
            '<input %s%s',
            $this->createAttributesString($attributes),
            $closingBracket
        );

        if ($element->useHiddenElement()) {
            $hiddenAttributes = [
                'disabled' => isset($attributes['disabled']) ? $attributes['disabled'] : false,
                'name'     => $attributes['name'],
                'value'    => $element->getUncheckedValue(),
            ];

            $rendered = sprintf(
                '<input type="hidden" %s%s',
                $this->createAttributesString($hiddenAttributes),
                $closingBracket
            ) . $rendered;
        }

        return $rendered;
    }

    /**
     * Return input type
     *
     * @return string
     */
    protected function getInputType()
    {
        return 'checkbox';
    }
}
