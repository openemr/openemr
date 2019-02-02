<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View\Helper;

use Traversable;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;

class FormElementErrors extends AbstractHelper
{
    /**@+
     * @var string Templates for the open/close/separators for message tags
     */
    protected $messageCloseString     = '</li></ul>';
    protected $messageOpenFormat      = '<ul%s><li>';
    protected $messageSeparatorString = '</li><li>';
    /**@-*/

    /**
     * @var array Default attributes for the open format tag
     */
    protected $attributes = [];

    /**
     * @var bool Whether or not to translate error messages during render.
     */
    protected $translateErrorMessages = true;

    /**
     * Invoke helper as functor
     *
     * Proxies to {@link render()} if an element is passed.
     *
     * @param  ElementInterface $element
     * @param  array            $attributes
     * @return string|FormElementErrors
     */
    public function __invoke(ElementInterface $element = null, array $attributes = [])
    {
        if (! $element) {
            return $this;
        }

        return $this->render($element, $attributes);
    }

    /**
     * Render validation errors for the provided $element
     *
     * If {@link $translateErrorMessages} is true, and a translator is
     * composed, messages retrieved from the element will be translated; if
     * either is not the case, they will not.
     *
     * @param  ElementInterface $element
     * @param  array $attributes
     * @throws Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element, array $attributes = [])
    {
        $messages = $element->getMessages();
        if (empty($messages)) {
            return '';
        }

        $messages = $messages instanceof Traversable ? iterator_to_array($messages) : $messages;
        if (! is_array($messages)) {
            throw new Exception\DomainException(sprintf(
                '%s expects that $element->getMessages() will return an array or Traversable; received "%s"',
                __METHOD__,
                (is_object($messages) ? get_class($messages) : gettype($messages))
            ));
        }

        // Flatten message array
        $messages = $this->flattenMessages($messages);
        if (empty($messages)) {
            return '';
        }

        // Prepare attributes for opening tag
        $attributes = array_merge($this->attributes, $attributes);
        $attributes = $this->createAttributesString($attributes);
        if (! empty($attributes)) {
            $attributes = ' ' . $attributes;
        }

        // Generate markup
        $markup  = sprintf($this->getMessageOpenFormat(), $attributes);
        $markup .= implode($this->getMessageSeparatorString(), $messages);
        $markup .= $this->getMessageCloseString();

        return $markup;
    }

    /**
     * Set the attributes that will go on the message open format
     *
     * @param  array $attributes key value pairs of attributes
     * @return FormElementErrors
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Get the attributes that will go on the message open format
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the string used to close message representation
     *
     * @param  string $messageCloseString
     * @return FormElementErrors
     */
    public function setMessageCloseString($messageCloseString)
    {
        $this->messageCloseString = (string) $messageCloseString;
        return $this;
    }

    /**
     * Get the string used to close message representation
     *
     * @return string
     */
    public function getMessageCloseString()
    {
        return $this->messageCloseString;
    }

    /**
     * Set the formatted string used to open message representation
     *
     * @param  string $messageOpenFormat
     * @return FormElementErrors
     */
    public function setMessageOpenFormat($messageOpenFormat)
    {
        $this->messageOpenFormat = (string) $messageOpenFormat;
        return $this;
    }

    /**
     * Get the formatted string used to open message representation
     *
     * @return string
     */
    public function getMessageOpenFormat()
    {
        return $this->messageOpenFormat;
    }

    /**
     * Set the string used to separate messages
     *
     * @param  string $messageSeparatorString
     * @return FormElementErrors
     */
    public function setMessageSeparatorString($messageSeparatorString)
    {
        $this->messageSeparatorString = (string) $messageSeparatorString;
        return $this;
    }

    /**
     * Get the string used to separate messages
     *
     * @return string
     */
    public function getMessageSeparatorString()
    {
        return $this->messageSeparatorString;
    }

    /**
     * Set the flag detailing whether or not to translate error messages.
     *
     * @param bool $flag
     * @return self
     */
    public function setTranslateMessages($flag)
    {
        $this->translateErrorMessages = (bool) $flag;
        return $this;
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessages(array $messages)
    {
        return $this->translateErrorMessages && $this->getTranslator()
            ? $this->flattenMessagesWithTranslator($messages)
            : $this->flattenMessagesWithoutTranslator($messages);
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessagesWithoutTranslator(array $messages)
    {
        $messagesToPrint = [];
        array_walk_recursive($messages, function ($item) use (&$messagesToPrint) {
            $messagesToPrint[] = $item;
        });
        return $messagesToPrint;
    }

    /**
     * @param array $messages
     * @return array
     */
    private function flattenMessagesWithTranslator(array $messages)
    {
        $translator      = $this->getTranslator();
        $textDomain      = $this->getTranslatorTextDomain();
        $messagesToPrint = [];
        $messageCallback = function ($item) use (&$messagesToPrint, $translator, $textDomain) {
            $messagesToPrint[] = $translator->translate($item, $textDomain);
        };
        array_walk_recursive($messages, $messageCallback);
        return $messagesToPrint;
    }
}
