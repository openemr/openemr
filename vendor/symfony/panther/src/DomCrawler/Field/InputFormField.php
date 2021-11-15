<?php

/*
 * This file is part of the Panther project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\Panther\DomCrawler\Field;

use Symfony\Component\DomCrawler\Field\InputFormField as BaseInputFormField;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class InputFormField extends BaseInputFormField
{
    use FormFieldTrait;

    public function setValue($value): void
    {
        if (\in_array($this->element->getAttribute('type'), ['text', 'email', 'number'], true)) {
            $this->setTextValue($value);

            return;
        }

        if (\is_bool($value)) {
            $this->element->click();

            return;
        }

        $this->element->sendKeys($value);
    }

    /**
     * Initializes the form field.
     *
     * @throws \LogicException When node type is incorrect
     */
    protected function initialize(): void
    {
        $tagName = $this->element->getTagName();
        if ('input' !== $tagName && 'button' !== $tagName) {
            throw new \LogicException(\sprintf('An InputFormField can only be created from an input or button tag (%s given).', $tagName));
        }

        $type = \strtolower((string) $this->element->getAttribute('type'));
        if ('checkbox' === $type) {
            throw new \LogicException('Checkboxes should be instances of ChoiceFormField.');
        }

        if ('file' === $type) {
            throw new \LogicException('File inputs should be instances of FileFormField.');
        }
    }
}
