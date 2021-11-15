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

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Process\Process;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * @internal
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
trait WebServerReadinessProbeTrait
{
    /**
     * @throws \RuntimeException
     */
    private function checkPortAvailable(string $hostname, int $port, bool $throw = true): void
    {
        $currentState = error_reporting();
        error_reporting(0);
        $resource = fsockopen($hostname, $port);
        error_reporting($currentState);
        if (\is_resource($resource)) {
            fclose($resource);
            if ($throw) {
                throw new \RuntimeException(\sprintf('The port %d is already in use.', $port));
            }
        }
    }

    public function waitUntilReady(Process $process, string $url, string $service, bool $allowNotOkStatusCode = false, int $timeout = 30): void
    {
        $client = HttpClient::create(['timeout' => $timeout]);

        $start = microtime(true);

        while (true) {
            $status = $process->getStatus();
            if (Process::STATUS_TERMINATED === $status) {
                throw new \RuntimeException(sprintf('Could not start %s. Exit code: %d (%s). Error output: %s', $service, $process->getExitCode(), $process->getExitCodeText(), $process->getErrorOutput()));
            }

            if (Process::STATUS_STARTED !== $status) {
                if (microtime(true) - $start >= $timeout) {
                    throw new \RuntimeException("Could not start $service (or it crashed) after $timeout seconds.");
                }

                usleep(1000);

                continue;
            }

            $response = $client->request('GET', $url);
            $e = $statusCode = null;
            try {
                $statusCode = $response->getStatusCode();
                if ($allowNotOkStatusCode || 200 === $statusCode) {
                    return;
                }
            } catch (ExceptionInterface $e) {
            }

            if (microtime(true) - $start >= $timeout) {
                if ($e) {
                    $message = $e->getMessage();
                } else {
                    $message = "Status code: $statusCode";
                }
                throw new \RuntimeException("Could not connect to $service after $timeout seconds ($message).");
            }

            usleep(1000);
        }
    }
}
