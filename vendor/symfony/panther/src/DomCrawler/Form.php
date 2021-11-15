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

namespace Symfony\Component\Panther\DomCrawler;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Support\XPathEscaper;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Facebook\WebDriver\WebDriverSelectInterface;
use Symfony\Component\DomCrawler\Field\FormField;
use Symfony\Component\DomCrawler\Form as BaseForm;
use Symfony\Component\Panther\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\Panther\DomCrawler\Field\FileFormField;
use Symfony\Component\Panther\DomCrawler\Field\InputFormField;
use Symfony\Component\Panther\DomCrawler\Field\TextareaFormField;
use Symfony\Component\Panther\ExceptionThrower;
use Symfony\Component\Panther\WebDriver\WebDriverCheckbox;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class Form extends BaseForm
{
    use ExceptionThrower;

    /**
     * @var WebDriverElement
     */
    private $button;

    /**
     * @var WebDriverElement
     */
    private $element;
    private $webDriver;

    public function __construct(WebDriverElement $element, WebDriver $webDriver)
    {
        $this->webDriver = $webDriver;
        $this->setElement($element);

        $this->currentUri = $webDriver->getCurrentURL();
    }

    private function setElement(WebDriverElement $element): void
    {
        $this->button = $element;
        $tagName = $element->getTagName();
        if ('button' === $tagName || ('input' === $tagName && \in_array(\strtolower($element->getAttribute('type')), ['submit', 'button', 'image'], true))) {
            if (null !== $formId = $element->getAttribute('form')) {
                // if the node has the HTML5-compliant 'form' attribute, use it
                try {
                    $form = $this->webDriver->findElement(WebDriverBy::id($formId));
                } catch (NoSuchElementException $e) {
                    throw new \LogicException(\sprintf('The selected node has an invalid form attribute (%s).', $formId));
                }

                $this->element = $form;

                return;
            }
            // we loop until we find a form ancestor
            do {
                try {
                    $element = $element->findElement(WebDriverBy::xpath('..'));
                } catch (NoSuchElementException $e) {
                    throw new \LogicException('The selected node does not have a form ancestor.');
                }
            } while ('form' !== $element->getTagName());
        } elseif ('form' !== $tagName = $element->getTagName()) {
            throw new \LogicException(\sprintf('Unable to submit on a "%s" tag.', $tagName));
        }

        $this->element = $element;
    }

    public function getButton(): ?WebDriverElement
    {
        return $this->element === $this->button ? null : $this->button;
    }

    public function getElement(): WebDriverElement
    {
        return $this->element;
    }

    public function getFormNode(): \DOMElement
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    public function setValues(array $values): self
    {
        foreach ($values as $name => $value) {
            $this->setValue($name, $value);
        }

        return $this;
    }

    /**
     * Gets the field values.
     *
     * The returned array does not include file fields (@see getFiles).
     *
     * @return array An array of field values
     */
    public function getValues(): array
    {
        $values = [];
        foreach ($this->element->findElements(WebDriverBy::xpath('.//input[@name] | .//textarea[@name] | .//select[@name] | .//button[@name]')) as $element) {
            $name = $element->getAttribute('name');
            $type = $element->getAttribute('type');

            if ('file' === $type) {
                continue;
            }

            $value = $this->getValue($element);

            $isArrayElement = \is_array($value) && '[]' === \substr($name, -2);
            if ($isArrayElement) {
                // compatibility with the DomCrawler API
                $name = \substr($name, 0, -2);
            }

            if ('checkbox' === $type) {
                if (!$value) {
                    // Remove non-checked checkboxes
                    continue;
                }

                // Flatten non array-checkboxes
                if (\is_array($value) && !$isArrayElement && 1 === \count($value)) {
                    $value = $value[0];
                }
            }

            $values[$name] = $value;
        }

        return $values;
    }

    public function getFiles(): array
    {
        if (!\in_array($this->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            return [];
        }

        $files = [];

        foreach ($this->all() as $field) {
            if ($field->isDisabled()) {
                continue;
            }

            if ($field instanceof Field\FileFormField) {
                $files[$field->getName()] = $field->getValue();
            }
        }

        return $files;
    }

    public function getMethod(): string
    {
        if (null !== $this->method) {
            return $this->method;
        }

        // If the form was created from a button rather than the form node, check for HTML5 method override
        if ($this->button !== $this->element && null !== $this->button->getAttribute('formmethod')) {
            return \strtoupper($this->button->getAttribute('formmethod'));
        }

        return $this->element->getAttribute('method') ? \strtoupper($this->element->getAttribute('method')) : 'GET';
    }

    public function has($name): bool
    {
        try {
            $this->getFormElement($name);
        } catch (NoSuchElementException $e) {
            return false;
        }

        return true;
    }

    public function remove($name): void
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    public function set(FormField $field): void
    {
        $this->setValue($field->getName(), $field->getValue());
    }

    public function get($name)
    {
        return $this->getFormField($this->getFormElement($name));
    }

    public function all(): array
    {
        $fields = [];
        foreach ($this->getAllElements() as $element) {
            $fields[] = $this->getFormField($element);
        }

        return $fields;
    }

    public function offsetExists($name): bool
    {
        return $this->has($name);
    }

    public function offsetGet($name)
    {
        return $this->get($name);
    }

    public function offsetSet($name, $value): void
    {
        $this->setValue($name, $value);
    }

    public function offsetUnset($name): void
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    protected function getRawUri(): string
    {
        // If the form was created from a button rather than the form node, check for HTML5 action overrides
        if ($this->element !== $this->button && null !== $this->button->getAttribute('formaction')) {
            return $this->button->getAttribute('formaction');
        }

        return (string) $this->element->getAttribute('action');
    }

    /**
     * @throws NoSuchElementException
     */
    private function getFormElement(string $name): WebDriverElement
    {
        return $this->element->findElement(WebDriverBy::xpath(
            \sprintf('.//input[@name=%1$s] | .//textarea[@name=%1$s] | .//select[@name=%1$s] | .//button[@name=%1$s] | .//input[@name=%2$s] | .//textarea[@name=%2$s] | .//select[@name=%2$s] | .//button[@name=%2$s]', XPathEscaper::escapeQuotes($name), XPathEscaper::escapeQuotes($name.'[]'))
        ));
    }

    private function getFormField(WebDriverElement $element): FormField
    {
        $tagName = $element->getTagName();

        if ('textarea' === $tagName) {
            return new TextareaFormField($element);
        }

        $type = $element->getAttribute('type');
        if ('select' === $tagName || ('input' === $tagName && ('radio' === $type || 'checkbox' === $type))) {
            return new ChoiceFormField($element);
        }

        if ('input' === $tagName && 'file' === $type) {
            return new FileFormField($element);
        }

        return new InputFormField($element);
    }

    /**
     * @return WebDriverElement[]
     */
    private function getAllElements(): array
    {
        return $this->element->findElements(WebDriverBy::xpath('.//input[@name] | .//textarea[@name] | .//select[@name] | .//button[@name]'));
    }

    private function getWebDriverSelect(WebDriverElement $element): ?WebDriverSelectInterface
    {
        $type = $element->getAttribute('type');

        $tagName = $element->getTagName();
        $select = 'select' === $tagName;

        if (!$select && ('input' !== $tagName || ('radio' !== $type && 'checkbox' !== $type))) {
            return null;
        }

        return $select ? new WebDriverSelect($element) : new WebDriverCheckbox($element);
    }

    /**
     * @return string|array
     */
    private function getValue(WebDriverElement $element)
    {
        if (null === $webDriverSelect = $this->getWebDriverSelect($element)) {
            if (!$this->webDriver instanceof JavaScriptExecutor) {
                throw new \RuntimeException('To retrieve this value, the browser must support JavaScript.');
            }

            return $this->webDriver->executeScript('return arguments[0].value', [$element]);
        }

        if (!$webDriverSelect->isMultiple()) {
            $selectedOption = $webDriverSelect->getFirstSelectedOption();

            return $selectedOption->getAttribute('value') ?? $selectedOption->getText();
        }

        $values = [];
        foreach ($webDriverSelect->getAllSelectedOptions() as $selectedOption) {
            $values[] = $selectedOption->getAttribute('value') ?? $selectedOption->getText();
        }

        return $values;
    }

    /**
     * @param mixed $value
     */
    private function setValue(string $name, $value): void
    {
        try {
            $element = $this->element->findElement(WebDriverBy::name($name));
        } catch (NoSuchElementException $e) {
            if (!\is_array($value)) {
                throw $e;
            }

            // Compatibility with the DomCrawler API
            $element = $this->element->findElement(WebDriverBy::name($name.'[]'));
        }

        if (null === $webDriverSelect = $this->getWebDriverSelect($element)) {
            $element->clear();
            $element->sendKeys($value);

            return;
        }

        if (!\is_array($value)) {
            $webDriverSelect->selectByValue((string) $value);

            return;
        }

        foreach ($value as $v) {
            $webDriverSelect->selectByValue((string) $v);
        }
    }
}
