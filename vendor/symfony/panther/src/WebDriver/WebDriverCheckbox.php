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

namespace Symfony\Component\Panther\WebDriver;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Exception\UnsupportedOperationException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Support\XPathEscaper;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelectInterface;

/**
 * Provides helper methods for checkboxes and radio buttons.
 *
 * This class has been proposed to php-webdriver/php-webdriver and will be deleted from this project when it will me merged.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 *
 * @internal
 *
 * @see https://github.com/php-webdriver/php-webdriver/pull/545
 */
class WebDriverCheckbox implements WebDriverSelectInterface
{
    private $element;
    private $type;
    private $name;

    public function __construct(WebDriverElement $element)
    {
        if ('input' !== $tagName = $element->getTagName()) {
            throw new UnexpectedTagNameException('input', $tagName);
        }

        $type = $element->getAttribute('type');
        if ('checkbox' !== $type && 'radio' !== $type) {
            throw new WebDriverException('The input must be of type "checkbox" or "radio".');
        }

        if (null === $name = $element->getAttribute('name')) {
            throw new WebDriverException('The input have a "name" attribute.');
        }

        $this->element = $element;
        $this->type = $type;
        $this->name = $name;
    }

    public function isMultiple(): bool
    {
        return 'checkbox' === $this->type;
    }

    public function getOptions(): array
    {
        return $this->getRelatedElements();
    }

    public function getAllSelectedOptions(): array
    {
        $selectedOptions = [];
        foreach ($this->getRelatedElements() as $element) {
            if ($element->isSelected()) {
                $selectedOptions[] = $element;

                if (!$this->isMultiple()) {
                    return $selectedOptions;
                }
            }
        }

        return $selectedOptions;
    }

    public function getFirstSelectedOption(): WebDriverElement
    {
        foreach ($this->getRelatedElements() as $element) {
            if ($element->isSelected()) {
                return $element;
            }
        }

        throw new NoSuchElementException('No options are selected');
    }

    public function selectByIndex($index): void
    {
        $this->byIndex($index);
    }

    public function selectByValue($value): void
    {
        $this->byValue($value);
    }

    public function selectByVisibleText($text): void
    {
        $this->byVisibleText($text);
    }

    public function selectByVisiblePartialText($text): void
    {
        $this->byVisibleText($text, true);
    }

    public function deselectAll(): void
    {
        if (!$this->isMultiple()) {
            throw new UnsupportedOperationException('You may only deselect all options of checkboxes');
        }

        foreach ($this->getRelatedElements() as $option) {
            $this->deselectOption($option);
        }
    }

    public function deselectByIndex($index): void
    {
        if (!$this->isMultiple()) {
            throw new UnsupportedOperationException('You may only deselect checkboxes');
        }

        $this->byIndex($index, false);
    }

    public function deselectByValue($value): void
    {
        if (!$this->isMultiple()) {
            throw new UnsupportedOperationException('You may only deselect checkboxes');
        }

        $this->byValue($value, false);
    }

    public function deselectByVisibleText($text): void
    {
        if (!$this->isMultiple()) {
            throw new UnsupportedOperationException('You may only deselect checkboxes');
        }

        $this->byVisibleText($text, false, false);
    }

    public function deselectByVisiblePartialText($text): void
    {
        if (!$this->isMultiple()) {
            throw new UnsupportedOperationException('You may only deselect checkboxes');
        }

        $this->byVisibleText($text, true, false);
    }

    private function byValue($value, $select = true): void
    {
        $matched = false;
        foreach ($this->getRelatedElements($value) as $element) {
            $select ? $this->selectOption($element) : $this->deselectOption($element);
            if (!$this->isMultiple()) {
                return;
            }

            $matched = true;
        }

        if (!$matched) {
            throw new NoSuchElementException(\sprintf('Cannot locate option with value: %s', $value));
        }
    }

    private function byIndex($index, $select = true): void
    {
        $options = $this->getRelatedElements();
        if (!isset($options[$index])) {
            throw new NoSuchElementException(\sprintf('Cannot locate option with index: %d', $index));
        }

        $select ? $this->selectOption($options[$index]) : $this->deselectOption($options[$index]);
    }

    private function byVisibleText($text, $partial = false, $select = true): void
    {
        foreach ($this->getRelatedElements() as $element) {
            $normalizeFilter = \sprintf($partial ? 'contains(normalize-space(.), %s)' : 'normalize-space(.) = %s', XPathEscaper::escapeQuotes($text));

            $xpath = 'ancestor::label';
            $xpathNormalize = \sprintf('%s[%s]', $xpath, $normalizeFilter);
            if (null !== $id = $element->getAttribute('id')) {
                $idFilter = \sprintf('@for = %s', XPathEscaper::escapeQuotes($id));

                $xpath .= \sprintf(' | //label[%s]', $idFilter);
                $xpathNormalize .= \sprintf(' | //label[%s and %s]', $idFilter, $normalizeFilter);
            }

            try {
                $element->findElement(WebDriverBy::xpath($xpathNormalize));
            } catch (NoSuchElementException $e) {
                if ($partial) {
                    continue;
                }

                try {
                    // Since the mechanism of getting the text in xpath is not the same as
                    // webdriver, use the expensive getText() to check if nothing is matched.
                    if ($text !== $element->findElement(WebDriverBy::xpath($xpath))->getText()) {
                        continue;
                    }
                } catch (NoSuchElementException $e) {
                    continue;
                }
            }

            $select ? $this->selectOption($element) : $this->deselectOption($element);
            if (!$this->isMultiple()) {
                return;
            }
        }
    }

    private function getRelatedElements($value = null): array
    {
        $valueSelector = $value ? \sprintf(' and @value = %s', XPathEscaper::escapeQuotes($value)) : '';
        if (null === $formId = $this->element->getAttribute('form')) {
            $form = $this->element->findElement(WebDriverBy::xpath('ancestor::form'));
            if ('' === $formId = (string) $form->getAttribute('id')) {
                return $form->findElements(WebDriverBy::xpath(\sprintf('.//input[@name = %s%s]', XPathEscaper::escapeQuotes($this->name), $valueSelector)));
            }
        }

        return $this->element->findElements(WebDriverBy::xpath(
            \sprintf('//form[@id = %1$s]//input[@name = %2$s%3$s] | //input[@form = %1$s and @name = %2$s%3$s]', XPathEscaper::escapeQuotes($formId), XPathEscaper::escapeQuotes($this->name), $valueSelector)
        ));
    }

    private function selectOption(WebDriverElement $option): void
    {
        if (!$option->isSelected()) {
            $option->click();
        }
    }

    private function deselectOption(WebDriverElement $option): void
    {
        if ($option->isSelected()) {
            $option->click();
        }
    }
}
