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
use Symfony\Component\DomCrawler\Image as BaseImage;
use Symfony\Component\Panther\ExceptionThrower;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class Image extends BaseImage
{
    use ExceptionThrower;

    private $element;

    public function __construct(WebDriverElement $element)
    {
        if ('img' !== $tagName = $element->getTagName()) {
            throw new \LogicException(\sprintf('Unable to visualize a "%s" tag.', $tagName));
        }

        $this->element = $element;
        $this->method = 'GET';
    }

    public function getNode(): \DOMElement
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    protected function setNode(\DOMElement $node): void
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    protected function getRawUri(): string
    {
        return (string) $this->element->getAttribute('src');
    }
}
