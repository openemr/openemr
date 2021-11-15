<?php

namespace Facebook\WebDriver\Firefox;

use Facebook\WebDriver\Local\LocalWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\DriverCommand;
use Facebook\WebDriver\Remote\Service\DriverCommandExecutor;
use Facebook\WebDriver\Remote\WebDriverCommand;

class FirefoxDriver extends LocalWebDriver
{
    const PROFILE = 'firefox_profile';

    /**
     * Creates a new FirefoxDriver using default configuration.
     * This includes starting a new geckodriver process  each time this method is called. However this may be
     * unnecessary overhead - instead, you can start the process once using FirefoxDriverService and pass
     * this instance to startUsingDriverService() method.
     *
     * @return static
     */
    public static function start(DesiredCapabilities $capabilities = null)
    {
        $service = FirefoxDriverService::createDefaultService();

        return static::startUsingDriverService($service, $capabilities);
    }

    /**
     * Creates a new FirefoxDriver using given FirefoxDriverService.
     * This is usable when you for example don't want to start new geckodriver process for each individual test
     * and want to reuse the already started geckodriver, which will lower the overhead associated with spinning up
     * a new process.
     *
     * @return static
     */
    public static function startUsingDriverService(
        FirefoxDriverService $service,
        DesiredCapabilities $capabilities = null
    ) {
        if ($capabilities === null) {
            $capabilities = DesiredCapabilities::firefox();
        }

        $executor = new DriverCommandExecutor($service);
        $newSessionCommand = new WebDriverCommand(
            null,
            DriverCommand::NEW_SESSION,
            [
                'capabilities' => [
                    'firstMatch' => [(object) $capabilities->toW3cCompatibleArray()],
                ],
            ]
        );

        $response = $executor->execute($newSessionCommand);

        $returnedCapabilities = DesiredCapabilities::createFromW3cCapabilities($response->getValue()['capabilities']);
        $sessionId = $response->getSessionID();

        return new static($executor, $sessionId, $returnedCapabilities, true);
    }
}
