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

namespace Symfony\Component\Panther\Cookie;

use Facebook\WebDriver\Cookie as WebDriverCookie;
use Facebook\WebDriver\Exception\NoSuchCookieException;
use Facebook\WebDriver\WebDriver;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar as BaseCookieJar;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\Panther\ExceptionThrower;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class CookieJar extends BaseCookieJar
{
    use ExceptionThrower;

    private $webDriver;

    public function __construct(WebDriver $webDriver)
    {
        $this->webDriver = $webDriver;
    }

    public function set(Cookie $cookie): void
    {
        $this->webDriver->manage()->addCookie($this->symfonyToWebDriver($cookie));
    }

    public function get($name, $path = '/', $domain = null): ?Cookie
    {
        if (null === $cookie = $this->getWebDriverCookie($name, $path, $domain)) {
            return null;
        }

        return $this->webDriverToSymfony($cookie);
    }

    public function expire($name, $path = '/', $domain = null): void
    {
        if (null !== $this->getWebDriverCookie($name, $path, $domain)) {
            $this->webDriver->manage()->deleteCookieNamed($name);
        }
    }

    public function clear(): void
    {
        $this->webDriver->manage()->deleteAllCookies();
    }

    public function updateFromSetCookie(array $setCookies, $uri = null): void
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    public function updateFromResponse(Response $response, $uri = null): void
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    public function all(): array
    {
        $cookies = [];
        foreach ($this->webDriver->manage()->getCookies() as $webDriverCookie) {
            $cookies[] = $this->webDriverToSymfony($webDriverCookie);
        }

        return $cookies;
    }

    public function allValues($uri, $returnsRawValue = false): array
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    public function allRawValues($uri): array
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    public function flushExpiredCookies(): void
    {
        throw $this->createNotSupportedException(__METHOD__);
    }

    private function symfonyToWebDriver(Cookie $cookie): WebDriverCookie
    {
        $webDriverCookie = new WebDriverCookie($cookie->getName(), $cookie->getValue());

        if ('' !== $domain = $cookie->getDomain()) {
            $webDriverCookie->setDomain($domain);
        }

        if (null !== $expiresTime = $cookie->getExpiresTime()) {
            $webDriverCookie->setExpiry((int) $expiresTime);
        }

        if ('/' !== $path = $cookie->getPath()) {
            $webDriverCookie->setPath($path);
        }

        if ($cookie->isHttpOnly()) {
            $webDriverCookie->setHttpOnly(true);
        }

        if ($cookie->isSecure()) {
            $webDriverCookie->setSecure(true);
        }

        return $webDriverCookie;
    }

    private function webDriverToSymfony(WebDriverCookie $cookie): Cookie
    {
        $expiry = $cookie->getExpiry();
        if (null !== $expiry) {
            $expiry = (string) $expiry;
        }

        return new Cookie($cookie->getName(), $cookie->getValue(), $expiry, $cookie->getPath(), (string) $cookie->getDomain(), (bool) $cookie->isSecure(), (bool) $cookie->isHttpOnly());
    }

    private function getWebDriverCookie(string $name, string $path = '/', ?string $domain = null): ?WebDriverCookie
    {
        try {
            $cookie = $this->webDriver->manage()->getCookieNamed($name);
        } catch (NoSuchCookieException $e) {
            return null;
        }

        if (null === $cookie) {
            return null;
        }

        $cookiePath = $cookie->getPath() ?? '/';
        if (0 !== strpos($path, $cookiePath)) {
            return null;
        }

        $cookieDomain = $cookie->getDomain();
        if (null === $domain || null === $cookieDomain) {
            return $cookie;
        }

        $cookieDomain = '.'.ltrim($cookieDomain, '.');
        if ($cookieDomain !== substr('.'.$domain, -\strlen($cookieDomain))) {
            return null;
        }

        return $cookie;
    }
}
