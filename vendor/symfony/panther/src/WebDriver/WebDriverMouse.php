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

use Facebook\WebDriver\Interactions\Internal\WebDriverCoordinates;
use Facebook\WebDriver\Internal\WebDriverLocatable;
use Facebook\WebDriver\WebDriverMouse as BaseWebDriverMouse;
use Symfony\Component\Panther\Client;

/**
 * @author Dany Maillard <danymaillard93b@gmail.com>
 */
final class WebDriverMouse implements BaseWebDriverMouse
{
    private $mouse;
    private $client;

    public function __construct(BaseWebDriverMouse $mouse, Client $client)
    {
        $this->mouse = $mouse;
        $this->client = $client;
    }

    public function click(WebDriverCoordinates $where): self
    {
        $this->mouse->click($where);

        return $this;
    }

    public function clickTo($cssSelector): self
    {
        return $this->click($this->toCoordinates($cssSelector));
    }

    public function contextClick(WebDriverCoordinates $where): self
    {
        $this->mouse->contextClick($where);

        return $this;
    }

    public function contextClickTo($cssSelector): self
    {
        return $this->contextClick($this->toCoordinates($cssSelector));
    }

    public function doubleClick(WebDriverCoordinates $where): self
    {
        $this->mouse->doubleClick($where);

        return $this;
    }

    public function doubleClickTo($cssSelector): self
    {
        return $this->doubleClick($this->toCoordinates($cssSelector));
    }

    public function mouseDown(WebDriverCoordinates $where): self
    {
        $this->mouse->mouseDown($where);

        return $this;
    }

    public function mouseDownTo($cssSelector): self
    {
        return $this->mouseDown($this->toCoordinates($cssSelector));
    }

    public function mouseMove(WebDriverCoordinates $where, $xOffset = null, $yOffset = null): self
    {
        $this->mouse->mouseMove($where, $xOffset, $yOffset);

        return $this;
    }

    public function mouseMoveTo($cssSelector, $xOffset = null, $yOffset = null): self
    {
        return $this->mouseMove($this->toCoordinates($cssSelector), $xOffset, $yOffset);
    }

    public function mouseUp(WebDriverCoordinates $where): self
    {
        $this->mouse->mouseUp($where);

        return $this;
    }

    public function mouseUpTo($cssSelector): self
    {
        return $this->mouseUp($this->toCoordinates($cssSelector));
    }

    private function toCoordinates($cssSelector): WebDriverCoordinates
    {
        $element = $this->client->getCrawler()->filter($cssSelector)->getElement(0);

        if (!$element instanceof WebDriverLocatable) {
            throw new \RuntimeException(sprintf('The element of "%s" CSS selector does not implement "%s".', $cssSelector, WebDriverLocatable::class));
        }

        return $element->getCoordinates();
    }
}
