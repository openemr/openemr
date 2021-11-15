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

use Symfony\Component\DomCrawler\Field\TextareaFormField as BaseTextareaFormField;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class TextareaFormField extends BaseTextareaFormField
{
    use FormFieldTrait;

    public function setValue($value): void
    {
        $this->setTextValue($value);
    }

    /**
     * Initializes the form field.
     *
     * @throws \LogicException When node type is incorrect
     */
    protected function initialize(): void
    {
        $tagName = $this->element->getTagName();
        if ('textarea' !== $tagName) {
            throw new \LogicException(\sprintf('A TextareaFormField can only be created from a textarea tag (%s given).', $tagName));
        }
    }
}
