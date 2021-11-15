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

namespace Symfony\Component\Panther;

use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\JavaScriptExecutor;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCapabilities;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverHasInputDevices;
use Facebook\WebDriver\WebDriverKeyboard;
use Facebook\WebDriver\WebDriverNavigationInterface;
use Facebook\WebDriver\WebDriverOptions;
use Facebook\WebDriver\WebDriverTargetLocator;
use Facebook\WebDriver\WebDriverWait;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\BrowserKit\History;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Link;
use Symfony\Component\Panther\Cookie\CookieJar;
use Symfony\Component\Panther\DomCrawler\Crawler as PantherCrawler;
use Symfony\Component\Panther\DomCrawler\Form as PantherForm;
use Symfony\Component\Panther\DomCrawler\Link as PantherLink;
use Symfony\Component\Panther\ProcessManager\BrowserManagerInterface;
use Symfony\Component\Panther\ProcessManager\ChromeManager;
use Symfony\Component\Panther\ProcessManager\FirefoxManager;
use Symfony\Component\Panther\ProcessManager\SeleniumManager;
use Symfony\Component\Panther\WebDriver\PantherWebDriverExpectedCondition;
use Symfony\Component\Panther\WebDriver\WebDriverMouse;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 * @author Dany Maillard <danymaillard93b@gmail.com>
 *
 * @method PantherCrawler getCrawler()
 */
final class Client extends AbstractBrowser implements WebDriver, JavaScriptExecutor, WebDriverHasInputDevices
{
    use ExceptionThrower;

    /**
     * @var WebDriver|null
     */
    private $webDriver;
    private $browserManager;
    private $baseUri;
    private $isFirefox = false;

    /**
     * @param string[]|null $arguments
     */
    public static function createChromeClient(?string $chromeDriverBinary = null, ?array $arguments = null, array $options = [], ?string $baseUri = null): self
    {
        return new self(new ChromeManager($chromeDriverBinary, $arguments, $options), $baseUri);
    }

    /**
     * @param string[]|null $arguments
     */
    public static function createFirefoxClient(?string $geckodriverBinary = null, ?array $arguments = null, array $options = [], ?string $baseUri = null): self
    {
        return new self(new FirefoxManager($geckodriverBinary, $arguments, $options), $baseUri);
    }

    public static function createSeleniumClient(?string $host = null, ?WebDriverCapabilities $capabilities = null, ?string $baseUri = null, array $options = []): self
    {
        return new self(new SeleniumManager($host, $capabilities, $options), $baseUri);
    }

    public function __construct(BrowserManagerInterface $browserManager, ?string $baseUri = null)
    {
        $this->browserManager = $browserManager;
        $this->baseUri = $baseUri;
    }

    public function getBrowserManager(): BrowserManagerInterface
    {
        return $this->browserManager;
    }

    public function __sleep(): array
    {
        throw new \BadMethodCallException('Cannot serialize '.__CLASS__);
    }

    public function __wakeup(): void
    {
        throw new \BadMethodCallException('Cannot unserialize '.__CLASS__);
    }

    public function __destruct()
    {
        $this->quit();
    }

    public function start(): void
    {
        if (null !== $this->webDriver) {
            return;
        }

        $this->webDriver = $this->browserManager->start();
        if ($this->browserManager instanceof FirefoxManager) {
            $this->isFirefox = true;

            return;
        }

        if ($this->browserManager instanceof ChromeManager) {
            $this->isFirefox = false;

            return;
        }

        if (method_exists($this->webDriver, 'getCapabilities')) {
            $this->isFirefox = 'firefox' === $this->webDriver->getCapabilities()->getBrowserName();

            return;
        }

        $this->isFirefox = false;
    }

    public function getRequest()
    {
        throw new \LogicException('HttpFoundation Request object is not available when using WebDriver.');
    }

    public function getResponse()
    {
        throw new \LogicException('HttpFoundation Response object is not available when using WebDriver.');
    }

    public function followRedirects($followRedirects = true): void
    {
        if (!$followRedirects) {
            throw new \InvalidArgumentException('Redirects are always followed when using WebDriver.');
        }
    }

    public function isFollowingRedirects(): bool
    {
        return true;
    }

