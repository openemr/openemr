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

namespace Symfony\Component\Panther\ProcessManager;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
final class FirefoxManager implements BrowserManagerInterface
{
    use WebServerReadinessProbeTrait;

    private $process;
    private $arguments;
    private $options;

    /**
     * @throws \RuntimeException
     */
    public function __construct(?string $geckodriverBinary = null, ?array $arguments = null, array $options = [])
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
        $this->process = new Process([$geckodriverBinary ?: $this->findGeckodriverBinary(), '--port='.$this->options['port']], null, null, null, null);
        $this->arguments = $arguments ?? $this->getDefaultArguments();
    }

    /**
     * @throws \RuntimeException
     */
    public function start(): WebDriver
    {
        $url = $this->options['scheme'].'://'.$this->options['host'].':'.$this->options['port'];
        if (!$this->process->isRunning()) {
            $this->checkPortAvailable($this->options['host'], $this->options['port']);
            $this->process->start();
            $this->waitUntilReady($this->process, $url.$this->options['path'], 'firefox');
        }

        $firefoxOptions = [];
        if (isset($_SERVER['PANTHER_FIREFOX_BINARY'])) {
            $firefoxOptions['binary'] = $_SERVER['PANTHER_FIREFOX_BINARY'];
        }
        if ($this->arguments) {
            $firefoxOptions['args'] = $this->arguments;
        }

        $capabilities = DesiredCapabilities::firefox();
        $capabilities->setCapability('moz:firefoxOptions', $firefoxOptions);

        foreach ($this->options['capabilities'] as $capability => $value) {
            $capabilities->setCapability($capability, $value);
        }

        return RemoteWebDriver::create($url, $capabilities, $this->options['connection_timeout_in_ms'] ?? null, $this->options['request_timeout_in_ms'] ?? null);
    }

    public function quit(): void
    {
        $this->process->stop();
    }

    /**
     * @throws \RuntimeException
     */
    private function findGeckodriverBinary(): string
    {
        if ($binary = (new ExecutableFinder())->find('geckodriver', null, ['./drivers'])) {
            return $binary;
        }

        throw new \RuntimeException('"geckodriver" binary not found. Install it using the package manager of your operating system or by running "composer require --dev dbrekelmans/bdi && vendor/bin/bdi detect drivers".');
    }

    private function getDefaultArguments(): array
    {
        // Enable the headless mode unless PANTHER_NO_HEADLESS is defined
        $args = ($_SERVER['PANTHER_NO_HEADLESS'] ?? false) ? ['--devtools'] : ['--headless', '--window-size=1200,1100'];

        // Add custom arguments with PANTHER_FIREFOX_ARGUMENTS
        if ($_SERVER['PANTHER_FIREFOX_ARGUMENTS'] ?? false) {
            $arguments = explode(' ', $_SERVER['PANTHER_FIREFOX_ARGUMENTS']);
            $args = array_merge($args, $arguments);
        }

        return $args;
    }

    private function getDefaultOptions(): array
    {
        return [
            'scheme' => 'http',
            'host' => '127.0.0.1',
            'port' => 4444,
            'path' => '/status',
            'capabilities' => [],
        ];
    }
}
