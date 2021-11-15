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

use Facebook\WebDriver\WebDriverElement;
use Symfony\Component\DomCrawler\Link as BaseLink;
use Symfony\Component\Panther\ExceptionThrower;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class Link extends BaseLink
{
    use ExceptionThrower;

    private $element;

    public function __construct(WebDriverElement $element, string $currentUri)
    {
        $tagName = $element->getTagName();
        if ('a' !== $tagName && 'area' !== $tagName && 'link' !== $tagName) {
            throw new \LogicException(\sprintf('Unable to navigate from a "%s" tag.', $tagName));
        }

        $this->element = $element;
        $this->method = 'GET';
        $this->currentUri = $currentUri;
    }

    public function getElement(): WebDriverElement
    {
        return $this->element;
    }

    public function getNode()
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    protected function setNode(\DOMElement $node): void
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    protected function getRawUri(): string
    {
        return (string) $this->element->getAttribute('href');
    }
}