    public function setMaxRedirects($maxRedirects): void
    {
        if (-1 !== $maxRedirects) {
            throw new \InvalidArgumentException('There are no max redirects when using WebDriver.');
        }
    }

    public function getMaxRedirects(): int
    {
        return -1;
    }

    public function insulate($insulated = true): void
    {
        if (!$insulated) {
            throw new \InvalidArgumentException('Requests are always insulated when using WebDriver.');
        }
    }

    public function setServerParameters(array $server): void
    {
        throw new \InvalidArgumentException('Server parameters cannot be set when using WebDriver.');
    }

    public function setServerParameter($key, $value): void
    {
        throw new \InvalidArgumentException('Server parameters cannot be set when using WebDriver.');
    }

    public function getServerParameter($key, $default = '')
    {
        throw new \InvalidArgumentException('Server parameters cannot be retrieved when using WebDriver.');
    }

    public function getHistory(): History
    {
        throw new \LogicException('History is not available when using WebDriver.');
    }

    public function click(Link $link): Crawler
    {
        if ($link instanceof PantherLink) {
            $link->getElement()->click();

            return $this->crawler = $this->createCrawler();
        }

        return parent::click($link);
    }

    public function submit(Form $form, array $values = [], array $serverParameters = []): Crawler
    {
        if ($form instanceof PantherForm) {
            foreach ($values as $field => $value) {
                $form->get($field)->setValue($value);
            }

            $button = $form->getButton();

            if ($this->isFirefox) {
                // For Firefox, we have to wait for the page to reload
                // https://github.com/SeleniumHQ/selenium/issues/4570#issuecomment-327473270
                $selector = WebDriverBy::cssSelector('html');
                $previousId = $this->webDriver->findElement($selector)->getID();

                null === $button ? $form->getElement()->submit() : $button->click();

                try {
                    $this->webDriver->wait(5)->until(static function (WebDriver $driver) use ($previousId, $selector) {
                        try {
                            return $previousId !== $driver->findElement($selector)->getID();
                        } catch (NoSuchElementException $e) {
                            // The html element isn't already available
                            return false;
                        }
                    });
                } catch (TimeoutException $e) {
                    // Probably a form using AJAX, do nothing
                }
            } else {
                null === $button ? $form->getElement()->submit() : $button->click();
            }

            return $this->crawler = $this->createCrawler();
        }

        return parent::submit($form, $values, $serverParameters);
    }

    public function refreshCrawler(): PantherCrawler
    {
        return $this->crawler = $this->createCrawler();
    }

