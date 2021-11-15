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

use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use PHPUnit\Runner\BeforeTestHook;

/**
 *  @author Dany Maillard <danymaillard93b@gmail.com>
 */
final class ServerExtension implements BeforeFirstTestHook, AfterLastTestHook, BeforeTestHook, AfterTestHook, AfterTestErrorHook, AfterTestFailureHook
{
    use ServerTrait;

    /** @var bool */
    private static $enabled = false;

    /** @var Client[] */
    private static $registeredClients = [];

    public static function registerClient(Client $client): void
    {
        if (self::$enabled) {
            self::$registeredClients[] = $client;
        }
    }

    public function executeBeforeFirstTest(): void
    {
        self::$enabled = true;
        $this->keepServerOnTeardown();
    }

    public function executeAfterLastTest(): void
    {
        $this->stopWebServer();
    }

    public function executeBeforeTest(string $test): void
    {
        self::reset();
    }

    public function executeAfterTest(string $test, float $time): void
    {
        self::reset();
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        $this->takeScreenshots('error', $test);
        $this->pause(sprintf('Error: %s', $message));
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        $this->takeScreenshots('failure', $test);
        $this->pause(sprintf('Failure: %s', $message));
    }

    private static function reset(): void
    {
        self::$registeredClients = [];
    }

    private function takeScreenshots(string $type, string $test): void
    {
        if (!($_SERVER['PANTHER_ERROR_SCREENSHOT_DIR'] ?? false)) {
            return;
        }

        foreach (self::$registeredClients as $i => $client) {
            $client->takeScreenshot(sprintf('%s/%s_%s_%s-%d.png',
                $_SERVER['PANTHER_ERROR_SCREENSHOT_DIR'],
                date('Y-m-d_H-i-s'),
                $type,
                strtr($test, ['\\' => '-', ':' => '_']),
                $i
            ));
        }
    }
}
