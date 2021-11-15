<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\View\Helper;

use Laminas\View\Exception;

/**
 * Helper for setting and retrieving title element for HTML head.
 *
 * Duck-types against Laminas\I18n\Translator\TranslatorAwareInterface.
 */
class HeadTitle extends Placeholder\Container\AbstractStandalone
{
    use TranslatorAwareTrait;

    /**
     * Default title rendering order (i.e. order in which each title attached)
     *
     * @var string
     */
    protected $defaultAttachOrder = null;

    /**
     * Retrieve placeholder for title element and optionally set state
     *
     * @param  string $title
     * @param  string $setType
     * @return HeadTitle
     */
    public function __invoke($title = null, $setType = null)
    {
        if (null === $setType) {
            $setType = (null === $this->getDefaultAttachOrder())
                     ? Placeholder\Container\AbstractContainer::APPEND
                     : $this->getDefaultAttachOrder();
        }

        $title = (string) $title;
        if ($title !== '') {
            if ($setType == Placeholder\Container\AbstractContainer::SET) {
                $this->set($title);
            } elseif ($setType == Placeholder\Container\AbstractContainer::PREPEND) {
                $this->prepend($title);
            } else {
                $this->append($title);
            }
        }

        return $this;
    }

    /**
     * Render title (wrapped by title tag)
     *
     * @param  string|null $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $output = $this->renderTitle();

        return $indent . '<title>' . $output . '</title>';
    }

    /**
     * Render title string
     *
     * @return string
     */
    public function renderTitle()
    {
        $items = [];

        $itemCallback = $this->getTitleItemCallback();
        foreach ($this as $item) {
            $items[] = $itemCallback($item);
        }

        $separator = $this->getSeparator();
        $output = '';

        $prefix = $this->getPrefix();
        if ($prefix) {
            $output  .= $prefix;
        }

        $output .= implode($separator, $items);

        $postfix = $this->getPostfix();
        if ($postfix) {
            $output .= $postfix;
        }

        $output = ($this->autoEscape) ? $this->escape($output) : $output;

        return $output;
    }

    /**
     * Set a default order to add titles
     *
     * @param  string $setType
     * @throws Exception\DomainException
     * @return HeadTitle
     */
    public function setDefaultAttachOrder($setType)
    {
        if (! in_array($setType, [
            Placeholder\Container\AbstractContainer::APPEND,
            Placeholder\Container\AbstractContainer::SET,
            Placeholder\Container\AbstractContainer::PREPEND
        ])) {
            throw new Exception\DomainException(
                "You must use a valid attach order: 'PREPEND', 'APPEND' or 'SET'"
            );
        }
        $this->defaultAttachOrder = $setType;

        return $this;
    }

    /**
     * Get the default attach order, if any.
     *
     * @return mixed
     */
    public function getDefaultAttachOrder()
    {
        return $this->defaultAttachOrder;
    }


    /**
     * Create and return a callback for normalizing title items.
     *
     * If translation is not enabled, or no translator is present, returns a
     * callable that simply returns the provided item; otherwise, returns a
     * callable that returns a translation of the provided item.
     *
     * @return callable
     */
    private function getTitleItemCallback()
    {
        if (! $this->isTranslatorEnabled() || ! $this->hasTranslator()) {
            return function ($item) {
                return $item;
            };
        }

        $translator = $this->getTranslator();
        $textDomain = $this->getTranslatorTextDomain();
        return function ($item) use ($translator, $textDomain) {
            return $translator->translate($item, $textDomain);
        };
    }
}