    public function request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], string $content = null, bool $changeHistory = true): PantherCrawler
    {
        if ('GET' !== $method) {
            throw new \InvalidArgumentException('Only the GET method is supported when using WebDriver.');
        }
        if (null !== $content) {
            throw new \InvalidArgumentException('Setting a content is not supported when using WebDriver.');
        }
        if (!$changeHistory) {
            throw new \InvalidArgumentException('The history always change when using WebDriver.');
        }

        foreach (['parameters', 'files', 'server'] as $arg) {
            if ([] !== $$arg) {
                throw new \InvalidArgumentException(\sprintf('The parameter "$%s" is not supported when using WebDriver.', $arg));
            }
        }

        $this->get($uri);

        return $this->crawler;
    }

    protected function createCrawler(): PantherCrawler
    {
        $this->start();
        $elements = $this->webDriver->findElements(WebDriverBy::cssSelector('html'));

        return new PantherCrawler($elements, $this->webDriver, $this->webDriver->getCurrentURL());
    }

    protected function doRequest($request)
    {
        throw new \LogicException('Not useful in WebDriver mode.');
    }

    public function back(): PantherCrawler
    {
        $this->start();
        $this->webDriver->navigate()->back();

        return $this->crawler = $this->createCrawler();
    }

    public function forward(): PantherCrawler
    {
        $this->start();
        $this->webDriver->navigate()->forward();

        return $this->crawler = $this->createCrawler();
    }

    public function reload(): PantherCrawler
    {
        $this->start();
        $this->webDriver->navigate()->refresh();

        return $this->crawler = $this->createCrawler();
    }

    public function followRedirect(): PantherCrawler
    {
        throw new \LogicException('Redirects are always followed when using WebDriver.');
    }

    public function restart(): void
    {
        if (null !== $this->webDriver) {
            $this->webDriver->manage()->deleteAllCookies();
        }

        $this->quit(false);
        $this->start();
    }

    public function getCookieJar(): CookieJar
    {
        $this->start();

        return new CookieJar($this->webDriver);
    }

    /**
     * @param string $locator The path to an element to be waited for. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitFor(string $locator, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            WebDriverExpectedCondition::presenceOfElementLocated($by)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to an element that will be removed from the DOM.
     *                        Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForStaleness(string $locator, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);
        $element = $this->findElement($by);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            WebDriverExpectedCondition::stalenessOf($element)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to an element to be waited for. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForVisibility(string $locator, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated($by)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to an element waited to be invisible. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForInvisibility(string $locator, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            WebDriverExpectedCondition::invisibilityOfElementLocated($by)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to the element that will contain the given text. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForElementToContain(string $locator, string $text, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            WebDriverExpectedCondition::elementTextContains($by, $text)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to the element that will not contain the given text. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForElementToNotContain(string $locator, string $text, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            PantherWebDriverExpectedCondition::elementTextNotContains($by, $text)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to the element that will have an attribute containing the given the text. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForAttributeToContain(string $locator, string $attribute, string $text, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            PantherWebDriverExpectedCondition::elementAttributeContains($by, $attribute, $text)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to the element that will have an attribute not containing the given the text. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForAttributeToNotContain(string $locator, string $attribute, string $text, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            PantherWebDriverExpectedCondition::elementAttributeNotContains($by, $attribute, $text)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to the element that will be enabled. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForEnabled(string $locator, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            PantherWebDriverExpectedCondition::elementEnabled($by)
        );

        return $this->crawler = $this->createCrawler();
    }

    /**
     * @param string $locator The path to the element that will be disabled. Can be a CSS selector or Xpath expression.
     *
     * @throws NoSuchElementException
     * @throws TimeoutException
     */
    public function waitForDisabled(string $locator, int $timeoutInSecond = 30, int $intervalInMillisecond = 250): PantherCrawler
    {
        $by = self::createWebDriverByFromLocator($locator);

        $this->wait($timeoutInSecond, $intervalInMillisecond)->until(
            PantherWebDriverExpectedCondition::elementDisabled($by)
        );

        return $this->crawler = $this->createCrawler();
    }

    public function getWebDriver(): WebDriver
    {
        $this->start();

        return $this->webDriver;
    }

    /**
     * @param string $url
     */
    public function get($url): self
    {
        $this->start();

        // Prepend the base URI to URIs without a host
        if (null !== $this->baseUri && (false !== $components = \parse_url($url)) && !isset($components['host'])) {
            $url = $this->baseUri.$url;
        }

        $this->internalRequest = new Request($url, 'GET');
        $this->webDriver->get($url);
        $this->internalResponse = new Response($this->webDriver->getPageSource());

        $this->crawler = $this->createCrawler();

        return $this;
    }

    public function close(): WebDriver
    {
        $this->start();

        return $this->webDriver->close();
    }

    public function getCurrentURL(): string
    {
        $this->start();

        return $this->webDriver->getCurrentURL();
    }

    public function getPageSource(): string
    {
        $this->start();

        return $this->webDriver->getPageSource();
    }

    public function getTitle(): string
    {
        $this->start();

        return $this->webDriver->getTitle();
    }

    public function getWindowHandle(): string
    {
        $this->start();

        return $this->webDriver->getWindowHandle();
    }

    public function getWindowHandles(): array
    {
        $this->start();

        return $this->webDriver->getWindowHandles();
    }

    public function quit(bool $quitBrowserManager = true): void
    {
        if (null !== $this->webDriver) {
            $this->webDriver->quit();
            $this->webDriver = null;
        }

        if ($quitBrowserManager) {
            $this->browserManager->quit();
        }
    }

    public function takeScreenshot($saveAs = null): string
    {
        $this->start();

        return $this->webDriver->takeScreenshot($saveAs);
    }

    /**
     * @param int $timeoutInSecond
     * @param int $intervalInMillisecond
     */
    public function wait($timeoutInSecond = 30, $intervalInMillisecond = 250): WebDriverWait
    {
        $this->start();

        return $this->webDriver->wait($timeoutInSecond, $intervalInMillisecond);
    }

    public function manage(): WebDriverOptions
    {
        $this->start();

        return $this->webDriver->manage();
    }

    public function navigate(): WebDriverNavigationInterface
    {
        $this->start();

        return $this->webDriver->navigate();
    }

    public function switchTo(): WebDriverTargetLocator
    {
        $this->start();

        return $this->webDriver->switchTo();
    }

    /**
     * @param string $name
     * @param array  $params
     *
     * @return mixed
     */
    public function execute($name, $params)
    {
        $this->start();

        return $this->webDriver->execute($name, $params);
    }

    public function findElement(WebDriverBy $locator): WebDriverElement
    {
        $this->start();

        return $this->webDriver->findElement($locator);
    }

    /**
     * @return WebDriverElement[]
     */
    public function findElements(WebDriverBy $locator): array
    {
        $this->start();

        return $this->webDriver->findElements($locator);
    }

    /**
     * @param string $script
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function executeScript($script, array $arguments = [])
    {
        if (!$this->webDriver instanceof JavaScriptExecutor) {
            throw $this->createException(JavaScriptExecutor::class);
        }

        return $this->webDriver->executeScript($script, $arguments);
    }

    /**
     * @param string $script
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function executeAsyncScript($script, array $arguments = [])
    {
        if (!$this->webDriver instanceof JavaScriptExecutor) {
            throw $this->createException(JavaScriptExecutor::class);
        }

        return $this->webDriver->executeAsyncScript($script, $arguments);
    }

    /**
     * @throws \Exception
     */
    public function getKeyboard(): WebDriverKeyboard
    {
        if (!$this->webDriver instanceof WebDriverHasInputDevices) {
            throw $this->createException(WebDriverHasInputDevices::class);
        }

        return $this->webDriver->getKeyboard();
    }

    /**
     * @throws \Exception
     */
    public function getMouse(): WebDriverMouse
    {
        if (!$this->webDriver instanceof WebDriverHasInputDevices) {
            throw $this->createException(WebDriverHasInputDevices::class);
        }

        return new WebDriverMouse($this->webDriver->getMouse(), $this);
    }

    /**
     * @internal
     */
    public static function createWebDriverByFromLocator(string $locator): WebDriverBy
    {
        $locator = trim($locator);

        return '' === $locator || '/' !== $locator[0]
            ? WebDriverBy::cssSelector($locator)
            : WebDriverBy::xpath($locator);
    }

    /**
     * Checks the web driver connection (and logs "pong" into the DevTools console).
     *
     * @param int $timeout sets the connection/request timeout in ms
     *
     * @return bool true if connected, false otherwise
     */
    public function ping(int $timeout = 1000): bool
    {
        if (null === $this->webDriver) {
            return false;
        }

        if ($this->webDriver instanceof RemoteWebDriver) {
            $this
                ->webDriver
                ->getCommandExecutor()
                ->setConnectionTimeout($timeout)
                ->setRequestTimeout($timeout);
        }

        try {
            if ($this->webDriver instanceof JavaScriptExecutor) {
                $this->webDriver->executeScript('console.log("pong");');
            } else {
                $this->webDriver->findElement(WebDriverBy::tagName('html'));
            }
        } catch (\Exception $e) {
            return false;
        } finally {
            if ($this->webDriver instanceof RemoteWebDriver) {
                $this
                    ->webDriver
                    ->getCommandExecutor()
                    ->setConnectionTimeout(0)
                    ->setRequestTimeout(0);
            }
        }

        return true;
    }

    /**
     * @return \LogicException|\RuntimeException
     */
    private function createException(string $implementableClass): \Exception
    {
        if (null === $this->webDriver) {
            return new \LogicException(sprintf('WebDriver not started yet. Call method `start()` first before calling any `%s` method.', $implementableClass));
        }

        return new \RuntimeException(sprintf('"%s" does not implement "%s".', \get_class($this->webDriver), $implementableClass));
    }
}
